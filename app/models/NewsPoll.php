<?php

class NewsPoll extends Eloquent {

    protected $guarded = array('id');


    /*
    |--------------------------------------------------------------------------
    | One To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All answers on this poll
     *
     * @return Collection of NewsPollAnswers
     */
    public function answers()
    {
        return $this->hasMany('NewsPollAnswer', 'poll_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Many To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All posts attached to this poll
     *
     * @return Collection of NewsPosts
     */
    public function posts()
    {
        return $this->belongsToMany('NewsPost', 'news_posts_news_polls', 'news_poll_id', 'news_post_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Has Many Through Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All users who have voted on this poll
     *
     * @return Collection of NewsPollAnswerVotes
     */
    public function votes()
    {
        return $this->hasManyThrough('NewsPollAnswerVote', 'NewsPollAnswer', 'poll_id', 'answer_id');
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
        $voterIds = $this->votes()->lists('user_id');
        return in_array($user->id, $voterIds);
    }

}
