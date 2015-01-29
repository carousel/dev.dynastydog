<?php

class Contest extends Eloquent {

    protected $guarded = array('id');

    protected $dates = ['run_on'];

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    |
    |
    */

    public function getHasRunAttribute($hasRun)
    {
        return (bool) $hasRun;
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the creator.
     *
     * @return User
     */
    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | One To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All prerequisites
     *
     * @return Collection of ContestPrerequisites
     */
    public function prerequisites()
    {
        return $this->hasMany('ContestPrerequisite', 'contest_id', 'id');
    }

    /**
     * All requirements
     *
     * @return Collection of ContestRequirements
     */
    public function requirements()
    {
        return $this->hasMany('ContestRequirement', 'contest_id', 'id');
    }

    /**
     * All contest entries
     *
     * @return Collection of ContestEntries
     */
    public function entries()
    {
        return $this->hasMany('ContestEntry', 'contest_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public static function minRunDate()
    {
        return Carbon::today();
    }

    public static function maxRunDate()
    {
        return Carbon::today()->addDays(Config::get('game.contest.max_days_in_advance'));
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function dogHasBeenEntered($dog)
    {
        return ($this->entries()->where('dog_id', $dog->id)->count() > 0);
    }

    public function dogHasUnlockedRequirements($dog)
    {
        $requirementIds = $this->requirements()->lists('characteristic_id');
        $totalKnown = $dog->characteristics()->whereKnown()->whereIn('characteristic_id', $requirementIds)->count();

        return ($totalKnown == count($requirementIds));
    }

    public function dogMeetsPrerequisites($dog)
    {
        // Get all of the prerequisites
        $prerequisites = $this->prerequisites()->with(array('characteristic', 'genotypes', 'phenotypes'))->get();

        // The contest has prerequisites to check against
        if (count($prerequisites) > 0)
        {
            // Grab all known characteristics from the dog
            $dogCharacteristics = $dog->characteristics()->whereKnown()->whereVisible()->get();
            $collectedDogCharacteristics = array_collect($dogCharacteristics, 'characteristic_id');

            foreach($prerequisites as $prerequisite)
            {
                // Check if the dog has this characteristic AND it's known AND it's not hidden
                if ( ! isset($collectedDogCharacteristics[$prerequisite->characteristic_id]))
                {
                    return false;
                }

                $dogCharacteristic = $collectedDogCharacteristics[$prerequisite->characteristic_id][0];

                if ( ! $prerequisite->checkDogCharacteristic($dogCharacteristic))
                {
                    return false;
                }
            }
        }

        return true;
    }

    public function hasRun()
    {
        return $this->has_run and ! $this->run_on->isPast();
    }

    public function isSmall()
    {
        return ($this->total_entries >= Config::get('game.contest.size_min_small') and $this->total_entries < Config::get('game.contest.size_min_medium'));
    }

    public function isMedium()
    {
        return ($this->total_entries >= Config::get('game.contest.size_min_medium') and $this->total_entries < Config::get('game.contest.size_min_large'));
    }

    public function isLarge()
    {
        return ($this->total_entries >= Config::get('game.contest.size_min_large'));
    }

    public function run()
    {
        DB::transaction(function()
        {
            // Grab all of the requirements
            $requirements = $this->requirements()->with('characteristic')->get();
            $requirementCharacteristicIds = $requirements->lists('characteristic_id');

            if ($this->total_entries > 0 and count($requirementCharacteristicIds) > 0)
            {
                // Get adjustments
                $randomScoreAdjustment      = Config::get('game.contest.random_score_adjustment');
                $scoreAdjustmentForSymptoms = Config::get('game.contest.symptoms_score_adjustment');

                // Mark the contest as has run
                $this->has_run = true;
                $this->save();

                // Keep track of all of the scores and entries
                $scores = [];
                $sortedEntries = [];

                // Grab all of the entries
                $entries = $this->entries()->with('dog.owner')->get();

                foreach($entries as $entry)
                {
                    // All scores start out at 0
                    $score = 0;

                    // Save the total expressed symptoms
                    $totalExpressedSymptoms = 0;

                    // Store for use later
                    $sortedEntries[$entry->id] = $entry;

                    // Grab the dog
                    $dog = $entry->dog;

                    // Get the dog's characteristics from the requirements
                    $dogCharacteristics = $dog->characteristics()->whereIn('characteristic_id', $requirementCharacteristicIds)->get();
                    $collectedDogCharacteristics = array_collect($dogCharacteristics, 'characteristic_id');

                    // Go through all of the requirements
                    foreach($requirements as $requirement)
                    {
                        // Get the dog's characteristic equivalent
                        if (array_key_exists($requirement->characteristic_id, $collectedDogCharacteristics))
                        {
                            $dogCharacteristic = $collectedDogCharacteristics[$requirement->characteristic_id][0];

                            $score += $requirement->scoreDogCharacteristic($dogCharacteristic);

                            // See if there are any expressed symptoms
                            $totalExpressedSymptoms += $dogCharacteristic->symptoms()->whereExpressed()->count();
                        }
                    }

                    // Add random factor
                    $randomFactor = mt_rand(0, $randomScoreAdjustment);
                    $score += (mt_rand(0, 1) == 1) ? $randomFactor : (0 - $randomFactor);

                    // Add in symptoms factors for all expressed symptoms of this dog
                    $score += ($totalExpressedSymptoms * $scoreAdjustmentForSymptoms);

                    // Save the score for later
                    $scores[$entry->id] = $score;
                }

                // Sort the scores in ascending order and maintain the keys
                asort($scores);

                // Go through all of the scores and update the entries
                $rank = 1;

                // Rank the entries by their scores
                foreach($scores as $entry_id => $score)
                {
                    $entry = $sortedEntries[$entry_id];

                    // Mark their score and rank in the entry
                    $entry->score = $score;
                    $entry->rank  = $rank;
                    $entry->save();

                    if ($rank == 1) // This is our winner
                    {
                        $winningDog = $entry->dog;

                        if ( ! is_null($winningDog))
                        {
                            // Give them their win increment
                            if ($this->isSmall())
                            {
                                $winningDog->small_contest_wins += 1;
                            }
                            else if ($this->isMedium())
                            {
                                $winningDog->medium_contest_wins += 1;
                            }
                            else if ($this->isLarge())
                            {
                                $winningDog->large_contest_wins += 1;
                            }

                            $winningDog->save();

                            // Notify the winning dog's owner
                            $winningOwner = $winningDog->owner;

                            if ( ! is_null($winningOwner))
                            {
                                $params = array(
                                    'contest'     => $this->toArray(), 
                                    'winning_dog' => $winningDog->nameplate(), 
                                    'winning_dog_route' => URL::route('dog/profile', $winningDog->id), 
                                );

                                $body = Lang::get('notifications/dog.won_contest.to_owner', array_map('htmlentities', array_dot($params)));

                                $winningOwner->notify($body, UserNotification::TYPE_SUCCESS);
                            }
                        }
                    }

                    // Increment the rank
                    $rank += 1;
                }
            }
            else // No entries or no requirements
            {
                // Delete the contest
                $this->delete();
            }
        });
    }

}
