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
use Dog;
use Dynasty;
use Dynasty\Dogs\Exceptions as DynastyDogsExceptions;
use Dynasty\Users\Exceptions as DynastyUsersExceptions;

class ReferralsController extends AuthorizedController {

    public function getIndex()
    {
        $kennelGroups = $this->currentUser->kennelGroups()->whereNotCemetery()
            ->whereHas('dogs', function($query)
                {
                    $query->whereComplete()->whereAlive()->whereWorked();
                })
            ->with(array(
                'dogs' => function($query)
                {
                    $query->whereComplete()->whereAlive()->whereWorked()->orderBy('id', 'asc');
                }))
            ->orderBy('id', 'asc')->get();

        // Show the page
        return View::make('frontend/user/referrals/index', compact('kennelGroups'));
    }

    public function postResetStatus()
    {
        // Declare the rules for the form validation
        $rules = array(
            'dog_id' => 'required|exists:dogs,id',
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
            $dog = Dog::find(Input::get('dog_id'));

            // User must own the dog
            if ( ! $this->currentUser->ownsDog($dog))
            {
                throw new DynastyUsersExceptions\DoesNotOwnDogException;
            }

            // Dog must be worked
            if ( ! $dog->isWorked())
            {
                throw new DynastyDogsExceptions\NotWorkedException;
            }


            if ($dog->isDeceased())
            {
                throw new DynastyDogsExceptions\DeceasedException;
            }

            // Make sure the user has enough points
            $cost = Config::get('game.referral.reset_dog_status_cost');

            if ( ! $this->currentUser->canAffordReferralPoints($cost))
            {
                throw new DynastyUsersExceptions\NotEnoughReferralPointsException;
            }

            // Start transaction
            DB::transaction(function() use ($dog, $cost)
            {
                // Take away the points
                $this->currentUser->referral_points -= $cost;
                $this->currentUser->save();

                // Make the dog unworked
                $dog->worked = false;
                $dog->save();
            });

            $params = array(
                'dog' => array(
                    'id' => $dog->id, 
                    'full_name' => $dog->fullName(), 
                ), 
            );

            $success = Lang::get('forms/dog.reset_worked_status.success', array_dot($params));

            return Redirect::route('user/referrals')->with('success', $success);
        }
        catch(DynastyUsersExceptions\DoesNotOwnDogException $e)
        {
            $error = Lang::get('forms/dog.reset_worked_status.wrong_owner');
        }
        catch(DynastyDogsExceptions\NotWorkedException $e)
        {
            $error = Lang::get('forms/dog.reset_worked_status.not_worked');
        }
        catch(DynastyDogsExceptions\DeceasedException $e)
        {
            $error = Lang::get('forms/dog.reset_worked_status.deceased');
        }
        catch(DynastyUsersExceptions\NotEnoughReferralPointsException $e)
        {
            $error = Lang::get('forms/dog.reset_worked_status.not_enough_referral_points');
        }
        // We want to catch all exceptions thrown in the transaction block and 
        // give a generic error to the user
        catch(Exception $e)
        {
            $error = Lang::get('forms/dog.reset_worked_status.error');
        }

        return Redirect::route('user/referrals')->withInput()->with('error', $error);
    }

    public function postExchange()
    {
        // Declare the rules for the form validation
        $rules = array(
            'credits' => 'required|numeric|between:1,10',
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
            // Make sure the user has enough points
            $pointsPerCredit = Config::get('game.referral.points_per_credit');

            $credits = Input::get('credits');
            $cost    = $pointsPerCredit * $credits;

            if ( ! $this->currentUser->canAffordReferralPoints($cost))
            {
                throw new DynastyUsersExceptions\NotEnoughReferralPointsException;
            }

            // Take away the points
            $this->currentUser->referral_points -= $cost;

            // Add the credits
            $this->currentUser->credits += $credits;

            // Save the user
            $this->currentUser->save();

            $params = array(
                'referral_points' => Dynasty::referralPoints($cost), 
                'credits'         => Dynasty::credits($credits), 
            );

            $success = Lang::get('forms/user.trade_referral_points.success', array_dot($params));

            return Redirect::route('user/referrals')->with('success', $success);
        }
        catch(DynastyUsersExceptions\NotEnoughReferralPointsException $e)
        {
            $error = Lang::get('forms/user.trade_referral_points.not_enough_referral_points');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.trade_referral_points.error');
        }

        return Redirect::route('user/referrals')->withInput()->with('error', $error);
    }

}
