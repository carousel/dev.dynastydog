<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class ForumTopic extends Eloquent {

    use SoftDeletingTrait;

    protected $guarded = array('id');

    protected $dates = ['deleted_at', 'last_activity_at'];

    public function getLockedAttribute($locked)
    {
        return (bool) $locked;
    }

    public function getStickiedAttribute($stickied)
    {
        return (bool) $stickied;
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

    /**
     * Return the last editor.
     *
     * @return User
     */
    public function editor()
    {
        return $this->belongsTo('User', 'editor_id');
    }

    /**
     * Return the forum.
     *
     * @return Forum
     */
    public function forum()
    {
        return $this->belongsTo('Forum', 'forum_id');
    }

    /*
    |--------------------------------------------------------------------------
    | One To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All posts
     *
     * @return Collection of ForumPost
     */
    public function posts()
    {
        return $this->hasMany('ForumPost', 'topic_id', 'id');
    }

    public function lastPost()
    {
        return $this->posts()
            ->with('author')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function isStickied()
    {
        return $this->stickied;
    }

    public function isLocked()
    {
        return $this->locked;
    }

    public function lock()
    {
        $this->locked = true;
        $this->save();

        return $this;
    }

    public function unlock()
    {
        $this->locked = false;
        $this->save();

        return $this;
    }

    public function sticky()
    {
        $this->stickied = true;
        $this->save();

        return $this;
    }

    public function unsticky()
    {
        $this->stickied = false;
        $this->save();

        return $this;
    }

}
