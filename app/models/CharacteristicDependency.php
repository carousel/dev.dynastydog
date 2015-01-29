<?php

class CharacteristicDependency extends Eloquent {

    const TYPE_R2R_GT       = 0;
    const TYPE_R2R_LT       = 1;
    const TYPE_R2R_GTE      = 2;
    const TYPE_R2R_LTE      = 3;
    const TYPE_R2R_PER      = 4;
    const TYPE_R2R_GT_AVG   = 5;
    const TYPE_R2R_LT_AVG   = 6;
    const TYPE_R2R_GTE_AVG  = 7;
    const TYPE_R2R_LTE_AVG  = 8;
    const TYPE_R2R_GT_SUM   = 9;
    const TYPE_R2R_LT_SUM   = 10;
    const TYPE_R2R_GTE_SUM  = 11;
    const TYPE_R2R_LTE_SUM  = 12;
    const TYPE_R2R_GT_DIFF  = 13;
    const TYPE_R2R_LT_DIFF  = 14;
    const TYPE_R2R_GTE_DIFF = 15;
    const TYPE_R2R_LTE_DIFF = 16;
    const TYPE_G2R          = 17;
    const TYPE_R2G          = 18;
    const TYPE_G2G          = 19;
    const TYPE_R2R_PER_AVG  = 20;

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

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    |
    */

