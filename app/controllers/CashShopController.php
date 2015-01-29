<?php

use Dynasty\Users\Exceptions as DynastyUsersExceptions;

class CashShopController extends AuthorizedController {

    public function getIndex()
    {
        // Check if there was a payment that went through
        $this->successfulPayment();

        // Show the page
        return View::make('frontend.cash_shop.index', compact('payment'));
    }

    protected function successfulPayment()
    {
        $st  = Input::get('st', null);
        $amt = Input::get('amt', 0);

        if ($st !== 'Completed')
        {
            return null;
        }

        $creditPackage = CreditPackage::where('cost', $amt)->first();

        if (is_null($creditPackage))
        {
            return null;
        }

        $credits = Dynasty::credits($creditPackage->credit_amount);

        $success = Lang::choice('forms/user.paypal.success', $creditPackage->credit_amount, [ 'credits' => $credits ]);

        Session::flash('success', $success);
    }

    public function postPurchaseUpgrade()
    {
        try
        {
            $upgradeCost = Config::get('game.user.upgrade_cost');
            $redeemable  = max(0, min($upgradeCost, $this->currentUser->banked_credits));
            $totalCost   = $upgradeCost - $redeemable;

            // Make sure the user has enough credits
            if ( ! $this->currentUser->canAffordCredits($totalCost))
            {
                throw new DynastyUsersExceptions\NotEnoughCreditsException;
            }

            // Start transaction
            DB::transaction(function() use ($upgradeCost, $redeemable, $totalCost)
            {
                // Take away what is needed
                $this->currentUser->banked_credits -= $redeemable;
                $this->currentUser->credits -= $totalCost;

                // Give the upgrade
                $this->currentUser->upgraded_until = $this->currentUser->isUpgraded()
                    ? $this->currentUser->upgraded_until->addDays(30)
                    : Carbon::now()->addDays(30);

                $this->currentUser->save();

                // Log the transaction
                $this->currentUser->logCreditTransaction(UserCreditTransaction::UPGRADE, 1, $totalCost, $totalCost, array('cost_of_upgrade' => $upgradeCost, 'banked_used' => $redeemable));
            });

            $diffInDays = $this->currentUser->upgraded_until->diffInDays();
            $expires    = number_format($diffInDays).' '.Str::plural('day', $diffInDays);
            $success    = Lang::get('forms/user.purchase_upgrade.success', array_dot([ 'expires' => $expires ]));

            return Redirect::route('cash_shop')->with('success', $success);
        }
        catch (DynastyUsersExceptions\NotEnoughCreditsException $e)
        {
            $error = Lang::get('forms/user.purchase_upgrade.not_enough_credits');
        }
        catch (Exception $e)
        {
            $error = Lang::get('forms/user.purchase_upgrade.error');
        }

        return Redirect::route('cash_shop')->with('error', $error);
    }

    public function postPurchaseTurns()
    {
        // Declare the rules for the form validation
        $rules = array(
            'turn_package_id' => 'required|exists:turn_packages,id',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('cash_shop')->withInput()->withErrors($validator);
        }

        try
        {
            // Make sure the user has enough credits
            $turnPackage = TurnPackage::find(Input::get('turn_package_id'));

            if ( ! $this->currentUser->canAffordCredits($turnPackage->credit_cost))
            {
                throw new DynastyUsersExceptions\NotEnoughCreditsException;
            }

            // Start transaction
            DB::transaction(function() use ($turnPackage)
            {
                // Take away the credits
                $this->currentUser->credits -= $turnPackage->credit_cost;

                // Give the turns
                $this->currentUser->turns += $turnPackage->amount;

                // Save the changes
                $this->currentUser->save();

                // Log the transaction
                $this->currentUser->logCreditTransaction(UserCreditTransaction::TURN_PACKAGE.UserCreditTransaction::SEPERATOR.$turnPackage->amount, 1, $turnPackage->credit_cost, $turnPackage->credit_cost);
            });

            $success = Lang::get('forms/user.purchase_turns.success', array_dot([ 'turns' => Dynasty::turns($turnPackage->amount), 'credits' => Dynasty::credits($turnPackage->credit_cost) ]));

            return Redirect::route('cash_shop')->with('success', $success);
        }
        catch (DynastyUsersExceptions\NotEnoughCreditsException $e)
        {
            $error = Lang::get('forms/user.purchase_turns.not_enough_credits');
        }
        catch (Exception $e)
        {
            $error = Lang::get('forms/user.purchase_turns.error');
        }

        return Redirect::route('cash_shop')->with('error', $error);
    }

