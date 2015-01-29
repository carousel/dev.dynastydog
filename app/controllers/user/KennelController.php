<?php namespace Controllers\User;

use AuthorizedController;
use View;
use Input;
use Lang;
use Redirect;
use Validator;
use DB;
use URL;
use App;

use KennelGroup;
use CharacteristicTest;
use Characteristic;
use Dog;
use UserNotification;

use Exception;
use Dynasty\Dogs\Exceptions as DynastyDogsExceptions;
use Dynasty\Users\Exceptions as DynastyUsersExceptions;
use Dynasty\CharacteristicTests\Exceptions as DynastyCharacteristicTestsExceptions;
use Dynasty\Characteristics\Exceptions as DynastyCharacteristicsExceptions;
use Dynasty\KennelGroups\Exceptions as DynastyKennelGroupsExceptions;
use Dynasty\StudRequests\Exceptions as DynastyStudRequestsExceptions;

class KennelController extends AuthorizedController {

    protected $layout = 'frontend/layouts/kennel';

    public function getIndex($kennel = null)
    {
        // @TUTORIAL: complete start-first-individual-challenge
        $this->currentUser->completeTutorialStage('start-first-individual-challenge');

        // @TUTORIAL: complete first-import-dog
        $this->currentUser->completeTutorialStage('first-import-dog');

        if (is_null($kennel) or $kennel->id == $this->currentUser->id)
        {
            $kennel = $this->currentUser;
        }

        // All kennel groups regardless if they have dogs or not
        $kennelGroups = $kennel->kennelGroups()
            ->select('kennel_groups.*', DB::raw("IF(kennel_groups.type_id = ".KennelGroup::CEMETERY.", 1, 0) as cemetery"))
            ->orderBy('cemetery', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $actionKennelGroups = $kennelGroups->filter(function($item)
            {
                return $item->isNotCemetery();
            });

        $actionKennelGroupsWithDogs = $kennel->kennelGroups()
            ->whereNotCemetery()
            ->has('dogs')
            ->with(array(
                'dogs' => function($query)
                {
                    $query->orderBy('id', 'asc');
                }))
            ->orderBy('id', 'asc')
            ->get();

        // Get all breedable dogs
        $breedableDogs = $kennel->dogs()
            ->whereAlive()->whereMale()
            ->get()
            ->filter(function($item)
            {
                return $item->canBeBredImmediately();
            });

        // Get all breedable bitches
        $breedableBitches = $kennel->dogs()
            ->whereAlive()->whereFemale()
            ->get()
	    ->filter(function($item)
	    {
		return $item->canBeBredImmediately();
	    });
	//dd($breedableBitches);

        // Get all received stud requests
        $receivedStudRequests = $kennel->receivedStudRequests()->whereWaiting()->orderBy('created_at', 'asc')->get();

        // Get all sent stud requests
        $sentStudRequests = $kennel->sentStudRequests()->orderBy('created_at', 'asc')->get();

        // Grab the characteristic tests that are active
        $characteristicTests = CharacteristicTest::whereActive()
            ->whereHas('characteristic', function($query)
            {
                $query->whereVisible()->whereActive();
            })
            ->orderByCharacteristic()
            ->get();

        // Grab comparison characteristics
        $characteristics = Characteristic::whereActive()->whereVisible()->orderBy('name', 'asc')->get();

        // Show the page
        return View::make('frontend/user/kennel/index', compact(
            'kennel', 'kennelGroups', 'actionKennelGroups', 'actionKennelGroupsWithDogs', 
            'breedableDogs', 'breedableBitches', 'receivedStudRequests', 'sentStudRequests', 
            'characteristicTests', 'characteristics'
        ));
    }

    public function postTestDogs()
    {
        try
        {
            if ( ! $this->currentUser->isUpgraded())
            {
                throw new DynastyUsersExceptions\NotUpgradedException;
            }

            $characteristicTestId = Input::get('test');

            // Make sure the test exists and is active
            $characteristicTest = CharacteristicTest::find($characteristicTestId);

            if (is_null($characteristicTest))
            {
                throw new DynastyCharacteristicTestsExceptions\NotFoundException;
            }

            if ( ! $characteristicTest->isActive())
            {
                throw new DynastyCharacteristicTestsExceptions\NotActiveException;
            }

            // Save the dogs for later
            $massTestResults = [];

            // Start transaction
            DB::transaction(function() use ($characteristicTest, &$massTestResults)
            {
                $dogIds = explode(',', Input::get('dogs', ''));
                
                // Always add -1
                $dogIds[] = -1;

                // Get valid dogs
                $dogs = $this->currentUser->dogs()
                    ->whereComplete()
                    ->whereAlive()
                    ->whereUnworked()
                    ->whereIn('id', $dogIds)
                    ->get();

                foreach($dogs as $dog)
                {
                    // Make sure the dog can have the test
                    if ($characteristicTest->validAge($dog->age))
                    {
                        // Make sure the dog has the characteristic to test and that it's not hidden
                        $dogCharacteristic = $dog->characteristics()
                            ->whereVisible()
                            ->whereCharacteristic($characteristicTest->characteristic_id)
                            ->first();

                        if ( ! is_null($dogCharacteristic) and ! $dogCharacteristic->hasHadTest($characteristicTest))
                        {
                            // Work the dog
                            $dog->worked = true;
                            $dog->save();

                            // Perform the test
                            $message = $characteristicTest->performOnDogCharacteristic($dogCharacteristic);

                            // Render the view for the dog's characteristic
                            $showTests = false;
                            $view = trim(View::make('frontend/dog/_characteristic', compact('dogCharacteristic', 'showTests', 'message')));

                            $massTestResults[] = array(
                                'dog'  => $dog, 
                                'data' => $view, 
                            );
                        }
                    }
                }
            });

            $success = Lang::get('forms/user.mass_test_dogs.success');

            return Redirect::route('user/kennel')
                ->with('success', $success)
                ->with('characteristicTest', $characteristicTest)
                ->with('massTestResults', $massTestResults);
        }
        catch(DynastyUsersExceptions\NotUpgradedException $e)
        {
            $error = Lang::get('forms/user.mass_test_dogs.not_upgraded');
        }
        catch(DynastyCharacteristicTestsExceptions\NotFoundException $e)
        {
            $error = Lang::get('forms/user.mass_test_dogs.test_not_found');
        }
        catch(DynastyCharacteristicTestsExceptions\NotActiveException $e)
        {
            $error = Lang::get('forms/user.mass_test_dogs.test_not_active');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.mass_test_dogs.error');
        }

        return Redirect::route('user/kennel')->with('error', $error);
    }

    public function postCompareDogs()
    {
        $characteristicId = Input::get('characteristic');

        try
        {
            // Make sure the characteristic exists and is active
            $characteristic = Characteristic::whereActive()->whereVisible()->where('id', $characteristicId)->first();

            if (is_null($characteristic))
            {
                throw new DynastyCharacteristicsExceptions\NotFoundException;
            }
                
            $dogIds = explode(',', Input::get('dogs', ''));

            // Always add -1
            $dogIds[] = -1;

            // Save the dogs for later
            $dogComparisonResults = [];

            // Get valid dogs
            $dogs = $this->currentUser->dogs()
                ->whereComplete()
                ->whereAlive()
                ->whereIn('id', $dogIds)
                ->get();

            foreach($dogs as $dog)
            {
                // Make sure the dog has the characteristic
                $dogCharacteristic = $dog->characteristics()
                    ->whereVisible()
                    ->whereCharacteristic($characteristic->id)
                    ->first();

                if ( ! is_null($dogCharacteristic))
                {
                    // Render the view for the dog's characteristic
                    $showTests = false;
                    $view = trim(View::make('frontend/dog/_characteristic', compact('dogCharacteristic', 'showTests', 'message')));

                    $dogComparisonResults[] = array(
                        'dog'      => $dog, 
                        'is_known' => $dogCharacteristic->isKnown(), 
                        'dog_characteristic'  => $dogCharacteristic, 
                        'data' => $view, 
                    );
                }
            }

            return Redirect::route('user/kennel')
                ->with('characteristic', $characteristic)
                ->with('dogComparisonResults', $dogComparisonResults);
        }
        catch(DynastyCharacteristicsExceptions\NotFoundException $e)
        {
            $error = Lang::get('forms/user.compare_dog_characteristics.characteristic_not_found');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.compare_dog_characteristics.error');
        }

        return Redirect::route('user/kennel')->with('error', $error);
    }

    public function postMoveDogs()
    {
        try
        {
            $kennelGroupId = Input::get('tab');

            // Make sure the kennel group exists
            $kennelGroup = $this->currentUser->kennelGroups()->whereNotCemetery()->where('id', $kennelGroupId)->first();

            if (is_null($kennelGroup))
            {
                throw new DynastyKennelGroupsExceptions\NotFoundException;
            }

            $dogIds = explode(',', Input::get('dogs', ''));

            if ( ! empty($dogIds))
            {
                // Move the dogs
                DB::table('dogs')
                    ->where('owner_id', $this->currentUser->id)
                    ->whereNull('deceased_at')
                    ->whereIn('id', $dogIds)
                    ->update(array(
                        'kennel_group_id' => $kennelGroup->id, 
                    ));
            }

            $success = Lang::get('forms/user.move_dogs_to_kennel_group.success');

            return Redirect::route('user/kennel')->with('success', $success);
        }
        catch(DynastyKennelGroupsExceptions\NotFoundException $e)
        {
            $error = Lang::get('forms/user.move_dogs_to_kennel_group.kennel_group_not_found');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.move_dogs_to_kennel_group.error');
        }

        return Redirect::route('user/kennel')->with('error', $error);
    }

    public function postStudDogs()
    {
        try
        {
            $studding = Input::get('stud_type');

            if ( ! in_array($studding, array_keys(Dog::studdingOptions())))
            {
                throw new DynastyDogsExceptions\InvalidStuddingException;
            }

            $dogIds   = explode(',', Input::get('dogs', ''));

            // Always add -1
            $dogIds[] = -1;

            // Get valid dogs
            $dogs = $this->currentUser->dogs()
                ->whereComplete()
                ->whereAlive()
                ->whereMale()
                ->whereIn('dogs.id', $dogIds)
                ->get()
                ->filter(function($item)
                {
                    return $item->isBreedable();
                });

            $validDogIds = $dogs->lists('id');

            if ( ! empty($validDogIds))
            {
                // Move the dogs
                DB::table('dogs')
                    ->whereIn('dogs.id', $validDogIds)
                    ->update(array(
                        'studding' => $studding, 
                    ));
            }

            $success = Lang::get('forms/user.manage_dogs_studding.success');

            return Redirect::route('user/kennel')->with('success', $success);
        }
        catch(DynastyDogsExceptions\InvalidStuddingException $e)
        {
            $error = Lang::get('forms/user.manage_dogs_studding.invalid_studding');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.manage_dogs_studding.error');
        }

        return Redirect::route('user/kennel')->with('error', $error);
    }

    public function postCopyDogSummary()
    {
        try
        {
            $summaryDogId = Input::get('dog');
            $dogIds = explode(',', Input::get('dogs', ''));

            // Always add -1
            $dogIds[] = -1;

            // Make sure the dog exists
            $summaryDog = $this->currentUser->dogs()
                ->whereComplete()
                ->whereAlive()
                ->where('id', $summaryDogId)
                ->first();

            if (is_null($summaryDog))
            {
                throw new DynastyDogsExceptions\NotFoundException;
            }

            // Get valid dogs
            $dogs = $this->currentUser->dogs()
                ->whereComplete()
                ->whereAlive()
                ->where('dogs.id', '<>', $summaryDog->id)
                ->whereIn('dogs.id', $dogIds)
                ->get();

            $validDogIds = $dogs->lists('id');

            if ( ! empty($validDogIds))
            {
                DB::transaction(function() use($summaryDog, $validDogIds)
                {
                    // Get the summarized characteristic ids
                    $summarizedCharacteristicIds = $summaryDog->characteristics()->whereInSummary()->lists('characteristic_id');

                    // Update the dogs' summaries
                    DB::table('dog_characteristics')
                        ->whereIn('dog_id', $validDogIds)
                        ->update(array(
                            'in_summary' => false, 
                        ));

                    if ( ! empty($summarizedCharacteristicIds))
                    {
                        DB::table('dog_characteristics')
                            ->whereIn('dog_id', $validDogIds)
                            ->whereIn('characteristic_id', $summarizedCharacteristicIds)
                            ->update(array(
                                'in_summary' => true, 
                            ));
                    }
                });
            }

            $success = Lang::get('forms/user.copy_dog_summary.success');

            return Redirect::route('user/kennel')->with('success', $success);
        }
        catch(DynastyDogsExceptions\NotFoundException $e)
        {
            $error = Lang::get('forms/user.copy_dog_summary.summary_dog_not_found');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.copy_dog_summary.error');
        }

        return Redirect::route('user/kennel')->with('error', $error);
    }

    public function postPetHomeDogs()
    {
        try
        {
            if ( ! $this->currentUser->hasCompletedTutorialStage('first-breeding'))
            {
                throw new DynastyUsersExceptions\IncompleteTutorialException;
            }

            $dogIds = explode(',', Input::get('dogs', ''));

            // Always add -1
            $dogIds[] = -1;

            // Get valid dogs
            $dogs = $this->currentUser->dogs()
                ->whereAlive()
                ->whereIn('dogs.id', $dogIds)
                ->orderBy('id', 'desc')
                ->get();

            if ( ! $dogs->isEmpty())
            {
                DB::transaction(function() use ($dogs)
                {
                    // Pet home them all
                    foreach($dogs as $dog)
                    {
                        $dog->petHome();
                    }
                });
            }

            $success = Lang::get('forms/user.pet_home_dogs.success');

            return Redirect::route('user/kennel')->with('success', $success);
        }
        catch(DynastyUsersExceptions\IncompleteTutorialException $e)
        {
            $error = Lang::get('forms/user.pet_home_dogs.incomplete_tutorial');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.pet_home_dogs.error');
        }

        return Redirect::route('user/kennel')->with('error', $error);
    }

    public function postUpdateKennelGroup($kennelGroup)
    {
        // Make sure this user owns this kennel group
        $totalFound = $this->currentUser->kennelGroups()
            ->where('id', $kennelGroup->id)
            ->count();

        if ($totalFound < 1)
        {
            App::abort('404', 'Kennel tab not found!');
        }

        try
        {
            // Declare the rules for the form validation
            $rules = array(
                'description' => 'max:10000',
                'dog_order'   => 'required|in:'.implode(',', array_keys(KennelGroup::getDogOrders())),
            );

            // Create a new validator instance from our validation rules
            $validator = Validator::make(Input::all(), $rules);

            $validator->sometimes('name', 'required|max:32', function($input) use ($kennelGroup)
            {
                return $kennelGroup->canBeEdited();
            });

            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::route('user/kennel')->withInput()->with('error', $validator->errors()->first());
            }

            // Update the kennel group
            if ($kennelGroup->canBeEdited())
            {
                $kennelGroup->name = Input::get('name');
                $kennelGroup->description = Input::get('description');
            }

            $kennelGroup->dog_order_id = Input::get('dog_order');
            $kennelGroup->save();

            $success = Lang::get('forms/user.update_kennel_group.success');

            return Redirect::route('user/kennel')->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.update_kennel_group.error');
        }

        return Redirect::route('user/kennel')->withInput()->with('error', $error);
    }

