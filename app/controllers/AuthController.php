<?php

class AuthController extends BaseController {

    /**
     * Account sign in.
     *
     * @return View
     */
    public function getLogin()
    {
        // Show the page
        return View::make('frontend.auth.login');
    }

    /**
     * Account sign in form processing.
     *
     * @return Redirect
     */
    public function postLogin()
    {
        // Declare the rules for the form validation
        $rules = array(
            'username' => 'required',
            'password' => 'required',
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
            // Check if the username exists
            $usernameUser = User::where('username', Input::get('username'))->first();

            if ( ! is_null($usernameUser) and $usernameUser->passwordResetRequired())
            {
                throw new Dynasty\Users\Exceptions\ResetPasswordException;
            }

            // Try to log the user in
            $user = Sentry::authenticate(Input::only('username', 'password'), Input::get('remember_me', 0));

            // Log their ip
            $user->last_login_ip = Request::getClientIp();
            $user->save();

            if ($user->isBanned())
            {
                // Log the user out
                Sentry::logout();

                $params = array(
                    'expiry_date' => $user->banned_until->format('F jS, Y g:i A'), 
                    'reason'      => $user->ban_reason, 
                );

                $banMessage = Lang::get('auth/message.login.banned', array_map('htmlentities', array_dot($params)));

                // Redirect to the home page
                return Redirect::route('home')->with('error', $banMessage);
            }

            // Need to check if this ip is banned
            $bannedIp = BannedIp::find($user->last_login_ip);

            if ( ! is_null($bannedIp))
            {
                // If they weren't banned, ban them now
                if ( ! $user->isIpBanned())
                {
                    $user->ip_banned = true;
                    $user->save();
                }

                // Log the user out
                Sentry::logout();

                $banMessage = Lang::get('auth/message.login.ip_banned');
                
                // Redirect to the home page
                return Redirect::route('home')->with('error', $banMessage);
            }
            else if ($user->isIpBanned())
            {
                // Unban them
                $user->ip_banned = false;
                $user->save();
            }
            
            // Go to the news
            $redirect = Session::get('loginRedirect', URL::route('news'));

            // Unset the page we were before from the session
            Session::forget('loginRedirect');

            // Redirect to the user's profile
            return Redirect::to($redirect)->with('success', Lang::get('auth/message.login.success'));
        }
        catch (Dynasty\Users\Exceptions\ResetPasswordException $e)
        {
            $this->messageBag->add('password', Lang::get('auth/message.reset_password'));
        }
        catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
        {
            $this->messageBag->add('username', Lang::get('auth/message.account_not_found'));
        }
        catch (Cartalyst\Sentry\Users\UserNotActivatedException $e)
        {
            $this->messageBag->add('username', Lang::get('auth/message.account_not_activated'));
        }
        catch (Cartalyst\Sentry\Throttling\UserSuspendedException $e)
        {
            $this->messageBag->add('username', Lang::get('auth/message.account_suspended'));
        }
        catch (Cartalyst\Sentry\Throttling\UserBannedException $e)
        {
            $this->messageBag->add('username', Lang::get('auth/message.account_banned'));
        }

        // Ooops.. something went wrong
        return Redirect::back()->withInput()->with('error', $this->messageBag->first());
    }

    /**
     * Account sign up.
     *
     * @return View
     */
    public function getRegister()
    {
        $breeds = Breed::whereImportable()->whereActive()->orderBy('name', 'asc')->get();
        $sexes  = Sex::orderBy('name', 'asc')->get();

        // Show the page
        return View::make('frontend.auth.register', compact('breeds', 'sexes'));
    }

