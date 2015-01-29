<?php namespace Controllers\Dog;

use AuthorizedController;
use Redirect;
use View;
use Input;
use Validator;
use Lang;
use Config;
use DB;
use Str;
use Dog;
use Breed;
use CharacteristicCategory;
use StudRequest;
use URL;
use UserNotification;
use UserCreditTransaction;
use User;
use LendRequest;
use Carbon;
use Pedigree;
use Dynasty;

use Exception;
use Dynasty\Exceptions as DynastyExceptions;
use Dynasty\Users\Exceptions as DynastyUsersExceptions;
use Dynasty\Dogs\Exceptions as DynastyDogsExceptions;
use Dynasty\Breeds\Exceptions as DynastyBreedsExceptions;
use Dynasty\DogCharacteristics\Exceptions as DynastyDogCharacteristicsExceptions;

class ProfileController extends AuthorizedController {

    public function getIndex($dog)
    {
        // @TUTORIAL: complete find-dog-for-first-individual-challenge
        $this->currentUser->completeTutorialStage('find-dog-for-first-individual-challenge');

        if (is_null($dog->kennelGroup))
        {
            $previous = $next = null;
        }
        else
        {
            list($previous, $next) = $dog->kennelGroup->getNeighbors($dog);
        }

        $characteristicCategories = $this->getCategories($dog);
        $summarizedCategories = $this->getCategories($dog, true);

        // Grab all characteristic IDs that belong to the dog
        $dogCharacteristicIds = $dog->characteristics()->whereVisible()->lists('characteristic_id');

        // Always add -1 in it
        $dogCharacteristicIds[] = -1;

        // Grab all characteristic IDs that belong to the dog and are summarized
        $summarizedDogCharacteristicIds = $dog->characteristics()->whereInSummary()->lists('characteristic_id');

        // Get potential characteristics to be added to the summary
        $summaryCharacteristicCategories = CharacteristicCategory::select('characteristic_categories.*')
            ->join('characteristic_categories as parent', 'parent.id', '=', 'characteristic_categories.parent_category_id')
            ->whereHas('characteristics', function($query) use ($dogCharacteristicIds, $summarizedDogCharacteristicIds)
                {
                    if (empty($summarizedDogCharacteristicIds))
                    {
                        $query
                            ->whereActive()
                            ->whereVisible()
                            ->whereIn('id', $dogCharacteristicIds);
                    }
                    else
                    {
                        $query
                            ->whereActive()
                            ->whereVisible()
                            ->whereIn('id', $dogCharacteristicIds)
                            ->whereNotIn('id', $summarizedDogCharacteristicIds);
                    }
                })
            ->with(array(
                'parent', 
                'characteristics' => function($query) use ($dogCharacteristicIds, $summarizedDogCharacteristicIds)
                {
                    if (empty($summarizedDogCharacteristicIds))
                    {
                        $query
                            ->whereActive()
                            ->whereVisible()
                            ->whereIn('id', $dogCharacteristicIds)
                            ->orderBy('name', 'asc');
                    }
                    else
                    {
                        $query
                            ->whereActive()
                            ->whereVisible()
                            ->whereIn('id', $dogCharacteristicIds)
                            ->whereNotIn('id', $summarizedDogCharacteristicIds)
                            ->orderBy('name', 'asc');
                    }
                }))
            ->orderBy('parent.name', 'asc')
            ->orderBy('characteristic_categories.name', 'asc')
            ->get();

        $offspring = $dog->offspring()->orderBy('dogs.id', 'asc')->get();

        if ($dog->isComplete() and $dog->hasPedigree())
        {
            $pedigreeHeight = $this->currentUser->hasBreedersPrize() 
                ? Pedigree::MAX_HEIGHT
                : Config::get('game.dog.pedigree_display_limit');

            $pedigreeSlots = $dog->pedigree->displayData($pedigreeHeight);
        }
        else
        {
            $pedigreeSlots = [];
        }

        $recentContestEntries = $dog->contestEntries()
            ->select('contest_entries.*')
            ->join('contests', 'contests.id', '=', 'contest_entries.contest_id')
            ->orderBy('contests.run_on', 'desc')
            ->get();

        $symptoms = $dog->symptoms()->whereExpressed()
            ->with('characteristicSeveritySymptom.symptom')
            ->select('dog_characteristic_symptoms.*')
            ->join('characteristic_severity_symptoms', 'characteristic_severity_symptoms.id', '=', 'dog_characteristic_symptoms.characteristic_severity_symptom_id')
            ->join('symptoms', 'symptoms.id', '=', 'characteristic_severity_symptoms.symptom_id')
            ->orderBy('symptoms.name', 'asc')
            ->get();

        $breedableBitches = $this->currentUser->dogs()->whereAlive()->whereComplete()->whereFemale();

        if ($this->currentUser->ownsDog($dog) or $dog->isForImmediateStud())
        {
            $breedableBitches = $breedableBitches->whereInHeat()->whereUnworked();
        }

        $breedableBitches = $breedableBitches->get();

        $displayImageOptions = Dog::displayImageOptions();
        $lendingOptions      = Dog::lendingOptions();
        $changeableBreeds    = Breed::whereImportable()->whereActive()->orderBy('name', 'asc')->get();

        // Figure out the active tab
        $activeTab = Input::get('view');

        if (is_null($activeTab))
        {
            if ($dog->hasOwner() and $dog->owner->isUpgraded())
            {
                $activeTab = 'summary';
            }
            else if ( ! empty($characteristicCategories))
            {
                $activeTab = 'charcat'.$characteristicCategories[count($characteristicCategories) - 1]['id'];
            }
            else
            {
                $activeTab = 'contests';
            }
        }

        // Show the page
        return View::make('frontend/dog/profile/index', compact(
            'dog', 'previous', 'next', 'characteristicCategories', 'summarizedCategories', 'summaryCharacteristicCategories', 
            'offspring', 'pedigreeSlots', 'recentContestEntries', 'symptoms', 
            'displayImageOptions', 'lendingOptions', 'changeableBreeds', 'breedableBitches', 'activeTab'
        ));
    }

