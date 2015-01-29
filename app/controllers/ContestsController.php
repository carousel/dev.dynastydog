<?php

class ContestsController extends AuthorizedController {

    public function getIndex()
    {
        // Get the current user's kennel groups
        $kennelGroups = $this->currentUser->kennelGroups()->whereNotCemetery()
            ->whereHas('dogs', function($query)
                {
                    $query->whereComplete()->whereAlive()->whereUnworked();
                })
            ->with(array(
                'dogs' => function($query)
                {
                    $query->whereComplete()->whereAlive()->whereUnworked()->orderBy('id', 'asc');
                }))
            ->orderBy('id', 'asc')->get();

        $results     = null;
        $searchedDog = null;

        if (Input::get('search'))
        {
            $dogId = Input::get('dog');
            $dog = Dog::find($dogId);

            if ( ! is_null($dog) and $this->currentUser->ownsDog($dog) and $dog->isAlive() and $dog->isComplete())
            {
                $searchedDog = $dog;

                $contests = Contest::select('contests.*', DB::raw('IF(contest_entries.id IS NULL, 0, 1) as entered'))
                    ->leftJoin('contest_entries', function($join) use ($searchedDog)
                    {
                        $join->on('contest_entries.contest_id', '=', 'contests.id')
                            ->where('contest_entries.dog_id', '=', $searchedDog->id);
                    })
                    ->where('contests.run_on', '>=', Carbon::today())
                    ->where('contests.has_run', false)
                    ->orderBy('entered', 'asc')
                    ->orderBy('contests.run_on', 'asc')
                    ->orderBy('contests.id', 'asc')
                    ->take(50)
                    ->get();

                $results = [];

                foreach($contests as $contest)
                {
                    // Only show contests that fit
                    if ($contest->dogMeetsPrerequisites($searchedDog))
                    {
                        $results[] = $contest;
                    }
                }
            }
        }

        // Show the page
        return View::make('frontend.contests.index', compact('kennelGroups', 'results', 'searchedDog'));
    }

    public function getManage()
    {
        $contests = $this->currentUser->contests()->with(array('entries.dog', 'prerequisites.characteristic', 'requirements.characteristic'))->orderBy('run_on', 'desc')->paginate(10);
        $contestTypes = $this->currentUser->contestTypes()->with(array('prerequisites.characteristic', 'requirements.characteristic'))->orderBy('name', 'asc')->get();

        // Show the page
        return View::make('frontend.contests.manage', compact('contests', 'contestTypes'));
    }

    public function getType($contestType)
    {
        // Make sure this user owns this type
        if ($contestType->user_id != $this->currentUser->id)
        {
            App::abort('404', 'Contest type not found!');
        }

        $prerequisites = $contestType->prerequisites()->orderByCharacteristic()->get();
        $requirements  = $contestType->requirements()->orderByCharacteristic()->get();

        $attachedPrerequisiteCharacteristicIds = array_fetch($prerequisites->toArray(), 'characteristic_id');
        $attachedRequirementCharacteristicIds = array_fetch($requirements->toArray(), 'characteristic_id');

        $prerequisiteCategories = CharacteristicCategory::whereNotHealth()
            ->whereHas('characteristics', function($query) use ($attachedPrerequisiteCharacteristicIds)
            {
                empty($attachedPrerequisiteCharacteristicIds)
                    ? $query->whereActive()->whereVisible()
                    : $query->whereActive()->whereVisible()->whereNotIn('characteristics.id', $attachedPrerequisiteCharacteristicIds);
            }, '>=', 1)
            ->with(array(
                'parent', 
                'characteristics' => function($query) use ($attachedPrerequisiteCharacteristicIds)
                {
                    empty($attachedPrerequisiteCharacteristicIds)
                        ? $query->whereActive()->whereVisible()->orderBy('characteristics.name', 'asc')
                        : $query->whereActive()->whereVisible()->whereNotIn('characteristics.id', $attachedPrerequisiteCharacteristicIds)->orderBy('characteristics.name', 'asc');
                }
            ))
            ->select('characteristic_categories.*')
            ->join('characteristic_categories as parent', 'parent.id', '=', 'characteristic_categories.parent_category_id')
            ->orderBy('parent.name', 'asc')
            ->orderBy('characteristic_categories.name', 'asc')
            ->get();

        $requirementCategories = CharacteristicCategory::whereNotHealth()
            ->whereHas('characteristics', function($query) use ($attachedRequirementCharacteristicIds)
            {
                empty($attachedRequirementCharacteristicIds)
                    ? $query->whereActive()->whereVisible()->whereNotNull('characteristics.min_ranged_value')
                    : $query->whereActive()->whereVisible()->whereNotNull('characteristics.min_ranged_value')->whereNotIn('characteristics.id', $attachedRequirementCharacteristicIds);
            }, '>=', 1)
            ->with(array(
                'parent', 
                'characteristics' => function($query) use ($attachedRequirementCharacteristicIds)
                {
                    empty($attachedRequirementCharacteristicIds)
                        ? $query->whereActive()->whereVisible()->whereNotNull('characteristics.min_ranged_value')->orderBy('characteristics.name', 'asc')
                        : $query->whereActive()->whereVisible()->whereNotNull('characteristics.min_ranged_value')->whereNotIn('characteristics.id', $attachedRequirementCharacteristicIds)->orderBy('characteristics.name', 'asc');
                }
            ))
            ->select('characteristic_categories.*')
            ->join('characteristic_categories as parent', 'parent.id', '=', 'characteristic_categories.parent_category_id')
            ->orderBy('parent.name', 'asc')
            ->orderBy('characteristic_categories.name', 'asc')
            ->get();

        // Show the page
        return View::make('frontend.contests.type', compact('contestType', 'prerequisites', 'requirements', 'prerequisiteCategories', 'requirementCategories'));
    }

