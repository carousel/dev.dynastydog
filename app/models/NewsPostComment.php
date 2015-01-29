<?php

class NewsPostComment extends Eloquent {

    protected $guarded = array('id');

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
     * Return the post.
     *
     * @return NewsPost
     */
    public function post()
    {
        return $this->belongsTo('NewsPost', 'news_post_id');
    }

    /**
     * Return the author.
     *
     * @return User
     */
    public function author()
    {
        return $this->belongsTo('User', 'author_id');
    }

}
