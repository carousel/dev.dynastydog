<?php namespace Controllers\User;

use AuthorizedController;
use Redirect;
use View;
use Input;
use Validator;
use Lang;
use Config;
use DB;
use Str;
use URL;
use User;
use Request;
use Floats;
use Dog;
use DogCharacteristic;
use Characteristic;
use UserNotification;
use Session;

use Exception;
use Dynasty\Users\Exceptions as DynastyUsersExceptions;

class ProfileController extends AuthorizedController {

    public function getIndex($profile = null)
    {
        if (is_null($profile) or $profile->id == $this->currentUser->id)
        {
            $profile = $this->currentUser;
        }

        // Show the page
        return View::make('frontend/user/profile/index', compact('profile'));
    }

    public function getAdvanceTurn()
    {
        $redirectTo = is_null(Request::header('referer'))
                ? Redirect::route('user/kennel')
                : Redirect::back();

        try
        {
            // The user must have a turn to spend
            if ( ! $this->currentUser->canAffordTurns(1))
            {
                throw new DynastyUsersExceptions\NotEnoughTurnsException;
            }

            // The user must not have a request out for BLR
            if ($this->currentUser->hasRequestedBeginnersLuck())
            {
                throw new DynastyUsersExceptions\RequestedBeginnersLuckException;
            }

            // Log the report for later
            $report = [];

            DB::transaction(function() use (&$report)
            {
                // Take the turn immediately
                $this->currentUser->turns -= 1;
                $this->currentUser->save();

                // Get how much a dog should age per turn
                $monthsToAge = Config::get('game.dog.months_to_age');

                // Get the immune system formulas
                $immuneSystemGetFormula  = Config::get('game.formulas.immune_system_get');
                $immuneSystemHealFormula = Config::get('game.formulas.immune_system_heal');

                // Get all dogs that are alive
                $dogs = $this->currentUser->dogs()->whereAlive()->orderBy('id', 'asc')->get();

                $workedLimit = Config::get('game.dog.advanced_turn_worked_limit');
                $counter = 0;

                foreach($dogs as $dog)
                {
                    if ($counter < $workedLimit and $dog->isWorked())
                    {
                        // Show that a dog was worked
                        $counter += 1;

                        // Only age dogs that have been worked
                        $dog->age += $monthsToAge;
                        $dog->worked = false;
                        
                        // Save the dog
                        $dog->save();

                        // Only do the rest if the dog is complete
                        if ($dog->isComplete())
                        {
                            /*** Coming of age ***/

                            // If a dog is not already marked as sexually mature, but is 
                            if ( ! $dog->isSexuallyMature() and $dog->checkSexualMaturity())
                            {
                                $dog->sexually_mature = true;
                                
                                // Save the dog
                                $dog->save();

                                if ($dog->isMale())
                                {
                                    // Add it to the report
                                    $report['mature'][] = "Congratulations! ".$dog->nameplate()." has reached sexual maturity and is now able to breed.";
                                }
                            }

                            /******/

                            /*** Sexual decline ***/

                            // If a dog is not already marked as in sexual decline, but is 
                            if ( ! $dog->isInSexualDecline() and $dog->checkSexualDecline())
                            {
                                $dog->sexual_decline = true;
                                
                                // Save the dog
                                $dog->save();

                                // Give them the "Getting Old" characteristic on their FDO char
                                $gettingOld = Characteristic::where('type_id', Characteristic::TYPE_OLD_AGE)->first();

                                // Give the dog the characteristic
                                $dog->addCharacteristic($gettingOld);

                                // Add it to the report
                                $report['sexual_decline'][] = $dog->nameplate()." is looking rather frail these days, just not how they used to be. The quality of their puppies is going to start decreasing, and age-related infertility is not too far in the future...";
                            }

                            /******/

                            /*** Infertile ***/

                            // Only report infertility once
                            if ( ! $dog->isInfertile() and $dog->checkInfertility())
                            {
                                $dog->infertile = true;
                                $dog->heat = false;
                                $dog->studding = Dog::STUDDING_NONE;
                                
                                // Save the dog
                                $dog->save();

                                // Remove all stud requests
                                DB::table('stud_requests')->where('bitch_id', $dog->id)->orWhere('stud_id', $dog->id)->delete();

                                // Remove all blrs
                                DB::table('beginners_luck_requests')->where('bitch_id', $dog->id)->orWhere('dog_id', $dog->id)->delete();

                                $report['infertile'][] = "Oh no! ".$dog->nameplate()." has gotten too old to breed. ".($dog->isMale() ? "He" : "She")." is now infertile.";
                            }

                            /******/

                            /*** Heat ***/

                            // Bitches are in heat for only one turn at a time
                            if ($dog->isInHeat())
                            {
                                $dog->heat = false;
                                
                                // Save the dog
                                $dog->save();
                            }
                            else if ($dog->checkHeat())
                            {
                                $dog->heat = true;
                                
                                // Save the dog
                                $dog->save();

                                // Add it to the report
                                $report['heat'][] = $dog->nameplate()." is in heat this turn.";

                                // @TUTORIAL: complete visit-kennel-after-import
                                $this->currentUser->completeTutorialStage('visit-kennel-after-import');
                            }

                            /******/

                            /*** Litters ***/

                            if ($dog->isFemale())
                            {
                                $litters = $dog->litters()->whereUnborn()->get();

                                foreach($litters as $litter)
                                {
                                    $numberOfPuppiesBorn = $litter->birth();

                                    if ($numberOfPuppiesBorn > 0)
                                    {
                                        $report['litter'][] = $dog->nameplate()." gave birth to a litter of ".$numberOfPuppiesBorn." ".Str::plural('puppy', $numberOfPuppiesBorn)."!";

                                        // @TUTORIAL: complete first-breeding
                                        $this->currentUser->completeTutorialStage('first-breeding');
                                    }
                                    else
                                    {
                                        $report['litter'][] = $dog->nameplate()." did not take at her last breeding and did not produce a litter.";
                                    }
                                }
                            }

                            /******/
                            
                            /*** Immune system ***/

                            // Get the immune system characteristic
                            $immuneSystem = $dog->getImmuneSystem();

                            if ( ! is_null($immuneSystem))
                            {
                                // Check if a dog should get an immune system disease
                                $value = $immuneSystem->current_ranged_value / 100.00; // Make into decimals

                                $expr = strtr($immuneSystemGetFormula, array(
                                    ':is' => $value, 
                                ));

                                $chance = eval('return '.$expr.';') * 100;

                                // Get all diseases this dog currently has
                                $dogDiseases = $dog->diseases;

                                if (mt_rand(1, 100) <= $chance)
                                {
                                    // Grab the bered
                                    $breed = $dog->breed;

                                    if ( ! is_null($breed))
                                    {
                                        $specificDiseaseIds = $dog->breed->characteristics()->with('characteristic')
                                            ->whereActive()
                                            ->whereHas('characteristic', function($query)
                                                {
                                                    $query->whereActive()->where('type_id', Characteristic::TYPE_IMMUNE_SYSTEM_DISEASE);
                                                })
                                            ->lists('characteristic_id');
                                    }
                                    else
                                    {
                                        // Grab it from the general characteristics
                                        $specificDiseaseIds = array();
                                    }

                                    // Start selection from all characteristic diseases
                                    $diseases = Characteristic::whereActive()->where('type_id', Characteristic::TYPE_IMMUNE_SYSTEM_DISEASE);

                                    // Only select from diseases that are available within the breed
                                    if ( ! empty($specificDiseaseIds))
                                    {
                                        $diseases = $diseases->whereIn('id', $specificDiseaseIds);
                                    }

                                    // Do not give the dog a disease it already has
                                    if ( ! $dogDiseases->isEmpty())
                                    {
                                        $diseases = $diseases->whereNotIn('id', $dogDiseases->lists('characteristic_id'));
                                    }

                                    // Get all possible diseases
                                    $diseases = $diseases->get();

                                    if ( ! $diseases->isEmpty())
                                    {
                                        // Grab one from random
                                        $disease = $diseases->random();

                                        // Give it to the dog
                                        if ( ! is_null($dog->addCharacteristic($disease)))
                                        {
                                            // Add it to the report
                                            $report['became_ill'][] = "Oh no! ".$dog->nameplate()." has caught ".$disease->name."!";
                                        }
                                    }
                                }

                                // Check if a dog should be healed from an immune system disease
                                $expr = strtr($immuneSystemHealFormula, array(
                                    ':is' => $value, 
                                ));

                                $chance = eval('return '.$expr.';') * 100;

                                if (mt_rand(1, 100) <= $chance)
                                {
                                    if ( ! $dogDiseases->isEmpty())
                                    {
                                        // Grab one from random
                                        $dogDisease = $dogDiseases->random();

                                        // Add it to the report
                                        $report['healed'][] = $dog->nameplate()." is no longer sick with ".$dogDisease->characteristic->name.". Awesome!";
                                        
                                        // Remove the disease
                                        $dogDisease->delete();
                                    }
                                }
                            }

                            /******/

                            /*** Characteristics ***/

                            // Log for later
                            $expressedSymptoms = [];
                            $lethalSymptoms    = [];

                            $dogCharacteristics = $dog->characteristics;

                            foreach($dogCharacteristics as $dogCharacteristic)
                            {
                                /*** Genetic ***/

                                if ( ! $dogCharacteristic->genotypesAreRevealed() and $dogCharacteristic->genotypesCanBeRevealed() and $dog->age >= $dogCharacteristic->age_to_reveal_genotypes)
                                {
                                    $dogCharacteristic->genotypes_revealed = true;
                                }

                                if ( ! $dogCharacteristic->phenotypesAreRevealed() and $dogCharacteristic->phenotypesCanBeRevealed() and $dog->age >= $dogCharacteristic->age_to_reveal_phenotypes)
                                {
                                    $dogCharacteristic->phenotypes_revealed = true;
                                }

                                /******/

                                /*** Range ***/

                                if ( ! $dogCharacteristic->rangedValueIsRevealed() and $dogCharacteristic->rangedValueCanBeRevealed() and $dog->age >= $dogCharacteristic->age_to_reveal_ranged_value)
                                {
                                    $dogCharacteristic->ranged_value_revealed = true;
                                }

                                if ($dogCharacteristic->hasRangedGrowth() and Floats::compare($dogCharacteristic->current_ranged_value, $dogCharacteristic->final_ranged_value, '!='))
                                {
                                    $dogCharacteristic->current_ranged_value = DogCharacteristic::currentRangedValue($dogCharacteristic->final_ranged_value, $dogCharacteristic->age_to_stop_growing, $dog->age);
                                }


                                /******/

                                /*** Health ***/

                                if ( ! $dogCharacteristic->severityIsExpressed() and $dogCharacteristic->severityCanBeExpressed() and $dog->age >= $dogCharacteristic->age_to_express_severity)
                                {
                                    $dogCharacteristic->severity_expressed = true;
                                }

                                if ( ! $dogCharacteristic->severityValueIsRevealed() and $dogCharacteristic->severityValueCanBeRevealed() and $dog->age >= $dogCharacteristic->age_to_reveal_severity_value)
                                {
                                    $dogCharacteristic->severity_value_revealed = true;
                                }

                                // Get all the dog's symptoms that are not expressed
                                $dogSymptoms = $dog->symptoms()->whereNotExpressed()->get();

                                foreach($dogSymptoms as $dogSymptom)
                                {
                                    if ($dogSymptom->canBeExpressed() and $dog->age >= $dogSymptom->age_to_express)
                                    {
                                        $dogSymptom->expressed = true;
                                        $dogSymptom->save();

                                        $symptomName = $dogSymptom->characteristicSeveritySymptom->symptom->name;

                                        $expressedSymptoms[] = $symptomName;

                                        if ($dog->isAlive() and $dogSymptom->isLethal())
                                        {
                                            // Kill the dog
                                            $dogSymptom->killDog();

                                            // Reload the dog
                                            $dog = Dog::find($dog->id);

                                            $lethalSymptoms[] = $symptomName;
                                        }
                                    }
                                }

                                // Save the characteristic
                                $dogCharacteristic->save();
                            }

                            if ( ! empty($expressedSymptoms))
                            {
                                // Add it to the report
                                $report['symptom'][] = "Oh no! ".$dog->nameplate()." has started showing the following symptoms: ".implode(', ', $expressedSymptoms).".";
                            }

                            if ( ! empty($lethalSymptoms))
                            {
                                // Add it to the report
                                $report['death'][] = "Unfortunately, ".$dog->nameplate()." passed away due to :".implode(', ', $lethalSymptoms).".";
                            }

                            /******/

                            /*** Lifespan ***/

                            if ($dog->isAlive() and $dog->checkLifeSpan())
                            {
                                // Kill the dog 
                                $dog->kill();

                                // Reload the dog
                                $dog = Dog::find($dog->id);

                                if ( ! is_null($dog->owner))
                                {
                                    $params = array(
                                        'dog'     => $dog->nameplate(), 
                                        'dogUrl'  => URL::route('dog/profile', $dog->id), 
                                        'pronoun' => ($dog->isFemale() ? 'her' : 'his'), 
                                    );

                                    $body = Lang::get('notifications/dog.old_age_death.to_owner', array_map('htmlentities', array_dot($params)));
                                    
                                    $dog->owner->notify($body, UserNotification::TYPE_DANGER);
                                }

                                // Add it to the report
                                $report['death'][] = "Unfortunately, ".$dog->nameplate()." passed away due to old age.";
                            }

                            /******/
                        } // End complete check
                    } // End worked check
                    
                    // Do lending check and make sure the receiver has actually accepted the dog
                    if ($dog->isPendingOwnership() and $dog->lendRequest->isTurnSensitive() and $dog->lendRequest->receiver_id == $dog->owner_id)
                    {
                        // Take away a turn
                        $dog->lendRequest->turns_left -= 1;

                        if ($dog->lendRequest->turns_left <= 0)
                        {
                            // Transfer the ownership
                            $dog->owner_id = $dog->lendRequest->sender->id;

                            // Put the dog in its old kennel
                            $newKennelGroup = $dog->lendRequest->sender->kennelGroups()->whereNotCemetery()->first();

                            $dog->kennel_group_id = is_null($newKennelGroup)
                                ? null
                                : $newKennelGroup->id;

                            // Save the dog
                            $dog->save();

                            // Notify the receiver
                            $params = array(
                                'sender'    => $dog->lendRequest->sender->nameplate(), 
                                'senderUrl' => URL::route('user/profile', $dog->lendRequest->sender->id), 
                                'dog'       => $dog->nameplate(), 
                                'dogUrl'    => URL::route('dog/profile', $dog->id), 
                            );

                            $body = Lang::get('notifications/user.expired_lend_request.to_receiver', array_map('htmlentities', array_dot($params)));
                            
                            $dog->lendRequest->receiver->notify($body, UserNotification::TYPE_SUCCESS);

                            // Notify the sender
                            $params = array(
                                'receiver'    => $dog->lendRequest->receiver->nameplate(), 
                                'receiverUrl' => URL::route('user/profile', $dog->lendRequest->receiver->id), 
                                'dog'         => $dog->nameplate(), 
                                'dogUrl'      => URL::route('dog/profile', $dog->id), 
                            );

                            $body = Lang::get('notifications/user.expired_lend_request.to_sender', array_map('htmlentities', array_dot($params)));
                            
                            $dog->lendRequest->sender->notify($body, UserNotification::TYPE_SUCCESS);

                            // Delete the request
                            $dog->lendRequest->delete();
                        }
                        else
                        {
                            // Save the request
                            $dog->lendRequest->save();
                        }
                    }
                } // End dogs loop

                // @TUTORIAL: complete first-test-dog
                $this->currentUser->completeTutorialStage('first-test-dog');
            });

            $success = Lang::get('forms/user.advance_turn.success');

            $report['nothing'] = empty($report);

            return $redirectTo->with('success', $success)->with('advancedTurnReport', $report);
        }
        catch(DynastyUsersExceptions\NotEnoughTurnsException $e)
        {
            $error = Lang::get('forms/user.advance_turn.not_enough_turns');
        }
        catch(DynastyUsersExceptions\RequestedBeginnersLuckException $e)
        {
            $error = Lang::get('forms/user.advance_turn.requested_beginners_luck');
        }
        // We want to catch all exceptions thrown in the transaction block and 
        // give a generic error to the user
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.advance_turn.error');
        }

        return $redirectTo->with('error', $error);
    }

}