    public function postPurchaseImports()
    {
        $min = Config::get('game.import.min_purchase_amount');
        $max = Config::get('game.import.max_purchase_amount');

        // Declare the rules for the form validation
        $rules = array(
            'number_of_imports' => 'required|between:'.$min.','.$max,
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('cash_shop')->withInput()->withErrors($validator);
        }

        try
        {
            // Make sure the user has enough credits
            $imports        = Input::get('number_of_imports');
            $pricePerImport = Config::get('game.import.price');
            $totalCost      = $imports * $pricePerImport;

            if ( ! $this->currentUser->canAffordCredits($totalCost))
            {
                throw new DynastyUsersExceptions\NotEnoughCreditsException;
            }

            // Start transaction
            DB::transaction(function() use ($imports, $pricePerImport, $totalCost)
            {
                // Take away the credits
                $this->currentUser->credits -= $totalCost;

                // Give the imports
                $this->currentUser->imports += $imports;

                // Save the changes
                $this->currentUser->save();

                // Log the transaction
                $this->currentUser->logCreditTransaction(UserCreditTransaction::IMPORT, $imports, $pricePerImport, $totalCost);
            });

            $imports = strtolower(Dynasty::imports($imports));

            $success = Lang::get('forms/user.purchase_imports.success', array_dot([ 'imports' => $imports, 'credits' => Dynasty::credits($totalCost) ]));

            return Redirect::route('cash_shop')->with('success', $success);
        }
        catch (DynastyUsersExceptions\NotEnoughCreditsException $e)
        {
            $error = Lang::get('forms/user.purchase_imports.not_enough_credits');
        }
        catch (Exception $e)
        {
            $error = Lang::get('forms/user.purchase_imports.error');
        }

        return Redirect::route('cash_shop')->with('error', $error);
    }

    public function postPurchaseCustomImports()
    {
        $min = Config::get('game.custom_import.min_purchase_amount');
        $max = Config::get('game.custom_import.max_purchase_amount');

        // Declare the rules for the form validation
        $rules = array(
            'number_of_custom_imports' => 'required|between:'.$min.','.$max,
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('cash_shop')->withInput()->withErrors($validator);
        }

        try
        {
            // Make sure the user has enough credits
            $customImports        = Input::get('number_of_custom_imports');
            $pricePerCustomImport = Config::get('game.custom_import.price');
            $totalCost            = $customImports * $pricePerCustomImport;

            if ( ! $this->currentUser->canAffordCredits($totalCost))
            {
                throw new DynastyUsersExceptions\NotEnoughCreditsException;
            }

            // Start transaction
            DB::transaction(function() use ($customImports, $pricePerCustomImport, $totalCost)
            {
                // Take away the credits
                $this->currentUser->credits -= $totalCost;

                // Give the custom imports
                $this->currentUser->custom_imports += $customImports;

                // Save the changes
                $this->currentUser->save();

                // Log the transaction
                $this->currentUser->logCreditTransaction(UserCreditTransaction::CUSTOM_IMPORT, $customImports, $pricePerCustomImport, $totalCost);
            });

            $custom_imports = strtolower(Dynasty::customImports($customImports));

            $success = Lang::get('forms/user.purchase_custom_imports.success', array_dot([ 'custom_imports' => $custom_imports, 'credits' => Dynasty::credits($totalCost) ]));

            return Redirect::route('cash_shop')->with('success', $success);
        }
        catch (DynastyUsersExceptions\NotEnoughCreditsException $e)
        {
            $error = Lang::get('forms/user.purchase_custom_imports.not_enough_credits');
        }
        catch (Exception $e)
        {
            $error = Lang::get('forms/user.purchase_custom_imports.error');
        }

        return Redirect::route('cash_shop')->with('error', $error);
    }

