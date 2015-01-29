<?php

class NewsPollAnswer extends Eloquent {

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
     * Return the poll.
     *
     * @return NewsPoll
     */
    public function poll()
    {
        return $this->belongsTo('NewsPoll', 'poll_id');
    }


    /*
    |--------------------------------------------------------------------------
    | One To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All votes on this answer
     *
     * @return Collection of NewsPollAnswerVotes
     */
    public function votes()
    {
        return $this->hasMany('NewsPollAnswerVote', 'answer_id', 'id');
    }


    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function votedOnBy($user)
    {
        $vote = $this->votes()->where('user_id', $user->id)->first();
        return ( ! is_null($vote));
    }

}
