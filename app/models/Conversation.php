<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Conversation extends Eloquent {

    use SoftDeletingTrait;

    protected $guarded = array('id');

    protected $dates = ['deleted_at'];

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the sender.
     *
     * @return User
     */
    public function sender()
    {
        return $this->belongsTo('User', 'sender_id', 'id');
    }

    /**
     * Return the receiver.
     *
     * @return User
     */
    public function receiver()
    {
        return $this->belongsTo('User', 'receiver_id', 'id');
    }


    /*
    |--------------------------------------------------------------------------
    | One To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All messages a part of this conversation
     *
     * @return Collection of ConversationMessages
     */
    public function messages()
    {
        return $this->hasMany('ConversationMessage', 'conversation_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Many To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All users who have this conversation in their inbox
     *
     * @return Collection of Users
     */
    public function inboxes()
    {
        return $this->belongsToMany('User', 'user_conversations', 'conversation_id', 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

}
