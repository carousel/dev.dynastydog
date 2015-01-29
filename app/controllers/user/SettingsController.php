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
use User;
use Dynasty\Users\Exceptions as DynastyUsersExceptions;

class SettingsController extends AuthorizedController {

    public function getIndex()
    {
        // Show the page
        return View::make('frontend/user/settings/index');
    }

    public function postUpdateBasic()
    {
        // Declare the rules for the form validation
        $rules = array(
            'display_name'  => 'required|max:50', 
            'avatar'        => 'max:255|image_url:png,gif,jpeg|image_url_size:<=150',
            'kennel_name'   => 'required|max:50', 
            'kennel_prefix' => 'max:5|unique:users,kennel_prefix,'.$this->currentUser->id,
        );

        if ( ! $this->currentUser->isUpgraded())
        {
            unset($rules['avatar']);
        }

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::back()->withInput()->with('error', $validator->errors()->first());
        }

        $this->currentUser->display_name  = Input::get('display_name');
        $this->currentUser->kennel_name   = Input::get('kennel_name');
        $this->currentUser->kennel_prefix = Input::get('kennel_prefix');
        $this->currentUser->show_gifter_level = (Input::get('show_gifter_level', 'no') == 'yes');

        if ($this->currentUser->isUpgraded())
        {
            $this->currentUser->avatar = Input::get('avatar');
        }

        $this->currentUser->save();

        // Redirect to the items management page
        return Redirect::route('user/settings')->with('success', Lang::get('forms/user.update_basic.success'));
    }

    public function postChangePassword()
    {
        // Declare the rules for the form validation
        $rules = array(
            'current_password'          => 'required',
            'new_password'              => 'required|min:3',
            'new_password_confirmation' => 'required|same:new_password'
        );

        $input = Input::all();

        // Create a new validator instance from our dynamic rules
        $validator = Validator::make($input, $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::back()->withInput()->with('error', $validator->errors()->first());
        }

        if ( ! $this->currentUser->checkPassword(Input::get('current_password'))) 
        {
            return Redirect::back()->withInput()->withErrors(array(
                'current_password' => Lang::get('forms/user.change_password.wrong_password'), 
            ));
        }

        $this->currentUser->password = Input::get('new_password');
        $this->currentUser->save();

        return Redirect::route('user/settings')->with('success', Lang::get('forms/user.change_password.success'));
    }

    public function postBlock()
    {
        // Declare the rules for the form validation
        $rules = array(
            'user_id_to_block' => 'required|exists:users,id',
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
            $userId = Input::get('user_id_to_block');

            // Cannot Block self
            if ($this->currentUser->id == $userId)
            {
                throw new DynastyUsersExceptions\CannotBlockSelfException;
            }

            $user = User::find($userId);

            // Cannot already have the user blocked
            if ($this->currentUser->hasBlocked($user))
            {
                throw new DynastyUsersExceptions\AlreadyBlockedUserException;
            }

            // Cannot block a user belonging a group            
            if ( ! $user->canBeBlocked())
            {
                throw new DynastyUsersExceptions\CannotBlockUserException;
            }

            // Block the user
            $this->currentUser->blocked()->attach($user->id);

            $success = Lang::get('forms/user.block.success', array_dot([ 'user' => $user->toArray() ]));

            return Redirect::route('user/settings')->with('success', $success);
        }
        catch(DynastyUsersExceptions\CannotBlockSelfException $e)
        {
            $error = Lang::get('forms/user.block.cannot_block_self');
        }
        catch(DynastyUsersExceptions\AlreadyBlockedUserException $e)
        {
            $error = Lang::get('forms/user.block.already_blocked', array_dot([ 'user' => $user->toArray() ]));
        }
        catch(DynastyUsersExceptions\CannotBlockUserException $e)
        {
            $error = Lang::get('forms/user.block.cannot_block', array_dot([ 'user' => $user->toArray() ]));
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.block.error');
        }

        return Redirect::route('user/settings')->withInput()->with('error', $error);
    }

    public function postUnblock()
    {
        // Declare the rules for the form validation
        $rules = array(
            'user_id_to_unblock' => 'required|exists:users,id',
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
            $userId = Input::get('user_id_to_unblock');

            $user = User::find($userId);

            // Cannot already have the user blocked
            if ( ! $this->currentUser->hasBlocked($user))
            {
                throw new DynastyUsersExceptions\NotBlockedException;
            }

            // Unblock the user
            $this->currentUser->blocked()->detach($user->id);

            $success = Lang::get('forms/user.unblock.success', array_dot([ 'user' => $user->toArray() ]));

            return Redirect::route('user/settings')->with('success', $success);
        }
        catch(DynastyUsersExceptions\NotBlockedException $e)
        {
            $error = Lang::get('forms/user.unblock.not_blocked', array_dot([ 'user' => $user->toArray() ]));
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.unblock.error');
        }

        return Redirect::route('user/settings')->withInput()->with('error', $error);
    }

    public function postChangeEmail()
    {
        // Declare the rules for the form validation
        $rules = array(
            'email' => 'required|email|max:127|unique:users,email,'.$this->currentUser->id,
            'confirm_change_email_password' => 'required',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::back()->withInput()->with('error', $validator->errors()->first());
        }

        if ( ! $this->currentUser->checkPassword(Input::get('confirm_change_email_password'))) 
        {
            return Redirect::back()->withInput()->withErrors(array(
                'confirm_change_email_password' => Lang::get('forms/user.change_email.wrong_password'), 
            ));
        }

        $this->currentUser->email = Input::get('email');
        $this->currentUser->save();

        // Redirect to the items management page
        return Redirect::route('user/settings')->with('success', Lang::get('forms/user.change_email.success'));
    }

    public function postUpdateKennelDescription()
    {
        // Declare the rules for the form validation
        $rules = array(
            'kennel_description' => 'max:10000', 
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::back()->withInput()->with('error', $validator->errors()->first());
        }

        $this->currentUser->kennel_description = Input::get('kennel_description');

        $this->currentUser->save();

        // Redirect to the items management page
        return Redirect::route('user/settings')->with('success', Lang::get('forms/user.update_kennel_description.success'));
    }

}
