<?php

class DogCharacteristic extends Eloquent {

    public $timestamps = false;

    protected $guarded = array('id');

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    |
    */

    public function scopeWhereCharacteristic($query, $characteristicId)
    {
        return $query->where('dog_characteristics.characteristic_id', $characteristicId);
    }

    public function scopeWhereInCharacteristics($query, $characteristicIds)
    {
        return $query->whereIn('dog_characteristics.characteristic_id', $characteristicIds);
    }

    public function scopeWhereType($query, $typeId)
    {
        return $query->whereHas('characteristic', function($query) use ($typeId)
        {
            $query->whereType($typeId);
        });
    }

    public function scopeWhereNotHealth($query)
    {
        return $query->whereHas('characteristic', function($query)
            {
                $query->whereNotHealth()->select(DB::raw('count(*)'));
            }, '>=', 1);
    }

    public function scopeWhereVisible($query)
    {
        return $query->where('dog_characteristics.hide', false);
    }

    public function scopeWhereInSummary($query)
    {
        return $query->where('dog_characteristics.in_summary', true);
    }

    public function scopeWhereGenotypesAreRevealed($query)
    {
        return $query->where('genotypes_revealed', true);
    }

    public function scopeWherePhenotypesAreRevealed($query)
    {
        return $query->where('phenotypes_revealed', true);
    }

    public function scopeWhereRangedValueIsRevealed($query)
    {
        return $query->where('ranged_value_revealed', true);
    }

    public function scopeWhereKnown($query)
    {
        return $query->where('hide', false)
            ->where(function($query)
            {
                $query->where('ranged_value_revealed', true)
                    ->orWhere('genotypes_revealed', true)
                    ->orWhere('phenotypes_revealed', true);
            });
    }