    public function getDeleteKennelGroup($kennelGroup)
    {
        // Make sure this user owns this kennel group
        $totalFound = $this->currentUser->kennelGroups()
            ->where('id', $kennelGroup->id)
            ->count();

        if ( ! $kennelGroup->canBeDeleted() or $totalFound < 1)
        {
            App::abort('404', 'Kennel tab not found!');
        }

        try
        {
            // Make sure the kennel group doesn't have any dogs in it
            if ( ! $kennelGroup->isEmpty())
            {
                throw new DynastyKennelGroupsExceptions\NotEmptyException;
            }

            // Make sure this isn't the user's last kennel (excluding cemetery)
            if ($this->currentUser->kennelGroups()->whereNotCemetery()->count() <= 1)
            {
                throw new DynastyKennelGroupsExceptions\LastOneException;
            }

            // Delete the group
            $kennelGroup->delete();

            $success = Lang::get('forms/user.delete_kennel_group.success');

            return Redirect::route('user/kennel')->with('success', $success);
        }
        catch(DynastyKennelGroupsExceptions\NotEmptyException $e)
        {
            $error = Lang::get('forms/user.delete_kennel_group.not_empty');
        }
        catch(DynastyKennelGroupsExceptions\LastOneException $e)
        {
            $error = Lang::get('forms/user.delete_kennel_group.last_kennel_group');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.delete_kennel_group.error');
        }

        return Redirect::route('user/kennel')->withInput()->with('error', $error);
    }

