<?php namespace Controllers\BreedRegistry;

use Illuminate\Support\Collection;
use AuthorizedController;
use App;
use Redirect;
use View;
use Input;
use Validator;
use Lang;
use DB;
use URL;
use Carbon;
use Config;
use Str;
use BreedDraft;
use CharacteristicCategory;
use Characteristic;
use BreedDraftCharacteristic;
use Dog;
use Breed;
use BreedCharacteristic;
use Genotype;
use UserNotification;

use Exception;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Dynasty\BreedDrafts\Exceptions as DynastyBreedDraftsExceptions;
use Dynasty\BreedDraftCharacteristics\Exceptions as DynastyBreedDraftCharacteristicsExceptions;
use Dynasty\Characteristics\Exceptions as DynastyCharacteristicsExceptions;
use Dynasty\Exceptions as DynastyExceptions;
use Dynasty\Users\Exceptions as DynastyUsersExceptions;
use Dynasty\Dogs\Exceptions as DynastyDogsExceptions;

class DraftController extends AuthorizedController {

    public function getForm($breedDraft)
    {
        // Make sure the draft belongs to the user
        if ($this->currentUser->id != $breedDraft->user_id)
        {
            App::abort('404', 'Draft not found!');
        }

        // Must be in draft state to edit
        if ( ! $breedDraft->isEditable())
        {
            // Redirect to the submission
            return Redirect::route('breed_registry/draft/submitted', $breedDraft->id);
        }

        // Get the user's eligible dogs
        $kennelGroups = $this->currentUser->kennelGroups()->whereNotCemetery()
            ->whereHas('dogs', function($query)
                {
                    $query->whereComplete()->whereAlive();
                })
            ->with(array(
                'dogs' => function($query)
                {
                    $query->whereComplete()->whereAlive()->orderBy('name', 'asc');
                }))
            ->orderBy('id', 'asc')->get();

        // Get all of the characteristics attached to the breed draft
        $breedDraftCharacteristics = $breedDraft->characteristics()->with('characteristic')->orderByCharacteristic()->get();

        // Get all of the characteristic IDs
        $usedCharacteristicsIds = $breedDraftCharacteristics->lists('characteristic_id');

        // Add -1 to avoid errors
        $usedCharacteristicsIds[] = -1;

        // Get the characteristics categories
        $characteristicCategories = CharacteristicCategory::with(array(
                'parent', 
                'characteristics' => function($query) use ($usedCharacteristicsIds)
                    {
                        $query
                            ->whereActive()
                            ->whereVisible()
                            ->where('type_id', '<>', Characteristic::TYPE_FERTILITY)
                            ->where('type_id', '<>', Characteristic::TYPE_FERTILITY_SPAN)
                            ->where('type_id', '<>', Characteristic::TYPE_FERTILITY_DROP_OFF)
                            ->whereNotIn('id', $usedCharacteristicsIds)
                            ->orderBy('name', 'asc');
                    }
            ))
            ->whereHas('characteristics', function($query) use ($usedCharacteristicsIds)
                {
                    $query
                        ->whereActive()
                        ->whereVisible()
                        ->where('type_id', '<>', Characteristic::TYPE_FERTILITY)
                        ->where('type_id', '<>', Characteristic::TYPE_FERTILITY_SPAN)
                        ->where('type_id', '<>', Characteristic::TYPE_FERTILITY_DROP_OFF)
                        ->whereNotIn('id', $usedCharacteristicsIds);
                })
            ->select('characteristic_categories.*')
            ->join('characteristic_categories as parent', 'parent.id', '=', 'characteristic_categories.parent_category_id')
            ->whereNotHealth()
            ->orderBy('parent.name', 'asc')
            ->orderBy('characteristic_categories.name', 'asc')
            ->get();

        // Show the page
        return View::make('frontend/breed_registry/draft/form', compact(
            'kennelGroups', 'characteristicCategories', 'breedDraftCharacteristics', 
            'breedDraft'
        ));
    }

