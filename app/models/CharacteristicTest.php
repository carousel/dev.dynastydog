<?php

class CharacteristicTest extends Eloquent {

    const TYPE_NORMAL        = 0;
    const TYPE_DNA           = 1;
    const TYPE_EMPIRICAL     = 2;
    const TYPE_OBV_EMPIRICAL = 3;

    public $timestamps = false;
    
    protected $guarded = array('id');

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    |
    |
    */

    public function getActiveAttribute($active)
    {
        return (bool) $active;
    }

    public function getRevealGenotypesAttribute($revealGenotypes)
    {
        return (bool) $revealGenotypes;
    }

    public function getRevealPhenotypesAttribute($revealPhenotypes)
    {
        return (bool) $revealPhenotypes;
    }

    public function getRevealRangedValueAttribute($revealRangedValue)
    {
        return (bool) $revealRangedValue;
    }

    public function getRevealSeverityValueAttribute($revealSeverityValue)
    {
        return (bool) $revealSeverityValue;
    }

    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    |
    |
    */

    public function setMinAgeAttribute($minAge)
    {
        $this->attributes['min_age'] = strlen($minAge) ? $minAge : null;
    }

    public function setMaxAgeAttribute($maxAge)
    {
        $this->attributes['max_age'] = strlen($maxAge) ? $maxAge : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    |
    */

    public function scopeWhereActive($query)
    {
        return $query->where('characteristic_tests.active', true);
    }

    public function scopeWhereInTestableAgeRange($query, $age)
    {
        return $query
            ->where(function($query) use ($age)
            {
                $query
                    ->where(function($query) use ($age)
                    {
                        $query
                            ->whereNull('characteristic_tests.min_age')
                            ->orWhere('characteristic_tests.min_age', '<=', $age);
                    })
                    ->where(function($query) use ($age)
                    {
                        $query
                            ->whereNull('characteristic_tests.max_age')
                            ->orWhere('characteristic_tests.max_age', '>=', $age);
                    });
            });
    }

    public function scopeOrderByCharacteristic($query)
    {
        return $query->select('characteristic_tests.*')
            ->leftJoin('characteristics', 'characteristics.id', '=', 'characteristic_tests.characteristic_id')
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
     * Return the characteristic
     *
     * @return Characteristic
     */
    public function characteristic()
    {
        return $this->belongsTo('Characteristic', 'characteristic_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All dog characteristics
     *
     * @return Collection of DogCharacteristics
     */
    public function DogCharacteristics()
    {
        return $this->belongsToMany('DogCharacteristic', 'dog_characteristic_tests', 'test_id', 'dog_characteristic_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Has Many Through Relationships
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
        return $this->hasManyThrough('Dog', 'DogCharacteristic', 'test_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public static function types()
    {
        return array(
            CharacteristicTest::TYPE_NORMAL        => 'Normal', 
            CharacteristicTest::TYPE_DNA           => 'DNA', 
            CharacteristicTest::TYPE_EMPIRICAL     => 'Empirical', 
            CharacteristicTest::TYPE_OBV_EMPIRICAL => 'Obvious Empirical', 
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
        $types = CharacteristicTest::types();

        return $types[$this->type_id];
    }

    public function isNormal()
    {
        return ($this->type_id == CharacteristicTest::TYPE_NORMAL);
    }

    public function isDNA()
    {
        return ($this->type_id == CharacteristicTest::TYPE_DNA);
    }

    public function isEmpirical()
    {
        return ($this->type_id == CharacteristicTest::TYPE_EMPIRICAL);
    }

    public function isObviousEmpirical()
    {
        return ($this->type_id == CharacteristicTest::TYPE_OBV_EMPIRICAL);
    }

    public function isActive()
    {
        return $this->active;
    }

    public function revealsGenotypes()
    {
        return $this->reveal_genotypes;
    }

    public function revealsPhenotypes()
    {
        return $this->reveal_phenotypes;
    }

    public function revealsRangedValue()
    {
        return $this->reveal_ranged_value;
    }

    public function revealsSeverityValue()
    {
        return $this->reveal_severity_value;
    }

    public function hasMinimumAgeRequirement()
    {
        return ( ! is_null($this->min_age));
    }

    public function hasMaximumAgeRequirement()
    {
        return ( ! is_null($this->max_age));
    }

    public function hasAgeRequirement()
    {
        return ($this->hasMinimumAgeRequirement() or $this->hasMaximumAgeRequirement());
    }

    public function validAge($age)
    {
        if ($this->hasMinimumAgeRequirement() and $age < $this->min_age)
        {
            return false;
        }

        if ($this->hasMaximumAgeRequirement() and $age > $this->max_age)
        {
            return false;
        }

        return true;
    }

    public function performOnDogCharacteristic($dogCharacteristic)
    {
        // Say that the dog characteristic has been tested
        $dogCharacteristic->last_tested_at_months = $dogCharacteristic->dog->age;

        // Check if it's empirical
        if ($this->isEmpirical() and ! $dogCharacteristic->hasExpressedSymptoms())
        {
            $message = 'Clear at the time of examination.';
        }
        else
        {
            // Add the test
            $dogCharacteristic->tests()->attach($this->id);

            if ($this->revealsGenotypes())
            {
                $dogCharacteristic->genotypes_revealed = true;
            }

            if ($this->revealsPhenotypes())
            {
                $dogCharacteristic->phenotypes_revealed = true;
            }

            if ($this->revealsRangedValue())
            {
                $dogCharacteristic->ranged_value_revealed = true;
            }

            if ($this->revealsSeverityValue())
            {
                $dogCharacteristic->severity_value_revealed = true;
            }

            // No message to display
            $message = '';
        }

        $dogCharacteristic->save();

        return $message;
    }

}