    public function postGiftCredits()
    {
        $redirectUrl = parse_url(Request::header('referer'), PHP_URL_PATH);

        // Declare the rules for the form validation
        $rules = array(
            'number_of_credits_to_gift' => 'required|numeric|min:1',
            'credit_receiver_id'        => 'required|exists:users,id',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::to($redirectUrl)->withInput()->withErrors($validator);
        }

        try
        {
            $receiverId = Input::get('credit_receiver_id');

            // The credits must be sent to another user
            if ($this->currentUser->id == $receiverId)
            {
                throw new DynastyUsersExceptions\CannotGiftSelfException;
            }

            // Make sure they have that many credits to send
            if ( ! $this->currentUser->canAffordCredits(Input::get('number_of_credits_to_gift')))
            {
                throw new DynastyUsersExceptions\NotEnoughCreditsException;
            }

            $receiver = User::find($receiverId);

            // Make sure the user hasn't blocked the receiver
            if ($this->currentUser->hasBlocked($receiver))
            {
                throw new DynastyUsersExceptions\BlockedUserException;
            }

            // Make sure the receiver hasn't blocked the user
            if ($receiver->hasBlocked($this->currentUser))
            {
                throw new DynastyUsersExceptions\IsBlockedException;
            }

            $credits = null;

            // Start transaction
            DB::transaction(function() use ($receiver, &$credits)
            {
                $credits = Input::get('number_of_credits_to_gift');

                // Take away the credits
                $this->currentUser->credits -= $credits;
                $this->currentUser->save();

                // Give the credits
                $receiver->credits += $credits;
                $receiver->save();

                // Log the gift
                $this->currentUser->gifted($credits);

                // Log the transfer
                $this->currentUser->logCreditTransfer($receiver->id, $credits);

                // Notify the receiver
                $message = htmlentities(Purifier::clean(Input::get('message_to_send_with_credits'), 'strip_all'));

                if (Input::get('gift_credits_anonymously') == 'yes')
                {
                    if (strlen($message))
                    {
                        $params = array(
                            'credits' => Dynasty::credits($credits), 
                            'route'   => URL::route('cash_shop'), 
                            'message' => $message, 
                        );
                        
                        $body = Lang::get('notifications/user.gift_credits.to_receiver_anonymous_message', array_map('htmlentities', array_dot($params)));
                    }
                    else
                    {
                        $params = array(
                            'credits' => Dynasty::credits($credits), 
                            'route'   => URL::route('cash_shop'), 
                        );

                        $body = Lang::get('notifications/user.gift_credits.to_receiver_anonymous', array_map('htmlentities', array_dot($params)));
                    }
                }
                else
                {
                    if (strlen($message))
                    {
                        $params = array(
                            'credits' => Dynasty::credits($credits), 
                            'route'   => URL::route('cash_shop'), 
                            'sender'  => $this->currentUser->toArray(), 
                            'message' => $message, 
                        );

                        $body = Lang::get('notifications/user.gift_credits.to_receiver_message', array_map('htmlentities', array_dot($params)));
                    }
                    else
                    {
                        $params = array(
                            'credits' => Dynasty::credits($credits), 
                            'route'   => URL::route('cash_shop'), 
                            'sender'  => $this->currentUser->toArray(), 
                        );

                        $body = Lang::get('notifications/user.gift_credits.to_receiver', array_map('htmlentities', array_dot($params)));
                    }
                }

                $receiver->notify($body, UserNotification::TYPE_SUCCESS);

                // Notify the sender
                $params = array(
                    'credits'  => Dynasty::credits($credits), 
                    'receiver' => $receiver->toArray(), 
                );

                $body = Lang::get('notifications/user.gift_credits.to_sender', array_map('htmlentities', array_dot($params)));

                $this->currentUser->notify($body, UserNotification::TYPE_INFO, false, false);
            });

            $success = Lang::get('forms/user.gift_credits.success', array_dot([ 'receiver' => $receiver->toArray(), 'credits' => Dynasty::credits($credits) ]));

            return Redirect::to($redirectUrl)->with('success', $success);
        }
        catch (DynastyUsersExceptions\CannotGiftSelfException $e)
        {
            $error = Lang::get('forms/user.gift_credits.cannot_gift_self');
            
            return Redirect::to($redirectUrl)->withInput()->withErrors(['credit_receiver_id' => $error]);
        }
        catch (DynastyUsersExceptions\NotEnoughCreditsException $e)
        {
            $error = Lang::get('forms/user.gift_credits.not_enough_credits');
            
            return Redirect::to($redirectUrl)->withInput()->withErrors(['number_of_credits_to_gift' => $error]);
        }
        catch (DynastyUsersExceptions\BlockedUserException $e)
        {
            $error = Lang::get('forms/user.gift_credits.blocked_receiver');
            
            return Redirect::to($redirectUrl)->withInput()->withErrors(['credit_receiver_id' => $error]);
        }
        catch (DynastyUsersExceptions\IsBlockedException $e)
        {
            $error = Lang::get('forms/user.gift_credits.is_blocked');

            return Redirect::to($redirectUrl)->withInput()->withErrors(['credit_receiver_id' => $error]);
        }
        catch (Exception $e)
        {
            $error = Lang::get('forms/user.gift_credits.error');

            // Do this to close the modal on the profile page
            Input::merge(array('gift_credits' => null));
        }

        return Redirect::to($redirectUrl)->withInput()->with('error', $error);
    }