    public function postForm($breedDraft)
    {
        // Make sure the draft belongs to the user
        if ($this->currentUser->id != $breedDraft->user_id)
        {
            App::abort('404', 'Draft not found!');
        }

        // Must be in draft state to edit
        if ( ! $breedDraft->isEditable())
        {
            // Redirect to the submission
            return Redirect::route('breed_registry/draft/submitted', $breedDraft->id);
        }

        try
        {
            // Need to make sure the dog exists for database integrity
            $dog = Dog::find(Input::get('dog'));

            $breedDraft->dog_id = is_null($dog)
                ? null
                : $dog->id;

            // Save the rest of the draft regardless of errors
            $breedDraft->name = Input::get('name');
            $breedDraft->description = Input::get('description');

            if ($breedDraft->isOfficial())
            {
                $breedDraft->health_disorders = Input::get('health_disorders');
            }

            $breedDraft->edited_at = Carbon::now();

            $breedDraft->save();

            // Validate the image first
            $rules = array(
                'image' => 'image|mimes:png|image_size:<=700,<=500',
            );

            // Create a new validator instance from our validation rules
            $validator = Validator::make(Input::all(), $rules);

            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::route('breed_registry/draft/form', $breedDraft->id)->with('warning', $validator->errors()->first());
            }

            if ( ! $breedDraft->isOfficial() and Input::hasFile('image'))
            {
                $image = Input::file('image');

                if ( ! $image->isValid())
                {
                    throw new DynastyBreedDraftsExceptions\InvalidImageException;
                }

                // Find the directory
                $dir = public_path($breedDraft->getImageDirectory());
                $ext = $breedDraft->getImageExtension();

                // Create the filename
                $filename = $breedDraft->id.'.'.$ext;

                // Save the image
                $image->move($dir, $filename);
            }

            // Declare the rules for the form validation
            $rules = array(
                'name' => 'required|max:32|unique:breeds,name', 
                'dog'  => 'required|exists:dogs,id',
                'description' => 'max:1000', 
                'health_disorders' => 'max:255', 
            );

            if ($breedDraft->isOfficial())
            {
                unset($rules['dog']);
            }
            else
            {
                unset($rules['health_disorders']);
            }

            // Create a new validator instance from our validation rules
            $validator = Validator::make(Input::all(), $rules);

            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::route('breed_registry/draft/form', $breedDraft->id)->with('warning', $validator->errors()->first());
            }

            if ( ! is_null($dog))
            {
                // Check on the dog
                $breedDraft->checkOriginator();
            }

            $success = Lang::get('forms/user.save_breed_draft.success');

