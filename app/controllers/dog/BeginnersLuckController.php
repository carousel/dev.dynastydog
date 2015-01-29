<?php namespace Controllers\Dog;

use AuthorizedController;
use App;
use Redirect;
use View;
use Input;
use Validator;
use Lang;
use DB;
use URL;

use Dog;
use User;
use UserNotification;
use BeginnersLuckRequest;

use Dynasty\Users\Exceptions as DynastyUsersExceptions;
use Dynasty\Dogs\Exceptions as DynastyDogsExceptions;

class BeginnersLuckController  extends AuthorizedController {

    public function getIndex($dog, $bitch)
    {
        try
        {
            // Validate the pair
            $this->validatePair($dog, $bitch);

            // Get the online beginners
            $onlineBeginners = User::whereOnline()->whereBeginner()->orderBy('last_action_at', 'desc')->orderBy('id', 'desc')->get();

            // Show the page
            return View::make('frontend/dog/beginners_luck/index', compact('dog', 'bitch', 'onlineBeginners'));
        }
        catch (DynastyDogsExceptions\DogNotBreedableException $e)
        {
            $abort = "Dog is not eligible for Breeder's Luck!";
        }
        catch (DynastyDogsExceptions\BitchNotBreedableException $e)
        {
            $abort = "Bitch is not eligible for Breeder's Luck!";
        }

        App::abort('404', $abort);
    }

    public function getRequest($dog, $bitch, $beginner)
    {
        try
        {
            // Validate the pair
            $this->validatePair($dog, $bitch);

            // Validate the beginner
            if ( ! $beginner->isBeginner())
            {
                throw new DynastyUsersExceptions\NotBeginnerException;
            }

            DB::transaction(function() use ($dog, $bitch, $beginner)
            {
                // Create the request
                $request = BeginnersLuckRequest::create(array(
                    'user_id'     => $this->currentUser->id, 
                    'dog_id'      => $dog->id, 
                    'bitch_id'    => $bitch->id, 
                    'beginner_id' => $beginner->id, 
                ));

                // Send a notification to the beginner
                $params = array(
                    'owner'     => $this->currentUser->nameplate(), 
                    'ownerUrl'  => URL::route('user/profile', $this->currentUser->id), 
                    'dog'       => $dog->nameplate(), 
                    'dogUrl'    => URL::route('dog/profile', $dog->id), 
                    'bitch'     => $bitch->nameplate(), 
                    'bitchUrl'  => URL::route('dog/profile', $bitch->id), 
                    'acceptUrl' => URL::route('dog/blr/accept', $request->id), 
                    'rejectUrl' => URL::route('dog/blr/reject', $request->id), 
                );

                $body = Lang::get('notifications/user.request_beginners_luck.to_beginner', array_map('htmlentities', array_dot($params)));
                
                $persistentNotification = $beginner->notify($body, UserNotification::TYPE_INFO, true, true, true);

                // We want to save this notification to the request
                if ( ! is_null($persistentNotification))
                {
                    $request->persistent_notification_id = $persistentNotification->id;
                    $request->save();
                }

                // Send a notification to the bitch's owner
                $params = array(
                    'beginner'    => $beginner->nameplate(), 
                    'beginnerUrl' => URL::route('user/profile', $beginner->id), 
                    'bitch'       => $bitch->nameplate(), 
                    'bitchUrl'    => URL::route('dog/profile', $bitch->id), 
                );

                $body = Lang::get('notifications/user.request_beginners_luck.to_owner', array_map('htmlentities', array_dot($params)));
                
                $this->currentUser->notify($body, UserNotification::TYPE_INFO, false, false);
            });

            $success = Lang::get('forms/user.request_beginners_luck.success');

            return Redirect::route('dog/profile', $bitch->id)->with('success', $success);
        }
        catch (DynastyDogsExceptions\DogNotBreedableException $e)
        {
            App::abort('404', "Dog is not eligible for Breeder's Luck!");
        }
        catch (DynastyDogsExceptions\BitchNotBreedableException $e)
        {
            App::abort('404', "Bitch is not eligible for Breeder's Luck!");
        }
        catch(DynastyUsersExceptions\NotBeginnerException $e)
        {
            $error = Lang::get('forms/user.request_beginners_luck.invalid_beginner');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.request_beginners_luck.error');
        }

        return Redirect::route('dog/blr', [$dog->id, $bitch->id])->with('error', $error);
    }

