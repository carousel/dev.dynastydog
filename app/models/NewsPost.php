<?php

class NewsPost extends Eloquent {

    protected $guarded = array('id');

    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    |
    |
    */

    public function setBodyAttribute($body)
    {
        $this->attributes['body'] = Purifier::clean($body, 'linkify');
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the author.
     *
     * @return User
     */
    public function author()
    {
        return $this->belongsTo('User', 'author_id');
    }

    /*
    |--------------------------------------------------------------------------
    | One To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All comments on this post
     *
     * @return Collection of NewsPostComments
     */
    public function comments()
    {
        return $this->hasMany('NewsPostComment', 'news_post_id', 'id');
    }


    /*
    |--------------------------------------------------------------------------
    | Many To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All polls attached to this post
     *
     * @return Collection of NewsPolls
     */
    public function polls()
    {
        return $this->belongsToMany('NewsPoll', 'news_posts_news_polls', 'news_post_id', 'news_poll_id');
    }

}
