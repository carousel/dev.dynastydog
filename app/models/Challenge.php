<?php

class Challenge extends Eloquent {

    protected $guarded = array('id');

    protected $dates = ['completed_at'];

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    |
    */

    public function scopeWhereIncomplete($query)
    {
        return $query->whereNull('completed_at');
    }

    public function scopeWhereComplete($query)
    {
        return $query->whereNotNull('completed_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the user.
     *
     * @return User
     */
    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id');
    }

    /**
     * Return the level.
     *
     * @return ChallengeLevel
     */
    public function level()
    {
        return $this->belongsTo('ChallengeLevel', 'level_id', 'id');
    }

    /**
     * Return the dog.
     *
     * @return Dog
     */
    public function dog()
    {
        return $this->belongsTo('Dog', 'dog_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | One To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All characteristics
     *
     * @return Collection of ChallengeCharacteristics
     */
    public function characteristics()
    {
        return $this->hasMany('ChallengeCharacteristic', 'challenge_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public static function rollForUser($user)
    {
        // Grab the challenge level
        $challengeLevel = $user->challengeLevel;

        // Create the challenge
        $challenge = Challenge::create(array(
            'user_id'  => $user->id, 
            'level_id' => $challengeLevel->id, 
        ));

        // Roll the characteristics
        $challenge->rollCharacteristics();

        return $challenge;
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function isIncomplete()
    {
        return is_null($this->completed_at);
    }

    public function isComplete()
    {
        return ( ! is_null($this->completed_at));
    }

    public function canBeRerolled()
    {
        return $this->user->canRerollChallenges();
    }

    public function checkDog(Dog $dog)
    {
        // Check against the challenge's characteristics
        $challengeCharacteristics = $this->characteristics()->with('characteristic')->get();

        // None to compare against
        if ($challengeCharacteristics->isEmpty())
        {
            return true;
        }

        // Get the characteristic ids
        $characteristicIds = $challengeCharacteristics->lists('characteristic_id');

        // Get the appropriate dog's characteristics
        $dogCharacteristics = $dog->characteristics()
            ->whereVisible()
            ->whereIn('characteristic_id', $characteristicIds)
            ->get();

        // Make sure all were grabbed
        if ($dogCharacteristics->count() != $challengeCharacteristics->count())
        {
            return false;
        }

        foreach($challengeCharacteristics as $challengeCharacteristic)
        {
            $characteristicId = $challengeCharacteristic->characteristic_id;

            // Find the dog characteristic
            $dogCharacteristic = $dogCharacteristics->filter(function($item) use ($characteristicId)
                {
                    return $item->characteristic_id == $characteristicId;
                })
                ->first();

            if (is_null($dogCharacteristic))
            {
                return false;
            }

            // Check the dog's characteristic against the challenge's characteristic
            if ( ! $challengeCharacteristic->checkDogCharacteristic($dogCharacteristic))
            {
                // If any characteristic check fails, the entire challenge check fails
                return false;
            }
        }

        // Passed all characteristic checks
        return true;
    }

    public function rollCharacteristics()
    {
        if ($this->user->isOnTutorialStage('start-tutorial')) // Special tutorial settings
        {
            $currentStage = $this->user->tutorialStages()->current()->first();

            // We are going to pick a characteristic from their dog that can also be tested
            $dogId = $currentStage->data['dog_id'];

            // Grab the dog
            $dog = Dog::find($dogId);

            if (is_null($dog))
            {
                // Get the first alive dog you can
                $dog = $this->user->dogs()->whereAlive()->orderBy('id', 'asc')->first();

                if (is_null($dog))
                {
                    $this->delete();

                    throw new Dynasty\UserTutorials\Exceptions\CannotContinueException;
                }

                // We should update the tutorial here to add the dog
                $currentStage->data = is_array($currentStage->data)
                    ? array('dog_id' => $dog->id) + $currentStage->data
                    : array('dog_id' => $dog->id);
                
                $currentStage->save();
            }

            // Get all testable characteristic ids
            $testableCharacteristicIds = CharacteristicTest::whereActive()->whereInTestableAgeRange($dog->age)->lists('characteristic_id');

            if (empty($testableCharacteristicIds))
            {
                $this->delete();
                    
                throw new Dynasty\Challenges\Exceptions\NoTestableCharacteristicsException;
            }

            // Grab the dog's characteristics
            $dogCharacteristics = $dog->characteristics()
                ->whereVisible()
                ->whereNotHealth()
                ->whereInCharacteristics($testableCharacteristicIds)
                ->get();

            $totalDogCharacteristics = $dogCharacteristics->count();

            if ($totalDogCharacteristics < 1)
            {
                $this->delete();
                
                throw new Dynasty\Challenges\Exceptions\NoTestableDogCharacteristicsException;
            }

            // Find a dog characteristic to use
            $dogCharacteristic = $dogCharacteristics->random();

            // Grab the characteristic
            $characteristic = $dogCharacteristic->characteristic;

            // Add the characteristic to the challenge
            $challengeCharacteristic = ChallengeCharacteristic::create(array(
                'challenge_id'      => $this->id, 
                'characteristic_id' => $characteristic->id, 
                'ranged_value'      => $dogCharacteristic->current_ranged_value, 
            ));

            if ($characteristic->isGenetic())
            {
                // Check if the dog characteristic has phenotypes and if it does, just use the first one
                $phenotype = $dogCharacteristic->phenotypes()->first();

                if ( ! is_null($phenotype))
                {
                    $challengeCharacteristic->phenotypes()->attach($phenotype->id);
                }
                else // No phenotype found
                {
                    // Use the genotypes
                    $genotypeIds = $dogCharacteristic->genotypes()->lists('id');

                    if ( ! empty($genotypeIds))
                    {
                        $challengeCharacteristic->genotypes()->attach($genotypeIds);
                    }
                }
            }
        }
        else // Doesn't need to bother with special tutorial settings
        {
            // Get the genotypes for this challenge
            $genotypes = Genotype::whereActive()
                ->whereHas('breeds', function($query)
                {
                    $query->whereActive()->where('frequency', '>', 0);
                }, '>=', 1)
                ->get();

            // Collect on the locus
            $collectedGenotypes = array_collect($genotypes, 'locus_id');

            // Create the genome
            $chosenGenotypes = [];

            foreach($collectedGenotypes as $locusId => $genotypes)
            {
                $index = mt_rand(0, count($genotypes) - 1);
                $chosenGenotypes[$locusId] = $genotypes[$index];
            }

            // Get all characteristics from the breeds that are active and not in the Health categories
            $characteristics = Characteristic::with('loci', 'dependencies.independentCharacteristics')
                ->whereActive()
                ->whereVisible()
                ->whereNotHealth()
                ->whereHas('breedCharacteristics', function($query)
                {
                    $query->whereActive()->whereVisible();
                }, '>=', 1)
                ->get();

            // Make sure enough were found to generate the challenge
            if ($this->level->characteristics_generated > $characteristics->count())
            {
                throw new Dynasty\Challenges\Exceptions\NotEnoughCharacteristicsToGenerateException;
            }

            // Randomly choose the characteristics to use
            $chosenCharacteristics = ($this->level->characteristics_generated == 1)
                ? array($characteristics->random()) // random() won't return an array of items if the amount is 1
                : $characteristics->random($this->level->characteristics_generated);

            $chosenCharacteristicIds = [];

            // Store the challenge characteristics for dependencies
            $challengeCharacteristics = [];

            foreach($chosenCharacteristics as $characteristic)
            {
                // Store for later
                $chosenCharacteristicIds[] = $characteristic->id;

                // Add the characteristic to the challenge
                $challengeCharacteristic = ChallengeCharacteristic::create(array(
                    'challenge_id'      => $this->id, 
                    'characteristic_id' => $characteristic->id, 
                    'ranged_value'      => $characteristic->getRandomRangedValue(), // null if not ranged
                ));

                $challengeCharacteristics[$characteristic->id] = $challengeCharacteristic;

                if ($characteristic->isGenetic())
                {
                    // Get the loci attached
                    $locusIds = $characteristic->loci->lists('id');

                    // Gather the genotypes for this characteristic
                    $genotypeIds = [];

                    foreach($locusIds as $locusId)
                    {
                        if (array_key_exists($locusId, $chosenGenotypes))
                        {
                            $genotypeIds[] = $chosenGenotypes[$locusId]->id;
                        }
                    }

                    if ( ! empty($genotypeIds))
                    {
                        // Check if it has any phenotypes first
                        $phenotypes = $characteristic->queryPhenotypes()->get();

                        $phenotype = $phenotypes->filter(function($item) use ($genotypeIds)
                            {
                                $phenotypeGenotypeIds = $item->genotypes()->lists('id');

                                // Make sure all the genotypeIds are in phenotypeGenotypeIds
                                $diff = array_diff($genotypeIds, $phenotypeGenotypeIds);

                                return empty($diff);
                            })
                            ->random();

                        if ( ! is_null($phenotype))
                        {
                            // Attach it
                            $challengeCharacteristic->phenotypes()->attach($phenotype->id);
                        }
                        else // We need to use the genotypes
                        {
                            $challengeCharacteristic->genotypes()->attach($genotypeIds);
                        }
                    }
                }
            }

            // Go through again and check for dependencies only if all independent characteristics are present
            foreach($chosenCharacteristics as $characteristic)
            {
                $challengeCharacteristic = $challengeCharacteristics[$characteristic->id];

                // Get all dependencies for this characteristics
                $dependencies = $characteristic->dependencies;

                foreach($dependencies as $dependency)
                {
                    // Only do the active ones
                    if ($dependency->isActive())
                    {
                        // Get all independent characteristics
                        $independentCharacteristics = $dependency->independentCharacteristics;
                        $independentCharacteristicCharacteristicIds = $independentCharacteristics->lists('characteristic_id');

                        $diff = array_diff($independentCharacteristicCharacteristicIds, $chosenCharacteristicIds);

                        // Must have been generated with all of the independents
                        if (empty($diff))
                        {
                            $independentRangedValues = [];
                            $independentGenotypes = [];

                            foreach($independentCharacteristics as $independentCharacteristic)
                            {
                                if ($dependency->takesInRanged())
                                {
                                    // Get the independent characteristic range values for this challenge characteristic
                                    $independentRangedValues[$independentCharacteristic->id] = $challengeCharacteristics[$independentCharacteristic->characteristic_id]->ranged_value;
                                }
                                else if ($dependency->takesInGenotypes())
                                {
                                    // Get the independent characteristics required genotypes
                                    $independentCharacteristicLoci = $independentCharacteristic->loci()->whereActive()->get();

                                    foreach($independentCharacteristicLoci as $locus)
                                    {
                                        if (array_key_exists($locus->id, $chosenGenotypes))
                                        {
                                            $independentGenotypes[$locus->id] = $chosenGenotypes[$locus->id];
                                        }
                                    }
                                }
                            }

                            // Check for violations
                            if ($dependency->outputsRanged())
                            {
                                // Get this characteristics dependent value for this dog
                                $rangedValue = $challengeCharacteristic->ranged_value;

                                $newRangedValue = $rangedValue;

                                // Do the dependencies
                                if ($dependency->isR2R())
                                {
                                    $newRangedValue = $dependency->doR2R($rangedValue, $independentRangedValues);
                                }
                                else if ($dependency->isG2R())
                                {
                                    $newRangedValue = $dependency->doG2R($rangedValue, $independentGenotypes);
                                }

                                // Only need to update and bind if it changed
                                if (Floats::compare($rangedValue, $newRangedValue, '!='))
                                {
                                    // Save it back on the characteristic
                                    $challengeCharacteristic->ranged_value = $newRangedValue;
                                    $challengeCharacteristic->save();
                                }
                            }
                            else if ($dependency->outputsGenotypes())
                            {
                                // We no longer support X2G dependencies, but this check is here for legacy value
                            }
                        }
                    }
                }
            } // End dependency checks
        }
    }

    public function rerollCharacteristics()
    {
        // Delete all of the current characteristics
        $this->characteristics()->delete();

        // Roll the characteristics
        $this->rollCharacteristics();

        return $this;
    }

}