    public function scopeWhereDependent($query)
    {
        return $query
            ->select('dog_characteristics.*')
            ->join('characteristics', 'characteristics.id', '=', 'dog_characteristics.characteristic_id')
            ->where(DB::raw("
                (SELECT COUNT(characteristic_dependencies.id) 
                FROM characteristic_dependencies
                WHERE characteristic_dependencies.dependent_id = characteristics.id
                    AND characteristic_dependencies.active = 1)
            "), '>=', 1);
    }

    public function scopeOrderByCharacteristic($query)
    {
        return $query->select('dog_characteristics.*')
            ->leftJoin('characteristics', 'characteristics.id', '=', 'dog_characteristics.characteristic_id')
            ->orderBy('characteristics.name', 'asc');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    |
    |
    */

    public function getHideAttribute($hide)
    {
        return (bool) $hide;
    }

    public function getGenotypesRevealedAttribute($genotypesRevealed)
    {
        return (bool) $genotypesRevealed;
    }

    public function getPhenotypesRevealedAttribute($phenotypesRevealed)
    {
        return (bool) $phenotypesRevealed;
    }

    public function getRangedValueRevealedAttribute($rangedValueRevealed)
    {
        return (bool) $rangedValueRevealed;
    }

    public function getSeverityExpressedAttribute($severityExpressed)
    {
        return (bool) $severityExpressed;
    }

    public function getSeverityValueRevealedAttribute($severityValueRevealed)
    {
        return (bool) $severityValueRevealed;
    }

    public function getInSummaryAttribute($inSummary)
    {
        return (bool) $inSummary;
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the dog
     *
     * @return Dog
     */
    public function dog()
    {
        return $this->belongsTo('Dog', 'dog_id');
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
    | Has Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All symptoms
     *
     * @return Collection of DogCharacteristicSymptoms
     */
    public function symptoms()
    {
        return $this->hasMany('DogCharacteristicSymptom', 'dog_characteristic_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All tests
     *
     * @return Collection of CharacteristicTests
     */
    public function tests()
    {
        return $this->belongsToMany('CharacteristicTest', 'dog_characteristic_tests', 'dog_characteristic_id', 'test_id');
    }

    /**
     * All genotypes
     *
     * @return Collection of Genotypes
     */
    public function genotypes()
    {
        return $this->belongsToMany('Genotype', 'dog_characteristic_genotypes', 'dog_characteristic_id', 'genotype_id');
    }

    /**
     * All phenotypes
     *
     * @return Collection of Phenotypes
     */
    public function phenotypes()
    {
        return $this->belongsToMany('Phenotype', 'dog_characteristic_phenotypes', 'dog_characteristic_id', 'phenotype_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Has Many Through Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All untested tests
     *
     * @return Collection of CharacteristicTests
     */
    public function untestedTests()
    {
        // Get all tested test ids
        $testedIds = $this->tests()->lists('id');

        return empty($testedIds)
            ? $this->characteristic->tests()->whereActive()
            : $this->characteristic->tests()->whereActive()->whereNotIn('id', $testedIds);
    }

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public static function currentRangedValue($finalRangedValue, $ageToStopGrowing, $currentAge)
    {
        return (is_null($finalRangedValue) or is_null($ageToStopGrowing) or $currentAge >= $ageToStopGrowing)
            ? $finalRangedValue
            : ($finalRangedValue / ($ageToStopGrowing + 1.00)) * ($currentAge + 2.00);

    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function genotypesAreRevealed()
    {
        return $this->genotypes_revealed;
    }

    public function phenotypesAreRevealed()
    {
        return $this->phenotypes_revealed;
    }

    public function rangedValueIsRevealed()
    {
        return $this->ranged_value_revealed;
    }

    public function severityIsExpressed()
    {
        return $this->severity_expressed;
    }

    public function severityValueIsRevealed()
    {
        return $this->severity_value_revealed;
    }

    public function genotypesCanBeRevealed()
    {
        return $this->characteristic->genotypesCanBeRevealed();
    }

    public function phenotypesCanBeRevealed()
    {
        return $this->characteristic->phenotypesCanBeRevealed();
    }

    public function rangedValueCanBeRevealed()
    {
        return $this->characteristic->rangedValueCanBeRevealed();
    }

    public function severityCanBeExpressed()
    {
        return is_null($this->characteristicSeverity)
            ? false
            : $this->characteristicSeverity->canBeExpressed();
    }

    public function severityValueCanBeRevealed()
    {
        return is_null($this->characteristicSeverity)
            ? false
            : $this->characteristicSeverity->valueCanBeRevealed();
    }

    public function hasRangedGrowth()
    {
        return $this->characteristic->hasRangedGrowth();
    }

    public function isInSummary()
    {
        return $this->in_summary;
    }

    public function formatRangedValue()
    {
        return $this->characteristic->formatRangedValue($this->current_ranged_value);
    }

    public function hasSeverity()
    {
        return ( ! is_null($this->characteristicSeverity));
    }

    public function formatSeverityValue()
    {
        return is_null($this->characteristicSeverity)
            ? $this->severity_value
            : $this->characteristicSeverity->formatValue($this->severity_value);
    }

    public function hasTest()
    {
        return $this->characteristic->hasTest();
    }

    public function hasActiveTest()
    {
        return $this->characteristic->hasActiveTest();
    }

    public function hasHadTest($test)
    {
        return ($this->tests()->where('id', $test->id)->count() > 0);
    }

    public function hasUntestedTest()
    {
        return ($this->untestedTests()->count() > 0);
    }

    public function hasUntestedTestableTest()
    {
        return ($this->untestedTests()->whereInTestableAgeRange($this->dog->age)->count() > 0);
    }

    public function hasBeenTested()
    {
        return ( ! is_null($this->last_tested_at_months));
    }

    public function hasExpressedSymptoms()
    {
        return ($this->symptoms()->whereExpressed()->count() > 0);
    }

    public function isHidden()
    {
        return $this->hide;
    }

    public function isVisible()
    {
        return ( ! $this->hide);
    }

    public function isKnown()
    {
        return ( ! $this->isHidden() and ($this->rangedValueIsRevealed() or $this->genotypesAreRevealed() or $this->phenotypesAreRevealed()));
    }

}