    public function postGiftUpgrade()
    {
        $redirectUrl = parse_url(Request::header('referer'), PHP_URL_PATH);

        // Declare the rules for the form validation
        $rules = array(
            'upgrade_receiver_id' => 'required|exists:users,id',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::to($redirectUrl)->withInput()->withErrors($validator);
        }

        try
        {
            $receiverId = Input::get('upgrade_receiver_id');

            // The upgrade must be sent to another user
            if ($this->currentUser->id == $receiverId)
            {
                throw new DynastyUsersExceptions\CannotGiftSelfException;
            }

            // Make sure the user has enough credits
            $upgradeCost = Config::get('game.user.upgrade_cost');

            if ( ! $this->currentUser->canAffordCredits($upgradeCost))
            {
                throw new DynastyUsersExceptions\NotEnoughCreditsException;
            }

            $receiver = User::find($receiverId);

            // Make sure the user hasn't blocked the receiver
            if ($this->currentUser->hasBlocked($receiver))
            {
                throw new DynastyUsersExceptions\BlockedUserException;
            }

            // Make sure the receiver hasn't blocked the user
            if ($receiver->hasBlocked($this->currentUser))
            {
                throw new DynastyUsersExceptions\IsBlockedException;
            }

            // Start transaction
            DB::transaction(function() use ($receiver, $upgradeCost)
            {
                // Take away the credits
                $this->currentUser->credits -= $upgradeCost;

                // Bank credits
                $this->currentUser->banked_credits += Config::get('game.user.gift_upgrade_bonus');

                // Save the user
                $this->currentUser->save();

                // Give the upgrade
                $receiver->upgraded_until = $receiver->isUpgraded()
                    ? $receiver->upgraded_until->addDays(30)
                    : Carbon::now()->addDays(30);

                $receiver->save();

                // Clean the message
                $message = htmlentities(Purifier::clean(Input::get('message_to_send_with_upgrade'), 'strip_all'));

                // Log the transaction
                $this->currentUser->logCreditTransaction(UserCreditTransaction::GIFT_UPGRADE, 1, $upgradeCost, $upgradeCost, array('gifted_to' => $receiver->id, 'message' => $message));

                // Notify the receiver
                if (Input::get('gift_upgrade_anonymous') == 'yes')
                {
                    if (strlen($message))
                    {
                        $params = array(
                            'route'   => URL::route('cash_shop'), 
                            'message' => $message, 
                        );
                        
                        $body = Lang::get('notifications/user.gift_upgrade.to_receiver_anonymous_message', array_map('htmlentities', array_dot($params)));
                    }
                    else
                    {
                        $params = array(
                            'route'   => URL::route('cash_shop'), 
                        );

                        $body = Lang::get('notifications/user.gift_upgrade.to_receiver_anonymous', array_map('htmlentities', array_dot($params)));
                    }
                }
                else
                {
                    if (strlen($message))
                    {
                        $params = array(
                            'route'   => URL::route('cash_shop'), 
                            'sender'  => $this->currentUser->toArray(), 
                            'message' => $message, 
                        );

                        $body = Lang::get('notifications/user.gift_upgrade.to_receiver_message', array_map('htmlentities', array_dot($params)));
                    }
                    else
                    {
                        $params = array(
                            'route'  => URL::route('cash_shop'), 
                            'sender' => $this->currentUser->toArray(), 
                        );

                        $body = Lang::get('notifications/user.gift_upgrade.to_receiver', array_map('htmlentities', array_dot($params)));
                    }
                }

                $receiver->notify($body, UserNotification::TYPE_SUCCESS);

                // Notify the sender
                $params = array(
                    'receiver' => $receiver->toArray(), 
                    'credits'  => Dynasty::credits($upgradeCost), 
                );

                $body = Lang::get('notifications/user.gift_upgrade.to_sender', array_map('htmlentities', array_dot($params)));

                $this->currentUser->notify($body, UserNotification::TYPE_INFO, false, false);
            });

            $success = Lang::get('forms/user.gift_upgrade.success', array_dot([ 'receiver' => $receiver->toArray(), 'credits' => Dynasty::credits($upgradeCost) ]));

            return Redirect::to($redirectUrl)->with('success', $success);
        }
        catch (DynastyUsersExceptions\CannotGiftSelfException $e)
        {
            $error = Lang::get('forms/user.gift_upgrade.cannot_gift_self');
            
            return Redirect::to($redirectUrl)->withInput()->withErrors(['upgrade_receiver_id' => $error]);
        }
        catch (DynastyUsersExceptions\NotEnoughCreditsException $e)
        {
            $error = Lang::get('forms/user.gift_upgrade.not_enough_credits');
            
            return Redirect::to($redirectUrl)->withInput()->withErrors(['gift_upgrade_cost' => $error]);
        }
        catch (DynastyUsersExceptions\BlockedUserException $e)
        {
            $error = Lang::get('forms/user.gift_upgrade.blocked_receiver');
            
            return Redirect::to($redirectUrl)->withInput()->withErrors(['upgrade_receiver_id' => $error]);
        }
        catch (DynastyUsersExceptions\IsBlockedException $e)
        {
            $error = Lang::get('forms/user.gift_upgrade.is_blocked');

            return Redirect::to($redirectUrl)->withInput()->withErrors(['upgrade_receiver_id' => $error]);
        }
        catch (Exception $e)
        {
            $error = Lang::get('forms/user.gift_upgrade.error');

            // Do this to close the modal on the profile page
            Input::merge(array('gift_upgrade' => null));
        }

        return Redirect::to($redirectUrl)->withInput()->with('error', $error);
    }

