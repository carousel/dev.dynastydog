<?php

class BeginnersLuckRequest extends Eloquent {

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
     * Return the user who made the request.
     *
     * @return User
     */
    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id');
    }

    /**
     * Return the beginner.
     *
     * @return User
     */
    public function beginner()
    {
        return $this->belongsTo('User', 'beginner_id', 'id');
    }

    /**
     * Return the bitch.
     *
     * @return Dog
     */
    public function bitch()
    {
        return $this->belongsTo('Dog', 'bitch_id', 'id');
    }

    /**
     * Return the dog.
     *
     * @return Dog
     */
    public function dog()
    {
        return $this->belongsTo('Dog', 'dog_id', 'id');
    }

    /**
     * Return the notification.
     *
     * @return UserNotification
     */
    public function notification()
    {
        return $this->belongsTo('UserNotification', 'persistent_notification_id', 'id');
    }

}