    public function postRequestToBreedDogs()
    {
        try
        {
            $dogId   = Input::get('dog');
            $bitchId = Input::get('bitch');

            // Make sure the dog exists
            $dog = $this->currentUser->dogs()
                ->whereComplete()
                ->whereAlive()
                ->whereMale()
                ->where('dogs.id', $dogId)
                ->first();

            if (is_null($dog))
            {
                throw new DynastyDogsExceptions\NotFoundException;
            }

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
                throw new DynastyDogsExceptions\NotFoundException;
            }

            if ( ! $bitch->canBeBredImmediately())
            {
                throw new DynastyDogsExceptions\BitchNotBreedableException;
            }

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

            return Redirect::route('user/kennel')->with('beginnersLuck', $beginnersLuck);
        }
        catch(DynastyDogsExceptions\NotFoundException $e)
        {
            $error = Lang::get('forms/user.breed_dogs.dog_not_found');
        }
        catch(DynastyDogsExceptions\DogNotBreedableException $e)
        {
            $error = Lang::get('forms/user.breed_dogs.dog_not_breedable');
        }
        catch(DynastyDogsExceptions\NotFoundException $e)
        {
            $error = Lang::get('forms/user.breed_dogs.bitch_not_found');
        }
        catch(DynastyDogsExceptions\BitchNotBreedableException $e)
        {
            $error = Lang::get('forms/user.breed_dogs.bitch_not_breedable');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.breed_dogs.error');
        }