    public function postGiftTurns()
    {
        $redirectUrl = parse_url(Request::header('referer'), PHP_URL_PATH);
        
        // Declare the rules for the form validation
        $rules = array(
            'gift_turn_package_id'  => 'required|exists:turn_packages,id',
            'turn_receiver_id'      => 'required|exists:users,id',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::to($redirectUrl)->withInput()->withErrors($validator);
        }

        try
        {
            $receiverId = Input::get('turn_receiver_id');

            // The turns must be sent to another user
            if ($this->currentUser->id == $receiverId)
            {
                throw new DynastyUsersExceptions\CannotGiftSelfException;
            }

            // Make sure the user has enough credits
            $turnPackage = TurnPackage::find(Input::get('gift_turn_package_id'));

            if ( ! $this->currentUser->canAffordCredits($turnPackage->credit_cost))
            {
                throw new DynastyUsersExceptions\NotEnoughCreditsException;
            }

            $receiver = User::find($receiverId);

            // Make sure the user hasn't blocked the receiver
            if ($this->currentUser->hasBlocked($receiver))
            {
                throw new DynastyUsersExceptions\BlockedUserException;
            }

            // Make sure the receiver hasn't blocked the user
            if ($receiver->hasBlocked($this->currentUser))
            {
                throw new DynastyUsersExceptions\IsBlockedException;
            }

            // Start transaction
            DB::transaction(function() use ($receiver, $turnPackage)
            {
                // Take away the credits
                $this->currentUser->credits -= $turnPackage->credit_cost;
                $this->currentUser->save();

                // Give the turns
                $receiver->turns += $turnPackage->amount;
                $receiver->save();

                // Clean the message
                $message = htmlentities(Purifier::clean(Input::get('message_to_send_with_turns'), 'strip_all'));

                // Log the transaction
                $this->currentUser->logCreditTransaction(UserCreditTransaction::GIFT_TURN_PACKAGE.UserCreditTransaction::SEPERATOR.$turnPackage->amount, 1, $turnPackage->credit_cost, $turnPackage->credit_cost, array('gifted_to' => $receiver->id, 'message' => $message, 'id' => $turnPackage->id));

                // Notify the receiver
                if (Input::get('gift_turns_anonymous') == 'yes')
                {
                    if (strlen($message))
                    {
                        $params = array(
                            'turns' => Dynasty::turns($turnPackage->amount), 
                            'route'   => URL::route('cash_shop'), 
                            'message' => $message, 
                        );
                        
                        $body = Lang::get('notifications/user.gift_turns.to_receiver_anonymous_message', array_map('htmlentities', array_dot($params)));
                    }
                    else
                    {
                        $params = array(
                            'turns' => Dynasty::turns($turnPackage->amount), 
                            'route'   => URL::route('cash_shop'), 
                        );

                        $body = Lang::get('notifications/user.gift_turns.to_receiver_anonymous', array_map('htmlentities', array_dot($params)));
                    }
                }
                else
                {
                    if (strlen($message))
                    {
                        $params = array(
                            'turns' => Dynasty::turns($turnPackage->amount), 
                            'route'   => URL::route('cash_shop'), 
                            'sender'  => $this->currentUser->toArray(), 
                            'message' => $message, 
                        );

                        $body = Lang::get('notifications/user.gift_turns.to_receiver_message', array_map('htmlentities', array_dot($params)));
                    }
                    else
                    {
                        $params = array(
                            'turns' => Dynasty::turns($turnPackage->amount), 
                            'route'   => URL::route('cash_shop'), 
                            'sender'  => $this->currentUser->toArray(), 
                        );

                        $body = Lang::get('notifications/user.gift_turns.to_receiver', array_map('htmlentities', array_dot($params)));
                    }
                }

                $receiver->notify($body, UserNotification::TYPE_SUCCESS);

                // Notify the sender
                $params = array(
                    'turns'    => Dynasty::turns($turnPackage->amount), 
                    'credits'  => Dynasty::credits($turnPackage->credit_cost), 
                    'receiver' => $receiver->toArray(), 
                );

                $body = Lang::get('notifications/user.gift_turns.to_sender', array_map('htmlentities', array_dot($params)));

                $this->currentUser->notify($body, UserNotification::TYPE_INFO, false, false);
            });

            $success = Lang::get('forms/user.gift_turns.success', array_dot([ 'receiver' => $receiver->toArray(), 'turns' => Dynasty::turns($turnPackage->amount), 'credits' => Dynasty::credits($turnPackage->credit_cost) ]));

            return Redirect::to($redirectUrl)->with('success', $success);
        }
        catch (DynastyUsersExceptions\CannotGiftSelfException $e)
        {
            $error = Lang::get('forms/user.gift_turns.cannot_gift_self');
            
            return Redirect::to($redirectUrl)->withInput()->withErrors(['turn_receiver_id' => $error]);
        }
        catch (DynastyUsersExceptions\NotEnoughCreditsException $e)
        {
            $error = Lang::get('forms/user.gift_turns.not_enough_credits');
            
            return Redirect::to($redirectUrl)->withInput()->withErrors(['gift_turn_package_id' => $error]);
        }
        catch (DynastyUsersExceptions\BlockedUserException $e)
        {
            $error = Lang::get('forms/user.gift_turns.blocked_receiver');
            
            return Redirect::to($redirectUrl)->withInput()->withErrors(['turn_receiver_id' => $error]);
        }
        catch (DynastyUsersExceptions\IsBlockedException $e)
        {
            $error = Lang::get('forms/user.gift_turns.is_blocked');

            return Redirect::to($redirectUrl)->withInput()->withErrors(['turn_receiver_id' => $error]);
        }
        catch (Exception $e)
        {
            $error = Lang::get('forms/user.gift_turns.error');

            // Do this to close the modal on the profile page
            Input::merge(array('gift_turns' => null));
        }

        return Redirect::to($redirectUrl)->withInput()->with('error', $error);
    }

}
