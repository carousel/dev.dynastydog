<?php

class ContestEntry extends Eloquent {

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
     * Return the contest.
     *
     * @return Contest
     */
    public function contest()
    {
        return $this->belongsTo('Contest', 'contest_id');
    }

    /**
     * Return the dog.
     *
     * @return Dog
     */
    public function dog()
    {
        return $this->belongsTo('Dog', 'dog_id');
    }

    public function hasWon()
    {
        return ($this->rank == 1);
    }

}
