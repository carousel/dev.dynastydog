<?php

class UserCreditTransfer extends Eloquent {

    protected $guarded = array('id');


    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the sender.
     *
     * @return User
     */
    public function sender()
    {
        return $this->belongsTo('User', 'sender_id');
    }

    /**
     * Return the receiver.
     *
     * @return User
     */
    public function receiver()
    {
        return $this->belongsTo('User', 'receiver_id');
    }


    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */


}
