<?php

class Forum extends Eloquent {

    protected $guarded = array('id');

    public $timestamps = false;

    /*
    |--------------------------------------------------------------------------
    | One To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All topics
     *
     * @return Collection of ForumTopics
     */
    public function topics()
    {
        return $this->hasMany('ForumTopic', 'forum_id', 'id');
    }

    public function lastTopic()
    {
        return $this->topics()
            ->with('author')
            ->orderBy('last_activity_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();
    }

}