    public function postCreateContest()
    {
        // Declare the rules for the form validation
        $rules = array(
            'contest_name' => 'required|max:32',
            'contest_type' => 'required|exists:user_contest_types,id,user_id,'.$this->currentUser->id,
            'run_date'     => 'required|date|after:'.Contest::minRunDate()->subDay()->format('m/d/Y').'|before:'.Contest::maxRunDate()->addDay()->format('m/d/Y'),
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::back()->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            // Grab the type
            $type = UserContestType::find(Input::get('contest_type'));

            // Make sure the type has completed prerequisites
            if ( ! $type->hasCompletedPrerequisites())
            {
                throw new Dynasty\UserContestTypes\Exceptions\IncompletePrerequisitesException;
            }

            // Make sure the type has at least one requirement
            if ($type->requirements()->count() < 1)
            {
                throw new Dynasty\UserContestTypes\Exceptions\NotEnoughRequirementsException;
            }

            $contest = null;

            DB::transaction(function() use (&$contest, $type)
            {
                // Create the contest
                $contest = Contest::create(array(
                    'user_id'   => $this->currentUser->id, 
                    'name'      => Input::get('contest_name'), 
                    'run_on'    => Carbon::parse(Input::get('run_date'))->toDateString(), 
                    'type_name' => $type->name, 
                    'type_description' => $type->description, 
                    'has_run'   => false, 
                    'total_entries' => 0, 
                ));

                // Copy over the type's data
                foreach($type->prerequisites as $prerequisite)
                {
                    $copiedPrerequisite = ContestPrerequisite::create(array(
                        'contest_id'        => $contest->id, 
                        'characteristic_id' => $prerequisite->characteristic_id, 
                        'min_ranged_value'  => $prerequisite->min_ranged_value, 
                        'max_ranged_value'  => $prerequisite->max_ranged_value, 
                    ));

                    // Grab the genotype ids from the prerequisite
                    $genotypeIds = $prerequisite->genotypes()->lists('id');
                    
                    if ( ! empty($genotypeIds))
                    {
                        $copiedPrerequisite->genotypes()->attach($genotypeIds);
                    }

                    // Grab the phenotype ids from the prerequisite
                    $phenotypeIds = $prerequisite->phenotypes()->lists('id');

                    if ( ! empty($phenotypeIds))
                    {
                        $copiedPrerequisite->phenotypes()->attach($phenotypeIds);
                    }
                }

                foreach($type->requirements as $requirement)
                {
                    ContestRequirement::create(array(
                        'contest_id'        => $contest->id, 
                        'characteristic_id' => $requirement->characteristic_id, 
                        'type_id'           => $requirement->type_id, 
                    ));
                }
            });

            $success = Lang::get('forms/user.create_contest.success');

            return Redirect::route('contests/manage')->with('success', $success);
        }
        catch(Dynasty\UserContestTypes\Exceptions\IncompletePrerequisitesException $e)
        {
            $error = Lang::get('forms/user.create_contest.incomplete_prerequisites');
        }
        catch(Dynasty\UserContestTypes\Exceptions\NotEnoughRequirementsException $e)
        {
            $error = Lang::get('forms/user.create_contest.not_enough_requirements');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.create_contest.error');
        }

        return Redirect::route('contests/manage')->withInput()->with('error', $error);
    }

