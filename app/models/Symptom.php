<?php

class Symptom extends Eloquent {

    public $timestamps = false;
    
    protected $guarded = array('id');

    /*
    |--------------------------------------------------------------------------
    | Has Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All severity symptoms
     *
     * @return Collection of CharacteristicSeveritySymptom
     */
    public function characteristicSeveritySymptoms()
    {
        return $this->hasMany('CharacteristicSeveritySymptom', 'symptom_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All characteristic severities that have this symptom
     *
     * @return Collection of CharacteristicSeverities
     */
    public function characteristicSeverities()
    {
        $severityIds = $this->characteristicSeveritySymptoms()->lists('severity_id');

        // Add -1
        $severityIds[] = -1;

        return CharacteristicSeverity::whereIn('id', $severityIds);
    }

    /**
     * All characteristics have this symptom
     *
     * @return Collection of Characteristics
     */
    public function characteristics()
    {
        $characteristicIds = $this->characteristicSeverities()->lists('characteristic_id');

        // Add -1
        $characteristicIds[] = -1;

        return Characteristic::whereIn('id', $characteristicIds);
    }

}