    public function scopeWhereActive($query)
    {
        return $query->where('active', true);
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
        return $this->belongsTo('Characteristic', 'dependent_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Has Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All groups
     *
     * @return Collection of CharacteristicDependencyGroups
     */
    public function groups()
    {
        return $this->hasMany('CharacteristicDependencyGroup', 'characteristic_dependency_id', 'id');
    }

    /**
     * All independent characteristics
     *
     * @return Collection of CharacteristicDependencyIndependentCharacteristics
     */
    public function independentCharacteristics()
    {
        return $this->hasMany('CharacteristicDependencyIndependentCharacteristic', 'characteristic_dependency_id', 'id');
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
            CharacteristicDependency::TYPE_R2R_GT => 'R2R_GT',
            CharacteristicDependency::TYPE_R2R_LT => 'R2R_LT', 
            CharacteristicDependency::TYPE_R2R_GTE => 'R2R_GTE', 
            CharacteristicDependency::TYPE_R2R_LTE => 'R2R_LTE', 
            CharacteristicDependency::TYPE_R2R_PER => 'R2R_PER', 
            CharacteristicDependency::TYPE_R2R_GT_AVG => 'R2R_GT_AVG', 
            CharacteristicDependency::TYPE_R2R_LT_AVG => 'R2R_LT_AVG', 
            CharacteristicDependency::TYPE_R2R_GTE_AVG => 'R2R_GTE_AVG', 
            CharacteristicDependency::TYPE_R2R_LTE_AVG => 'R2R_LTE_AVG', 
            CharacteristicDependency::TYPE_R2R_PER_AVG => 'R2R_PER_AVG', 
            CharacteristicDependency::TYPE_R2R_GT_SUM => 'R2R_GT_SUM', 
            CharacteristicDependency::TYPE_R2R_LT_SUM => 'R2R_LT_SUM', 
            CharacteristicDependency::TYPE_R2R_GTE_SUM => 'R2R_GTE_SUM', 
            CharacteristicDependency::TYPE_R2R_LTE_SUM => 'R2R_LTE_SUM', 
            CharacteristicDependency::TYPE_R2R_GT_DIFF => 'R2R_GT_DIFF', 
            CharacteristicDependency::TYPE_R2R_LT_DIFF => 'R2R_LT_DIFF', 
            CharacteristicDependency::TYPE_R2R_GTE_DIFF => 'R2R_GTE_DIFF', 
            CharacteristicDependency::TYPE_R2R_LTE_DIFF => 'R2R_LTE_DIFF', 
            CharacteristicDependency::TYPE_R2G => 'R2G', 
            CharacteristicDependency::TYPE_G2G => 'G2G', 
            CharacteristicDependency::TYPE_G2R => 'G2R', 
        );
    }

    public static function typeOf($typeId)
    {
        $types = CharacteristicDependency::types();
        $type  = $types[$typeId];
        $parts = explode('_', $type);

        return $parts[0];
    }

    public static function validTypeForDependentCharacteristic($characteristic, $typeId)
    {
        $parent = CharacteristicDependency::typeOf($typeId);

        switch ($parent)
        {
            case 'R2G':
            case 'G2G':
                return $characteristic->isGenetic();

            case 'G2R':
            case 'R2R':
                return $characteristic->isRanged();
            
            default:
                return false;
        }
    }

    public static function validTypeForIndependentCharacteristic($characteristic, $typeId)
    {
        $parent = CharacteristicDependency::typeOf($typeId);

        switch ($parent)
        {
            case 'R2G':
            case 'R2R':
                return $characteristic->isRanged();

            case 'G2R':
            case 'G2G':
                return $characteristic->isGenetic();
            
            default:
                return false;
        }
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
        $types = CharacteristicDependency::types();

        return $types[$this->type_id];
    }

    public function isActive()
    {
        return $this->active;
    }

    public function takesInRanged()
    {
        $parent = CharacteristicDependency::typeOf($this->type_id);

        return ($parent == 'R2R' or $parent == 'R2G');
    }

    public function takesInGenotypes()
    {
        $parent = CharacteristicDependency::typeOf($this->type_id);

        return ($parent == 'G2G' or $parent == 'G2R');
    }

    public function outputsRanged()
    {
        $parent = CharacteristicDependency::typeOf($this->type_id);

        return ($parent == 'R2R' or $parent == 'G2R');
    }

    public function outputsGenotypes()
    {
        $parent = CharacteristicDependency::typeOf($this->type_id);

        return ($parent == 'G2G' or $parent == 'R2G');
    }

    public function isR2R()
    {
        $parent = CharacteristicDependency::typeOf($this->type_id);

        return ($parent == 'R2R');
    }

    public function isR2G()
    {
        $parent = CharacteristicDependency::typeOf($this->type_id);

        return ($parent == 'R2G');
    }

    public function isG2R()
    {
        $parent = CharacteristicDependency::typeOf($this->type_id);

        return ($parent == 'G2R');
    }

    public function isG2G()
    {
        $parent = CharacteristicDependency::typeOf($this->type_id);

        return ($parent == 'G2G');
    }

    public function needsRangedPercents()
    {
        return ($this->type_id == CharacteristicDependency::TYPE_R2R_PER OR $this->type_id == CharacteristicDependency::TYPE_R2R_PER_AVG);
    }

    public function doR2R($finalRangedValue, $independentRangedValues)
    {
        if (is_null($finalRangedValue) OR empty($independentRangedValues))
        {
            return $finalRangedValue;
        }

        return $this->needsRangedPercents()
            ? $this->doR2RPercents($finalRangedValue, $independentRangedValues)
            : $this->doR2RNonpercents($finalRangedValue, $independentRangedValues);
    }

    public function doR2RPercents($finalRangedValue, $independentRangedValues)
    {
        $independentCharacteristicIds = array_keys($independentRangedValues);

        // We need to get the percents the independent characteristics
        $firstIndependentCharacteristic = $this->independentCharacteristics()->first();

        // Always take percents from the first independent characteristic
        $minPercent = $firstIndependentCharacteristic->min_percent;
        $maxPercent = $firstIndependentCharacteristic->max_percent;

        $averageOfIndependentRangedValues = array_sum($independentRangedValues) / count($independentRangedValues);

        $percentageOfAverage = ($finalRangedValue / $averageOfIndependentRangedValues) * 100.0;

        if ($percentageOfAverage >= $minPercent AND $percentageOfAverage <= $maxPercent)
        {
            return $finalRangedValue;
        }

        return ($percentageOfAverage > $maxPercent)
            ? $averageOfIndependentRangedValues * ($maxPercent / 100.0)
            : $averageOfIndependentRangedValues * ($minPercent / 100.0);
    }

    public function doR2RNonpercents($finalRangedValue, $independentRangedValues)
    {
        switch ($this->type_id)
        {
            case CharacteristicDependency::TYPE_R2R_GT:
            case CharacteristicDependency::TYPE_R2R_LT:
            case CharacteristicDependency::TYPE_R2R_GTE:
            case CharacteristicDependency::TYPE_R2R_LTE:
                $passableValue = reset($independentRangedValues);
                break;

            case CharacteristicDependency::TYPE_R2R_GT_AVG:
            case CharacteristicDependency::TYPE_R2R_LT_AVG:
            case CharacteristicDependency::TYPE_R2R_GTE_AVG:
            case CharacteristicDependency::TYPE_R2R_LTE_AVG:
                $passableValue = array_sum($independentRangedValues) / count($independentRangedValues);
                break;

            case CharacteristicDependency::TYPE_R2R_GT_SUM:
            case CharacteristicDependency::TYPE_R2R_LT_SUM:
            case CharacteristicDependency::TYPE_R2R_GTE_SUM:
            case CharacteristicDependency::TYPE_R2R_LTE_SUM:
                $passableValue = array_sum($independentRangedValues);
                break;

            case CharacteristicDependency::TYPE_R2R_GT_DIFF:
            case CharacteristicDependency::TYPE_R2R_LT_DIFF:
            case CharacteristicDependency::TYPE_R2R_GTE_DIFF:
            case CharacteristicDependency::TYPE_R2R_LTE_DIFF:
                $diff = null;

                foreach ($independentRangedValues as $independentRangedValue)
                {
                    $diff = (is_null($diff) ? $independentRangedValue : $diff - $independentRangedValue);
                }

                $passableValue = $diff;
                break;
            
            default:
                return $finalRangedValue;
        }

        switch ($this->type_id)
        {
            case CharacteristicDependency::TYPE_R2R_GT:
            case CharacteristicDependency::TYPE_R2R_GT_AVG:
            case CharacteristicDependency::TYPE_R2R_GT_SUM:
            case CharacteristicDependency::TYPE_R2R_GT_DIFF:
                $passed = ($finalRangedValue > $passableValue);
                break;

            case CharacteristicDependency::TYPE_R2R_LT:
            case CharacteristicDependency::TYPE_R2R_LT_AVG:
            case CharacteristicDependency::TYPE_R2R_LT_SUM:
            case CharacteristicDependency::TYPE_R2R_LT_DIFF:
                $passed = ($finalRangedValue < $passableValue);
                break;

            case CharacteristicDependency::TYPE_R2R_GTE:
            case CharacteristicDependency::TYPE_R2R_GTE_AVG:
            case CharacteristicDependency::TYPE_R2R_GTE_SUM:
            case CharacteristicDependency::TYPE_R2R_GTE_DIFF:
                $passed = ($finalRangedValue >= $passableValue);
                break;

            case CharacteristicDependency::TYPE_R2R_LTE:
            case CharacteristicDependency::TYPE_R2R_LTE_AVG:
            case CharacteristicDependency::TYPE_R2R_LTE_SUM:
            case CharacteristicDependency::TYPE_R2R_LTE_DIFF:
                $passed = ($finalRangedValue <= $passableValue);
                break;

            default:
                return $finalRangedValue;
        }

        return ($passed)
            ? $finalRangedValue
            : $passableValue;
    }

    public function getGroupByGenotypes($independentGenotypes)
    {
        // Get the independent characteristics
        $independentCharacteristics = $this->independentCharacteristics;

        // Get all the groups for this dependency
        $groups = $this->groups()->with('independentCharacteristicGenotypes', 'independentCharacteristicRanges')->get();

        $groupMatches = [];
        $sortedGroups = [];

        foreach ($independentCharacteristics as $independentCharacteristic)
        {
            // Find the groups it matches
            foreach ($groups as $group)
            {
                $matches = $group->independentCharacteristicGenotypes()
                    ->where('characteristic_dependency_ind_characteristic_id', $independentCharacteristic->id)
                    ->whereIn('genotype_id', $independentGenotypes)
                    ->count();

                if ($matches > 0)
                {
                    if ( ! isset($groupMatches[$group->id]))
                    {
                        $groupMatches[$group->id] = 0;
                    }
                    
                    ++$groupMatches[$group->id];
                }

                $sortedGroups[$group->id] = $group;
            }
        }

        if (empty($groupMatches))
        {
            return null;
        }

        $mostMatches = max($groupMatches);

        $mostMatchedGroupId = array_search($mostMatches, $groupMatches);

        // Get the most matched group
        return $sortedGroups[$mostMatchedGroupId];
    }

    public function doG2R($finalRangedValue, $independentGenotypes)
    {
        if (is_null($finalRangedValue) or empty($independentGenotypes))
        {
            return $finalRangedValue;
        }

        $dependencyGroup = $this->getGroupByGenotypes($independentGenotypes);

        if (is_null($dependencyGroup))
        {
            return $finalRangedValue;
        }

        // Grab the possible independent characteristic ranges
        $independentCharacteristicRanges = $dependencyGroup->independentCharacteristicRanges->toArray();

        // Pick a random possible range
        $chosen = array_random($independentCharacteristicRanges);

        $minRangedValue = $chosen['min_ranged_value'];
        $maxRangedValue = $chosen['max_ranged_value'];

        if ($finalRangedValue >= $minRangedValue and $finalRangedValue <= $maxRangedValue)
        {
            return $finalRangedValue;
        }

        return Floats::mtRand($minRangedValue, $maxRangedValue, 2);
    }

    public function hasIndependentCharacteristics()
    {
        return ($this->independentCharacteristics->count() > 0);
    }

    public function validDependentCharacteristic($characteristic)
    {
        return CharacteristicDependency::validTypeForDependentCharacteristic($characteristic, $this->type_id);
    }

    public function validIndependentCharacteristic($characteristic)
    {
        if ( ! CharacteristicDependency::validTypeForIndependentCharacteristic($characteristic, $this->type_id))
        {
            return false;
        }

        // Make sure the independent characteristic is independent of the dependent characteristic
        if ($this->dependent_id == $characteristic->id)
        {
            return false;
        }

        return CharacteristicDependencyIndependentCharacteristic::whereHas('dependency', function($query) use ($characteristic)
                {
                    $query->where('dependent_id', $characteristic->id);
                })
            ->where('independent_characteristic_id', $this->dependent_id)
            ->get()
            ->isEmpty();
    }

}
