<?php

class BreedCharacteristicSeverity extends Eloquent {

    public $timestamps = false;

    protected $guarded = array('id');

    /**
     * Return the breed characteristic
     *
     * @return BreedCharacteristic
     */
    public function breedCharacteristic()
    {
        return $this->belongsTo('BreedCharacteristic', 'breed_characteristic_id');
    }

    /**
     * Return the characteristic severity
     *
     * @return CharacteristicSeverity
     */
    public function characteristicSeverity()
    {
        return $this->belongsTo('CharacteristicSeverity', 'characteristic_severity_id');
    }

    /**
     * All symptoms
     *
     * @return Collection of BreedCharacteristicSeveritySymptom
     */
    public function symptoms()
    {
        return $this->hasMany('BreedCharacteristicSeveritySymptom', 'breed_characteristic_severity_id', 'id');
    }

    public function getRandomAgeToExpress()
    {
        if (is_null($this->min_age_to_express))
        {
            return null;
        }

        if ($this->min_age_to_express < $this->max_age_to_express)
        {
            $min = $this->min_age_to_express;
            $max = $this->max_age_to_express;
        }
        else
        {
            $min = $this->max_age_to_express;
            $max = $this->min_age_to_express;
        }
        
        return mt_rand($min, $max);
    }
    
    public function getRandomValue()
    {
        if (is_null($this->characteristicSeverity->min_value))
        {
            return null;
        }

        if ($this->characteristicSeverity->min_value < $this->characteristicSeverity->max_value)
        {
            $min = $this->characteristicSeverity->min_value;
            $max = $this->characteristicSeverity->max_value;
        }
        else
        {
            $min = $this->characteristicSeverity->max_value;
            $max = $this->characteristicSeverity->min_value;
        }
        
        return mt_rand($min, $max);
    }

    public function getRandomAgeToRevealSeverityValue()
    {
        if (is_null($this->min_age_to_reveal_value))
        {
            return null;
        }

        if ($this->min_age_to_reveal_value < $this->max_age_to_reveal_value)
        {
            $min = $this->min_age_to_reveal_value;
            $max = $this->max_age_to_reveal_value;
        }
        else
        {
            $min = $this->max_age_to_reveal_value;
            $max = $this->min_age_to_reveal_value;
        }
        
        return mt_rand($min, $max);
    }

    public function canBeExpressed()
    {
        return $this->characteristicSeverity->canBeExpressed();
    }

    public function valueCanBeRevealed()
    {
        return $this->characteristicSeverity->valueCanBeRevealed();
    }

}
