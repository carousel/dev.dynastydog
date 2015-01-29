<?php

class CharacteristicSeveritySymptom extends Eloquent {

    public $timestamps = false;

    protected $guarded = array('id');

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    |
    |
    */

    public function getLethalAttribute($lethal)
    {
        return (bool) $lethal;
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the characteristic severity
     *
     * @return CharacteristicSeverity
     */
    public function characteristicSeverity()
    {
        return $this->belongsTo('CharacteristicSeverity', 'severity_id');
    }

    /**
     * Return the symptom
     *
     * @return Symptom
     */
    public function symptom()
    {
        return $this->belongsTo('Symptom', 'symptom_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function getRandomAgeToExpress($severityExpressionAge)
    {
        if (is_null($this->min_offset_age_to_express))
        {
            return null;
        }
        
        return ($severityExpressionAge + mt_rand($this->min_offset_age_to_express, $this->max_offset_age_to_express));
    }

    public function canBeExpressed()
    {
        return ( ! is_null($this->min_offset_age_to_express));
    }

    public function isLethal()
    {
        return $this->lethal;
    }

}
