<?php

class NewsPollAnswerVote extends Eloquent {

    public $timestamps = false;

    protected $guarded = array('id');

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the answer.
     *
     * @return NewsPollAnswer
     */
    public function answer()
    {
        return $this->belongsTo('NewsPollAnswer', 'answer_id');
    }

    /**
     * Return the user.
     *
     * @return User
     */
    public function user()
    {
        return $this->belongsTo('User', 'user_id');
    }

}
