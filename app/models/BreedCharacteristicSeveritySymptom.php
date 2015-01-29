<?php

class BreedCharacteristicSeveritySymptom extends Eloquent {

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

    /**
     * Return the breed characteristic severity
     *
     * @return BreedCharacteristicSeverity
     */
    public function breedCharacteristicSeverity()
    {
        return $this->belongsTo('BreedCharacteristicSeverity', 'breed_characteristic_severity_id');
    }

    /**
     * Return the characteristic severity symptom
     *
     * @return CharacteristicSeveritySymptom
     */
    public function characteristicSeveritySymptom()
    {
        return $this->belongsTo('CharacteristicSeveritySymptom', 'characteristic_severity_symptom_id');
    }

    public function getRandomAgeToExpress($severityExpressionAge)
    {
        if (is_null($this->min_offset_age_to_express))
        {
            return null;
        }
        
        return ($severityExpressionAge + mt_rand($this->min_offset_age_to_express, $this->max_offset_age_to_express));
    }

    public function isLethal()
    {
        return $this->lethal;
    }

}