    public function getRevoke($blr)
    {
        try
        {
            // Make sure the blr's bitch belongs to this user
            if ( ! $this->currentUser->ownsDog($blr->bitch))
            {
                throw new DynastyUsersExceptions\DoesNotOwnDogException;
            }

            DB::transaction(function() use ($blr)
            {
                // Update the persistent notification
                if ( ! is_null($blr->notification))
                {
                    $notificationWasSeen = $blr->notification->isSeen();

                    $blr->notification->unseen = false;
                    $blr->notification->unread = false;
                    $blr->notification->persistent = false;
                    $blr->notification->save();
                }
                else
                {
                    $notificationWasSeen = false;
                }

                // Send a notification to the beginner
                $params = array(
                    'owner'     => $this->currentUser->nameplate(), 
                    'ownerUrl'  => URL::route('user/profile', $this->currentUser->id), 
                    'dog'       => $blr->dog->nameplate(), 
                    'dogUrl'    => URL::route('dog/profile', $blr->dog->id), 
                    'bitch'     => $blr->bitch->nameplate(), 
                    'bitchUrl'  => URL::route('dog/profile', $blr->bitch->id), 
                );

                $body = Lang::get('notifications/user.revoke_beginners_luck.to_beginner', array_map('htmlentities', array_dot($params)));
                
                if ($notificationWasSeen)
                {
                    // Mark the new notification as read and seen
                    $blr->beginner->notify($body, UserNotification::TYPE_DANGER, false, false);
                }
                else
                {
                    $blr->beginner->notify($body, UserNotification::TYPE_DANGER, true, true);
                }

                // Delete the request
                $blr->delete();
            });

            $success = Lang::get('forms/user.revoke_beginners_luck.success');

            return Redirect::route('dog/profile', $blr->bitch->id)->with('success', $success);
        }
        catch(DynastyUsersExceptions\DoesNotOwnDogException $e)
        {
            App::abort(404, "Beginners luck request does not exist!");
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.revoke_beginners_luck.error');
        }

        return Redirect::route('dog/profile', $blr->bitch->id)->with('error', $error);
    }

    public function getReject($blr)
    {
        try
        {
            // Make sure the blr was requested from this user
            if ($this->currentUser->id != $blr->beginner_id)
            {
                throw new DynastyBeginnersLuckRequestsExceptions\NotBeginnerException;
            }

            DB::transaction(function() use ($blr)
            {
                // Update the persistent notification
                if ( ! is_null($blr->notification))
                {
                    $blr->notification->unseen = false;
                    $blr->notification->unread = false;
                    $blr->notification->persistent = false;
                    $blr->notification->save();
                }

                if ( ! is_null($blr->bitch->owner))
                {
                    // Send a notification to the owner
                    $params = array(
                        'beginner'    => $blr->beginner->nameplate(), 
                        'beginnerUrl' => URL::route('user/profile', $blr->beginner->id), 
                        'dog'       => $blr->dog->nameplate(), 
                        'dogUrl'    => URL::route('dog/profile', $blr->dog->id), 
                        'bitch'     => $blr->bitch->nameplate(), 
                        'bitchUrl'  => URL::route('dog/profile', $blr->bitch->id), 
                    );

                    $body = Lang::get('notifications/user.reject_beginners_luck.to_owner', array_map('htmlentities', array_dot($params)));
                    
                    $blr->bitch->owner->notify($body, UserNotification::TYPE_DANGER);
                }


                // Delete the request
                $blr->delete();
            });

            $success = Lang::get('forms/user.reject_beginners_luck.success');

            return Redirect::route('dog/profile', $blr->bitch->id)->with('success', $success);
        }
        catch(DynastyBeginnersLuckRequestsExceptions\NotBeginnerException $e)
        {
            App::abort(404, "Beginners luck request does not exist!");
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.reject_beginners_luck.error');
        }

        return Redirect::route('dog/profile', $blr->bitch->id)->with('error', $error);
    }

