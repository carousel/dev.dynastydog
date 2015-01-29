<?php

class BreedCharacteristic extends Eloquent {

    public $timestamps = false;

    protected $guarded = array('id');

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    |
    */

    public function scopeWhereActive($query)
    {
        return $query->where(function($q)
            {
                $q->where('breed_characteristics.active', true)->whereHas('characteristic', function($qq)
                    {
                        $qq->whereActive();
                    });
            });
    }

    public function scopeWhereVisible($query)
    {
        return $query->where('breed_characteristics.hide', false);
    }

    public function scopeOrderByCharacteristic($query)
    {
        return $query->select('breed_characteristics.*')
            ->leftJoin('characteristics', 'characteristics.id', '=', 'breed_characteristics.characteristic_id')
            ->orderBy('characteristics.name', 'asc');
    }

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

    public function getHideAttribute($hide)
    {
        return (bool) $hide;
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the breed
     *
     * @return Breed
     */
    public function breed()
    {
        return $this->belongsTo('Breed', 'breed_id');
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
     * All severities
     *
     * @return Collection of BreedCharacteristicSeverity
     */
    public function severities()
    {
        return $this->hasMany('BreedCharacteristicSeverity', 'breed_characteristic_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function isActive()
    {
        return $this->active;
    }

    public function isHidden()
    {
        return $this->hide;
    }

    public function isVisible()
    {
        return ( ! $this->hide);
    }

    public function getRandomAgeToRevealGenotypes()
    {
        if (is_null($this->min_age_to_reveal_genotypes))
        {
            return null;
        }

        return mt_rand($this->min_age_to_reveal_genotypes, $this->max_age_to_reveal_genotypes);
    }

    public function getRandomAgeToRevealPhenotypes()
    {
        if (is_null($this->min_age_to_reveal_phenotypes))
        {
            return null;
        }
        
        return mt_rand($this->min_age_to_reveal_phenotypes, $this->max_age_to_reveal_phenotypes);
    }

    public function getRandomRangedValue($sex = 'female')
    {
        return ($sex == 'female')
            ? $this->getRandomRangedFemaleValue()
            : $this->getRandomRangedMaleValue();
    }

    public function getRandomRangedFemaleValue()
    {
        if (is_null($this->min_female_ranged_value))
        {
            return null;
        }
        
        return mt_rand($this->min_female_ranged_value, $this->max_female_ranged_value);
    }

    public function getRandomRangedMaleValue()
    {
        if (is_null($this->min_male_ranged_value))
        {
            return null;
        }
        
        return mt_rand($this->min_male_ranged_value, $this->max_male_ranged_value);
    }

    public function getRandomAgeToStopGrowing()
    {
        if (is_null($this->min_age_to_stop_growing))
        {
            return null;
        }
        
        return mt_rand($this->min_age_to_stop_growing, $this->max_age_to_stop_growing);
    }

    public function getRandomAgeToRevealRangedValue()
    {
        if (is_null($this->min_age_to_reveal_ranged_value))
        {
            return null;
        }
        
        return mt_rand($this->min_age_to_reveal_ranged_value, $this->max_age_to_reveal_ranged_value);
    }

    public function getRandomSeverity($nonlethalForCurrentAge = false, $currentAgeOfDog = 0)
    {
        if ($this->severities()->count() > 0)
        {
            // Get all health severities
            if ($nonlethalForCurrentAge)
            {
                // Get all lethal symptoms
                $invalidBreedSeverityIds = DB::table('breed_characteristic_severity_symptoms')
                    ->join('breed_characteristic_severities', 'breed_characteristic_severities.id', '=', 'breed_characteristic_severity_symptoms.breed_characteristic_severity_id')
                    ->where('breed_characteristic_severity_symptoms.lethal', true)
                    ->where(function($query) use ($currentAgeOfDog)
                        {
                            $query
                                ->where(DB::raw('(`breed_characteristic_severities`.`max_age_to_express` + `breed_characteristic_severity_symptoms`.`max_offset_age_to_express`)'), '<=', $currentAgeOfDog)
                                ->orWhere(function($q) use ($currentAgeOfDog)
                                    {
                                        $q->where(DB::raw('(`breed_characteristic_severities`.`min_age_to_express` + `breed_characteristic_severity_symptoms`.`min_offset_age_to_express`)'), '<=', $currentAgeOfDog)
                                            ->where(DB::raw('(`breed_characteristic_severities`.`max_age_to_express` + `breed_characteristic_severity_symptoms`.`max_offset_age_to_express`)'), '>=', $currentAgeOfDog);
                                    });
                        })
                    ->lists('breed_characteristic_severity_symptoms.breed_characteristic_severity_id');

                // Always add -1
                $invalidBreedSeverityIds[] = -1;

                $severities = $this->severities()->with('characteristicSeverity', 'symptoms')->whereNotIn('id', $invalidBreedSeverityIds)->get();
            }
            else
            {
                $severities = $this->severities()->with('characteristicSeverity', 'symptoms')->get();
            }

            return $severities->random();
        }
        else
        {
            return null;
        }
    }

    public function bindRangedValue($value, $sex = 'female')
    {
        return ($sex == 'female')
            ? $this->bindRangedFemaleValue($value)
            : $this->bindRangedMaleValue($value);
    }

    public function bindRangedFemaleValue($value)
    {
        $lb = $this->min_female_ranged_value;
        $ub = $this->max_female_ranged_value;

        // Bind it between the bounds
        return max(min($value, $ub), $lb);
    }

    public function bindRangedMaleValue($value)
    {
        $lb = $this->min_male_ranged_value;
        $ub = $this->max_male_ranged_value;
        
        // Bind it between the bounds
        return max(min($value, $ub), $lb);
    }

    public function isInRange($value, $sex = 'female')
    {
        return ($sex == 'female')
            ? $this->isInFemaleRange($value)
            : $this->isInMaleRange($value);
    }

    public function isInFemaleRange($value)
    {
        return (Floats::compare($value, $this->min_female_ranged_value, '>=') and Floats::compare($value, $this->max_female_ranged_value, '<='));
    }

    public function isInMaleRange($value)
    {
        return (Floats::compare($value, $this->min_male_ranged_value, '>=') and Floats::compare($value, $this->max_male_ranged_value, '<='));
    }

    public function isValid()
    {
        if ( ! $this->isActive())
        {
            return false;
        }
        
        return $this->characteristic->isValid();
    }

    public function isRanged()
    {
        return $this->characteristic->isRanged();
    }

    public function isGenetic()
    {
        return $this->characteristic->isGenetic();
    }

    public function formatRangedValue($value, $allowLabel = true)
    {
        return $this->characteristic->formatRangedValue($value, $allowLabel);
    }

    public function queryPhenotypes()
    {
        // Get all phenotypes that match the loci
        $phenotypes = $this->characteristic->queryPhenotypes()->with('genotypes')->get();

        $matchedPhenotypeIds = [ -1 ];

        // Make sure the genotypes match for all of them
        foreach($phenotypes as $phenotype)
        {
            $phenotypeGenotypes = $phenotype->genotypes()->whereActive()->get();

            // Get the locus count
            $totalPhenotypeGenotypeLoci = count(array_unique($phenotypeGenotypes->lists('locus_id')));

            // Get the genotype IDs
            $phenotypeGenotypeIds = $phenotypeGenotypes->lists('id');

            // Make sure the breed has at least one genotype in all of the phenotypes
            $locusIds = $this->breed->genotypes()
                ->whereActive()
                ->wherePivot('frequency', '>', 0)
                ->whereIn('genotype_id', $phenotypeGenotypeIds)
                ->lists('locus_id');

            $matchedLoci = count(array_unique($locusIds));

            if($matchedLoci == $totalPhenotypeGenotypeLoci)
            {
                $matchedPhenotypeIds[] = $phenotype->id;
            }
        }

        $phenotypes = Phenotype::whereIn('id', $matchedPhenotypeIds);

        return $phenotypes;
    }

    public function hasGenotypes()
    {
        return ($this->characteristic->loci()->count() > 0);
    }

    public function hasPhenotypes()
    {
        return ($this->queryPhenotypes()->count() > 0);
    }

    public function isHealth()
    {
        return $this->characteristic->isHealth();
    }

    public function hasSeverities()
    {
        return ($this->severities()->count() > 0);
    }

}
