<?php

class LendRequest extends Eloquent {

    protected $guarded = array('id');

    protected $dates = ['return_at'];

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    |
    |
    */

    public function getPermanentAttribute($permanent)
    {
        return (bool) $permanent;
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

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
     * Return the sender.
     *
     * @return User
     */
    public function sender()
    {
        return $this->belongsTo('User', 'sender_id', 'id');
    }

    /**
     * Return the receiver.
     *
     * @return User
     */
    public function receiver()
    {
        return $this->belongsTo('User', 'receiver_id', 'id');
    }

    public function isPermanent()
    {
        return $this->permanent;
    }

    public function isTemporary()
    {
        return ( ! $this->permanent);
    }

    public function isTurnSensitive()
    {
        return ( ! is_null($this->turns_left));
    }

    public function isTimeSensitive()
    {
        return ( ! is_null($this->return_at));
    }

}