    public function getAccept($blr)
    {
        $user  = $blr->user;
        $dog   = $blr->dog;
        $bitch = $blr->bitch;
        $notification = $blr->notification;

        try
        {
            // Make sure the blr was requested from this user
            if ($this->currentUser->id != $blr->beginner_id)
            {
                throw new DynastyBeginnersLuckRequestsExceptions\NotBeginnerException;
            }

            // Validate the dog
            if ($dog->isPetHomed() or ! $dog->isMale() or ! $dog->isBreedable())
            {
                throw new DynastyDogsExceptions\DogNotBreedableException;
            }

            // Validate the bitch
            if ($bitch->isPetHomed() or ! $bitch->isFemale() or ! $bitch->canBeBredImmediately())
            {
                throw new DynastyDogsExceptions\BitchNotBreedableException;
            }

            DB::transaction(function() use ($blr, $dog, $bitch, $notification)
            {
                // Update the persistent notification
                if ( ! is_null($notification))
                {
                    $notification->unseen = false;
                    $notification->unread = false;
                    $notification->persistent = false;
                    $notification->save();
                }

                // Delete the request
                $blr->delete();

                // Get all beginners luck requests on the bitch to notify them that they have been revoked
                $invalidBeginnersLuckRequests = $bitch->beginnersLuckRequests;

                foreach($invalidBeginnersLuckRequests as $invalidBeginnersLuckRequest)
                {
                    // Update the persistent notification
                    if ( ! is_null($invalidBeginnersLuckRequest->notification))
                    {
                        $invalidBeginnersLuckRequest->notification->unseen = false;
                        $invalidBeginnersLuckRequest->notification->unread = false;
                        $invalidBeginnersLuckRequest->notification->persistent = false;
                        $invalidBeginnersLuckRequest->notification->save();
                    }

                    // Send a notification to the beginner
                    $params = array(
                        'bitch'    => $bitch->nameplate(), 
                        'bitchUrl' => URL::route('dog/profile', $bitch->id), 
                    );

                    $body = Lang::get('notifications/user.accept_beginners_luck.to_unresponsive', array_map('htmlentities', array_dot($params)));
                    
                    $invalidBeginnersLuckRequest->beginner->notify($body, UserNotification::TYPE_DANGER);

                    // Delete it
                    $invalidBeginnersLuckRequest->delete();
                }

                // Breed the dogs
                $this->currentUser->breedDogsWithBeginnersLuck($dog, $bitch);

                // Send a notification to the owner
                if ( ! is_null($bitch->owner))
                {
                    $params = array(
                        'beginner'    => $this->currentUser->nameplate(), 
                        'beginnerUrl' => URL::route('user/profile', $this->currentUser->id), 
                        'dog'         => $dog->nameplate(), 
                        'dogUrl'      => URL::route('dog/profile', $dog->id), 
                        'bitch'       => $bitch->nameplate(), 
                        'bitchUrl'    => URL::route('dog/profile', $bitch->id), 
                    );

                    $body = Lang::get('notifications/user.accept_beginners_luck.to_owner', array_map('htmlentities', array_dot($params)));
                    
                    $bitch->owner->notify($body, UserNotification::TYPE_SUCCESS);
                }
            });

            $params = array(
                'owner' => $user->nameplate(), 
                'dog'   => $dog->nameplate(), 
                'bitch' => $bitch->nameplate(), 
            );

            $success = Lang::get('forms/user.accept_beginners_luck.success', array_map('htmlentities', array_dot($params)));

            return Redirect::route('dog/profile', $bitch->id)->with('success', $success);
        }
        catch(DynastyBeginnersLuckRequestsExceptions\NotBeginnerException $e)
        {
            App::abort(404, "Beginners luck request does not exist!");
        }
        catch (DynastyDogsExceptions\DogNotBreedableException $e)
        {
            $error = Lang::get('forms/user.accept_beginners_luck.dog_not_breedable');
        }
        catch (DynastyDogsExceptions\BitchNotBreedableException $e)
        {
            $error = Lang::get('forms/user.accept_beginners_luck.bitch_not_breedable');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.accept_beginners_luck.error');
        }

        return Redirect::route('dog/profile', $bitch->id)->with('error', $error);
    }

    protected function validatePair($dog, $bitch)
    {
        // Validate the dog
        if (( ! $dog->isForImmediateStud() and ! $this->currentUser->ownsDog($dog)) or ! $dog->isMale() or ! $dog->isBreedable())
        {
            throw new DynastyDogsExceptions\DogNotBreedableException;
        }

        // Validate the bitch
        if ( ! $this->currentUser->ownsDog($bitch) or ! $bitch->isFemale() or ! $bitch->canBeBredImmediately() or $bitch->hasRequestedBeginnersLuckWith($dog))
        {
            throw new DynastyDogsExceptions\BitchNotBreedableException;
        }
    }

}
