<?php

class Characteristic extends Eloquent {

    const TYPE_NORMAL                = 0;
    const TYPE_SEXUAL_MATURITY       = 1;
    const TYPE_HEAT_CYCLE            = 2;
    const TYPE_LITTER_SIZE           = 3;
    const TYPE_FERTILITY_SPAN        = 4;
    const TYPE_FERTILITY_DROP_OFF    = 5;
    const TYPE_IMMUNE_SYSTEM         = 6;
    const TYPE_LIFE_SPAN             = 7;
    const TYPE_IMMUNE_SYSTEM_DISEASE = 8;
    const TYPE_FERTILITY             = 9;
    const TYPE_COLOUR                = 10;
    const TYPE_OLD_AGE               = 11;

    public $timestamps = false;
    
    protected $guarded = array('id');

    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    |
    |
    */

    public function setHelpPageIdAttribute($helpPageId)
    {
        $this->attributes['help_page_id'] = strlen($helpPageId) 
            ? $helpPageId 
            : null;
    }

    public function setMinAgeToRevealGenotypesAttribute($minAgeToRevealGenotypes)
    {
        $this->attributes['min_age_to_reveal_genotypes'] = $this->genotypes_can_be_revealed 
            ? $minAgeToRevealGenotypes 
            : null;
    }

    public function setMaxAgeToRevealGenotypesAttribute($maxAgeToRevealGenotypes)
    {
        $this->attributes['max_age_to_reveal_genotypes'] = $this->genotypes_can_be_revealed 
            ? $maxAgeToRevealGenotypes 
            : null;
    }

    public function setMinAgeToRevealPhenotypesAttribute($minAgeToRevealPhenotypes)
    {
        $this->attributes['min_age_to_reveal_phenotypes'] = $this->phenotypes_can_be_revealed 
            ? $minAgeToRevealPhenotypes 
            : null;
    }

    public function setMaxAgeToRevealPhenotypesAttribute($maxAgeToRevealPhenotypes)
    {
        $this->attributes['max_age_to_reveal_phenotypes'] = $this->phenotypes_can_be_revealed 
            ? $maxAgeToRevealPhenotypes 
            : null;
    }

    public function setMinAgeToRevealRangedValueAttribute($minAgeToRevealRangedValue)
    {
        $this->attributes['min_age_to_reveal_ranged_value'] = $this->ranged_value_can_be_revealed 
            ? $minAgeToRevealRangedValue 
            : null;
    }

    public function setMaxAgeToRevealRangedValueAttribute($maxAgeToRevealRangedValue)
    {
        $this->attributes['max_age_to_reveal_ranged_value'] = $this->ranged_value_can_be_revealed 
            ? $maxAgeToRevealRangedValue 
            : null;
    }

    public function setMinAgeToStopGrowingAttribute($minAgeToStopGrowing)
    {

        $this->attributes['min_age_to_stop_growing'] = $this->ranged_value_can_grow 
            ? $minAgeToStopGrowing 
            : null;
    }

