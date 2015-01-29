<?php namespace Controllers\User;

use AuthorizedController;
use View;
use Input;
use Lang;
use Redirect;
use Validator;
use DB;
use URL;
use User;
use Conversation;
use ConversationMessage;
use UserNotification;

use Dynasty\Users\Exceptions as DynastyUsersExceptions;
use Dynasty\Conversations\Exceptions as DynastyConversationsExceptions;

class InboxController extends AuthorizedController {

    public function getIndex()
    {
        $conversations = $this->currentUser->inbox()->orderBy('updated_at', 'desc')->orderBy('id', 'desc')->paginate(10);

        // Show the page
        return View::make('frontend/user/inbox/index', compact('conversations'));
    }

    public function getConversation($conversation)
    {
        // Make sure this user has it in their inbox
        if ( ! $this->currentUser->isInInbox($conversation))
        {
            App::abort('404', 'Conversation not found!');
        }

        $messages = $conversation->messages()->orderBy('created_at', 'asc')->orderBy('id', 'asc')->paginate(10);

        // Show the page
        return View::make('frontend/user/inbox/conversation', compact('conversation', 'messages'));
    }

    public function postCreateConversation()
    {
        // Declare the rules for the form validation
        $rules = array(
            'receiver_id' => 'required|exists:users,id',
            'subject'     => 'required|max:255',
            'body'        => 'required|max:10000',
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
            $receiverId = Input::get('receiver_id');

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

            $conversation = null;

            // Start transaction
            DB::transaction(function() use ($receiver, &$conversation)
            {
                // Create the conversation
                $conversation = Conversation::create(array(
                    'sender_id'   => $this->currentUser->id, 
                    'receiver_id' => $receiver->id, 
                    'subject'     => Input::get('subject'), 
                    'replies'     => 0, 
                ));

                // Add the opening message
                $message = ConversationMessage::create(array(
                    'user_id'         => $this->currentUser->id, 
                    'conversation_id' => $conversation->id, 
                    'body'            => Input::get('body'), 
                ));

                // Add the conversation to the sender
                $this->currentUser->inbox()->attach($conversation->id);

                // Notify the receiver
                if ($this->currentUser->id != $receiver->id)
                {
                    // Add the conversation to the receiver
                    $receiver->inbox()->attach($conversation->id);

                    $params = array(
                        'sender' => $this->currentUser->toArray(), 
                        'route'  => URL::route('user/inbox/conversation', $conversation->id), 
                    );

                    $body = Lang::get('notifications/user.compose.to_receiver', array_map('htmlentities', array_dot($params)));

                    $receiver->notify($body, UserNotification::TYPE_INFO);
                }
            });

            $success = Lang::get('forms/user.compose.success');

            return Redirect::route('user/inbox')->with('success', $success);
        }
        catch(DynastyUsersExceptions\BlockedUserException $e)
        {
            $error = Lang::get('forms/user.compose.blocked_receiver');
        }
        catch(DynastyUsersExceptions\IsBlockedException $e)
        {
            $error = Lang::get('forms/user.compose.is_blocked');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.compose.error');
        }

        return Redirect::route('user/inbox')->withInput()->with('error', $error);
    }

    public function postReplyToConversation($conversation)
    {
        try
        {
            // Make sure this user has it in their inbox
            if ( ! $this->currentUser->isInInbox($conversation))
            {
                throw new DynastyConversationsExceptions\NotInInboxException;
            }

            // Declare the rules for the form validation
            $rules = array(
                'body' => 'required|max:10000',
            );

            // Create a new validator instance from our validation rules
            $validator = Validator::make(Input::all(), $rules);

            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::back()->withInput()->with('error', $validator->errors()->first());
            }

            $receiver = ($this->currentUser->id == $conversation->receiver_id) 
                ? $conversation->sender
                : $conversation->receiver;

            if ( ! is_null($receiver))
            {
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
            }

            $lastPage = 1;

            // Start transaction
            DB::transaction(function() use ($receiver, $conversation, &$lastPage)
            {
                // Create the reply
                $message = ConversationMessage::create(array(
                    'user_id'         => $this->currentUser->id, 
                    'conversation_id' => $conversation->id, 
                    'body'            => Input::get('body'), 
                ));

                // Add a reply to the conversation
                $conversation->replies++;
                $conversation->save();

                $paginated = $conversation->messages()->paginate(10);
                $lastPage  = $paginated->getLastPage();

                // Notify the receiver
                if ( ! is_null($receiver) and $this->currentUser->id != $receiver->id)
                {
                    $params = array(
                        'sender' => $this->currentUser->toArray(), 
                        'route'  => URL::route('user/inbox/conversation', ['conversation' => $conversation->id, 'page' => $lastPage]), 
                    );

                    $body = Lang::get('notifications/user.reply_to_conversation.to_receiver', array_map('htmlentities', array_dot($params)));

                    $receiver->notify($body, UserNotification::TYPE_INFO);
                }
            });

            $success = Lang::get('forms/user.reply_to_conversation.success');

            return Redirect::route('user/inbox/conversation', ['conversation' => $conversation->id, 'page' => $lastPage])->with('success', $success);
        }
        catch(DynastyConversationsExceptions\NotInInboxException $e)
        {
            $error = Lang::get('forms/user.reply_to_conversation.not_in_inbox');
        }
        catch(DynastyUsersExceptions\BlockedUserException $e)
        {
            $error = Lang::get('forms/user.reply_to_conversation.blocked_receiver');
        }
        catch(DynastyUsersExceptions\IsBlockedException $e)
        {
            $error = Lang::get('forms/user.reply_to_conversation.is_blocked');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.reply_to_conversation.error');
        }

        return Redirect::route('user/inbox')->withInput()->with('error', $error);
    }

    public function postDeleteConversations()
    {
        $conversationIds = Input::get('ids');

        if (is_array($conversationIds) and ! empty($conversationIds))
        {
            // Detach each conversation
            foreach($conversationIds as $conversationId)
            {
                $this->currentUser->inbox()->detach($conversationId);

                // Delete the conversation if it is no longer attached to an inbox
                $conversation = Conversation::find($conversationId);

                if ( ! is_null($conversation) and $conversation->inboxes()->count() < 1)
                {
                    $conversation->delete();
                }
            }
        }

        $success = Lang::get('forms/user.delete_conversations.success');

        return Redirect::back()->with('success', $success);
    }

}
