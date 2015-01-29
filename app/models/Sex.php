<?php

class Sex extends Eloquent {

    public $timestamps = false;
    
    protected $guarded = array('id');

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    |
    |
    */

    public function getFemaleAttribute($female)
    {
        return (bool) $female;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    |
    */

    public function scopeWhereFemale($query)
    {
        return $query->where('female', true);
    }

    public function scopeWhereMale($query)
    {
        return $query->where('female', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Has Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All dogs
     *
     * @return Collection of Dogs
     */
    public function dogs()
    {
        return $this->hasMany('Dog', 'sex_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function isFemale()
    {
        return $this->female;
    }

    public function isMale()
    {
        return ( ! $this->female);
    }

}
