<?php

class CreditPackage extends Eloquent {

    public $timestamps = false;
    
    protected $guarded = array('id');


    /*
    |--------------------------------------------------------------------------
    | Many To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All PayPal payments made for this credit package
     *
     * @return Collection of Payments
     */
    public function payments()
    {
        return $this->belongsToMany('Payment', 'credit_package_payments', 'credit_package_id', 'payment_id');
    }

}