            return Redirect::route('breed_registry/draft/form', $breedDraft->id)->with('success', $success);
        }
        catch(DynastyBreedDraftsExceptions\InvalidImageException $e)
        {
            $error = Lang::get('forms/user.save_breed_draft.invalid_image');
        }
        catch(FileException $e)
        {
            $error = Lang::get('forms/user.save_breed_draft.could_not_save_image');
        }
        catch(DynastyUsersExceptions\DoesNotOwnDogException $e)
        {
            $error = Lang::get('forms/user.save_breed_draft.wrong_owner');
        }
        catch(DynastyDogsExceptions\DeceasedException $e)
        {
            $error = Lang::get('forms/user.save_breed_draft.deceased_dog');
        }
        catch(DynastyDogsException\IncompleteException $e)
        {
            $error = Lang::get('forms/user.save_breed_draft.incomplete_dog');
        }
        catch(DynastyDogsExceptions\NotEnoughGenerationsException $e)
        {
            $error = Lang::get('forms/user.submit_breed_draft.not_enough_generations');
        }
        catch(DynastyDogsException\BreedOriginatorException $e)
        {
            $error = Lang::get('forms/user.submit_breed_draft.dog_is_breed_originator');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.save_breed_draft.error');
        }

        return Redirect::route('breed_registry/draft/form', $breedDraft->id)->with('warning', $error);
    }

    public function postFormAddCharacteristic($breedDraft)
    {
        // Make sure the draft belongs to the user
        if ($this->currentUser->id != $breedDraft->user_id)
        {
            App::abort('404', 'Draft not found!');
        }

        // Must be in draft state to edit
        if ( ! $breedDraft->isEditable())
        {
            // Redirect to the submission
            return Redirect::route('breed_registry/draft/submitted', $breedDraft->id);
        }

        try
        {
            // Can only add characteristics to unofficial drafts
            if ($breedDraft->isOfficial())
            {
                throw new DynastyBreedDraftsExceptions\OfficialException;
            }

            // Get all of the characteristics IDs
            $characteristicIds = array_filter((array) Input::get('characteristics'));

            if (empty($characteristicIds))
            {
                throw new DynastyExceptions\NoneSelectedException;
            }

            // Get all of the characteristic IDs already attached
            $usedCharacteristicsIds = $breedDraft->characteristics()->lists('characteristic_id');

            // Add -1 to avoid errors
            $usedCharacteristicsIds[] = -1;

            // Get the characteristics
            $characteristics = Characteristic::whereActive()
                ->whereVisible()
                ->whereNotHealth()
                ->where('type_id', '<>', Characteristic::TYPE_FERTILITY)
                ->where('type_id', '<>', Characteristic::TYPE_FERTILITY_SPAN)
                ->where('type_id', '<>', Characteristic::TYPE_FERTILITY_DROP_OFF)
                ->whereNotIn('id', $usedCharacteristicsIds)
                ->whereIn('id', $characteristicIds)
                ->get();

            if ($characteristics->isEmpty())
            {
                throw new DynastyCharacteristicsExceptions\NotFoundException;
            }

            DB::transaction(function() use ($breedDraft, $characteristics)
            {
                // Add them to the draft
                foreach($characteristics as $characteristic)
                {
                    $breedDraftCharacteristic = BreedDraftCharacteristic::create(array(
                        'breed_draft_id'    => $breedDraft->id, 
                        'characteristic_id' => $characteristic->id, 
                        'min_female_ranged_value' => ceil($characteristic->min_ranged_value), 
                        'max_female_ranged_value' => floor($characteristic->max_ranged_value), 
                        'min_male_ranged_value'   => ceil($characteristic->min_ranged_value), 
                        'max_male_ranged_value'   => floor($characteristic->max_ranged_value), 
                    ));
                }

                $breedDraft->edited_at = Carbon::now();
                $breedDraft->save();
            });

            // Get the valid characteristic's names
            $validCharacteristicNames = $characteristics->lists('name');

            $params = array(
                'characteristics' => implode(', ', $validCharacteristicNames), 
            );

            $success = Lang::get('forms/user.add_characteristics_to_breed_draft.success', $params);

            return Redirect::route('breed_registry/draft/form', $breedDraft->id)->with('success', $success);
        }
        catch(DynastyBreedDraftsExceptions\OfficialException $e)
        {
            $error = Lang::get('forms/user.add_characteristics_to_breed_draft.official');
        }
        catch(DynastyExceptions\NoneSelectedException $e)
        {
            $error = Lang::get('forms/user.add_characteristics_to_breed_draft.none_selected');
        }
        catch(DynastyCharacteristicsExceptions\NotFoundException $e)
        {
            $error = Lang::get('forms/user.add_characteristics_to_breed_draft.invalid_characteristic');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.add_characteristics_to_breed_draft.error');
        }

        return Redirect::route('breed_registry/draft/form', $breedDraft->id)->with('error', $error);
    }

    public function getDelete($breedDraft)
    {
        // Make sure the draft belongs to the user
        if ($this->currentUser->id != $breedDraft->user_id or $breedDraft->isAccepted())
        {
            App::abort('404', 'Draft not found!');
        }

        try
        {
            $params = array(
                'breedDraft' => $breedDraft->toArray(), 
            );

            $breedDraft->delete();

            $success = Lang::get('forms/user.delete_breed_draft.success', array_map('htmlentities', array_dot($params)));

            return Redirect::route('breed_registry/manage')->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.delete_breed_draft.error');
        }

        return Redirect::route('breed_registry/manage')->with('error', $error);
    }

    public function getFormCharacteristic($breedDraftCharacteristic)
    {
        // Grab the breed draft
        $breedDraft     = $breedDraftCharacteristic->breedDraft;

        // Make sure the draft belongs to the user
        if ($this->currentUser->id != $breedDraft->user_id)
        {
            App::abort('404', 'Draft not found!');
        }

        // Must be in draft state to edit
        if ( ! $breedDraft->isEditable())
        {
            // Redirect to the submission
            return Redirect::route('breed_registry/draft/submitted/characteristic', $breedDraftCharacteristic->id);
        }

        // Grab the characteristic
        $characteristic = $breedDraftCharacteristic->characteristic;

        // Speed up ranged chars
        if ($breedDraftCharacteristic->characteristic->isGenetic())
        {
            $phenotypes = $characteristic->queryPhenotypes()->orderBy('name', 'asc')->get();

            $loci = $characteristic->loci()->with(array(
                    'genotypes' => function($query)
                    {
                        $query->orderByAlleles();
                    }
                ))
                ->whereActive()
                ->orderBy('name', 'asc')
                ->get();

            $savedPhenotypeIds = $breedDraftCharacteristic->phenotypes()->lists('id');
            $savedGenotypeIds  = $breedDraftCharacteristic->genotypes()->lists('id');

            $resultingPhenotypes = $breedDraftCharacteristic->possiblePhenotypes()->orderBy('name', 'asc')->get();
            $resultingLoci       = $breedDraftCharacteristic->possibleLociWithGenotypes()->orderBy('name', 'asc')->get();
        }
        else
        {
            $phenotypes = new Collection;
            $loci = new Collection;

            $savedPhenotypeIds = [];
            $savedGenotypeIds  = [];

            $resultingPhenotypes = new Collection;
            $resultingLoci       = new Collection;
        }

        // Show the page
        return View::make('frontend/breed_registry/draft/form_characteristic', compact(
            'phenotypes', 'savedPhenotypeIds', 'loci', 'savedGenotypeIds', 
            'resultingPhenotypes', 'resultingLoci', 
            'breedDraft', 'breedDraftCharacteristic', 'characteristic'
        ));
    }

    public function getRemoveFormCharacteristic($breedDraftCharacteristic)
    {
        // Grab the breed draft
        $breedDraft = $breedDraftCharacteristic->breedDraft;

        // Make sure the draft belongs to the user
        if ($this->currentUser->id != $breedDraft->user_id or ! $breedDraft->isEditable())
        {
            App::abort('404', 'Characteristic not found!');
        }

        try
        {
            // Can only remove characteristics from unofficial drafts
            if ($breedDraft->isOfficial())
            {
                throw new DynastyBreedDraftsExceptions\OfficialException;
            }

            $breedDraftCharacteristic->delete();

            $success = Lang::get('forms/user.remove_characteristic_from_breed_draft.success');

            return Redirect::route('breed_registry/draft/form', $breedDraft->id)->with('success', $success);
        }
        catch(DynastyBreedDraftsExceptions\OfficialException $e)
        {
            $error = Lang::get('forms/user.remove_characteristic_from_breed_draft.official');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.remove_characteristic_from_breed_draft.error');
        }

        return Redirect::route('breed_registry/draft/form/characteristic', $breedDraftCharacteristic->id)->with('error', $error);
    }

    public function postSaveFormCharacteristic($breedDraftCharacteristic)
    {
        // Grab the breed draft
        $breedDraft = $breedDraftCharacteristic->breedDraft;

        // Make sure the draft belongs to the user
        if ($this->currentUser->id != $breedDraft->user_id or ! $breedDraft->isEditable())
        {
            App::abort('404', 'Characteristic not found!');
        }

        try
        {
            DB::transaction(function() use ($breedDraftCharacteristic, $breedDraft)
            {
                // Grab the characteristic
                $characteristic = $breedDraftCharacteristic->characteristic;

                $breedDraftCharacteristic->ignored = ($breedDraft->isOfficial() and $characteristic->isIgnorable() and Input::get('ignore') == 'yes');

                if ($characteristic->isRanged())
                {
                    // Get the ranged values
                    $femaleRangedValues = explode(',', Input::get('range_female'));
                    $maleRangedValues = explode(',', Input::get('range_male'));

                    // Make sure there's enough values supplied
                    if (count($femaleRangedValues) === 0)
                    {
                        $minFemaleRangedValue = $characteristic->min_ranged_value;
                        $maxFemaleRangedValue = $characteristic->max_ranged_value;

                    }
                    else if (count($femaleRangedValues) == 1)
                    {
                        $minFemaleRangedValue = $maxFemaleRangedValue = $femaleRangedValues[0];
                    }
                    else
                    {
                        $minFemaleRangedValue = $femaleRangedValues[0];
                        $maxFemaleRangedValue = $femaleRangedValues[1];
                    }

                    if (count($maleRangedValues) === 0)
                    {
                        $minMaleRangedValue = $characteristic->min_ranged_value;
                        $maxMaleRangedValue = $characteristic->max_ranged_value;

                    }
                    else if (count($maleRangedValues) == 1)
                    {
                        $maxMaleRangedValue = $minMaleRangedValue = $maleRangedValues[0];
                    }
                    else
                    {
                        $maxMaleRangedValue = $maleRangedValues[0];
                        $minMaleRangedValue = $maleRangedValues[1];
                    }

                    // Swap the values if needed
                    if ($minFemaleRangedValue > $maxFemaleRangedValue)
                    {
                        $temp = $minFemaleRangedValue;
                        $minFemaleRangedValue = $maxFemaleRangedValue;
                        $maxFemaleRangedValue = $temp;
                    }

                    if ($minMaleRangedValue > $maxMaleRangedValue)
                    {
                        $temp = $minMaleRangedValue;
                        $minMaleRangedValue = $maxMaleRangedValue;
                        $maxMaleRangedValue = $temp;
                    }

                    // Compare against the labels if they exist
                    $minFemaleRangedLabel = $characteristic->getRangedValueLabel($minFemaleRangedValue);
                    $maxFemaleRangedLabel = $characteristic->getRangedValueLabel($maxFemaleRangedValue);
                    $minMaleRangedLabel   = $characteristic->getRangedValueLabel($minMaleRangedValue);
                    $maxMaleRangedLabel   = $characteristic->getRangedValueLabel($maxMaleRangedValue);

                    if ( ! is_null($minFemaleRangedLabel))
                    {
                        $minFemaleRangedValue = $minFemaleRangedLabel->min_ranged_value;
                    }

                    if ( ! is_null($maxFemaleRangedLabel))
                    {
                        $maxFemaleRangedValue = $maxFemaleRangedLabel->max_ranged_value;
                    }

                    if ( ! is_null($minMaleRangedLabel))
                    {
                        $minMaleRangedValue = $minMaleRangedLabel->min_ranged_value;
                    }

                    if ( ! is_null($maxMaleRangedLabel))
                    {
                        $maxMaleRangedValue = $maxMaleRangedLabel->max_ranged_value;
                    }

                    // Bind the female values
                    $minFemaleRangedValue = ceil($characteristic->bindRangedValue($minFemaleRangedValue));
                    $maxFemaleRangedValue = floor($characteristic->bindRangedValue($maxFemaleRangedValue));

                    // Bind the male values
                    $minMaleRangedValue = ceil($characteristic->bindRangedValue($minMaleRangedValue));
                    $maxMaleRangedValue = floor($characteristic->bindRangedValue($maxMaleRangedValue));

                    // Save them
                    $breedDraftCharacteristic->min_female_ranged_value = $minFemaleRangedValue;
                    $breedDraftCharacteristic->max_female_ranged_value = $maxFemaleRangedValue;
                    $breedDraftCharacteristic->min_male_ranged_value   = $minMaleRangedValue;
                    $breedDraftCharacteristic->max_male_ranged_value   = $maxMaleRangedValue;
                }

                if ($characteristic->isGenetic())
                {
                    // Get the user phenotype IDs
                    $potentialPhenotypeIds = (array) Input::get('phenotypes');

                    // Get the valid IDs
                    $phenotypeIds = $characteristic->queryPhenotypes()->lists('id');

                    // Use only the valid ones
                    $validPhenotypeIds = array_intersect($phenotypeIds, $potentialPhenotypeIds);

                    $breedDraftCharacteristic->phenotypes()->sync($validPhenotypeIds);

                    if ( ! $characteristic->hideGenotypes())
                    {
                        // Get the user genotype IDs
                        $potentialGenotypeIds = array_flatten((array) Input::get('genotypes'));

                        // Get the valid IDs
                        $genotypeIds = [];

                        $loci = $characteristic->loci()->with('genotypes')->whereActive()->get();

                        foreach($loci as $locus)
                        {
                            $genotypeIds = array_merge($genotypeIds, $locus->genotypes->lists('id'));
                        }

                        // Use only the valid ones
                        $validGenotypeIds = array_intersect($genotypeIds, $potentialGenotypeIds);

                        $breedDraftCharacteristic->genotypes()->sync($validGenotypeIds);
                    }
                }

                // Save the breed draft characteristic
                $breedDraftCharacteristic->save();
            });

            $success = Lang::get('forms/user.save_breed_draft_characteristic.success');

            return Redirect::route('breed_registry/draft/form/characteristic', $breedDraftCharacteristic->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.save_breed_draft_characteristic.error');
        }

        return Redirect::route('breed_registry/draft/form/characteristic', $breedDraftCharacteristic->id)->with('error', $error);
    }

    public function getSubmitForm($breedDraft)
    {
        // Make sure the draft belongs to the user
        if ($this->currentUser->id != $breedDraft->user_id)
        {
            App::abort('404', 'Draft not found!');
        }

        // Must be in draft state to submit
        if ( ! $breedDraft->isEditable())
        {
            // Redirect to the submission
            return Redirect::route('breed_registry/draft/submitted', $breedDraft->id);
        }

        try
        {
            // Declare the rules for the form validation
            $rules = array(
                'name' => 'required|max:32|unique:breeds,name', 
                'dog'  => 'required|exists:dogs,id',
                'description' => 'max:1000', 
                'health_disorders' => 'max:255', 
            );

            if ($breedDraft->isOfficial())
            {
                unset($rules['dog']);
            }
            else
            {
                unset($rules['health_disorders']);
            }

            $values = array(
                'name' => $breedDraft->name, 
                'dog'  => $breedDraft->dog_id, 
                'description' => $breedDraft->description, 
                'health_disorders' => $breedDraft->health_disorders, 
            );

            // Create a new validator instance from our validation rules
            $validator = Validator::make($values, $rules);

            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::route('breed_registry/draft/form', $breedDraft->id)->with('error', $validator->errors()->first());
            }

            DB::transaction(function() use ($breedDraft)
            {
                // Check on the dog
                $breedDraft->checkOriginator();

                // Check on the characteristics
                $breedDraft->checkCharacteristics();

                // Check on the dog and ancestor characteristics
                $breedDraft->checkOriginatorCharacteristics();

                // Set status to pending
                $breedDraft->status_id = BreedDraft::STATUS_PENDING;

                // Log when it was submitted
                $breedDraft->submitted_at = Carbon::now();

                // Clear any old rejection reasons
                $breedDraft->rejection_reasons = '';

                $breedDraft->save();
            });

            $success = Lang::get('forms/user.submit_breed_draft.success');

            return Redirect::route('breed_registry/draft/submitted', $breedDraft->id)->with('success', $success);
        }
        catch(DynastyBreedDraftsExceptions\MissingDogException $e)
        {
            $error = Lang::get('forms/user.submit_breed_draft.missing_dog');
        }
        catch(DynastyUsersExceptions\DoesNotOwnDogException $e)
        {
            $error = Lang::get('forms/user.submit_breed_draft.wrong_owner');
        }
        catch(DynastyDogsExceptions\DeceasedException $e)
        {
            $error = Lang::get('forms/user.submit_breed_draft.deceased_dog');
        }
        catch(DynastyDogsExceptions\IncompleteException $e)
        {
            $error = Lang::get('forms/user.submit_breed_draft.incomplete_dog');
        }
        catch(DynastyDogsExceptions\NotEnoughGenerationsException $e)
        {
            $error = Lang::get('forms/user.submit_breed_draft.not_enough_generations');
        }
        catch(DynastyDogsExceptions\BreedOriginatorException $e)
        {
            $error = Lang::get('forms/user.submit_breed_draft.dog_is_breed_originator');
        }
        catch(DynastyBreedDraftsExceptions\MissingCharacteristicException $e)
        {
            $error = Lang::get('forms/user.submit_breed_draft.no_characteristics');
        }
        catch(DynastyBreedDraftCharacteristicsExceptions\FemaleRangedValueOutOfBoundsException $e)
        {
            $params = array(
                'characteristic' => $e->getMessage(), 
            );

            $error = Lang::get('forms/user.submit_breed_draft.female_ranged_value_out_of_bounds', array_map('htmlentities', array_dot($params)));
        }
        catch(DynastyBreedDraftCharacteristicsExceptions\MaleRangedValueOutOfBoundsException $e)
        {
            $params = array(
                'characteristic' => $e->getMessage(), 
            );

            $error = Lang::get('forms/user.submit_breed_draft.male_ranged_value_out_of_bounds', array_map('htmlentities', array_dot($params)));
        }
        catch(DynastyBreedDraftCharacteristicsExceptions\IncompleteException $e)
        {
            $params = array(
                'characteristic' => $e->getMessage(), 
            );

            $error = Lang::get('forms/user.submit_breed_draft.incomplete_characteristic', array_map('htmlentities', array_dot($params)));
        }
        catch(DynastyBreedDraftCharacteristicsExceptions\GenotypesNotFoundInCharacteristicException $e)
        {
            $params = $e->getMessage();
            $error  = Lang::get('forms/user.submit_breed_draft.genotypes_not_found_in_characteristic', array_map('htmlentities', array_dot($params)));
        }
        catch(DynastyBreedDraftCharacteristicsExceptions\InternalConflictException $e)
        {
            $params = array(
                'characteristic' => $e->getMessage(), 
            );

            $error = Lang::get('forms/user.submit_breed_draft.internally_conflicted_characteristic', array_map('htmlentities', array_dot($params)));
        }
        catch(DynastyBreedDraftCharacteristicsExceptions\PhenotypeNotFoundInCharacteristicException $e)
        {
            $params = $e->getMessage();
            $error  = Lang::get('forms/user.submit_breed_draft.phenotype_not_found_in_characteristic', array_map('htmlentities', array_dot($params)));
        }
        catch(DynastyBreedDraftCharacteristicsExceptions\ExternalConflictException $e)
        {
            $params = array(
                'characteristic' => $e->getMessage(), 
            );

            $error = Lang::get('forms/user.submit_breed_draft.externally_conflicted_characteristic', array_map('htmlentities', array_dot($params)));
        }
        catch(DynastyBreedDraftsExceptions\DogDoesNotMeetRequirementsException $e)
        {
            $params = array(
                'failedCharacteristics' => $e->getMessage(), 
            );

            $error = Lang::get('forms/user.submit_breed_draft.requirements_unmet_by_dog', array_map('htmlentities', array_dot($params)));
        }
        catch(DynastyBreedDraftsExceptions\AncestorDoesNotMeetRequirementsException $e)
        {
            $params = array(
                'failedCharacteristics' => $e->getMessage(), 
            );

            $error = Lang::get('forms/user.submit_breed_draft.requirements_unmet_by_ancestor', array_map('htmlentities', array_dot($params)));
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.submit_breed_draft.error');
        }

        return Redirect::route('breed_registry/draft/form', $breedDraft->id)->with('error', $error);
    }

    public function getRevertSubmittedToDraft($breedDraft)
    {
        // Make sure the draft belongs to the user
        if ($this->currentUser->id != $breedDraft->user_id or $breedDraft->isDraft() or $breedDraft->isAccepted())
        {
            App::abort('404', 'Draft not found!');
        }

        try
        {
            // Set status to draft
            $breedDraft->status_id = BreedDraft::STATUS_DRAFT;

            $breedDraft->save();

            $success = Lang::get('forms/user.revert_breed_draft.success');

            return Redirect::route('breed_registry/draft/form', $breedDraft->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.revert_breed_draft.error');
        }

        return Redirect::route('revert_breed_draft/manage')->with('error', $error);
    }

    public function getSubmitted($breedDraft)
    {
        // Make sure the draft belongs to the user
        if ($this->currentUser->id != $breedDraft->user_id)
        {
            App::abort('404', 'Draft not found!');
        }

        if ($breedDraft->isEditable())
        {
            // Redirect to the submission
            return Redirect::route('breed_registry/draft/form', $breedDraft->id);
        }

        if ($breedDraft->isAccepted())
        {
            // Get the breed 
            if ( ! is_null($breedDraft->breed))
            {
                // Redirect to the breed
                return Redirect::route('breed_registry/breed', $breedDraft->breed->id);
            }
        }

        // Get all of the characteristics attached to the breed draft
        $breedDraftCharacteristics = $breedDraft->characteristics()->with('characteristic')->orderByCharacteristic()->get();

        if ($breedDraft->isExtinct())
        {
            $kennelGroups = $this->currentUser->kennelGroups()->whereNotCemetery()
                ->whereHas('dogs', function($query)
                    {
                        $query->whereComplete()->whereAlive();
                    })
                ->with(array(
                    'dogs' => function($query)
                    {
                        $query->whereComplete()->whereAlive()->orderBy('name', 'asc');
                    }))
                ->orderBy('id', 'asc')->get();
        }
        else
        {
            $kennelGroups = new Collection;
        }

        // Show the page
        return View::make('frontend/breed_registry/draft/submitted', compact(
            'breedDraftCharacteristics', 'kennelGroups', 
            'breedDraft'
        ));
    }

    public function getSubmittedCharacteristic($breedDraftCharacteristic)
    {
        // Grab the breed draft
        $breedDraft = $breedDraftCharacteristic->breedDraft;

        // Make sure the draft belongs to the user
        if ($this->currentUser->id != $breedDraft->user_id or $breedDraft->isAccepted())
        {
            App::abort('404', 'Draft not found!');
        }

        if ($breedDraft->isEditable())
        {
            // Redirect to the submission
            return Redirect::route('breed_registry/draft/form/characteristic', $breedDraftCharacteristic->id);
        }

        // Grab the characteristic
        $characteristic = $breedDraftCharacteristic->characteristic;

        $resultingPhenotypes = $breedDraftCharacteristic->possiblePhenotypes()->orderBy('name', 'asc')->get();
        $resultingLoci       = $breedDraftCharacteristic->possibleLociWithGenotypes()->orderBy('name', 'asc')->get();

        // Show the page
        return View::make('frontend/breed_registry/draft/submitted_characteristic', compact(
            'resultingPhenotypes', 'resultingLoci', 
            'breedDraft', 'breedDraftCharacteristic', 'characteristic'
        ));
    }

    public function postResubmitExtinct($breedDraft)
    {
        // Make sure the draft belongs to the user
        if ($this->currentUser->id != $breedDraft->user_id)
        {
            App::abort('404', 'Draft not found!');
        }

        // Must be in extinct state to submit
        if ( ! $breedDraft->isExtinct())
        {
            // Redirect to the submission
            return Redirect::route('breed_registry/draft/submitted', $breedDraft->id);
        }

        try
        {
            // Declare the rules for the form validation
            $rules = array(
                'dog' => 'required|exists:dogs,id',
            );

            // Create a new validator instance from our validation rules
            $validator = Validator::make(Input::all(), $rules);

            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::route('breed_registry/draft/submitted', $breedDraft->id)->with('error', $validator->errors()->first());
            }

            // Grab the breed
            $breed = $breedDraft->breed;

            DB::transaction(function() use ($breedDraft, $breed)
            {
                // Check on the dog
                $breedDraft->checkOriginator();

                // Check on the dog and ancestor characteristics
                $breedDraft->checkOriginatorCharacteristics();

                // Save the new originator
                $breedDraft->dog_id = Input::get('dog');

                // Set status to accepted
                $breedDraft->status_id = BreedDraft::STATUS_ACCEPTED;

                $breedDraft->save();

                // Set the breed back to active
                $breed->active = true;

                // Set the new originator
                $breed->originator_id = $breedDraft->dog_id;

                // Save the breed
                $breed->save();

                // Get the originator
                $originator = Dog::find($breedDraft->dog_id);

                // Set the dog to this breed
                $originator->breed_id = $breed->id;

                // Set the dog as an active member
                $originator->active_breed_member = true;

                // Save the dog
                $originator->save();

                // After everything, move the image if it exists
                if ($breedDraft->hasImage())
                {
                    // Get the image
                    $oldPath = $breedDraft->getImagePath();

                    // Get the new image path
                    $newPath = $breed->getImagePath();

                    File::move($oldPath, $newPath);
                }
            });

            $success = Lang::get('forms/user.resubmit_breed_draft.success');

            return Redirect::route('breed_registry/breed', $breed->id)->with('success', $success);
        }
        catch(DynastyBreedDraftsExceptions\MissingDogException $e)
        {
            $error = Lang::get('forms/user.resubmit_breed_draft.missing_dog');
        }
        catch(DynastyUsersExceptions\DoesNotOwnDogException $e)
        {
            $error = Lang::get('forms/user.resubmit_breed_draft.wrong_owner');
        }
        catch(DynastyDogsExceptions\DeceasedException $e)
        {
            $error = Lang::get('forms/user.resubmit_breed_draft.deceased_dog');
        }
        catch(DynastyDogsExceptions\IncompleteException $e)
        {
            $error = Lang::get('forms/user.resubmit_breed_draft.incomplete_dog');
        }
        catch(DynastyDogsExceptions\NotEnoughGenerationsException $e)
        {
            $error = Lang::get('forms/user.resubmit_breed_draft.not_enough_generations');
        }
        catch(DynastyDogsExceptions\BreedOriginatorException $e)
        {
            $error = Lang::get('forms/user.resubmit_breed_draft.dog_is_breed_originator');
        }
        catch(DynastyBreedDraftsExceptions\DogDoesNotMeetRequirementsException $e)
        {
            $params = array(
                'failedCharacteristics' => $e->getMessage(), 
            );

            $error = Lang::get('forms/user.resubmit_breed_draft.requirements_unmet_by_dog', array_map('htmlentities', array_dot($params)));
        }
        catch(DynastyBreedDraftsExceptions\AncestorDoesNotMeetRequirementsException $e)
        {
            $params = array(
                'failedCharacteristics' => $e->getMessage(), 
            );

            $error = Lang::get('forms/user.resubmit_breed_draft.requirements_unmet_by_ancestor', array_map('htmlentities', array_dot($params)));
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.resubmit_breed_draft.error');
        }

        return Redirect::route('breed_registry/draft/form', $breedDraft->id)->with('error', $error);
    }

}
