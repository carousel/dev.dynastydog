<?php

class StudRequest extends Eloquent {

    protected $guarded = array('id');

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    |
    |
    */

    public function getAcceptedeAttribute($accepted)
    {
        return (bool) $accepted;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    |
    */

    public function scopeWhereAccepted($query)
    {
        return $query->where('stud_requests.accepted', true);
    }

    public function scopeWhereWaiting($query)
    {
        return $query->where('stud_requests.accepted', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the stud.
     *
     * @return Dog
     */
    public function stud()
    {
        return $this->belongsTo('Dog', 'stud_id', 'id');
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

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function isAccepted()
    {
        return $this->accepted;
    }

    public function isWaiting()
    {
        return ( ! $this->accepted);
    }

    public function isInHeat()
    {
        return is_null($this->bitch)
            ? false
            : $this->bitch->isInHeat();
    }

}
