<?php

class ChallengeCharacteristic extends Eloquent {

    public $timestamps = false;

    protected $guarded = array('id');

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    |
    */

    public function scopeOrderByCharacteristic($query)
    {
        return $query->select('challenge_characteristics.*')
            ->leftJoin('characteristics', 'characteristics.id', '=', 'challenge_characteristics.characteristic_id')
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
     * Return the challenge.
     *
     * @return Challenge
     */
    public function challenge()
    {
        return $this->belongsTo('Challenge', 'challenge_id', 'id');
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
    | Belongs To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All genotypes
     *
     * @return Collection of Genotypes
     */
    public function genotypes()
    {
        return $this->belongsToMany('Genotype', 'challenge_characteristic_genotypes', 'challenge_characteristic_id', 'genotype_id');
    }

    /**
     * All phenotypes
     *
     * @return Collection of Phenotypes
     */
    public function phenotypes()
    {
        return $this->belongsToMany('Phenotype', 'challenge_characteristic_phenotypes', 'challenge_characteristic_id', 'phenotype_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function isRanged()
    {
        return ( ! is_null($this->ranged_value));
    }

    public function getRangedBounds()
    {
        if ( ! $this->isRanged())
        {
            return [null, null];
        }

        // Grab the characteristic
        $characteristic = $this->characteristic;

        $acceptance = Config::get('game.challenge.acceptance_range');

        $lowerBound = round($characteristic->bindRangedValue($this->ranged_value - $acceptance));
        $upperBound = round($characteristic->bindRangedValue($this->ranged_value + $acceptance));

        if ($lowerBound > $upperBound)
        {
            $temp       = $lowerBound;
            $lowerBound = $upperBound;
            $upperBound = $temp;
        
        }
        return [$lowerBound, $upperBound];
    }

    public function getGoalString()
    {
        // If there is a phenotype, use it
        $phenotype = $this->phenotypes()->first();

        if ( ! is_null($phenotype))
        {
            return $phenotype->name;
        }
        else if ($this->isRanged())
        {
            // Grab the characteristic
            $characteristic = $this->characteristic;

            // If there's a label, use it
            $label = $characteristic->getRangedValueLabel($this->ranged_value);

            if ( ! is_null($label))
            {
                return $label->name;
            }

            // Use the numerical bounds instead
            list($lowerBound, $upperBound) = $this->getRangedBounds();

            // Show only one of the numbers
            if ($lowerBound == $upperBound)
            {
                return $characteristic->formatRangedValue($lowerBound, false);
            }

            // Show the whole range
            return $characteristic->formatRangedValue($lowerBound, false).' - '.$characteristic->formatRangedValue($upperBound, false);
        }
        else
        {
            // Genotypes are the only things possible to show at this point
            $genotypes = $this->genotypes;

            $symbols = [];

            foreach($genotypes as $genotype)
            {
                $symbols[] = $genotype->toSymbol();
            }

            return implode(' ', $symbols);
        }

        // Something probably went wrong if it got to here
        return '';
    }

    public function checkDogCharacteristic(DogCharacteristic $dogCharacteristic)
    {
        // If there is a phenotype, use it
        $phenotype = $this->phenotypes()->first();

        if ( ! is_null($phenotype))
        {
            // Phenotypes must be known on that characteristic
            if ( ! $dogCharacteristic->phenotypesAreRevealed())
            {
                return false;
            }

            // Grab all the phenotype ids on the dog's characteristic
            $phenotypeIds = $dogCharacteristic->phenotypes()->lists('id');

            return in_array($phenotype->id, $phenotypeIds);
        }
        else if ($this->isRanged())
        {
            // Phenotypes must be known on that characteristic
            if ( ! $dogCharacteristic->rangedValueIsRevealed())
            {
                return false;
            }

            // Phenotypes must be known on that characteristic
            return $this->isValidRangedValue($dogCharacteristic->final_ranged_value);
        }
        else
        {
            // Genotypes must be known on that characteristic
            if ( ! $dogCharacteristic->genotypesAreRevealed())
            {
                return false;
            }

            // Genotypes are the only things possible to show at this point
            $genotypeIds = $this->genotypes()->lists('id');

            // Get all of the dog's genotype ids on it's characteristic
            $dogGenotypeIds = $dogCharacteristic->genotypes()->lists('id');

            // All of the genotypes must be in the dog characteristic's genotypes
            $diff = array_diff($genotypeIds, $dogGenotypeIds);

            return empty($diff);
        }

        // Got here so return true because none of the fail statements were hit
        return true;
    }

    public function isValidRangedValue($value)
    {
        // Grab the characteristic
        $characteristic = $this->characteristic;

        // If there's a label, use it
        $label = $characteristic->getRangedValueLabel($this->ranged_value);

        if ( ! is_null($label))
        {
            // The value must have the same label
            $valueLabel = $characteristic->getRangedValueLabel($value);

            return ($label->id == $valueLabel->id);
        }

        // Use the numerical bounds instead
        return $this->isRangedValueInBounds($value);

    }

    public function isRangedValueInBounds($value)
    {
        // Grab the bounds
        list($lowerBound, $upperBound) = $this->getRangedBounds();

        // Round the value
        $value = round($value);

        return (Floats::compare($value, $lowerBound, '>=') and Floats::compare($value, $upperBound, '<='));
    }

}
