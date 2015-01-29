<?php

class ChatController extends BaseController {

    public function getMessages()
    {
        return View::make('frontend.chat.messages');
    }

    public function postCreate()
    {
        // Make sure they aren't chat banned
        if ( $this->currentUser->isBannedFromChat())
        {
            return Response::make('Unauthorized', 403);
        }

        // Declare the rules for the form validation
        $rules = array(
            'body'  => 'required',
            'hex'   => 'required|hexcolor',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Just return like nothing happened
            return Response::json();
        }

        // Create the message
        ChatMessage::create(array(
            'author_id' => $this->currentUser->id, 
            'hex'       => Input::get('hex'), 
            'body'      => Purifier::clean(Purifier::clean(Input::get('body'), 'strip_all'), 'only_linkify'), 
        ));

        return Response::json();
    }

    public function postDelete()
    {
        if ( ! $this->currentUser->hasAnyAccess(['admin']))
        {
            return Response::make('Unauthorized', 403);
        }

        $chatMessage = ChatMessage::find(Input::get('id', null));

        if (is_null($chatMessage))
        {
            return Response::json();
        }

        // Delete it
        $chatMessage->delete();

        return Response::json();
    }

    public function postGiveTurns()
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
            return Redirect::back()->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            // Make sure the user has enough credits
            $turnPackage = TurnPackage::find(Input::get('turn_package_id'));

            if ( ! $this->currentUser->canAffordCredits($turnPackage->credit_cost))
            {
                throw new Dynasty\Users\Exceptions\NotEnoughCreditsException;
            }

            // Start transaction
            DB::transaction(function() use ($turnPackage)
            {
                // Create the chat turn
                $chatTurn = ChatTurn::create(array(
                    'user_id' => $this->currentUser->id, 
                    'amount'  => $turnPackage->amount, 
                ));

                // Log the credit transaction
                $this->currentUser->logCreditTransaction(UserCreditTransaction::CHAT_TURN_PACKAGE.UserCreditTransaction::SEPERATOR.$turnPackage->amount, 1, $turnPackage->credit_cost, $turnPackage->credit_cost, array('id' => $turnPackage->id));

                // Post about it in chat
                $body = '<a href="'.URL::route('chat/claim_turns', $chatTurn->id).'">Here are some turns for everyone! Click here to pick one up. (Your page will be refreshed.)</a>';

                // Create the message
                ChatMessage::create(array(
                    'author_id' => $this->currentUser->id, 
                    'hex'       => '000000', 
                    'body'      => $body, 
                ));
            });

            $params = array(
                'turns' => Dynasty::turns($turnPackage->amount), 
            );

            $success = Lang::get('forms/user.give_chat_turns.success', $params);

            return Redirect::back()->with('success', $success);
        }
        catch(Dynasty\Users\Exceptions\NotEnoughCreditsException $e)
        {
            $error = Lang::get('forms/user.give_chat_turns.not_enough_credits');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.give_chat_turns.error');
        }

        return Redirect::back()->withInput()->with('error', $error);
    }

    public function getClaimTurns($chatTurn)
    {
        try
        {
            // Make sure they're not the user who made this chat turn
            if ($this->currentUser->id == $chatTurn->user_id)
            {
                throw new Dynasty\ChatTurns\Exceptions\CannotClaimOwnException;
            }

            // Check if there are any turns left
            if ($chatTurn->amount < 1)
            {
                throw new Dynasty\ChatTurns\Exceptions\AllClaimedException;
            }

            // Start transaction
            DB::transaction(function() use ($chatTurn)
            {
                // Decrease the amount
                $chatTurn->amount -= 1;
                $chatTurn->save();

                // Give it to the user
                $this->currentUser->turns += 1;
                $this->currentUser->save();
            });

            $success = Lang::get('forms/user.claim_chat_turns.success', array_dot([ 'user' => $chatTurn->user->toArray() ]));

            return is_null(Request::header('referer'))
                ? Redirect::route('cash_shop')->with('success', $success)
                : Redirect::back()->with('success', $success);
        }
        catch(Dynasty\ChatTurns\Exceptions\CannotClaimOwnException $e)
        {
            $error = Lang::get('forms/user.claim_chat_turns.cannot_claim_own');
        }
        catch(Dynasty\ChatTurns\Exceptions\AllClaimedException $e)
        {
            $error = Lang::get('forms/user.claim_chat_turns.all_claimed', array_dot([ 'user' => $chatTurn->user->toArray() ]));
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.claim_chat_turns.error');
        }

        return is_null(Request::header('referer'))
            ? Redirect::route('cash_shop')->with('error', $error)
            : Redirect::back()->with('error', $error);
    }

}
