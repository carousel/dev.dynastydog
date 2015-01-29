<?php

class Breed extends Eloquent {

    protected $guarded = array('id');

    protected $imageDirectory = 'assets/img/breeds';
    protected $imageExtension = 'png';

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

    public function getImportableAttribute($importable)
    {
        return (bool) $importable;
    }

    public function getExtinctableAttribute($extinctable)
    {
        return (bool) $extinctable;
    }

    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    |
    |
    */

    public function setCreatorIdAttribute($creatorId)
    {
        $this->attributes['creator_id'] = strlen($creatorId) ? $creatorId : null;
    }

    public function setOriginatorIdAttribute($originatorId)
    {
        $this->attributes['originator_id'] = strlen($originatorId) ? $originatorId : null;
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

    public function scopeWhereInactive($query)
    {
        return $query->where('active', false);
    }

    public function scopeWhereImportable($query)
    {
        return $query->where('importable', true);
    }

    public function scopeWhereNotImportable($query)
    {
        return $query->where('importable', false);
    }

    public function scopeWhereExtinctable($query)
    {
        return $query->where('extinctable', true);
    }

    public function scopeWhereNotExtinctable($query)
    {
        return $query->where('extinctable', false);
    }

    public function scopeWherePlayerBreed($query)
    {
        return $query->whereNotNull('creator_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the breed's creator.
     *
     * @return User
     */
    public function creator()
    {
        return $this->belongsTo('User', 'creator_id', 'id');
    }

    /**
     * Return the breed's originator.
     *
     * @return Dog
     */
    public function originator()
    {
        return $this->belongsTo('Dog', 'originator_id', 'id');
    }

    /**
     * Return the breed's original draft.
     *
     * @return BreedDraft
     */
    public function draft()
    {
        return $this->belongsTo('BreedDraft', 'draft_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Has Many Relationships
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
        return $this->hasMany('Dog', 'breed_id', 'id');
    }

    /**
     * All characteristics
     *
     * @return Collection of BreedCharacteristics
     */
    public function characteristics()
    {
        return $this->hasMany('BreedCharacteristic', 'breed_id', 'id');
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
        return $this->belongsToMany('Genotype', 'breed_genotypes', 'breed_id', 'genotype_id')->withPivot('frequency');
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function delete()
    {
        // Remove its image
        $publicPath = $this->getImagePath();

        if (File::isFile($publicPath))
        {
            File::delete($publicPath);
        }

        return parent::delete();
    }

    public function getImageDirectory()
    {
        return $this->imageDirectory;
    }

    public function getImageExtension()
    {
        return $this->imageExtension;
    }

    public function isImportable()
    {
        return $this->importable;
    }

    public function isActive()
    {
        return $this->active;
    }

    public function isInactive()
    {
        return ( ! $this->active);
    }

    public function isExtinctable()
    {
        return $this->extinctable;
    }

    public function hasDescription()
    {
        return (strlen($this->description) > 0);
    }

    public function hasImage()
    {
        return File::exists($this->getImagePath());
    }

    public function getImageUrl()
    {
        // Grab the image
        return implode(DIRECTORY_SEPARATOR, [$this->getImageDirectory(), $this->image_url.'.'.$this->getImageExtension()]);
    }

    public function getImagePath()
    {
        return public_path($this->getImageUrl());
    }

    public function checkDog($dog)
    {
        // Store the failed characteristics here
        $failedCharacteristics = [];

        // Grab all genotype IDs from the breed
        $breedGenotypeIds = $this->genotypes()->wherePivot('frequency', '>', 0)->whereActive()->lists('id');

        // Get all characteristics this breed has that are not hidden
        $breedCharacteristics = $this->characteristics()
            ->with('characteristic')
            ->whereActive()
            ->whereVisible()
            ->whereHas('characteristic', function($query)
            {
                $query->whereActive()->whereVisible()->whereNotHealth();
            })
            ->get();

        foreach($breedCharacteristics as $breedCharacteristic)
        {
            // Grab the umbrella characteristic
            $characteristic = $breedCharacteristic->characteristic;

            // Get the dog's equivalent
            $dogCharacteristic = $dog->characteristics()->whereVisible()->whereCharacteristic($characteristic->id)->first();

            if (is_null($dogCharacteristic))
            {
                // Log the fail
                // $failedCharacteristics[] = $characteristic;

                // Continue to the next breed characteristic
                continue;
            }

            // Do genetic check
            if ($characteristic->isGenetic())
            {
                // Get all genotypes attached to this dog's characteristic
                $dogGenotypeIds = $dogCharacteristic->genotypes()->whereActive()->lists('id');

                // Make sure the breed has all of them
                $difference = array_diff($dogGenotypeIds, $breedGenotypeIds);

                if ( ! empty($difference))
                {
                    // Log the fail
                    $failedCharacteristics[] = $characteristic;

                    // Continue to the next breed characteristic
                    continue;
                }
            }

            // Do ranged check
            if ($characteristic->isRanged())
            {
                $sex = $dog->isFemale() ? 'female' : 'male';

                if ( ! $breedCharacteristic->isInRange($dogCharacteristic->final_ranged_value, $sex))
                {
                    // Log the fail
                    $failedCharacteristics[] = $characteristic;

                    // Continue to the next breed characteristic
                    continue;
                }
            }
        }

        return $failedCharacteristics;
    }

    public function hasCreator()
    {
        return ( ! is_null($this->creator));
    }

    public function hasOriginator()
    {
        return ( ! is_null($this->originator));
    }

    public function hasCharacteristics()
    {
        return ($this->characteristics()->count() > 0);
    }

    public function hasGenotypes()
    {
        return ($this->genotypes()->wherePivot('frequency', '>', 0)->count() > 0);
    }

    public function addCharacteristics($characteristics, $active, $hide, $reset = false)
    {
        $totalAdded = 0;

        if ( ! $characteristics->isEmpty())
        {
            // Get all used characteristics IDs
            $usedCharacteristicsIds = $this->characteristics()->lists('characteristic_id');

            foreach($characteristics as $characteristic)
            {
                $alreadyExists = in_array($characteristic->id, $usedCharacteristicsIds);

                // If reset is true, remove the existing breed characteristic
                if ($reset and $alreadyExists)
                {
                    DB::table('breed_characteristics')
                        ->where('breed_id', $this->id)
                        ->where('characteristic_id', $characteristic->id)
                        ->delete();

                    $alreadyExists = false;
                }

                if ( ! $alreadyExists)
                {
                    $breedCharacteristic = BreedCharacteristic::create(array(
                        'breed_id'          => $this->id, 
                        'characteristic_id' => $characteristic->id, 
                        'active'            => $active, 
                        'hide'              => $hide, 
                        'min_age_to_reveal_genotypes'  => $characteristic->min_age_to_reveal_genotypes, 
                        'max_age_to_reveal_genotypes'  => $characteristic->max_age_to_reveal_genotypes, 
                        'min_age_to_reveal_phenotypes' => $characteristic->min_age_to_reveal_phenotypes, 
                        'max_age_to_reveal_phenotypes' => $characteristic->max_age_to_reveal_phenotypes, 
                        'min_female_ranged_value' => $characteristic->min_ranged_value, 
                        'max_female_ranged_value' => $characteristic->max_ranged_value, 
                        'min_male_ranged_value'   => $characteristic->min_ranged_value, 
                        'max_male_ranged_value'   => $characteristic->max_ranged_value, 
                        'min_age_to_reveal_ranged_value' => $characteristic->min_age_to_reveal_ranged_value, 
                        'max_age_to_reveal_ranged_value' => $characteristic->max_age_to_reveal_ranged_value, 
                        'min_age_to_stop_growing' => $characteristic->min_age_to_stop_growing, 
                        'max_age_to_stop_growing' => $characteristic->max_age_to_stop_growing, 
                    ));

                    foreach($characteristic->severities as $characteristicSeverity)
                    {
                        $breedCharacteristicSeverity = BreedCharacteristicSeverity::create(array(
                            'breed_characteristic_id'    => $breedCharacteristic->id, 
                            'characteristic_severity_id' => $characteristicSeverity->id, 
                            'min_age_to_express'      => $characteristicSeverity->min_age_to_express, 
                            'max_age_to_express'      => $characteristicSeverity->max_age_to_express, 
                            'min_age_to_reveal_value' => $characteristicSeverity->min_age_to_reveal_value, 
                            'max_age_to_reveal_value' => $characteristicSeverity->max_age_to_reveal_value, 
                        ));

                        foreach($characteristicSeverity->symptoms as $characteristicSeveritySymptom)
                        {
                            BreedCharacteristicSeveritySymptom::create(array(
                                'breed_characteristic_severity_id'   => $breedCharacteristicSeverity->id, 
                                'characteristic_severity_symptom_id' => $characteristicSeveritySymptom->id, 
                                'min_offset_age_to_express' => $characteristicSeveritySymptom->min_offset_age_to_express, 
                                'max_offset_age_to_express' => $characteristicSeveritySymptom->max_offset_age_to_express, 
                                'lethal' => $characteristicSeveritySymptom->lethal, 
                            ));
                        }
                    }

                    ++$totalAdded;
                }
            }
        }

        return $totalAdded;
    }

}
