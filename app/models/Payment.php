<?php

class Payment extends Eloquent {

    const STATUS_CANCELED_REVERSAL = 'Canceled_Reversal';
    const STATUS_COMPLETED = 'Completed';
    const STATUS_CREATED   = 'Created';
    const STATUS_DENIED    = 'Denied';
    const STATUS_EXPIRED   = 'Expired';
    const STATUS_FAILED    = 'Failed';
    const STATUS_PENDING   = 'Pending';
    const STATUS_REFUNDED  = 'Refunded';
    const STATUS_REVERSED  = 'Reversed';
    const STATUS_PROCESSED = 'Processed';
    const STATUS_VOIDED    = 'Voided';
    
    protected $guarded = array('id');

    public function setIpnMessageAttribute($value)
    {
        $this->attributes['ipn_message'] = json_encode($value);
    }

    public function getIpnMessageAttribute($ipnMessage)
    {
        return json_decode($ipnMessage, true);
    }


    /*
    |--------------------------------------------------------------------------
    | Many To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * The user who made this payment
     *
     * @return Collection of Users
     */
    public function user()
    {
        return $this->belongsToMany('User', 'user_payments', 'payment_id', 'user_id')->first();
    }

    /**
     * All purchased credit packages
     *
     * @return Collection of CreditPackages
     */
    public function creditPackages()
    {
        return $this->belongsToMany('CreditPackage', 'credit_package_payments', 'payment_id', 'credit_package_id');
    }


    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Log the IPN message returned from PayPal for a transaction
     *
     * @return null
     */
    public static function logPaypalIpn($ipnMessage)
    {
        // Collect all the data
        $data = array();

        foreach ($ipnMessage as $key => $value)
        {
            $data[$key] = $value;
        }

        $payment = Payment::create(array(
            'transaction_id' => $ipnMessage['txn_id'], 
            'item_number'    => $ipnMessage['item_number'], 
            'item_name'      => $ipnMessage['item_name'], 
            'payment_status' => $ipnMessage['payment_status'], 
            'quantity'       => $ipnMessage['quantity'], 
            'payment_gross'  => $ipnMessage['payment_gross'], 
            'mc_gross'       => $ipnMessage['mc_gross'], 
            'mc_currency'    => $ipnMessage['mc_currency'], 
            'payer_id'       => $ipnMessage['payer_id'], 
            'payer_email'    => $ipnMessage['payer_email'], 
            'payer_name'     => trim($ipnMessage['first_name'].' '.$ipnMessage['last_name']), 
            'payment_date'   => Carbon::parse($ipnMessage['payment_date'])->format('Y-m-d H:i:s'), 
            'ipn_message'    => $data, 
        ));

        return $payment;
    }

    public function isCompleted()
    {
        return ($this->payment_status === Payment::STATUS_COMPLETED);
    }

}
