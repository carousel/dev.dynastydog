<?php

class Litter extends Eloquent {

    protected $guarded = array('id');

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    |
    |
    */

    public function getBornAttribute($born)
    {
        return (bool) $born;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    |
    */

    public function scopeBorn($query)
    {
        return $query->where('born', true);
    }
    
    public function scopeWhereBorn($query)
    {
        return $this->scopeBorn($query);
    }
    
    public function scopeUnborn($query)
    {
        return $query->where('born', false);
    }
    
    public function scopeWhereUnborn($query)
    {
        return $this->scopeUnborn($query);
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the breeder.
     *
     * @return User
     */
    public function breeder()
    {
        return $this->belongsTo('User', 'breeder_id');
    }

    /**
     * Return the dam.
     *
     * @return Dog
     */
    public function dam()
    {
        return $this->belongsTo('Dog', 'dam_id');
    }

    /**
     * Return the sire.
     *
     * @return Dog
     */
    public function sire()
    {
        return $this->belongsTo('Dog', 'sire_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Has Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All dogs from this litter
     *
     * @return Collection of Dogs
     */
    public function dogs()
    {
        return $this->hasMany('Dog', 'litter_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public static function calculateBredRanges($damValue, $damFertilityDropOffValue, $sireValue, $sireFertilityDropOffValue, $lb, $ub)
    {
        // null overrides all
        if ((is_null($damValue) and is_null($sireValue)) or is_null($ub) or is_null($lb))
        {
            return [ null, null ];
        }

        // If one is null, use the other parent's value
        if (is_null($damValue))
        {
            $sireValue = $damValue;
        }
        else if (is_null($sireValue))
        {
            $damValue = $sireValue;
        }

        $config = Config::get('game');

        $formula = (mt_rand(1, 100) <= $config['characteristics']['ranged_value_percent_of_time_close_to_midpoint']) // X% of the time
            ? $config['formulas']['ranged_characteristic_wiggle_room_close_to_midpoint']
            : $config['formulas']['ranged_characteristic_wiggle_room_far_from_midpoint'];

        $expression = strtr($formula, array(
            ':dam_fdo'  => $damFertilityDropOffValue, 
            ':sire_fdo' => $sireFertilityDropOffValue, 
            ':diff'     => abs($damValue - $sireValue), 
        ));

        $wiggleRoom = eval('return '.$expression.';');

        $average = ($damValue + $sireValue) / 2.00;

        $min = $average - $wiggleRoom;
        $max = $average + $wiggleRoom;

        $min = max($lb, min($ub, $min));
        $max = max($lb, min($ub, $max));

        return [ $min, $max ];
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function isBorn()
    {
        return $this->born;
    }

    public function isUnborn()
    {
        return ( ! $this->born);
    }

    /**
     * @throws Dynasty\Dogs\Exceptions\NotSavedException
     * @throws Dynasty\Pedigrees\Exceptions\NotSavedException
     * @throws Exception
     */
    public function birth()
    {
        // Grab the dam and sire
        $dam  = $this->dam;
        $sire = $this->sire;

        // Only create the puppies if both parents are still in the db and the litter passes its chance check
        $chance = mt_rand(1, 100);

        if (is_null($dam) or is_null($sire) or $chance > $this->litter_chance)
        {
            // Delete the litter
            $this->delete();

            return 0;
        }

        // Get the dam's fertility drop off
        $damFertilityDropOff  = $dam->getFertilityDropOff();

        $damFertilityDropOffValue = is_null($damFertilityDropOff)
            ? 0
            : $damFertilityDropOff->current_ranged_value;

        // Get the sire's fertility drop off
        $sireFertilityDropOff = $sire->getFertilityDropOff();

        $sireFertilityDropOffValue = is_null($sireFertilityDropOff)
            ? 0
            : $sireFertilityDropOff->current_ranged_value;


        // @TUTORIAL: check if the breeder is on the first-breeding stage
        if ($this->breeder->isOnTutorialStage('first-breeding'))
        {
            $litterSize = 1;
        }
        else
        {
            $damLitterSize = $dam->getLitterSize();

            $damLitterSizeValue = is_null($damLitterSize)
                ? 0
                : $damLitterSize->current_ranged_value;

            // Add randomness
            $litterSizeVariation = Config::get('game.dog.litter_size_variation');

            $ub = $damLitterSizeValue + $litterSizeVariation;
            $lb = $damLitterSizeValue - $litterSizeVariation;

            $litterSize = mt_rand($lb, $ub);

            $effectOnLitterSize = (($damFertilityDropOffValue + $sireFertilityDropOffValue) / 2.00) / 100.00;

            $litterSize = max(1, round($litterSize * (1 - $effectOnLitterSize))); // Always 1 or more puppies if the chance passes
        }

        // Same breed means the puppies will also be the same breed
        $breed = ($dam->breed_id == $sire->breed_id)
            ? $dam->breed
            : null; // Unregistered

        // Get the kennel group
        $kennelGroupId = null;

        if ( ! is_null($dam->kennelGroup)) // Find the dam's kennel group
        {
            $kennelGroupId = $dam->kennelGroup->id;
        } 
        else if ( ! is_null($dam->owner)) // Find the dam owner's first kennel group
        {
            $kennelGroup = $dam->owner->kennelGroups()->whereNotCemetery()->first();

            // Shouldn't ever be null, but we should check for it regardless
            if ( ! is_null($kennelGroup))
            {
                $kennelGroupId = $kennelGroup->id;
            }
        }

        // Get all sexes
        $sexes = Sex::all();

        // Get the breed ID
        $breedId = is_null($breed) ? null : $breed->id;

        // Start to create the puppies
        for ($i = 1; $i <= $litterSize; ++$i)
        {
            // Start the puppy
            $puppy = new Dog;

            $puppy->owner_id   = $dam->owner_id; // Puppies always go with the dam
            $puppy->breeder_id = $this->breeder_id;
            $puppy->litter_id  = $this->id;
            $puppy->breed_id   = $breedId;

            // Grab a random sex to use
            $sex = $sexes->random();
            $puppy->sex_id = $sex->id;

            // Get the minimum puppy age
            $puppy->age = Config::get('game.dog.min_puppy_age');

            // Name may be too long ...
            $name = 'Puppy '.$i;

            // ... so we need to shorten it
            if (strlen($name) > 32)
            {
                $name = Str::limit($name, 32);
            }

            $puppy->name = $name;

            // All puppies are incomplete
            $puppy->completed_at = null;

            // All puppies are alive until completed
            $puppy->deceased_at = null;

            // Give the puppy the kennel group id
            $puppy->kennel_group_id = $kennelGroupId;

            // Set the image display settings
            $puppy->display_image = Dog::DISP_IMAGE_DEFAULT;

            // Save the puppy
            $puppy->save();

            if ( ! $puppy)
            {
                throw new Dynasty\Dogs\Exceptions\NotSavedException;
            }
        }

        // Mark the litter as born
        $this->born = true;
        $this->save();

        return $litterSize;
    }

}
