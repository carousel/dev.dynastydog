<?php

class CommunityChallenge extends Eloquent {

    protected $guarded = array('id');

    protected $dates = ['start_date', 'end_date'];

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    |
    |
    */

    public function getHealthyAttribute($healthy)
    {
        return (bool) $healthy;
    }

    public function getJudgedAttribute($judged)
    {
        return (bool) $judged;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    |
    */

    public function scopeWhereUnjudged($query)
    {
        return $query->where('judged', false);
    }

    public function scopeWhereJudged($query)
    {
        return $query->where('judged', true);
    }

    public function scopeWhereOpen($query)
    {
        return $query->whereStarted()->whereUnended()->whereUnjudged();
    }

    public function scopeWhereUnstarted($query)
    {
        return $query->where('start_date', '>', Carbon::now()->toDateString());
    }

    public function scopeWhereStarted($query)
    {
        return $query->where('start_date', '<=', Carbon::now()->toDateString());
    }

    public function scopeWhereUnended($query)
    {
        return $query->where('end_date', '>=', Carbon::now()->toDateString());
    }

    public function scopeWhereEnded($query)
    {
        return $query->where('end_date', '<', Carbon::now()->toDateString());
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
     * @return Collection of CommunityChallengeCharacteristics
     */
    public function characteristics()
    {
        return $this->hasMany('CommunityChallengeCharacteristic', 'community_challenge_id', 'id');
    }

    /**
     * All entries
     *
     * @return Collection of CommunityChallengeEntries
     */
    public function entries()
    {
        return $this->hasMany('CommunityChallengeEntry', 'community_challenge_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Many To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * users who have no claimed their prize for this callenge
     *
     * @return Collection of Users
     */
    public function unclaimedPrizeWinners()
    {
        return $this->belongsToMany('User', 'community_challenge_prize_winners', 'community_challenge_id', 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function isOpen()
    {
        return ($this->hasStarted() and ! $this->hasEnded());
    }

    public function hasEnded()
    {
        return $this->end_date->isPast();
    }

    public function hasStarted()
    {
        return $this->start_date->isPast();
    }

    public function isJudged()
    {
        return $this->judged;
    }

    public function isUnjudged()
    {
        return ( ! $this->judged);
    }

    public function isHealthy()
    {
        return $this->healthy;
    }

    public function onlyAllowHealthyDogs()
    {
        return $this->isHealthy();
    }

    public function dogHasBeenEntered($dog)
    {
        return ($this->entries()->where('dog_id', $dog->id)->count() > 0);
    }

    public function checkDog(Dog $dog)
    {
        // Do health check
        if ($this->isHealthy() and $dog->isUnhealthy())
        {
            return false;
        }

        // Check against the community challenge's characteristics
        $communityChallengeCharacteristics = $this->characteristics()->with('characteristic')->get();

        // None to compare against
        if ($communityChallengeCharacteristics->isEmpty())
        {
            return true;
        }

        // Get the characteristic ids
        $characteristicIds = $communityChallengeCharacteristics->lists('characteristic_id');

        // Get the appropriate dog's characteristics
        $dogCharacteristics = $dog->characteristics()
            ->whereVisible()
            ->whereIn('characteristic_id', $characteristicIds)
            ->get();

        // Make sure all were grabbed
        if ($dogCharacteristics->count() != $communityChallengeCharacteristics->count())
        {
            return false;
        }

        foreach($communityChallengeCharacteristics as $communityChallengeCharacteristic)
        {
            $characteristicId = $communityChallengeCharacteristic->characteristic_id;

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

            // Check the dog's characteristic against the community challenge's characteristic
            if ( ! $communityChallengeCharacteristic->checkDogCharacteristic($dogCharacteristic))
            {
                // If any characteristic check fails, the entire community challenge check fails
                return false;
            }
        }

        // Passed all characteristic checks
        return true;
    }

    public function judge()
    {
        DB::transaction(function()
        {
            // Keep track of who has the most breeders
            $sortedEntriesByDogId  = [];
            $dogBreederTotals      = [];
            $dogBreederIds         = [];

            // Grab all of the entries
            $entries = $this->entries()->with('dog')->get();

            foreach($entries as $entry)
            {
                // Store all breeders in this dog's lineage
                $breederIds = [];

                // Grab the dog
                $dog = $entry->dog;

                // Sort the entry by dog ID
                $sortedEntriesByDogId[$dog->id] = $entry;

                // If the dog has a breeder, keep track of it
                if ( ! is_null($dog->breeder_id))
                {
                    $breederIds[] = $dog->breeder_id;
                }

                // Grab the dog's pedigree
                $pedigree = $dog->pedigree;

                // Keep track of the ancestor IDs
                $ancestorIds = [];

                // Get both sides of the pedigree's ancestor IDs
                foreach($pedigree->dam as $ancestor => $data)
                {
                    $ancestorIds[] = $data[Pedigree::ANCESTOR];
                }

                foreach($pedigree->sire as $ancestor => $data)
                {
                    $ancestorIds[] = $data[Pedigree::ANCESTOR];
                }

                // Grab the ancestor's breeder IDs
                if ( ! empty($ancestorIds))
                {
                    $ancestorBreederIds = Dog::whereIn('id', $ancestorIds)->whereNotNull('breeder_id')->lists('breeder_id');

                    // Merge these breeder IDs with the dog's current breeder if it existed
                    $breederIds = array_merge($breederIds, $ancestorBreederIds);
                }

                // Count the breeders after making them unique and filtering out empty values
                $breederIds = array_filter(array_unique($breederIds));

                // Log them
                $dogBreederIds[$dog->id]    = $breederIds;
                $dogBreederTotals[$dog->id] = count($breederIds);
            }

            // There had to have been some entries
            if ( ! empty($dogBreederTotals))
            {
                // Keep track of the winning dog and breeder IDs
                $winningDogIds     = [];
                $winningBreederIds = [];

                // Find the max
                $highestNumberOfUniqueBreeders = max($dogBreederTotals);

                // Find all dogs that match
                foreach($dogBreederTotals as $dogId => $total)
                {
                    if ($total == $highestNumberOfUniqueBreeders)
                    {
                        $winningDogIds[] = $dogId;
                    }
                }

                // Go through the sorted entries and update them
                foreach($sortedEntriesByDogId as $dogId => $entry)
                {
                    if (in_array($dogId, $winningDogIds))
                    {
                        // Mark the entries as winners
                        $entry->winner = true;

                        // Merge the breeders to send out notifications later
                        $winningBreederIds = array_merge($winningBreederIds, $dogBreederIds[$dogId]);
                    }

                    // Update the entry
                    $entry->num_breeders = $dogBreederTotals[$dogId];
                    $entry->save();
                }

                // Make the breeder IDs unique and filter out empty values
                $winningBreederIds = array_filter(array_unique($winningBreederIds));

                // Update the community challenge
                $this->judged  = true;
                $this->winners = count($winningBreederIds);
                $this->save();

                // Gather the winning dogs' nameplates
                $verboseWinners = [];

                foreach($winningDogIds as $winningDogId)
                {
                    $winningEntry = $sortedEntriesByDogId[$winningDogId];
                    $winningDog   = $winningEntry->dog;

                    // Store the nameplates of the dogs in an array
                    $verboseWinners[] = $winningDog->nameplate();
                }

                // Will only have one nameplate if there wasn't a tie
                $winningDogNameplates = implode(', ', $verboseWinners);

                // Check for ties
                $totalWinningDogs = count($winningDogIds);

                if ($totalWinningDogs == 1) // No tie
                {
                    $params = array(
                        'highest_number_of_unique_breeders' => $highestNumberOfUniqueBreeders, 
                        'prize_route' => route('goals/community/prizes'), 
                        'winning_dog' => $winningDogNameplates, 
                    );

                    $winnerBody = Lang::get('notifications/user.won_community_challenge.single', array_map('htmlentities', array_dot($params)));
                    $loserBody  = Lang::get('notifications/user.lost_community_challenge.single', array_map('htmlentities', array_dot($params)));
                }
                else // There was a tie
                {
                    $params = array(
                        'highest_number_of_unique_breeders' => $highestNumberOfUniqueBreeders, 
                        'prize_route'  => route('goals/community/prizes'), 
                        'winning_dogs' => $winningDogNameplates, 
                    );

                    $winnerBody = Lang::get('notifications/user.won_community_challenge.tie', array_map('htmlentities', array_dot($params)));
                    $loserBody  = Lang::get('notifications/user.lost_community_challenge.tie', array_map('htmlentities', array_dot($params)));
                }

                // Notify the winning breeders
                User::notifyOnly($winningBreederIds, $winnerBody, UserNotification::TYPE_SUCCESS, true, true, false);

                // Set the claimable prizes for the winners
                $this->unclaimedPrizeWinners()->attach($winningBreederIds);

                // Notify the losers
                User::notifyAll($loserBody, UserNotification::TYPE_DANGER, true, true, false, $winningBreederIds);
            }
        });
    }

    public function rollCharacteristics()
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
        if ($this->num_characteristics > $characteristics->count())
        {
            throw new Dynasty\CommunityChallenges\Exceptions\NotEnoughCharacteristicsToGenerateException;
        }

        // Randomly choose the characteristics to use
        $chosenCharacteristics = ($this->num_characteristics == 1)
            ? array($characteristics->random()) // random() won't return an array of items if the amount is 1
            : $characteristics->random($this->num_characteristics);

        $chosenCharacteristicIds = [];

        // Store the challenge characteristics for dependencies
        $challengeCharacteristics = [];

        foreach($chosenCharacteristics as $characteristic)
        {
            // Store for later
            $chosenCharacteristicIds[] = $characteristic->id;

            // Add the characteristic to the challenge
            $challengeCharacteristic = CommunityChallengeCharacteristic::create(array(
                'community_challenge_id' => $this->id, 
                'characteristic_id'      => $characteristic->id, 
                'ranged_value'           => $characteristic->getRandomRangedValue(), // null if not ranged
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

    public function rerollCharacteristics()
    {
        // Delete all of the current characteristics
        $this->characteristics()->delete();

        // Roll the characteristics
        $this->rollCharacteristics();

        return $this;
    }

}