        return Redirect::route('user/kennel')->with('error', $error);
    }

    public function postManageStudRequest()
    {
        switch (Input::get('manage'))
        {
            case 'accept':
                return $this->postAcceptStudRequest();

            case 'reject':
                return $this->postRejectStudRequest();

            case 'breed':
                return $this->postBreedStudRequest();

            case 'remove':
                return $this->postRemoveStudRequest();
            
            default:
                break;
        }

        return Redirect::route('user/kennel');
    }

    public function postAcceptStudRequest()
    {
        try
        {
            $studRequestId = Input::get('stud_request');

            // Make sure the request exists
            $request = $this->currentUser->receivedStudRequests()->whereWaiting()->where('stud_requests.id', $studRequestId)->first();

            if (is_null($request))
            {
                throw new DynastyStudRequestsExceptions\NotFoundException;
            }

            // Validate the stud
            if ($request->stud->isPetHomed() or ! $request->stud->isMale() or ! $request->stud->isBreedable())
            {
                throw new DynastyDogsExceptions\DogNotBreedableException;
            }

            // Validate the bitch
            if ($request->bitch->isPetHomed() or ! $request->bitch->isFemale() or ! $request->bitch->isBreedable())
            {
                throw new DynastyDogsExceptions\BitchNotBreedableException;
            }

            DB::transaction(function() use ($request)
            {
                // Notify the bitch's owner
                if ( ! is_null($request->bitch->owner) and $request->bitch->owner->id != $this->currentUser->id)
                {
                    // Send a notification to the owner
                    $params = array(
                        'user'      => $this->currentUser->nameplate(), 
                        'userUrl'   => URL::route('user/profile', $this->currentUser->id), 
                        'stud'      => $request->stud->nameplate(), 
                        'studUrl'   => URL::route('dog/profile', $request->stud->id), 
                        'bitch'     => $request->bitch->nameplate(), 
                        'bitchUrl'  => URL::route('dog/profile', $request->bitch->id), 
                    );

                    $body = Lang::get('notifications/user.accept_stud_request.to_owner', array_map('htmlentities', array_dot($params)));
                    
                    $request->bitch->owner->notify($body, UserNotification::TYPE_SUCCESS);
                }

                // Accept the stud request
                $request->accepted = true;
                $request->save();
            });


            $success = Lang::get('forms/user.accept_stud_request.success');

            return Redirect::route('user/kennel')->with('success', $success);
        }
        catch(DynastyStudRequestsExceptions\NotFoundException $e)
        {
            $error = Lang::get('forms/user.accept_stud_request.not_found');
        }
        catch(DynastyDogsExceptions\DogNotBreedableException $e)
        {
            $error = Lang::get('forms/user.accept_stud_request.dog_not_breedable');
        }
        catch(DynastyDogsExceptions\BitchNotBreedableException $e)
        {
            $error = Lang::get('forms/user.accept_stud_request.bitch_not_breedable');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.accept_stud_request.error');
        }

        return Redirect::route('user/kennel')->with('error', $error);
    }

    public function postRejectStudRequest()
    {
        try
        {
            $studRequestId = Input::get('stud_request');

            // Make sure the request exists
            $request = $this->currentUser->receivedStudRequests()->whereWaiting()->where('stud_requests.id', $studRequestId)->first();

            if (is_null($request))
            {
                throw new DynastyStudRequestsExceptions\NotFoundException;
            }

            DB::transaction(function() use ($request)
            {
                // Notify the bitch's owner
                if ( ! is_null($request->bitch->owner) and $request->bitch->owner->id != $this->currentUser->id)
                {
                    // Send a notification to the owner
                    $params = array(
                        'user'      => $this->currentUser->nameplate(), 
                        'userUrl'   => URL::route('user/profile', $this->currentUser->id), 
                        'stud'      => $request->stud->nameplate(), 
                        'studUrl'   => URL::route('dog/profile', $request->stud->id), 
                        'bitch'     => $request->bitch->nameplate(), 
                        'bitchUrl'  => URL::route('dog/profile', $request->bitch->id), 
                    );

                    $body = Lang::get('notifications/user.reject_stud_request.to_owner', array_map('htmlentities', array_dot($params)));
                    
                    $request->bitch->owner->notify($body, UserNotification::TYPE_DANGER);
                }

                // Remove the stud request
                $request->delete();
            });


            $success = Lang::get('forms/user.reject_stud_request.success');

            return Redirect::route('user/kennel')->with('success', $success);
        }
        catch(DynastyStudRequestsExceptions\NotFoundException $e)
        {
            $error = Lang::get('forms/user.reject_stud_request.not_found');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.reject_stud_request.error');
        }

        return Redirect::route('user/kennel')->with('error', $error);
    }

    public function postRemoveStudRequest()
    {
        try
        {
            $studRequestId = Input::get('stud_request');


            // Make sure the request exists
            $request = $this->currentUser->sentStudRequests()->where('stud_requests.id', $studRequestId)->first();

            if (is_null($request))
            {
                throw new DynastyStudRequestsExceptions\NotFoundException;
            }

            DB::transaction(function() use ($request)
            {
                // Notify the stud's owner
                if ( ! is_null($request->stud->owner) and $request->stud->owner->id != $this->currentUser->id)
                {
                    // Send a notification to the owner
                    $params = array(
                        'user'      => $this->currentUser->nameplate(), 
                        'userUrl'   => URL::route('user/profile', $this->currentUser->id), 
                        'stud'      => $request->stud->nameplate(), 
                        'studUrl'   => URL::route('dog/profile', $request->stud->id), 
                        'bitch'     => $request->bitch->nameplate(), 
                        'bitchUrl'  => URL::route('dog/profile', $request->bitch->id), 
                    );

                    $body = Lang::get('notifications/user.remove_stud_request.to_owner', array_map('htmlentities', array_dot($params)));
                    
                    $request->stud->owner->notify($body, UserNotification::TYPE_DANGER);
                }

                // Remove the stud request
                $request->delete();
            });


            $success = Lang::get('forms/user.remove_stud_request.success');

            return Redirect::route('user/kennel')->with('success', $success);
        }
        catch(DynastyStudRequestsExceptions\NotFoundException $e)
        {
            $error = Lang::get('forms/user.remove_stud_request.not_found');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.remove_stud_request.error');
        }

        return Redirect::route('user/kennel')->with('error', $error);
    }

    public function postBreedStudRequest()
    {
        try
        {
            $studRequestId = Session::get("request_id");

            // Make sure the request exists
            $request = $this->currentUser->sentStudRequests()->where('stud_requests.id', $studRequestId)->first();
	    

            if (is_null($request))
            {
                throw new DynastyStudRequestsExceptions\NotFoundException;
            }

            // Request must have been accepted to go ahead with breeding
            if ( ! $request->isAccepted())
            {
                throw new DynastyStudRequestsExceptions\WaitingException;
            }

            // Validate the dog
            if ($request->stud->isPetHomed() or ! $request->stud->isMale() or ! $request->stud->isBreedable())
            {
                throw new DynastyDogsExceptions\DogNotBreedableException;
            }

            // Validate the bitch
            if ($request->bitch->isPetHomed() or ! $request->bitch->isFemale() or ! $request->bitch->canBeBredImmediately())
            {
                throw new DynastyDogsExceptions\BitchNotBreedableException;
            }

            DB::transaction(function() use ($request)
            {
                $this->currentUser->breedDogs($request->stud, $request->bitch);
            });

            $params = array(
                'stud'  => $request->stud->nameplate(), 
                'bitch' => $request->bitch->nameplate(), 
            );

            $success = Lang::get('forms/user.breed_stud_request.success', array_map('htmlentities', array_dot($params)));

            return Redirect::route('user/kennel')->with('success', $success);
        }
        catch(DynastyStudRequestsExceptions\NotFoundException $e)
        {
            $error = Lang::get('forms/user.breed_stud_request.not_found');
        }
        catch(DynastyStudRequestsExceptions\WaitingException $e)
        {
            $error = Lang::get('forms/user.breed_stud_request.waiting');
        }
        catch(DynastyDogsExceptions\DogNotBreedableException $e)
        {
            $error = Lang::get('forms/user.breed_stud_request.dog_not_breedable');
        }
        catch(DynastyDogsExceptions\BitchNotBreedableException $e)
        {
            $error = Lang::get('forms/user.breed_stud_request.bitch_not_breedable');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.breed_stud_request.error');
        }

        return Redirect::route('user/kennel')->with('error', $error);
    }

    public function getAddKennelGroup()
    {
        try
        {
            if ( ! $this->currentUser->isUpgraded())
            {
                throw new DynastyUsersExceptions\NotUpgradedException;
            }

            if ( ! $this->currentUser->canAddNewKennelGroup())
            {
                throw new DynastyUsersExceptions\AtKennelGroupCapacityException;
            }

            // Create the kennel group
            $kennelGroup = KennelGroup::create(array(
                'user_id'      => $this->currentUser->id, 
                'name'         => 'New Tab', 
                'type_id'      => KennelGroup::EXTRA, 
                'dog_order_id' => KennelGroup::DOG_ORD_ID, 
            ));

            $success = Lang::get('forms/user.create_kennel_group.success');

            return Redirect::route('user/kennel')->with('success', $success);
        }
        catch(DynastyUsersExceptions\NotUpgradedException $e)
        {
            $error = Lang::get('forms/user.create_kennel_group.not_upgraded');
        }
        catch(DynastyUsersExceptions\AtKennelGroupCapacityException $e)
        {
            $error = Lang::get('forms/user.create_kennel_group.at_capacity');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.create_kennel_group.error');
        }

        return Redirect::route('user/kennel')->with('error', $error);
    }

}