    public function setMaxAgeToStopGrowingAttribute($maxAgeToStopGrowing)
    {
        $this->attributes['max_age_to_stop_growing'] = $this->ranged_value_can_grow 
            ? $maxAgeToStopGrowing 
            : null;
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

    public function getIgnorableAttribute($ignorable)
    {
        return (bool) $ignorable;
    }

    public function getHideGenotypesAttribute($hideGenotypes)
    {
        return (bool) $hideGenotypes;
    }

    public function getGenotypesCanBeRevealedAttribute($genotypesCanBeRevealed)
    {
        return (bool) $genotypesCanBeRevealed;
    }

    public function getPhenotypesCanBeRevealedAttribute($phenotypesCanBeRevealed)
    {
        return (bool) $phenotypesCanBeRevealed;
    }

    public function getRangedValueCanBeRevealedAttribute($rangedValueCanBeRevealed)
    {
        return (bool) $rangedValueCanBeRevealed;
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
        return $query->where('characteristics.active', true);
    }

    public function scopeWhereVisible($query)
    {
        return $query->where('characteristics.hide', false);
    }

    public function scopeWhereType($query, $typeId)
    {
        $query->where('characteristics.type_id', $typeId);
    }

    public function scopeWhereDependent($query)
    {
        return $query->whereHas('dependencies', function($query)
            {
                $query->whereActive();
            }, '>=', 1);
    }

    public function scopeWhereNotHealth($query)
    {
        return $query->whereHas('category', function($query)
            {
                $query->whereNotHealth()->select(DB::raw('count(*)'));
            }, '>=', 1);
    }

    public function scopeWhereHealth($query)
    {
        return $query->whereHas('category', function($query)
            {
                $query->whereHealth()->select(DB::raw('count(*)'));
            }, '>=', 1);
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the category
     *
     * @return CharacteristicCategory
     */
    public function category()
    {
        return $this->belongsTo('CharacteristicCategory', 'category_id');
    }

    /**
     * Return the help page
     *
     * @return HelpPage
     */
    public function helpPage()
    {
        return $this->belongsTo('HelpPage', 'help_page_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Has Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All severities
     *
     * @return Collection of CharacteristicSeverity
     */
    public function severities()
    {
        return $this->hasMany('CharacteristicSeverity', 'characteristic_id', 'id');
    }

    /**
     * All labels
     *
     * @return Collection of CharacteristicLabels
     */
    public function labels()
    {
        return $this->hasMany('CharacteristicLabel', 'characteristic_id', 'id');
    }

    /**
     * All dependencies
     *
     * @return Collection of CharacteristicDependencies
     */
    public function dependencies()
    {
        return $this->hasMany('CharacteristicDependency', 'dependent_id', 'id');
    }

    /**
     * All breed characteristics
     *
     * @return Collection of BreedCharacteristics
     */
    public function breedCharacteristics()
    {
        return $this->hasMany('BreedCharacteristic', 'characteristic_id', 'id');
    }

    /**
     * All dogs
     *
     * @return Collection of DogCharacteristics
     */
    public function dogCharacteristics()
    {
        return $this->hasMany('DogCharacteristic', 'characteristic_id', 'id');
    }

    /**
     * All tests
     *
     * @return Collection of CharacteristicTests
     */
    public function tests()
    {
        return $this->hasMany('CharacteristicTest', 'characteristic_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All loci
     *
     * @return Collection of Loci
     */
    public function loci()
    {
        return $this->belongsToMany('Locus', 'characteristics_loci', 'characteristic_id', 'locus_id');
    }

    /**
     * All genotypes
     *
     * @return Collection of Genotypes
     */
    public function genotypes()
    {
        return $this->belongsToMany('Genotype', 'characteristics_genotypes', 'characteristic_id', 'genotype_id');
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
            Characteristic::TYPE_NORMAL => 'NORMAL', 
            Characteristic::TYPE_SEXUAL_MATURITY => 'SEXUAL_MATURITY', 
            Characteristic::TYPE_HEAT_CYCLE => 'HEAT_CYCLE', 
            Characteristic::TYPE_LITTER_SIZE => 'LITTER_SIZE', 
            Characteristic::TYPE_FERTILITY_SPAN => 'FERTILITY_SPAN', 
            Characteristic::TYPE_FERTILITY_DROP_OFF => 'FERTILITY_DROP_OFF', 
            Characteristic::TYPE_IMMUNE_SYSTEM => 'IMMUNE_SYSTEM', 
            Characteristic::TYPE_LIFE_SPAN => 'LIFE_SPAN', 
            Characteristic::TYPE_IMMUNE_SYSTEM_DISEASE => 'IMMUNE_SYSTEM_DISEASE', 
            Characteristic::TYPE_FERTILITY => 'FERTILITY', 
            Characteristic::TYPE_COLOUR => 'COLOUR', 
            Characteristic::TYPE_OLD_AGE => 'OLD_AGE', 
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
        $types = Characteristic::types();

        return $types[$this->type_id];
    }

    public function eligibleForSeverity(array $genotypeIds)
    {
        // If this characteristic doesn't have any severities then it's not health related
        if ( ! $this->hasSeverities())
        {
            return false;
        }

        // Get all required
        $required = [];

        $genotypes = $this->genotypes;

        // No genotypes to match on
        if ($genotypes->isEmpty())
        {
            return true;
        }

        foreach($genotypes as $genotype)
        {
            $required[$genotype->id] = $genotype->locus_id;
        }

        $requiredIds   = array_keys($required);
        $matchesNeeded = count(array_unique($required));
        $matches       = count(array_intersect($requiredIds, $genotypeIds));

        return ($matches == $matchesNeeded);
    }

    public function isGenetic()
    {
        return ($this->loci()->count() > 0);
    }

    public function isRanged()
    {
        return ( ! is_null($this->min_ranged_value));
    }

    public function hasRangedGrowth()
    {
        return ( ! is_null($this->min_age_to_stop_growing));
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

    public function getRandomRangedValue()
    {
        if (is_null($this->min_ranged_value))
        {
            return null;
        }
        
        return mt_rand($this->min_ranged_value, $this->max_ranged_value);
    }

    public function getRandomRangedFemaleValue()
    {
        return $this->getRandomRangedValue();
    }

    public function getRandomRangedMaleValue()
    {
        return $this->getRandomRangedValue();
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
        if ($this->hasSeverities())
        {
            // Get all health severities
            if ($nonlethalForCurrentAge)
            {
                // Get all lethal symptoms
                $invalidSeverityIds = DB::table('characteristic_severity_symptoms')
                    ->join('characteristic_severities', 'characteristic_severities.id', '=', 'characteristic_severity_symptoms.severity_id')
                    ->where('characteristic_severity_symptoms.lethal', true)
                    ->where(function($query) use ($currentAgeOfDog)
                        {
                            $query
                                ->where(DB::raw('(`characteristic_severities`.`max_age_to_express` + `characteristic_severity_symptoms`.`max_offset_age_to_express`)'), '<=', $currentAgeOfDog)
                                ->orWhere(function($q) use ($currentAgeOfDog)
                                    {
                                        $q->where(DB::raw('(`characteristic_severities`.`min_age_to_express` + `characteristic_severity_symptoms`.`min_offset_age_to_express`)'), '<=', $currentAgeOfDog)
                                            ->where(DB::raw('(`characteristic_severities`.`max_age_to_express` + `characteristic_severity_symptoms`.`max_offset_age_to_express`)'), '>=', $currentAgeOfDog);
                                    });
                        })
                    ->lists('characteristic_severity_symptoms.severity_id');

                // Always add -1
                $invalidSeverityIds[] = -1;

                $severities = $this->severities()->with('symptoms')->whereNotIn('id', $invalidSeverityIds)->get();
            }
            else
            {
                $severities = $this->severities()->with('symptoms')->get();
            }
            
            return $severities->random();
        }
        else
        {
            return null;
        }
    }

    public function bindRangedValue($value)
    {
        $lb = $this->min_ranged_value;
        $ub = $this->max_ranged_value;

        // Bind it between the bounds
        return max(min($value, $ub), $lb);
    }

    public function bindRangedFemaleValue($value)
    {
        // Bind it between the bounds
        return $this->bindRangedValue($value);
    }

    public function bindRangedMaleValue($value)
    {
        // Bind it between the bounds
        return $this->bindRangedValue($value);
    }

    public function hasHelpPage()
    {
        return ( ! is_null($this->helpPage));
    }

    public function getRangedValueLabel($value)
    {
        // Check if a label should override the value
        $label = $this->labels()
            ->where('min_ranged_value', '<=', $value)
            ->where('max_ranged_value', '>=', $value)
            ->first();

        return $label;
    }

    public function hasPrefix()
    {
        return strlen($this->ranged_prefix_units);
    }

    public function hasSuffix()
    {
        return strlen($this->ranged_suffix_units);
    }

    public function formatPrefix()
    {
        if ($this->hasPrefix())
        {
            return preg_match('([a-zA-Z])', $this->ranged_prefix_units)
                ? $this->ranged_prefix_units.' '
                : $this->ranged_prefix_units;
        }

        return '';
    }

    public function prefixValue($value)
    {
        return $this->formatPrefix().$value;
    }

    public function formatSuffix()
    {
        if ($this->hasSuffix())
        {
            return preg_match('([a-zA-Z])', $this->ranged_suffix_units)
                ? ' '.$this->ranged_suffix_units
                : $this->ranged_suffix_units;
        }

        return '';
    }

    public function suffixValue($value)
    {
        return $value.$this->formatSuffix();
    }

    public function formatRangedValue($value, $allowLabel = true)
    {
        if ($allowLabel)
        {
            $label = $this->getRangedValueLabel($value);

            if ( ! is_null($label))
            {
                return $label->name;
            }
        }

        return $this->formatNumericRangedValue($value);
    }

    public function formatNumericRangedValue($value)
    {
        $value = number_format($value, $this->ranged_value_precision);

        return $this->suffixValue($this->prefixValue($value));
    }

    public function jsFormatRangedSlider($minRangedValue = null, $maxRangedValue = null)
    {
        // Get all of the range labels for the specified range
        $labels = $this->labels();

        if ( ! is_null($maxRangedValue))
        {
            $labels = $labels->where('min_ranged_value', '<=', $maxRangedValue);
        }

        if ( ! is_null($minRangedValue))
        {
            $labels = $labels->where('max_ranged_value', '>=', $minRangedValue);
        }

        $labels = $labels->get();

        $formatter = '';

        foreach($labels as $label)
        {
            if (strlen($formatter))
            {
                $formatter .= " else ";
            }

            $formatter .= "if (value >= $label->min_ranged_value && value <= $label->max_ranged_value) {return '$label->name';}";
        }

        if (strlen($formatter) < 1)
        {
            $prefix = $this->formatPrefix();
            $suffix = $this->formatSuffix();

            $formatter .= "return '$prefix'+value+'$suffix';";
        }

        return $formatter;
    }

    public function isActive()
    {
        return $this->active;
    }

    public function isHidden()
    {
        return $this->hide;
    }

    public function isIgnorable()
    {
        return $this->ignorable;
    }

    public function hideGenotypes()
    {
        return $this->hide_genotypes;
    }

    public function genotypesCanBeRevealed()
    {
        return $this->genotypes_can_be_revealed;
    }

    public function phenotypesCanBeRevealed()
    {
        return $this->phenotypes_can_be_revealed;
    }

    public function rangedValueCanBeRevealed()
    {
        return $this->ranged_value_can_be_revealed;
    }

    public function hasTest()
    {
        return ($this->tests()->count() > 0);
    }

    public function hasEmpiricalTest()
    {
        return ($this->tests()->where('type_id', CharacteristicTest::TYPE_EMPIRICAL)->count() > 0);
    }

    public function hasActiveTest()
    {
        return ($this->tests()->active->count() > 0);
    }

    public function isHealth()
    {
        return is_null($this->category)
            ? false
            : $this->category->isHealth();
    }

    public function queryPhenotypes()
    {
        // Get all loci
        $locusIds = $this->loci()->lists('id');

        if (empty($locusIds))
        {
            $locusIds = [ -1 ];
        }

        // Create the query to grab all phenotypes associated with this characteristic
        return Phenotype::matchesLoci($locusIds);
    }

    public function hasGenotypes()
    {
        return ($this->loci()->count() > 0);
    }

    public function hasPhenotypes()
    {
        return ($this->queryPhenotypes()->count() > 0);
    }

    public function isValid()
    {
        if ( ! $this->isActive())
        {
            return false;
        }

        // Also need to check loci in health profile because not having 
        // the correct genotypes means that the characteristic is deleted
        $totalInactiveLoci      = $this->loci()->whereInactive()->count();
        $totalInactiveGenotypes = $this->genotypes()->whereInactive()->count();

        return (($totalInactiveLoci + $totalInactiveGenotypes) < 1);
    }

    public function isType($typeId)
    {
        return ($typeId == $this->type_id);
    }

    public function isDependent()
    {
        return ($this->dependencies()->whereActive()->count() > 0);
    }

    public function hasHealthGenotypes()
    {
        return ($this->genotypes()->count() > 0);
    }

    public function hasSeverities()
    {
        return ($this->severities()->count() > 0);
    }

}