    protected function getCategories($dog, $inSummary = false)
    {
        $return = [];

        // Grab all the categories
        $categories = CharacteristicCategory::where('parent_category_id', null)->orderBy('name', 'desc')->get();

        foreach ($categories as $category)
        {
            $children = $category->children()
                ->orderBy('name', 'asc')
                ->get();

            $subcategories = [];
        
            foreach ($children as $subcategory)
            {
                if ($inSummary)
                {
                    $dogCharacteristics = $dog->characteristics()
                        ->with('characteristic')
                        ->whereHas('characteristic', function($query) use ($subcategory)
                            {
                                $query->whereVisible()->where('category_id', $subcategory->id);
                            })
                        ->whereInSummary()
                        ->whereVisible()
                        ->orderByCharacteristic()
                        ->get();
                }
                else
                {
                    $dogCharacteristics = $dog->characteristics()
                        ->with('characteristic')
                        ->whereHas('characteristic', function($query) use ($subcategory)
                            {
                                $query->whereVisible()->where('category_id', $subcategory->id);
                            })
                        ->whereVisible()
                        ->orderByCharacteristic()
                        ->get();
                }


                if ( ! $dogCharacteristics->isEmpty())
                {
                    $subcategories[] = array(
                        'id'   => $subcategory->id, 
                        'name' => $subcategory->name, 
                        'characteristics' => $dogCharacteristics, 
                        'parent_id'       => $category->id, 
                    );
                }
            }

            if ( ! empty($subcategories))
            {
                if ($category->isHealth())
                {
                    usort($subcategories, array($this, 'sortHealthCategories'));
                }
                else
                {
                    usort($subcategories, array($this, 'sortSubcategories'));
                }

                if ($inSummary)
                {
                    foreach($subcategories as $subcategory)
                    {
                        $return[] = array(
                            'id'          => $subcategory['id'], 
                            'parent_name' => $category->name, 
                            'name'        => $subcategory['name'], 
                            'characteristics' => $subcategory['characteristics'], 
                            'column' => null, 
                        );
                    }

                    $column = 12 / max(1, min(2, count($return)));

                    foreach($return as $index => $subcategory)
                    {
                        $return[$index]['column'] = $column;
                    }

                    usort($return, array($this, 'sortSubcategories'));
                }
                else
                {
                    $return[] = array(
                        'id'            => $category->id, 
                        'name'          => $category->name, 
                        'is_health'     => $category->isHealth(), 
                        'subcategories' => $subcategories, 
                        'column' => 12 / max(1, min(2, $children->count())), 
                        'active' => (( ! is_null($dog->owner) and $dog->owner->isUpgraded()) ? false : ($category->name == 'Appearance')), 
                    );
                }
            }
        }

        return $return;
    }

    protected function sortHealthCategories($a, $b)
    {
        $a_name = strtolower($a['name']);
        $b_name = strtolower($b['name']);

        if ($a_name == $b_name)
        {
            return 0;
        }

        return ($a_name < $b_name) ? -1 : 1;
    }

    protected function sortSubcategories($a, $b)
    {
        $totalA = $a['characteristics']->count();
        $totalB = $b['characteristics']->count();

        if ($totalA == $totalB)
        {
            return 0;
        }

        return ($totalA > $totalB) ? -1 : 1;
    }

    public function postRequestBreeding($dog)
    {
        try
        {
            $bitchId = Input::get('bitch_to_breed_with');

            // Make sure the dog exists
            if ( ! $dog->isAlive() or ! $dog->isMale() or ! $dog->hasOwner())
            {
                throw new DynastyDogsExceptions\DogNotFoundException;
            }

            if ( ! $this->currentUser->ownsDog($dog) and ! $dog->isForStud())
            {
                throw new DynastyDogsExceptions\NotUpForStudException;
            }

            // Make sure the dog is breedable
            if ( ! $dog->isBreedable())
            {
                throw new DynastyDogsExceptions\DogNotBreedableException;
            }

            // Make sure the bitch exists
            $bitch = $this->currentUser->dogs()
                ->whereComplete()
                ->whereAlive()
                ->whereFemale()
                ->where('dogs.id', $bitchId)
                ->first();

            if (is_null($bitch))
            {
                throw new DynastyDogsExceptions\BitchNotFoundException;
            }

            // Check if the bitch will be immediately bred
            $immediatelyBreedDogs = ($this->currentUser->ownsDog($dog) or $dog->isForImmediateStud());

            // Make sure the bitch is breedable
            if ( ! $bitch->isBreedable() or $bitch->hasRequestedBeginnersLuckWith($dog) or ($immediatelyBreedDogs and ! $bitch->canBeBredImmediately()))
            {
                throw new DynastyDogsExceptions\BitchNotBreedableException;
            }
            else if ( ! $immediatelyBreedDogs and $bitch->hasRequestedBreedingWith($dog))
            {
                throw new DynastyDogsExceptions\AlreadyRequestedBreedingFromDogException;
            }

            // All validation passed
            if ($immediatelyBreedDogs)
            {
                $dogFertility   = $dog->getFertility();
                $bitchFertility = $bitch->getFertility();

                $displayDogFertility   = ( ! is_null($dogFertility) and $dogFertility->isKnown());
                $displayBitchFertility = ( ! is_null($bitchFertility) and $bitchFertility->isKnown());
                $displayLitterChance   = ($displayDogFertility and $displayBitchFertility);

                $beginnersLuck = array(
                    'dog'   => $dog, 
                    'bitch' => $bitch, 
                    'dogFertility'   => ($displayDogFertility ? $dogFertility : null), 
                    'bitchFertility' => ($displayBitchFertility ? $bitchFertility : null), 
                    'litter_chance'  => ($displayLitterChance ? $bitch->calculateLitterChance($dog).'%' : null), 
                );

                return Redirect::route('dog/profile', $dog->id)->with('beginnersLuck', $beginnersLuck);
            }
            else
            {
                DB::transaction(function() use ($dog, $bitch)
                {
                    // Create a stud request
                    $request = StudRequest::create(array(
                        'stud_id'  => $dog->id, 
                        'bitch_id' => $bitch->id, 
                        'accepted' => false, 
                    ));

                    // Send a notification to the dog's owner
                    $params = array(
                        'user'     => $this->currentUser->nameplate(), 
                        'userUrl'  => URL::route('user/profile', $this->currentUser->id), 
                        'stud'     => $dog->nameplate(), 
                        'studUrl'  => URL::route('dog/profile', $dog->id), 
                        'bitch'    => $bitch->nameplate(), 
                        'bitchUrl' => URL::route('dog/profile', $bitch->id), 
                    );

                    $body = Lang::get('notifications/user.request_breeding.to_owner', array_map('htmlentities', array_dot($params)));
                    
                    $dog->owner->notify($body, UserNotification::TYPE_INFO);
                });

                $params = array(
                    'stud'  => $dog->nameplate(), 
                    'bitch' => $bitch->nameplate(), 
                );

                $success = Lang::get('forms/user.request_breeding.success', array_map('htmlentities', array_dot($params)));

                return Redirect::route('dog/profile', $dog->id)->with('success', $success);
            }
        }
        catch(DynastyDogsExceptions\DogNotFoundException $e)
        {
            $error = Lang::get('forms/user.breed_dogs.dog_not_found');
        }
        catch(DynastyDogsExceptions\DogNotBreedableException $e)
        {
            $error = Lang::get('forms/user.breed_dogs.dog_not_breedable');
        }
        catch(DynastyDogsExceptions\NotUpForStudException $e)
        {
            $error = Lang::get('forms/user.request_breeding.not_up_for_stud');
        }
        catch(DynastyDogsExceptions\BitchNotFoundException $e)
        {
            $error = Lang::get('forms/user.breed_dogs.bitch_not_found');
        }
        catch(DynastyDogsExceptions\BitchNotBreedableException $e)
        {
            $error = Lang::get('forms/user.breed_dogs.bitch_not_breedable');
        }
        catch(DynastyDogsExceptions\AlreadyRequestedBreedingFromDogException $e)
        {
            $error = Lang::get('forms/user.request_breeding.already_requested');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.breed_dogs.error');
        }

        return Redirect::route('dog/profile', $dog->id)->with('error', $error);
    }

