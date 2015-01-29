<?php

class UserContestTypeRequirement extends Eloquent {

    const TYPE_MIN = 0;
    const TYPE_MID = 1;
    const TYPE_MAX = 2;

    public $timestamps = false;

    protected $guarded = array('id');

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    |
    */

    public function scopeOrderByCharacteristic($query)
    {
        return $query->select('user_contest_type_requirements.*')
            ->leftJoin('characteristics', 'characteristics.id', '=', 'user_contest_type_requirements.characteristic_id')
            ->orderBy('characteristics.name', 'asc');
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the contest type.
     *
     * @return UserContestType
     */
    public function contestType()
    {
        return $this->belongsTo('UserContestType', 'contest_type_id', 'id');
    }

    /**
     * Return the characteristic.
     *
     * @return Characteristic
     */
    public function characteristic()
    {
        return $this->belongsTo('Characteristic', 'characteristic_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public static function getTypes()
    {
        return array(
            UserContestTypeRequirement::TYPE_MIN => 'Minimum',
            UserContestTypeRequirement::TYPE_MID => 'Middle', 
            UserContestTypeRequirement::TYPE_MAX => 'Maximum', 
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function getType()
    {
        $types = UserContestTypeRequirement::getTypes();

        return $types[$this->type_id];
    }

}