    public function postCreateContestType()
    {
        // Declare the rules for the form validation
        $rules = array(
            'contest_type_name' => 'required|max:32',
            'description'       => 'max:255',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::back()->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            // Create the type
            $type = UserContestType::create(array(
                'user_id'     => $this->currentUser->id, 
                'name'        => Input::get('contest_type_name'), 
                'description' => Input::get('description'), 
            ));

            $success = Lang::get('forms/user.create_contest_type.success');

            return Redirect::route('contests/type', $type->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.create_contest_type.error');
        }

        return Redirect::route('contests/manage')->withInput()->with('error', $error);
    }

    public function postUpdateContestType($contestType)
    {
        // Make sure this user owns this type
        if ($contestType->user_id != $this->currentUser->id)
        {
            Redirect::route('contests/manage');
        }

        // Declare the rules for the form validation
        $rules = array(
            'name'        => 'required|max:32',
            'description' => 'max:255',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::back()->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            // Update the type
            $contestType->name = Input::get('name');
            $contestType->description = Input::get('description');
            $contestType->save();

            $success = Lang::get('forms/user.update_contest_type.success');

            return Redirect::route('contests/type', $contestType->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.update_contest_type.error');
        }

        return Redirect::route('contests/type', $contestType->id)->withInput()->with('error', $error);
    }

    public function getDeleteContestType($contestType)
    {
        // Make sure this user owns this type
        if ($contestType->user_id != $this->currentUser->id)
        {
            Redirect::route('contests/manage');
        }

        try
        {
            // Delete it
            $contestType->delete();

            $success = Lang::get('forms/user.delete_contest_type.success');

            return Redirect::route('contests/manage')->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.delete_contest_type.error');
        }

        return Redirect::route('contests/manage')->with('error', $error);
    }

    public function postAddPrerequisites($contestType)
    {
        // Make sure this user owns this type
        if ($contestType->user_id != $this->currentUser->id)
        {
            Redirect::route('contests/manage');
        }

        // Grab all currently attached prerequisites
        $prerequisites = $contestType->prerequisites;

        // Make sure they are not over the max
        $maxPrerequisites = Config::get('game.contest.max_prerequisites');

        $max = max(0, $maxPrerequisites - count($prerequisites));

        // Declare the rules for the form validation
        $rules = array(
            'characteristics' => 'required|array|min:1|max:'.$max,
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::back()->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            DB::transaction(function() use ($contestType, $prerequisites)
            {
                $characteristicIds = Input::get('characteristics');

                // Grab the prerequisite characteristic ids
                $attachedPrerequisiteCharacteristicIds = array_fetch($prerequisites->toArray(), 'characteristic_id');

                foreach($characteristicIds as $characteristicId)
                {
                    // Make sure this is unique
                    if (in_array($characteristicId, $attachedPrerequisiteCharacteristicIds))
                    {
                        throw new Dynasty\UserContestTypePrerequisites\Exceptions\AlreadyAttachedException;
                    }

                    // Grab the characteristic
                    $characteristic = Characteristic::find($characteristicId);

                    if (is_null($characteristic) or ! $characteristic->isActive() or $characteristic->isHidden() or $characteristic->isHealth())
                    {
                        throw new Dynasty\UserContestTypePrerequisites\Exceptions\InvalidCharacteristicException;
                    }

                    // Add the prerequisite
                    UserContestTypePrerequisite::create(array(
                        'contest_type_id'   => $contestType->id, 
                        'characteristic_id' => $characteristic->id, 
                        'min_ranged_value'  => $characteristic->min_ranged_value, 
                        'max_ranged_value'  => $characteristic->max_ranged_value, 
                    ));
                }
            });

            $success = Lang::get('forms/user.add_prerequisites_to_contest_type.success');

            return Redirect::route('contests/type', $contestType->id)->with('success', $success);
        }
        catch(Dynasty\UserContestTypePrerequisites\Exceptions\AlreadyAttachedException $e)
        {
            $error = Lang::get('forms/user.add_prerequisites_to_contest_type.already_attached');
        }
        catch(Dynasty\UserContestTypePrerequisites\Exceptions\InvalidCharacteristicException $e)
        {
            $error = Lang::get('forms/user.add_prerequisites_to_contest_type.invalid_characteristic');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.add_prerequisites_to_contest_type.error');
        }

        return Redirect::route('contests/type', $contestType->id)->withInput()->with('error', $error);
    }

    public function postAddRequirements($contestType)
    {
        // Make sure this user owns this type
        if ($contestType->user_id != $this->currentUser->id)
        {
            Redirect::route('contests/manage');
        }

        // Grab all currently attached requirements
        $requirements = $contestType->requirements;

        // Make sure they are not over the max
        $maxRequirements = Config::get('game.contest.max_requirements');

        $max = max(0, $maxRequirements - count($requirements));

        // Declare the rules for the form validation
        $rules = array(
            'characteristics' => 'required|array|min:1|max:'.$max,
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::back()->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            DB::transaction(function() use ($contestType, $requirements)
            {
                $characteristicIds = Input::get('characteristics');

                // Grab the requirement characteristic ids
                $attachedRequirementCharacteristicIds = array_fetch($requirements->toArray(), 'characteristic_id');

                foreach($characteristicIds as $characteristicId)
                {
                    // Make sure this is unique
                    if (in_array($characteristicId, $attachedRequirementCharacteristicIds))
                    {
                        throw new Dynasty\UserContestTypeRequirements\Exceptions\AlreadyAttachedException;
                    }

                    // Grab the characteristic
                    $characteristic = Characteristic::find($characteristicId);

                    if (is_null($characteristic) or ! $characteristic->isActive() or $characteristic->isHidden() or $characteristic->isHealth() or ! $characteristic->isRanged())
                    {
                        throw new Dynasty\UserContestTypeRequirements\Exceptions\InvalidCharacteristicException;
                    }

                    // Add the requirement
                    UserContestTypeRequirement::create(array(
                        'contest_type_id'   => $contestType->id, 
                        'characteristic_id' => $characteristic->id, 
                        'type_id'           => UserContestTypeRequirement::TYPE_MID, 
                    ));
                }
            });

            $success = Lang::get('forms/user.add_requirements_to_contest_type.success');

            return Redirect::route('contests/type', $contestType->id)->with('success', $success);
        }
        catch(Dynasty\UserContestTypeRequirements\Exceptions\AlreadyAttachedException $e)
        {
            $error = Lang::get('forms/user.add_requirements_to_contest_type.already_attached');
        }
        catch(Dynasty\UserContestTypeRequirements\Exceptions\InvalidCharacteristicException $e)
        {
            $error = Lang::get('forms/user.add_requirements_to_contest_type.invalid_characteristic');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.add_requirements_to_contest_type.error');
        }

        return Redirect::route('contests/type', $contestType->id)->withInput()->with('error', $error);
    }

    public function getDeletePrerequisite($contestTypePrerequisite)
    {
        $contestType = $contestTypePrerequisite->contestType;

        // Make sure this user owns this type
        if ($contestType->user_id != $this->currentUser->id)
        {
            Redirect::route('contests/manage');
        }

        try
        {
            // Delete it
            $contestTypePrerequisite->delete();

            $success = Lang::get('forms/user.delete_contest_type_prerequisite.success');

            return Redirect::route('contests/type', $contestType->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.delete_contest_type_prerequisite.error');
        }

        return Redirect::route('contests/type', $contestType->id)->with('error', $error);
    }

    public function getDeleteRequirement($contestTypeRequirement)
    {
        $contestType = $contestTypeRequirement->contestType;

        // Make sure this user owns this type
        if ($contestType->user_id != $this->currentUser->id)
        {
            Redirect::route('contests/manage');
        }

        try
        {
            // Delete it
            $contestTypeRequirement->delete();

            $success = Lang::get('forms/user.delete_contest_type_requirement.success');

            return Redirect::route('contests/type', $contestType->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.delete_contest_type_requirement.error');
        }

        return Redirect::route('contests/type', $contestType->id)->with('error', $error);
    }

    public function postUpdateRequirement($contestType)
    {
        // Make sure this user owns this type
        if ($contestType->user_id != $this->currentUser->id)
        {
            Redirect::route('contests/manage');
        }

        // Declare the rules for the form validation
        $rules = array(
            'judging_requirement' => 'required|exists:user_contest_type_requirements,id,contest_type_id,'.$contestType->id,
            'range' => 'required|in:'.implode(',', array_keys(UserContestTypeRequirement::getTypes())), 
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::back()->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            // Update it
            $requirement = UserContestTypeRequirement::find(Input::get('judging_requirement'));

            $requirement->type_id = Input::get('range');
            $requirement->save();

            $success = Lang::get('forms/user.update_contest_type_requirement.success');

            return Redirect::route('contests/type', $contestType->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.update_contest_type_requirement.error');
        }

        return Redirect::route('contests/type', $contestType->id)->with('error', $error);
    }

    public function postUpdatePrerequisite($contestType)
    {
        // Make sure this user owns this type
        if ($contestType->user_id != $this->currentUser->id)
        {
            Redirect::route('contests/manage');
        }

        // Declare the rules for the form validation
        $rules = array(
            'prerequisite' => 'required|exists:user_contest_type_prerequisites,id,contest_type_id,'.$contestType->id,
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::back()->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            DB::transaction(function()
            {
                // Grab the prerequisite
                $prerequisite = UserContestTypePrerequisite::with('characteristic')
                    ->where('id', Input::get('prerequisite'))
                    ->first();

                // Grab the characteristic
                $characteristic = $prerequisite->characteristic;

                // Make sure if the characterisstic is ranged that the range is appropriate
                if ($characteristic->isRanged())
                {
                    list($minRangedValue, $maxRangedValue) = explode(',', Input::get('range'));

                    // Swap the values if the minimum is bigger than the max
                    if ($minRangedValue > $maxRangedValue)
                    {
                        $temp = $minRangedValue;
                        $minRangedValue = $maxRangedValue;
                        $maxRangedValue = $temp;
                    }

                    $rangedValues = array(
                        'minimum_ranged_value' => $minRangedValue, 
                        'maximum_ranged_value' => $maxRangedValue, 
                    );

                    // Need to make sure they're within the bounds
                    $rules = array(
                        'minimum_ranged_value' => 'required|integer|min:'.$characteristic->min_ranged_value.'|max:'.$characteristic->max_ranged_value,
                        'maximum_ranged_value' => 'required|integer|min:'.$characteristic->min_ranged_value.'|max:'.$characteristic->max_ranged_value,
                    );

                    // Create a new validator instance from our validation rules
                    $validator = Validator::make($rangedValues, $rules);

                    // If validation fails, we'll exit the operation now.
                    if ($validator->fails())
                    {
                        // Ooops.. something went wrong
                        throw new Dynasty\UserContestTypePrerequisites\Exceptions\InvalidRangeException;
                    }

                    // If there are labels, use the labels' bounds
                    if ($minLabel = $characteristic->getRangedValueLabel($minRangedValue))
                    {
                        $minRangedValue = $minLabel->min_ranged_value;
                    }

                    if ($maxLabel = $characteristic->getRangedValueLabel($maxRangedValue))
                    {
                        $maxRangedValue = $maxLabel->max_ranged_value;
                    }

                    $prerequisite->min_ranged_value = $minRangedValue;
                    $prerequisite->max_ranged_value = $maxRangedValue;

                    $prerequisite->save();
                }

                if ($characteristic->isGenetic())
                {
                    // Grab the chosen genotypes and phenotypes
                    $chosenGenotypeIds  = Input::get('genotypes', array());
                    $chosenPhenotypeIds = Input::get('phenotypes', array());

                    // Grab all possible genotypes for this prerequisite
                    $possibleLoci = $characteristic->loci()->with('genotypes')->whereActive()->get();
                    $possibleGenotypeIds = [];

                    foreach($possibleLoci as $locus)
                    {
                        foreach($locus->genotypes as $genotype)
                        {
                            $possibleGenotypeIds[] = $genotype->id;
                        }
                    }

                    $diff = array_diff($chosenGenotypeIds, $possibleGenotypeIds);

                    if ( ! empty($diff))
                    {
                        throw new Dynasty\UserContestTypePrerequisites\Exceptions\InvalidGenotypeException;
                    }

                    // We need to make sure all selected phenotypes are valid
                    $possiblePhenotypeIds = $characteristic->queryPhenotypes()->lists('id');

                    $diff = array_diff($chosenPhenotypeIds, $possiblePhenotypeIds);

                    if ( ! empty($diff))
                    {
                        throw new Dynasty\UserContestTypePrerequisites\Exceptions\InvalidPhenotypeException;
                    }

                    // Sync the genotypes and phenotypes
                    $prerequisite->genotypes()->sync($chosenGenotypeIds);
                    $prerequisite->phenotypes()->sync($chosenPhenotypeIds);
                }
            });

            $success = Lang::get('forms/user.update_contest_type_prerequisite.success');

            return Redirect::route('contests/type', $contestType->id)->with('success', $success);
        }
        catch(Dynasty\UserContestTypePrerequisites\Exceptions\InvalidRangeException $e)
        {
            $error = Lang::get('forms/user.update_contest_type_prerequisite.invalid_range');
        }
        catch(Dynasty\UserContestTypePrerequisites\Exceptions\InvalidGenotypeException $e)
        {
            $error = Lang::get('forms/user.update_contest_type_prerequisite.invalid_genotype');
        }
        catch(Dynasty\UserContestTypePrerequisites\Exceptions\InvalidPhenotypeException $e)
        {
            $error = Lang::get('forms/user.update_contest_type_prerequisite.invalid_phenotype');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.update_contest_type_prerequisite.error');
        }

        return Redirect::route('contests/type', $contestType->id)->with('error', $error);
    }

    public function getEnterDogInContest($dog, $contest)
    {
        try
        {
            // Make sure the user owns the dog
            if ( ! $this->currentUser->ownsDog($dog))
            {
                throw new Dynasty\Users\Exceptions\DoesNotOwnDogException;
            }

            // A dog must be alive
            if ( ! $dog->isAlive())
            {
                throw new Dynasty\Dogs\Exceptions\DeceasedException;
            }

            // A dog must be completed
            if ( ! $dog->isComplete())
            {
                throw new Dynasty\Dogs\Exceptions\IncompleteException;
            }

            // A dog must be unworked
            if ($dog->isWorked())
            {
                throw new Dynasty\Dogs\Exceptions\AlreadyWorkedException;
            }

            // Make sure the contest hasn't run yet
            if ($contest->hasRun())
            {
                throw new Dynasty\Contests\Exceptions\AlreadyRanException;
            }

            // Verify the dog meets the prerequisites
            if ( ! $contest->dogMeetsPrerequisites($dog))
            {
                throw new Dynasty\Contests\Exceptions\DogDoesNotMeetPrerequisitesException;
            }

            // Verify the dog unlocked the requirements
            if ( ! $contest->dogHasUnlockedRequirements($dog))
            {
                throw new Dynasty\Contests\Exceptions\DogHasNotUnlockedRequirementsException;
            }

            // A dog cannot enter twice
            if ($contest->dogHasBeenEntered($dog))
            {
                throw new Dynasty\Contests\Exceptions\DogHasAlreadyBeenEnteredException;
            }

            DB::transaction(function() use ($dog, $contest)
            {
                // Enter the dog
                ContestEntry::create(array(
                    'contest_id' => $contest->id, 
                    'dog_id'     => $dog->id, 
                )); 

                // Add to the counter
                $contest->total_entries += 1;
                $contest->save();

                // Mark the dog as worked
                $dog->worked = true;
                $dog->save();
            });

            $success = Lang::get('forms/user.enter_dog_in_contest.success');

            return Redirect::route('contests', ['dog' => $dog->id, 'search' => 'contests'])->with('success', $success);
        }
        catch(Dynasty\Users\Exceptions\DoesNotOwnDogException $e)
        {
            $error = Lang::get('forms/user.enter_dog_in_contest.invalid_dog');
        }
        catch(Dynasty\Dogs\Exceptions\DeceasedException $e)
        {
            $error = Lang::get('forms/user.enter_dog_in_contest.invalid_dog');
        }
        catch(Dynasty\Dogs\Exceptions\IncompleteException $e)
        {
            $error = Lang::get('forms/user.enter_dog_in_contest.invalid_dog');
        }
        catch(Dynasty\Dogs\Exceptions\AlreadyWorkedException $e)
        {
            $error = Lang::get('forms/user.enter_dog_in_contest.already_worked');
        }
        catch(Dynasty\Contests\Exceptions\AlreadyRanException $e)
        {
            $error = Lang::get('forms/user.enter_dog_in_contest.has_ran');
        }
        catch(Dynasty\Contests\Exceptions\DogDoesNotMeetPrerequisitesException $e)
        {
            $error = Lang::get('forms/user.enter_dog_in_contest.unmet_prerequisites');
        }
        catch(Dynasty\Contests\Exceptions\DogHasNotUnlockedRequirementsException $e)
        {
            $error = Lang::get('forms/user.enter_dog_in_contest.locked_requirements');
        }
        catch(Dynasty\Contests\Exceptions\DogHasAlreadyBeenEnteredException $e)
        {
            $error = Lang::get('forms/user.enter_dog_in_contest.already_entered');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.enter_dog_in_contest.error');
        }

        return Redirect::route('contests', ['dog' => $dog->id, 'search' => 'contests'])->withInput()->with('error', $error);
    }

}