    public function getBreedDogs($dog, $bitch)
    {
        try
        {
            // Validate the dog
            if (( ! $this->currentUser->ownsDog($dog) and ! $dog->isForImmediateStud()) or ! $dog->isMale() or ! $dog->isBreedable())
            {
                throw new DynastyDogsExceptions\DogNotBreedableException;
            }

            // Validate the bitch
            if ( ! $this->currentUser->ownsDog($bitch) or ! $bitch->isFemale() or ! $bitch->canBeBredImmediately() or $bitch->hasRequestedBeginnersLuckWithDogOtherThan($dog))
            {
                throw new DynastyDogsExceptions\BitchNotBreedableException;
            }

            DB::transaction(function() use ($dog, $bitch)
            {
                // Breed the dogs
                $this->currentUser->breedDogs($dog, $bitch);
            });

            $params = array(
                'dog'   => $dog->nameplate(), 
                'bitch' => $bitch->nameplate(), 
            );

            $success = Lang::get('forms/user.breed_dogs.success', array_map('htmlentities', array_dot($params)));

            return Redirect::route('dog/profile', $dog->id)->with('success', $success);
        }
        catch(DynastyDogsExceptions\DogNotBreedableException $e)
        {
            $error = Lang::get('forms/user.breed_dogs.dog_not_breedable');
        }
        catch(DynastyDogsExceptions\BitchNotBreedableException $e)
        {
            $error = Lang::get('forms/user.breed_dogs.bitch_not_breedable');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.breed_dogs.error');
        }

        return Redirect::route('dog/profile', $dog->id)->with('error', $error);
    }

    public function postChangeName($dog)
    {
        try
        {
            if ( ! $this->currentUser->ownsDog($dog))
            {
                throw new DynastyUsersExceptions\DoesNotOwnDogException;
            }

            if ( ! $dog->isAlive())
            {
                throw new DynastyDogsExceptions\DeceasedException;
            }

            // Declare the rules for the form validation
            $rules = array(
                'new_name' => 'required|max:32', 
            );

            // Create a new validator instance from our validation rules
            $validator = Validator::make(Input::all(), $rules);

            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::route('dog/profile', $dog->id)->withInput()->with('error', $validator->errors()->first());
            }

            // Save the dog
            $dog->name = Input::get('new_name');
            $dog->save();

            $success = Lang::get('forms/dog.change_name.success');

            return Redirect::route('dog/profile', $dog->id)->with('success', $success);
        }
        catch(DynastyUsersExceptions\DoesNotOwnDogException $e)
        {
            $error = Lang::get('forms/dog.change_name.wrong_owner');
        }
        catch(DynastyDogsExceptions\DeceasedException $e)
        {
            $error = Lang::get('forms/dog.change_name.deceased');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/dog.change_name.error');
        }

