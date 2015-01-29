<?php

class CharacteristicSeverity extends Eloquent {

    public $timestamps = false;
    
    protected $guarded = array('id');

    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    |
    |
    */

    public function setMinAgeToExpressAttribute($minAgeToExpress)
    {
        $this->attributes['min_age_to_express'] = $this->can_be_expressed 
            ? $minAgeToExpress 
            : null;
    }

    public function setMaxAgeToExpressAttribute($maxAgeToExpress)
    {
        $this->attributes['max_age_to_express'] = $this->can_be_expressed 
            ? $maxAgeToExpress 
            : null;
    }

    public function setMinAgeToRevealValueAttribute($minAgeToRevealValue)
    {
        $this->attributes['min_age_to_reveal_value'] = $this->value_can_be_revealed 
            ? $minAgeToRevealValue 
            : null;
    }

    public function setMaxAgeToRevealValueAttribute($maxAgeToRevealValue)
    {
        $this->attributes['max_age_to_reveal_value'] = $this->value_can_be_revealed 
            ? $maxAgeToRevealValue 
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    |
    |
    */

    public function getCanBeExpressedAttribute($canBeExpressed)
    {
        return (bool) $canBeExpressed;
    }

    public function getvalueCanBeRevealedAttribute($valueCanBeRevealed)
    {
        return (bool) $valueCanBeRevealed;
    }

    /**
     * Return the characteristic
     *
     * @return Characteristic
     */
    public function characteristic()
    {
        return $this->belongsTo('Characteristic', 'characteristic_id');
    }

    /**
     * All symptoms
     *
     * @return Collection of CharacteristicSeveritySymptoms
     */
    public function symptoms()
    {
        return $this->hasMany('CharacteristicSeveritySymptom', 'severity_id', 'id');
    }

    /**
     * All dog characteristics
     *
     * @return Collection of DogCharacteristics
     */
    public function dogCharacteristics()
    {
        return $this->hasMany('DogCharacteristic', 'characteristic_severity_id', 'id');
    }

    /**
     * All breed characteristics
     *
     * @return Collection of BreedCharacteristics
     */
    public function breedCharacteristics()
    {
        return $this->hasMany('BreedCharacteristic', 'characteristic_severity_id', 'id');
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
        if (is_null($this->min_value))
        {
            return null;
        }

        if ($this->min_value < $this->max_value)
        {
            $min = $this->min_value;
            $max = $this->max_value;
        }
        else
        {
            $min = $this->max_value;
            $max = $this->min_value;
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

    public function hasPrefix()
    {
        return strlen($this->prefix_units);
    }

    public function hasSuffix()
    {
        return strlen($this->suffix_units);
    }

    public function prefixValue($value)
    {
        if ($this->hasPrefix())
        {
            return preg_match('([a-zA-Z])', $this->prefix_units)
                ? $this->prefix_units.' '.$value
                : $this->prefix_units.$value;
        }

        return $value;
    }

    public function suffixValue($value)
    {
        if ($this->hasSuffix())
        {
            return preg_match('([a-zA-Z])', $this->suffix_units)
                ? $value.' '.$this->suffix_units
                : $value.$this->suffix_units;
        }

        return $value;
    }

    public function formatValue($value)
    {
        return $this->suffixValue($this->prefixValue($value));
    }

    public function canBeExpressed()
    {
        return $this->can_be_expressed;
    }

    public function valueCanBeRevealed()
    {
        return $this->value_can_be_revealed;
    }

    public function hasSymptoms()
    {
        return ($this->symptoms()->count() > 0);
    }

}