    /**
     * Account sign up form processing.
     *
     * @return Redirect
     */
    public function postRegister()
    {
        if (Input::get('register_begin'))
        {
            return Redirect::route('auth/register')->withInput();
        }
        else if (Input::get('register'))
        {
            $rules = array(
                'username'              => 'required|min:4|max:32|unique:users,username',
                'display_name'          => 'required|max:50',
                'email'                 => 'required|email|max:127|unique:users,email',
                'password'              => 'required|min:3',
                'password_confirmation' => 'required|same:password',
                'referred_by_id'        => 'integer|exists:users,id', 
                'tos'                   => 'accepted',

                'register_breed'    => 'required|exists:breeds,id',
                'register_sex'      => 'required|exists:sexes,id',
                'register_dog_name' => 'required|max:32',
            );

            // Check if we need to validate the alpha code
            if (Config::get('game.require_alpha_code'))
            {
                $rules['alpha_code'] = 'required|exists:alpha_codes,code';
            }

            // Create a new validator instance from our validation rules
            $validator = Validator::make(Input::all(), $rules);

            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::back()->withInput()->withErrors($validator);
            }

            try
            {
                if (Config::get('game.require_alpha_code'))
                {
                    // Get the alpha code model to use
                    $alphaCode = AlphaCode::find(Input::get('alpha_code'));

                    // Make sure the alpha code can still be used
                    if ($alphaCode->isAtCapacity())
                    {
                        throw new Dynasty\AlphaCodes\Exceptions\AtCapacityException;
                    }
                }
                else
                {
                    $alphaCode = null;
                }

                // Get the breed model to use
                $breed = Breed::find(Input::get('register_breed'));

                // Make sure the breed is active
                if ( ! $breed->isActive())
                {
                    throw new Dynasty\Breeds\Exceptions\NotActiveException;
                }

                // Make sure the breed can be imported
                if ( ! $breed->isImportable())
                {
                    throw new Dynasty\Breeds\Exceptions\NotImportableException;
                }

                // Start transaction
                DB::transaction(function() use ($breed, $alphaCode)
                {
                    $configNewUser = Config::get('game.user.starting');

                    // Get the referrer
                    $referrer = User::find(Input::get('referred_by_id'));

                    // Set their gifter level to the first one
                    $gifterLevelId = DB::table('gifter_levels')
                        ->select('id')
                        ->where('min', '<=', $configNewUser['gifts_given'])
                        ->orderBy('min', 'desc')
                        ->take(1)
                        ->pluck('id');

                    // Set their challenge level to the first one
                    $challengeLevelId = DB::table('challenge_levels')
                        ->select('id')
                        ->where('completed_challenges', $configNewUser['total_completed_challenges'])
                        ->take(1)
                        ->pluck('id');

                    // Set their challenge level to the first one
                    $referralLevelId = DB::table('referral_levels')
                        ->select('id')
                        ->where('referred_users', $configNewUser['total_referrals'])
                        ->take(1)
                        ->pluck('id');

                    // Register the user
                    $filling = array(
                        'username'               => Input::get('username'),
                        'display_name'           => Input::get('display_name'),
                        'password'               => Input::get('password'), 
                        'email'                  => Input::get('email'),
                        'campaign_code'          => Input::get('cc'),
                        'referred_by_id'         => (is_null($referrer) ? null : $referrer->id), 
                        'registered_alpha_code'  => (is_null($alphaCode) ? null : $alphaCode->code), 
                        'allow_marketing_emails' => (Input::get('allow_marketing_emails') == 'yes' ? true : false), 
                        'kennel_name'            => $configNewUser['kennel_name'], 
                        'turns'                  => $configNewUser['turns'], 
                        'imports'                => $configNewUser['imports'], 
                        'custom_imports'         => $configNewUser['custom_imports'], 
                        'gifts_given'            => $configNewUser['gifts_given'], 
                        'gifter_level_id'        => $gifterLevelId, 
                        'show_gifter_level'      => $configNewUser['show_gifter_level'], 
                        'total_completed_challenges' => $configNewUser['total_completed_challenges'], 
                        'challenge_level_id'     => $challengeLevelId, 
                        'total_referrals'        => $configNewUser['total_referrals'], 
                        'referral_level_id'      => $referralLevelId, 
                        'created_ip'             => Request::getClientIp(), 
                    );

                    $user = Sentry::register($filling);

                    // Give them their kennel groups
                    for ($i = 1; $i <= $configNewUser['kennel_groups']; ++$i)
                    {
                        KennelGroup::create(array(
                            'user_id'      => $user->id, 
                            'name'         => 'Tab '.$i, 
                            'type_id'      => KennelGroup::PRIMARY, 
                            'dog_order_id' => KennelGroup::DOG_ORD_ID, 
                        ));
                    }

                    // Create their cemetery
                    KennelGroup::create(array(
                        'user_id'      => $user->id, 
                        'name'         => 'Cemetery', 
                        'type_id'      => KennelGroup::CEMETERY, 
                        'dog_order_id' => KennelGroup::DOG_ORD_ID, 
                    ));

                    if (Config::get('game.require_alpha_code'))
                    {
                        // Add to the alpha code's population
                        $alphaCode->population += 1;
                        $alphaCode->save();
                    }

                    // Import their dog
                    $dog = $user->importDog(Input::get('register_dog_name'), $breed, Sex::find(Input::get('register_sex')), 24);

                    // Data to be used on the email view
                    $subject = 'Welcome to '.Config::get('game.name');

                    // Set and get the activation code
                    $activationCode = $user->getActivationCode();

                    $data = array(
                        'subject'        => $subject, // We pass it in here because both the template and Mail needs it
                        'user'           => $user,
                        'activationCode' => $activationCode,
                    );

                    // Send the activation code through email
                    Mail::send('emails.auth.register_activate', $data, function($m) use ($subject, $user)
                    {
                        $replyTo = Config::get('mail.reply-to');

                        $m->subject($subject);
                        $m->to($user->email, $user->display_name);
                        $m->replyTo($replyTo['address'], $replyTo['name']);
                    });
                });

                // Redirect to the register page
                return Redirect::back()->with('success', Lang::get('auth/message.register.success'));
            }
            catch (Cartalyst\Sentry\Users\UserExistsException $e)
            {
                $this->messageBag->add('username', Lang::get('auth/message.account_already_exists'));
            }
            catch (Dynasty\AlphaCodes\Exceptions\AtCapacityException $e)
            {
                $this->messageBag->add('alpha_code', Lang::get('auth/register.alpha_code_at_capacity'));
            }
            catch (Dynasty\Breeds\Exceptions\NotActiveException $e)
            {
                $this->messageBag->add('register_breed', Lang::get('auth/register.invalid_breed'));
            }
            catch (Dynasty\Breeds\Exceptions\NotImportableException $e)
            {
                $this->messageBag->add('register_breed', Lang::get('auth/register.invalid_breed'));
            }
            // We want to catch all exceptions thrown in the transaction block and 
            // give a generic error to the user
            catch(Exception $e)
            {
                // Prepare the error message
                $error = Lang::get('auth/message.register.error');

                return Redirect::route('auth/register')->withInput()->with('error', $error);
            }

            // Ooops.. something went wrong
            return Redirect::route('auth/register')->withInput()->withErrors($this->messageBag);
        } 
    }

    /**
     * User account activation page.
     *
     * @param  string  $actvationCode
     * @return
     */
    public function getActivate()
    {
        // Show the page
        return View::make('frontend.auth.activate');
    }

    public function postActivate()
    {
        try
        {
            $activationCode = Input::get('activation_code');
            $email          = Input::get('email');

            // Get the user we are trying to activate
            $user = User::where('email', $email)->where('activation_code', $activationCode)->first();

            if (is_null($user))
            {
                throw new Cartalyst\Sentry\Users\UserNotFoundException;
            }

            $currentStage = null;

            // Start transaction
            DB::transaction(function() use ($user, $activationCode, &$currentStage)
            {
                // Try to activate this user account
                if ($user->attemptActivation($activationCode))
                {
                    $referrer = $user->referrer;

                    if ( ! is_null($referrer))
                    {
                        // Check how many referrals are attached to the same IP
                        $countSameCreatedIp = $referrer->referred()->sameCreatedIp($user->created_ip)->count();

                        $maxPerIp = Config::get('game.referral.max_per_ip');

                        if ($countSameCreatedIp < $maxPerIp)
                        {
                            $referralLevel = $referrer->referralLevel;

                            $referrer->referral_points += $referralLevel->points_per_referral;
                            $referrer->total_referrals += 1;

                            // See if they went up a level
                            if ($referralLevel->referralsNeededUntilNextLevel($referrer->total_referrals) < 1)
                            {
                                // Level them up
                                if ( ! is_null($nextLevel = $referralLevel->getNextReferralLevel()))
                                {
                                    $referrer->referral_level_id = $nextLevel->id;
                                }
                            }

                            $referrer->save();
                        }
                    }

                    // Get their first dog
                    $dog = $user->dogs()->first();

                    // Start the tutorial
                    $currentStage = $user->advanceTutorial(array('dog_id' => $dog->id));

                    // Log the user in
                    Sentry::login($user, false);
                }
            });

            return ( ! is_null($currentStage))
                ? Redirect::to($currentStage->tutorialStage->uri)->with('success', Lang::get('auth/message.activate.success'))
                : Redirect::route('news')->with('success', Lang::get('auth/message.activate.success'));
        }
        catch (Exception $e)
        {
            $error = Lang::get('auth/message.activate.error');
        }

        // Ooops.. something went wrong
        return Redirect::route('auth/activate')->withInput()->with('error', $error);
    }

    /**
     * Forgot password page.
     *
     * @return View
     */
    public function getForgotPassword()
    {
        // Show the page
        return View::make('frontend.auth.forgot_password');
    }

    /**
     * Forgot password form processing page.
     *
     * @return Redirect
     */
    public function postForgotPassword()
    {
        // Declare the rules for the validator
        $rules = array(
            'email' => 'required|email', // See note below in catch about |exists:users,email
        );

        // Create a new validator instance from our dynamic rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('auth/forgot_password')->withInput()->withErrors($validator);
        }

        try
        {
            // Get the user password recovery code
            $user = Sentry::getUserProvider()->getEmptyUser()->where('email', '=', Input::get('email'))->first();

            if (is_null($user))
            {
                throw new Cartalyst\Sentry\Users\UserNotFoundException;
            }

            $recoveryCode = $user->getResetPasswordCode();

            $subject = 'Password Recovery for '.Config::get('game.name').' Account';

            $data = array(
                'subject'      => $subject, // We pass it in here because both the template and Mail needs it
                'user'         => $user,
                'recoveryCode' => $recoveryCode,
            );


            // Send the activation code through email
            Mail::send('emails.auth.forgot_password', $data, function($m) use ($user, $subject)
            {
                $replyTo = Config::get('mail.reply-to');

                $m->subject($subject);
                $m->to($user->email, $user->display_name);
                $m->replyTo($replyTo['address'], $replyTo['name']);
            });
        }
        catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
        {
            // Even though the email was not found, we will pretend
            // we have sent the password reset code through email,
            // this is a security measure against hackers.
        }

        //  Redirect to the forgot password
        return Redirect::route('auth/forgot_password')->with('success', Lang::get('auth/message.forgot_password.success'));
    }

    /**
     * Forgot Password Confirmation page.
     *
     * @param  string  $passwordResetCode
     * @return View
     */
    public function getForgotPasswordConfirm($passwordResetCode = null)
    {
        try
        {
            // Find the user using the password reset code
            $user = Sentry::getUserProvider()->findByResetPasswordCode($passwordResetCode);
        }
        catch(Cartalyst\Sentry\Users\UserNotFoundException $e)
        {
            // Redirect to the forgot password page
            return Redirect::route('auth/forgot_password')->with('error', Lang::get('auth/message.account_not_found'));
        }

        // Show the page
        return View::make('frontend.auth.forgot_password_confirm');
    }

    /**
     * Forgot Password Confirmation form processing page.
     *
     * @param  string  $passwordResetCode
     * @return Redirect
     */
    public function postForgotPasswordConfirm($passwordResetCode = null)
    {
        // Declare the rules for the form validation
        $rules = array(
            'password'              => 'required|min:6',
            'password_confirmation' => 'required|same:password',
        );

        // Create a new validator instance from our dynamic rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('auth/forgot_password_confirm', $passwordResetCode)->withInput()->withErrors($validator);
        }

        try
        {
            // Find the user using the password reset code
            $user = Sentry::getUserProvider()->findByResetPasswordCode($passwordResetCode);

            // Attempt to reset the user password
            if ($user->attemptResetPassword($passwordResetCode, Input::get('password')))
            {
                $user->password_reset_required = false;
                $user->save();
                
                // Password successfully reseted
                return Redirect::route('home')->with('success', Lang::get('auth/message.forgot_password_confirm.success'));
            }
            else
            {
                // Ooops.. something went wrong
                return Redirect::back()->with('error', Lang::get('auth/message.forgot_password_confirm.error'));
            }
        }
        catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
        {
            // Redirect to the forgot password page
            return Redirect::route('auth/forgot_password')->with('error', Lang::get('auth/message.account_not_found'));
        }
    }

    /**
     * Sign out page.
     *
     * @return Redirect
     */
    public function getLogout()
    {
        // Log the user out
        Sentry::logout();

        // Redirect to the users page
        return Redirect::route('home')->with('success', Lang::get('auth/message.logout.success'));
    }

}