        return Redirect::route('dog/profile', $dog->id)->with('error', $error);
    }

    public function postSaveNotes($dog)
    {
        try
        {
            if ( ! $this->currentUser->ownsDog($dog))
            {
                throw new DynastyUsersExceptions\DoesNotOwnDogException;
            }

            if ( ! $dog->isAlive())
            {
                throw new DynastyDogsExceptions\DeceasedException;
            }

            // Declare the rules for the form validation
            $rules = array(
                'notes' => 'required|max:10000', 
            );

            // Create a new validator instance from our validation rules
            $validator = Validator::make(Input::all(), $rules);

            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::route('dog/profile', $dog->id)->withInput()->with('error', $validator->errors()->first());
            }

            // Save the dog
            $dog->notes = Input::get('notes');
            $dog->save();

            $success = Lang::get('forms/dog.save_notes.success');

            return Redirect::route('dog/profile', $dog->id)->with('success', $success);
        }
        catch(DynastyUsersExceptions\DoesNotOwnDogException $e)
        {
            $error = Lang::get('forms/dog.save_notes.wrong_owner');
        }
        catch(DynastyDogsExceptions\DeceasedException $e)
        {
            $error = Lang::get('forms/dog.save_notes.deceased');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/dog.save_notes.error');
        }

        return Redirect::route('dog/profile', $dog->id)->with('error', $error);
    }

    public function postChangeBreed($dog)
    {
        try
        {
            if ( ! $this->currentUser->ownsDog($dog))
            {
                throw new DynastyUsersExceptions\DoesNotOwnDogException;
            }

            // The dog must be complete
            if ( ! $dog->isComplete())
            {
                throw new DynastyDogsExceptions\IncompleteException;
            }

            // The dog must be alive
            if ( ! $dog->isAlive())
            {
                throw new DynastyDogsExceptions\DeceasedException;
            }

            // The dog must not me an originator of its current breed
            if ($dog->isBreedOriginator())
            {
                throw new DynastyDogsExceptions\BreedOriginatorException;
            }

            // Declare the rules for the form validation
            $rules = array(
                'new_breed' => 'required|exists:breeds,id,active,1', 
            );

            // Create a new validator instance from our validation rules
            $validator = Validator::make(Input::all(), $rules);

            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::route('dog/profile', $dog->id)->withInput()->with('error', $validator->errors()->first());
            }

            // Make sure the new breed is different from the dog's current breed
            $newBreed = Breed::find(Input::get('new_breed'));

            if ($newBreed->id == $dog->breed_id)
            {
                throw new DynastyDogsExceptions\SameBreedException;
            }

            // Start the transaction
            DB::transaction(function() use ($dog, $newBreed)
            {
                // Unregistered dogs and dogs who have not had their breeds changed previously can be registered for free
                if ($dog->hasBreed() and $dog->hasHadBreedChanged())
                {
                    $costToChangeBreed = Config::get('game.dog.change_breed_cost');

                    // Make sure the user has enough credits
                    if ( ! $this->currentUser->canAffordCredits($costToChangeBreed))
                    {
                        throw new DynastyUsersExceptions\NotEnoughCreditsException;
                    }

                    // Pay for it now
                    $this->currentUser->credits -= $costToChangeBreed;
                    $this->currentUser->save();

                    // Log the transaction
                    $this->currentUser->logCreditTransaction(UserCreditTransaction::DOG_CHANGE_BREED, 1, $costToChangeBreed, $costToChangeBreed, array('id' => $dog->id));
                }

                // Check if the dog meets the new breed's requirements
                $failedCharacteristics = $newBreed->checkDog($dog);

                if ( ! empty($failedCharacteristics))
                {
                    $failedCharacteristicNames = [];

                    foreach($failedCharacteristics as $failedCharacteristic)
                    {
                        $failedCharacteristicNames[] = $failedCharacteristic->name;
                    }

                    throw new DynastyBreedsExceptions\DogDoesNotMeetRequirementsException(implode(', ', $failedCharacteristicNames));
                }

                // Give the dog the new breed
                $dog->breed_id = $newBreed->id;
                $dog->breed_changed = true;
                $dog->save();
            });

            $success = Lang::get('forms/dog.change_breed.success');

            return Redirect::route('dog/profile', $dog->id)->with('success', $success);
        }
        catch(DynastyUsersExceptions\DoesNotOwnDogException $e)
        {
            $error = Lang::get('forms/dog.change_breed.wrong_owner');
        }
        catch(DynastyDogsExceptions\IncompleteException $e)
        {
            $error = Lang::get('forms/dog.change_breed.incomplete');
        }
        catch(DynastyDogsExceptions\DeceasedException $e)
        {
            $error = Lang::get('forms/dog.change_breed.deceased');
        }
        catch(DynastyDogsExceptions\BreedOriginatorException $e)
        {
            $error = Lang::get('forms/dog.change_breed.breed_originator');
        }
        catch(DynastyDogsExceptions\SameBreedException $e)
        {
            $error = Lang::get('forms/dog.change_breed.same_breed');
        }
        catch(DynastyUsersExceptions\NotEnoughCreditsException $e)
        {
            $error = Lang::get('forms/dog.change_breed.not_enough_credits');
        }
        catch(DynastyBreedsExceptions\DogDoesNotMeetRequirementsException $e)
        {
            $params = array(
                'failedCharacteristics' => $e->getMessage(), 
            );

            $error = Lang::get('forms/dog.change_breed.breed_requirements_unmet', array_map('htmlentities', array_dot($params)));
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/dog.change_breed.error');
        }

        return Redirect::route('dog/profile', $dog->id)->with('error', $error);
    }

    public function postChangeImage($dog)
    {
        try
        {
            if ( ! $this->currentUser->ownsDog($dog))
            {
                throw new DynastyUsersExceptions\DoesNotOwnDogException;
            }

            if ( ! $dog->isAlive())
            {
                throw new DynastyDogsExceptions\DeceasedException;
            }

            // Declare the rules for the form validation
            $rules = array(
                'display_image_option' => 'required|in:'.implode(',', array_keys(Dog::displayImageOptions())), 
                'image_url' => 'max:255|image_url:png,gif,jpeg|image_url_size:<=700,<=500', 
            );

            // Create a new validator instance from our validation rules
            $validator = Validator::make(Input::all(), $rules);

            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::route('dog/profile', $dog->id)->withInput()->with('error', $validator->errors()->first());
            }

            // Save the dog
            $dog->display_image = Input::get('display_image_option');
            $dog->image_url     = Input::get('image_url');
            $dog->save();

            $success = Lang::get('forms/dog.change_image.success');

            return Redirect::route('dog/profile', $dog->id)->with('success', $success);
        }
        catch(DynastyUsersExceptions\DoesNotOwnDogException $e)
        {
            $error = Lang::get('forms/dog.change_image.wrong_owner');
        }
        catch(DynastyDogsExceptions\DeceasedException $e)
        {
            $error = Lang::get('forms/dog.change_image.deceased');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/dog.change_image.error');
        }

        return Redirect::route('dog/profile', $dog->id)->with('error', $error);
    }

    public function getAddPrefix($dog)
    {
        try
        {
            if ( ! $this->currentUser->ownsDog($dog))
            {
                throw new DynastyUsersExceptions\DoesNotOwnDogException;
            }

            if ( ! $dog->isAlive())
            {
                throw new DynastyDogsExceptions\DeceasedException;
            }

            // A dog can only ever get prefixd once
            if ($dog->hasKennelPrefix())
            {
                throw new DynastyDogsExceptions\AlreadyPrefixedException;
            }

            // Make sure the user has specified a kennel prefix
            if ( ! $this->currentUser->hasKennelPrefix())
            {
                throw new DynastyUsersExceptions\MissingKennelPrefixException;
            }

            $costToAddPrefix = Config::get('game.dog.prefix_cost');

            // Make sure the user has enough credits
            if ( ! $this->currentUser->canAffordCredits($costToAddPrefix))
            {
                throw new DynastyUsersExceptions\NotEnoughCreditsException;
            }

            DB::transaction(function() use ($dog,$costToAddPrefix)
            {
                // Pay for it now
                $this->currentUser->credits -= $costToAddPrefix;
                $this->currentUser->save();

                // Log the transaction
                $this->currentUser->logCreditTransaction(UserCreditTransaction::DOG_PREFIX, 1, $costToAddPrefix, $costToAddPrefix, array('id' => $dog->id));

                // Prefix the dog
                $dog->kennel_prefix = $this->currentUser->kennel_prefix;
                $dog->save();
            });

            $success = Lang::get('forms/dog.add_prefix.success');

            return Redirect::route('dog/profile', $dog->id)->with('success', $success);
        }
        catch(DynastyUsersExceptions\DoesNotOwnDogException $e)
        {
            $error = Lang::get('forms/dog.add_prefix.wrong_owner');
        }
        catch(DynastyDogsExceptions\DeceasedException $e)
        {
            $error = Lang::get('forms/dog.add_prefix.deceased');
        }
        catch(DynastyDogsExceptions\AlreadyPrefixedException $e)
        {
            $error = Lang::get('forms/dog.add_prefix.already_prefixed');
        }
        catch(DynastyUsersExceptions\MissingKennelPrefixException $e)
        {
            $error = Lang::get('forms/dog.add_prefix.no_prefix');
        }
        catch(DynastyUsersExceptions\NotEnoughCreditsException $e)
        {
            $error = Lang::get('forms/dog.add_prefix.not_enough_credits');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/dog.add_prefix.error');
        }

        return Redirect::route('dog/profile', $dog->id)->with('error', $error);
    }

    public function getPetHome($dog)
    {
        try
        {
            if ( ! $this->currentUser->hasCompletedTutorialStage('first-breeding'))
            {
                throw new DynastyUsersExceptions\IncompleteTutorialException;
            }

            if ( ! $this->currentUser->ownsDog($dog))
            {
                throw new DynastyUsersExceptions\DoesNotOwnDogException;
            }

            if ( ! $dog->isAlive())
            {
                throw new DynastyDogsExceptions\DeceasedException;
            }

            $deleted = false;

            DB::transaction(function() use ($dog, &$deleted)
            {
                $deleted = $dog->petHome();
            });

            $success = Lang::get('forms/dog.pet_home.success');

            return $deleted
                ? Redirect::route('user/kennel')->with('success', $success)
                : Redirect::route('dog/profile', $dog->id)->with('success', $success);
        }
        catch(DynastyUsersExceptions\IncompleteTutorialException $e)
        {
            $error = Lang::get('forms/dog.pet_home.incomplete_tutorial');
        }
        catch(DynastyUsersExceptions\DoesNotOwnDogException $e)
        {
            $error = Lang::get('forms/dog.pet_home.wrong_owner');
        }
        catch(DynastyDogsExceptions\DeceasedException $e)
        {
            $error = Lang::get('forms/dog.pet_home.deceased');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/dog.pet_home.error');
        }

        return Redirect::route('dog/profile', $dog->id)->with('error', $error);
    }

    public function postLend($dog)
    {
        try
        {
            if ( ! $this->currentUser->hasCompletedTutorialStage('first-breeding'))
            {
                throw new DynastyUsersExceptions\IncompleteTutorialException;
            }

            if ( ! $this->currentUser->ownsDog($dog))
            {
                throw new DynastyUsersExceptions\DoesNotOwnDogException;
            }

            if (Input::get('user') == $this->currentUser->id)
            {
                throw new DynastyUsersExceptions\AlreadyOwnsDogException;
            }

            if ( ! $dog->isAlive())
            {
                throw new DynastyDogsExceptions\DeceasedException;
            }

            if ($dog->isPendingOwnership())
            {
                throw new DynastyDogsExceptions\OwnershipPendingException;
            }

            // Declare the rules for the form validation
            $rules = array(
                'user' => 'required|exists:users,id', 
                'length_of_lending_period' => 'required|in:permanent,one_turn,five_turns,fifteen_turns,tonight,tomorrow_night,three_nights_from_now', 
            );

            // Create a new validator instance from our validation rules
            $validator = Validator::make(Input::all(), $rules);

            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::route('dog/profile', $dog->id)->withInput()->with('error', $validator->errors()->first());
            }

            // Get the receiver
            $receiver = User::find(Input::get('user'));

            DB::transaction(function() use ($dog, $receiver)
            {
                $permanent = false;
                $turnLeft  = null;
                $returnAt  = null;

                switch (Input::get('length_of_lending_period'))
                {
                    case 'permanent':
                        $permanent = true;
                        break;

                    case 'one_turn':
                        $turnLeft = 1;
                        break;

                    case 'five_turns':
                        $turnLeft = 5;
                        break;

                    case 'fifteen_turns':
                        $turns = 15;
                        break;

                    case 'tonight':
                        $returnAt = Carbon::today();
                        break;

                    case 'tomorrow_night':
                        $returnAt = Carbon::today()->addDay();
                        break;

                    case 'three_nights_from_now':
                        $returnAt = Carbon::today()->addDays(3);
                        break;
                    
                    default:
                        break;
                }

                // Create the request
                $request = LendRequest::create(array(
                    'dog_id'      => $dog->id, 
                    'sender_id'   => $this->currentUser->id, 
                    'receiver_id' => $receiver->id, 
                    'permanent'   => $permanent, 
                    'turns_left'  => $turnLeft, 
                    'return_at'   => $returnAt, 
                ));

                // Gather the notification parameters
                $params = array(
                    'sender'    => $this->currentUser->nameplate(), 
                    'receiver'  => $receiver->nameplate(), 
                    'dog'       => $dog->nameplate(), 
                    'dogUrl'    => URL::route('dog/profile', $dog->id), 
                    'acceptUrl' => URL::route('dog/lend/accept', $request->id), 
                    'rejectUrl' => URL::route('dog/lend/reject', $request->id), 
                    'revokeUrl' => URL::route('dog/lend/revoke', $request->id), 
                    'returnPeriod' => '', 
                );

                if ($request->isTemporary())
                {
                    $params['returnPeriod'] = $request->isTimeSensitive() 
                        ? ' until '.$request->return_at->format('F j, Y').' 11:59 PM'
                        : ' for '.Dynasty::turns($request->turns_left);
                }

                // Notify the potential new owner
                $body = Lang::get('notifications/user.send_lend_request.to_receiver', array_map('htmlentities', array_dot($params)));

                $receiver->notify($body, UserNotification::TYPE_INFO);

                // Send a notification to the dog's owner as well
                $body = Lang::get('notifications/user.send_lend_request.to_sender', array_map('htmlentities', array_dot($params)));

                $this->currentUser->notify($body, UserNotification::TYPE_INFO, false, false);
            });

            $params = array(
                'receiver' => $receiver->nameplate(), 
            );

            $success = Lang::get('forms/user.send_lend_request.success', array_map('htmlentities', array_dot($params)));

            return Redirect::route('dog/profile', $dog->id)->with('success', $success);
        }
        catch(DynastyUsersExceptions\IncompleteTutorialException $e)
        {
            $error = Lang::get('forms/user.send_lend_request.incomplete_tutorial');
        }
        catch(DynastyUsersExceptions\DoesNotOwnDogException $e)
        {
            $error = Lang::get('forms/user.send_lend_request.wrong_owner');
        }
        catch(DynastyUsersExceptions\AlreadyOwnsDogException $e)
        {
            $error = Lang::get('forms/user.send_lend_request.same_user');
        }
        catch(DynastyDogsExceptions\DeceasedException $e)
        {
            $error = Lang::get('forms/user.send_lend_request.deceased');
        }
        catch(DynastyDogsExceptions\OwnershipPendingException $e)
        {
            $error = Lang::get('forms/user.send_lend_request.ownership_pending');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.send_lend_request.error');
        }

        return Redirect::route('dog/profile', $dog->id)->with('error', $error);
    }

    public function getRevokeLendRequest($lendRequest)
    {
        try
        {
            // Make sure the request belongs to this user and the receiver hasn't already accepted the request and owns the dog
            if ($this->currentUser->id != $lendRequest->sender_id or $this->currentUser->id != $lendRequest->dog->owner_id)
            {
                App::abort('404', 'Lend request does not exist!');
            }

            // Delete the request
            $lendRequest->delete();

            $success = Lang::get('forms/user.revoke_lend_request.success');

            return Redirect::route('dog/profile', $lendRequest->dog->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.revoke_lend_request.error');
        }

        return Redirect::route('dog/profile', $lendRequest->dog->id)->with('error', $error);
    }

    public function getRejectLendRequest($lendRequest)
    {
        try
        {
            // Make sure the request  is being sent to this user and the user doesn't already have the dog
            if ($this->currentUser->id != $lendRequest->receiver_id or $this->currentUser->id == $lendRequest->dog->owner_id)
            {
                App::abort('404', 'Lend request does not exist!');
            }

            DB::transaction(function() use ($lendRequest)
            {
                // Send a notification to the sender
                $params = array(
                    'user'    => $this->currentUser->nameplate(), 
                    'userUrl' => URL::route('user/profile', $this->currentUser->id), 
                    'dog'     => $lendRequest->dog->nameplate(), 
                    'dogUrl'  => URL::route('dog/profile', $lendRequest->dog->id), 
                );

                $body = Lang::get('notifications/user.reject_lend_request.to_sender', array_map('htmlentities', array_dot($params)));
                
                $lendRequest->sender->notify($body, UserNotification::TYPE_DANGER);

                // Delete the request
                $lendRequest->delete();
            });

            $success = Lang::get('forms/user.reject_lend_request.success');

            return Redirect::route('dog/profile', $lendRequest->dog->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.reject_lend_request.error');
        }

        return Redirect::route('dog/profile', $lendRequest->dog->id)->with('error', $error);
    }

    public function getAcceptLendRequest($lendRequest)
    {
        try
        {
            // Make sure the request  is being sent to this user and the user doesn't already have the dog
            if ($this->currentUser->id != $lendRequest->receiver_id or $this->currentUser->id == $lendRequest->dog->owner_id)
            {
                App::abort('404', 'Lend request does not exist!');
            }

            DB::transaction(function() use ($lendRequest)
            {
                // Transfer the ownership
                $lendRequest->dog->owner_id = $this->currentUser->id;

                // Put the dog in its new kennel
                $newKennelGroup = $this->currentUser->kennelGroups()->whereNotCemetery()->first();

                $lendRequest->dog->kennel_group_id = is_null($newKennelGroup)
                    ? null
                    : $newKennelGroup->id;

                // Save the dog
                $lendRequest->dog->save();

                // Send a notification to the sender
                $params = array(
                    'user'    => $this->currentUser->nameplate(), 
                    'userUrl' => URL::route('user/profile', $this->currentUser->id), 
                    'dog'     => $lendRequest->dog->nameplate(), 
                    'dogUrl'  => URL::route('dog/profile', $lendRequest->dog->id), 
                );

                $body = Lang::get('notifications/user.accept_lend_request.to_sender', array_map('htmlentities', array_dot($params)));
                
                $lendRequest->sender->notify($body, UserNotification::TYPE_SUCCESS);

                // The request can be deleted if this is a permenent transfer
                if ($lendRequest->isPermanent())
                {
                    $lendRequest->delete();
                }
            });

            $success = Lang::get('forms/user.accept_lend_request.success');

            return Redirect::route('dog/profile', $lendRequest->dog->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.accept_lend_request.error');
        }

        return Redirect::route('dog/profile', $lendRequest->dog->id)->with('error', $error);
    }

    public function getReturnLendRequest($lendRequest)
    {
        try
        {
            // Make sure the request  is being sent to this user and the user doesn't already have the dog
            if ($this->currentUser->id != $lendRequest->receiver_id or $this->currentUser->id != $lendRequest->dog->owner_id)
            {
                App::abort('404', 'Lend request does not exist!');
            }

            DB::transaction(function() use ($lendRequest)
            {
                // Transfer the ownership
                $lendRequest->dog->owner_id = $lendRequest->sender->id;

                // Put the dog in its old kennel
                $newKennelGroup = $lendRequest->sender->kennelGroups()->whereNotCemetery()->first();

                $lendRequest->dog->kennel_group_id = is_null($newKennelGroup)
                    ? null
                    : $newKennelGroup->id;

                // Save the dog
                $lendRequest->dog->save();

                // Send a notification to the sender
                $params = array(
                    'user'    => $this->currentUser->nameplate(), 
                    'userUrl' => URL::route('user/profile', $this->currentUser->id), 
                    'dog'     => $lendRequest->dog->nameplate(), 
                    'dogUrl'  => URL::route('dog/profile', $lendRequest->dog->id), 
                );

                $body = Lang::get('notifications/user.return_lend_request.to_sender', array_map('htmlentities', array_dot($params)));
                
                $lendRequest->sender->notify($body, UserNotification::TYPE_WARNING);

                // Delete the request
                $lendRequest->delete();
            });

            $success = Lang::get('forms/user.return_lend_request.success');

            return Redirect::route('dog/profile', $lendRequest->dog->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.return_lend_request.error');
        }

        return Redirect::route('dog/profile', $lendRequest->dog->id)->with('error', $error);
    }

    public function postManageStudding($dog)
    {
        try
        {
            if ( ! $this->currentUser->ownsDog($dog))
            {
                throw new DynastyUsersExceptions\DoesNotOwnDogException;
            }

            if ( ! $dog->isAlive())
            {
                throw new DynastyDogsExceptions\DeceasedException;
            }

            if ( ! $dog->isComplete())
            {
                throw new DynastyDogsExceptions\IncompleteException;
            }

            if ( ! $dog->isMale())
            {
                throw new DynastyDogsExceptions\NotMaleException;
            }

            if ( ! $dog->isBreedable())
            {
                throw new DynastyDogsExceptions\NotBreedableException;
            }

            // Declare the rules for the form validation
            $rules = array(
                'up_for_stud' => 'required|in:yes,no', 
                'stud_type'   => 'required_if:up_for_stud,yes|in:immediate,request', 
            );

            // Create a new validator instance from our validation rules
            $validator = Validator::make(Input::all(), $rules);

            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::route('dog/profile', ['dog' => $dog->id, 'view' => 'studding'])->withInput()->with('error', $validator->errors()->first());
            }

            // Save the dog
            $dog->studding = (Input::get('up_for_stud') == 'no')
                ? Dog::STUDDING_NONE
                : (Input::get('stud_type') == 'immediate' ? Dog::STUDDING_IMMEDIATE : Dog::STUDDING_REQUEST);

            $dog->save();

            $success = Lang::get('forms/dog.manage_studding.success');

            return Redirect::route('dog/profile', ['dog' => $dog->id, 'view' => 'studding'])->with('success', $success);
        }
        catch(DynastyUsersExceptions\DoesNotOwnDogException $e)
        {
            $error = Lang::get('forms/dog.manage_studding.wrong_owner');
        }
        catch(DynastyDogsExceptions\DeceasedException $e)
        {
            $error = Lang::get('forms/dog.manage_studding.deceased');
        }
        catch(DynastyDogsExceptions\IncompleteException $e)
        {
            $error = Lang::get('forms/dog.manage_studding.incomplete');
        }
        catch(DynastyDogsExceptions\NotMaleException $e)
        {
            $error = Lang::get('forms/dog.manage_studding.not_male');
        }
        catch(DynastyDogsExceptions\NotBreedableException $e)
        {
            $error = Lang::get('forms/dog.manage_studding.not_breedable');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/dog.manage_studding.error');
        }

        return Redirect::route('dog/profile', ['dog' => $dog->id, 'view' => 'studding'])->with('error', $error);
    }

    public function postAddToSummary($dog)
    {
        try
        {
            // Make sure the user is upgraded
            if ( ! $this->currentUser->isUpgraded())
            {
                throw new DynastyUsersExceptions\NotUpgradedException;
            }

            $characteristicIds = (array) Input::get('characteristics_to_summarize');

            if (empty($characteristicIds))
            {
                throw new DynastyExceptions\NoneSelectedException;
            }

            // Make sure the user owns the dog
            if ( ! $this->currentUser->ownsDog($dog))
            {
                throw new DynastyUsersExceptions\DoesNotOwnDogException;
            }

            // Make sure the dog is alive
            if ( ! $dog->isAlive())
            {
                throw new DynastyDogsExceptions\DeceasedException;
            }

            // Make sure the dog is compelte
            if ( ! $dog->isComplete())
            {
                throw new DynastyDogsExceptions\IncompleteException;
            }

            // Make sure all of the characteristics selected are valid for this dog
            $dogCharacteristicIds = $dog->characteristics()
                ->whereVisible()
                ->whereIn('characteristic_id', $characteristicIds)
                ->whereHas('characteristic', function($query)
                {
                    $query->whereActive()->whereVisible();
                })
                ->lists('id');

            if (empty($dogCharacteristicIds))
            {
                throw new DynastyDogCharacteristicsExceptions\NotFoundException;
            }

            // Put them all in the summary
            DB::table('dog_characteristics')
                ->whereIn('id', $dogCharacteristicIds)
                ->update(array(
                    'in_summary' => true, 
                ));

            $success = Lang::get('forms/dog.summarize_characteristics.success');

            return Redirect::route('dog/profile', $dog->id)->with('success', $success);
        }
        catch(DynastyUsersExceptions\NotUpgradedException $e)
        {
            $error = Lang::get('forms/dog.summarize_characteristics.not_upgraded');
        }
        catch(DynastyExceptions\NoneSelectedException $e)
        {
            $error = Lang::get('forms/dog.summarize_characteristics.none_selected');
        }
        catch(DynastyUsersExceptions\DoesNotOwnDogException $e)
        {
            $error = Lang::get('forms/dog.summarize_characteristics.wrong_owner');
        }
        catch(DynastyDogsExceptions\DeceasedException $e)
        {
            $error = Lang::get('forms/dog.summarize_characteristics.deceased');
        }
        catch(DynastyDogsExceptions\IncompleteException $e)
        {
            $error = Lang::get('forms/dog.summarize_characteristics.incomplete');
        }
        catch(DynastyDogCharacteristicsExceptions\NotFoundException $e)
        {
            $error = Lang::get('forms/dog.summarize_characteristics.invalid_characteristic');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/dog.summarize_characteristics.error');
        }

        return Redirect::route('dog/profile', $dog->id)->with('error', $error);
    }

    public function getRemoveFromSummary($dogCharacteristic)
    {
        try
        {
            // Grab the dog
            $dog = $dogCharacteristic->dog;

            // Make sure the user is upgraded
            if ( ! $this->currentUser->isUpgraded())
            {
                throw new DynastyUsersExceptions\NotUpgradedException;
            }

            // Make sure the user owns the dog
            if ( ! $this->currentUser->ownsDog($dog))
            {
                throw new DynastyUsersExceptions\DoesNotOwnDogException;
            }

            // Make sure the dog is alive
            if ( ! $dog->isAlive())
            {
                throw new DynastyDogsExceptions\DeceasedException;
            }

            // Make sure the dog is compelte
            if ( ! $dog->isComplete())
            {
                throw new DynastyDogsExceptions\IncompleteException;
            }

            // Make sure the dog characteristic is in the summary
            if ( ! $dogCharacteristic->isInSummary())
            {
                throw new DynastyDogCharacteristicsExceptions\NotInSummaryException;
            }

            // Remove the characteristic from the summary
            $dogCharacteristic->in_summary = false;
            $dogCharacteristic->save();

            $success = Lang::get('forms/dog.remove_summarized_characteristic.success');

            return Redirect::route('dog/profile', $dog->id)->with('success', $success);
        }
        catch(DynastyUsersExceptions\NotUpgradedException $e)
        {
            $error = Lang::get('forms/dog.remove_summarized_characteristic.not_upgraded');
        }
        catch(DynastyUsersExceptions\DoesNotOwnDogException $e)
        {
            $error = Lang::get('forms/dog.remove_summarized_characteristic.wrong_owner');
        }
        catch(DynastyDogsExceptions\DeceasedException $e)
        {
            $error = Lang::get('forms/dog.remove_summarized_characteristic.deceased');
        }
        catch(DynastyDogsExceptions\IncompleteException $e)
        {
            $error = Lang::get('forms/dog.remove_summarized_characteristic.incomplete');
        }
        catch(DynastyDogCharacteristicsExceptions\NotInSummaryException $e)
        {
            $error = Lang::get('forms/dog.remove_summarized_characteristic.not_in_summary');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/dog.remove_summarized_characteristic.error');
        }

        return Redirect::route('dog/profile', $dog->id)->with('error', $error);
    }

    public function getComplete($dog)
    {
        try
        {
            if ( ! $this->currentUser->ownsDog($dog))
            {
                throw new DynastyUsersExceptions\DoesNotOwnDogException;
            }

            if ( ! $dog->isAlive())
            {
                throw new DynastyDogsExceptions\DeceasedException;
            }

            // The dog must NOT be complete
            if ($dog->isComplete())
            {
                throw new DynastyDogsExceptions\CompleteException;
            }

            DB::transaction(function() use ($dog)
            {
                // Complete the dog
                $dog->complete();

                // Check if the user is on this tutorial stage
                if ($this->currentUser->isOnTutorialStage('first-litter'))
                {
                    // Get the turns received for completing the tutorial
                    $turnsReceived = Config::get('game.tutorial.completion_turns');

                    // @TUTORIAL: complete first-litter
                    $this->currentUser->advanceTutorial(array('completion_turns' => $turnsReceived), false, true);

                    // Give the turns
                    $this->currentUser->turns += $turnsReceived;
                    $this->currentUser->save();
                }
            });

            $success = Lang::get('forms/dog.complete.success');

            return Redirect::route('dog/profile', $dog->id)->with('success', $success);
        }
        catch(DynastyUsersExceptions\DoesNotOwnDogException $e)
        {
            $error = Lang::get('forms/dog.complete.wrong_owner');
        }
        catch(DynastyDogsExceptions\DeceasedException $e)
        {
            $error = Lang::get('forms/dog.complete.deceased');
        }
        catch(DynastyDogsExceptions\CompleteException $e)
        {
            $error = Lang::get('forms/dog.complete.already_completed');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/dog.complete.error');
        }

        return Redirect::route('dog/profile', $dog->id)->with('error', $error);
    }

}
