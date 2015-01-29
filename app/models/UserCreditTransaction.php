<?php

class UserCreditTransaction extends Eloquent {

    const SEPERATOR                   = '_';

    const UPGRADE                     = 'UPGRADE';
    const GIFT_UPGRADE                = 'GIFT_UPGRADE';
    const TURN_PACKAGE                = 'TURN_PACKAGE';
    const GIFT_TURN_PACKAGE           = 'GIFT_TURN_PACKAGE';
    const CHAT_TURN_PACKAGE           = 'CHAT_TURN_PACKAGE';
    const IMPORT                      = 'IMPORT';
    const CUSTOM_IMPORT               = 'CUSTOM_IMPORT';
    const DOG_PREFIX                  = 'DOG_PREFIX';
    const DOG_CHANGE_BREED            = 'DOG_CHANGE_BREED';
    const CHALLENGE_REROLL            = 'CHALLENGE_REROLL';

    protected $guarded = array('id');

    public function setInfoAttribute($info)
    {
        $this->attributes['info'] = json_encode($info);
    }

    public function getInfoAttribute($info)
    {
        return json_decode($info, true);
    }


    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the user.
     *
     * @return User
     */
    public function user()
    {
        return $this->belongsTo('User', 'user_id');
    }


    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public static function types()
    {
        $types = array(
            UserCreditTransaction::UPGRADE, 
            UserCreditTransaction::GIFT_UPGRADE, 
            UserCreditTransaction::IMPORT, 
            UserCreditTransaction::CUSTOM_IMPORT, 
            UserCreditTransaction::DOG_PREFIX, 
            UserCreditTransaction::DOG_CHANGE_BREED, 
            UserCreditTransaction::CHALLENGE_REROLL, 
        );

        // Get all of the turn packages
        $turnPackages = TurnPackage::all();

        foreach($turnPackages as $turnPackage)
        {
            $types[] = UserCreditTransaction::TURN_PACKAGE.UserCreditTransaction::SEPERATOR.$turnPackage->amount;
            $types[] = UserCreditTransaction::GIFT_TURN_PACKAGE.UserCreditTransaction::SEPERATOR.$turnPackage->amount;
            $types[] = UserCreditTransaction::CHAT_TURN_PACKAGE.UserCreditTransaction::SEPERATOR.$turnPackage->amount;
        }

        $types = array_unique($types);

        return $types;
    }

}
