<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class ForumPost extends Eloquent {

    use SoftDeletingTrait;

    protected $guarded = array('id');

    protected $dates = ['deleted_at'];

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

    /**
     * Return the topic.
     *
     * @return ForumTopic
     */
    public function topic()
    {
        return $this->belongsTo('ForumTopic', 'topic_id');
    }

    /**
     * Return the editor.
     *
     * @return User
     */
    public function editor()
    {
        return $this->belongsTo('User', 'editor_id');
    }

}
