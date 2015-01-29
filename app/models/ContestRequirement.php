<?php

class ContestRequirement extends Eloquent {

    const MIN = 0;
    const MID = 50;
    const MAX = 100;

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
        return $this->belongsTo('Contest', 'contest_id', 'id');
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

    public function isMax()
    {
        return ($this->type_id == ContestRequirement::TYPE_MAX);
    }

    public function isMid()
    {
        return ($this->type_id == ContestRequirement::TYPE_MID);
    }

    public function isMin()
    {
        return ($this->type_id == ContestRequirement::TYPE_MIN);
    }

    public function scoreDogCharacteristic($dogCharacteristic)
    {
        $normalized = Floats::normalizeValueInRange(
            $dogCharacteristic->final_ranged_value, 
            array(
                $this->characteristic->min_ranged_value, 
                $this->characteristic->max_ranged_value, 
            ), 
            array(
                ContestRequirement::MIN, 
                ContestRequirement::MAX, 
        ));

        // Round it
        $normalized = round($normalized);

        switch ($this->type_id)
        {
            case UserContestTypeRequirement::TYPE_MAX:
                return abs(ContestRequirement::MAX - $normalized);
                break;

            case UserContestTypeRequirement::TYPE_MID:
                return abs($normalized - ContestRequirement::MID);

            case UserContestTypeRequirement::TYPE_MIN:
                return abs(ContestRequirement::MIN - $normalized);
            
            default:
                return 0;
        }
    }

}
