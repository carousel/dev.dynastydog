<?php

class ChatMessage extends Eloquent {

    protected $guarded = array('id');

    /**
     * Return the author
     *
     * @return User
     */
    public function author()
    {
        return $this->belongsTo('User', 'author_id');
    }

}
