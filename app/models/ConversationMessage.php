<?php

class ConversationMessage extends Eloquent {

    protected $guarded = array('id');

    protected $touches = array('conversation');

    public function setBodyAttribute($body)
    {
        $this->attributes['body'] = Purifier::clean($body);
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the conversation.
     *
     * @return Conversation
     */
    public function conversation()
    {
        return $this->belongsTo('Conversation', 'conversation_id', 'id');
    }

    /**
     * Return the user.
     *
     * @return User
     */
    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

}
