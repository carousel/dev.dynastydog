<?php

class ChatTurn extends Eloquent {

    protected $guarded = array('id');

    /**
     * Return the user
     *
     * @return User
     */
    public function user()
    {
        return $this->belongsTo('User', 'user_id');
    }

}
