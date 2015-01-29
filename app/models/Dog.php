<?php

class Dog extends Eloquent {

    const STUDDING_NONE      = 0;
    const STUDDING_REQUEST   = 1;
    const STUDDING_IMMEDIATE = 2;

    const DISP_IMAGE_NONE    = 0;
    const DISP_IMAGE_DEFAULT = 1;
    const DISP_IMAGE_CUSTOM  = 2;

    protected $guarded = array('id');

    protected $dates = ['completed_at', 'deceased_at'];

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    |
    |
    */

    public function getWorkedAttribute($worked)
    {
        return (bool) $worked;
    }

    public function getHeatAttribute($heat)
    {
        return (bool) $heat;
    }

    public function getBreedChangedAttribute($breedChanged)
    {
        return (bool) $breedChanged;
    }

    public function getActiveBreedMemberAttribute($activeBreedMember)
    {
        return (bool) $activeBreedMember;
    }

    public function getSexuallyMatureAttribute($sexuallyMature)
    {
        return (bool) $sexuallyMature;
    }

    public function getSexualDeclineAttribute($sexualDecline)
    {
        return (bool) $sexualDecline;
    }

    public function getInfertileAttribute($infertile)
    {
        return (bool) $infertile;
    }

    public function getCustomImportAttribute($customImport)
    {
        return (bool) $customImport;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    |
    */

    public function scopeWhereAlive($query)
    {
        return $query->whereNull('dogs.deceased_at');
    }

    public function scopeWhereActiveBreedMember($query)
    {
        return $query->where('dogs.active_breed_member', true);
    }

    public function scopeWhereComplete($query)
    {
        return $query->whereNotNull('dogs.completed_at');
    }

    public function scopeWhereInHeat($query)
    {
        return $query->where('dogs.heat', true);
    }

    public function scopeWhereWorked($query)
    {
        return $query->where('dogs.worked', true);
    }

    public function scopeWhereUnworked($query)
    {
        return $query->where('dogs.worked', false);
    }

    public function scopeWhereOwnedBy($query, $user)
    {
        $userId = is_null($user)
            ? null
            : $user->id;

        return $query->where('dogs.owner_id', $userId);
    }

    public function scopeWhereAdult($query)
    {
        $maxPuppyAge = Config::get('game.dog.max_puppy_age');
        return $query->where('dogs.age', '>', $maxPuppyAge);
    }

    public function scopeWherePuppy($query)
    {
        $maxPuppyAge = Config::get('game.dog.max_puppy_age');
        return $query->where('dogs.age', '<=', $maxPuppyAge);
    }

    public function scopeWhereFemale($query)
    {
        return $query->whereHas('sex', function($query)
            {
                $query->whereFemale();
            });
    }

    public function scopeWhereMale($query)
    {
        return $query->whereHas('sex', function($query)
            {
                $query->whereMale();
            });
    }

    public function scopeOrderByKennelGroup($query, $kennelGroup)
    {
        switch ($kennelGroup->dog_order_id)
        {
            case KennelGroup::DOG_ORD_AGE:
                return $query->orderBy('dogs.age', 'asc');
            
            case KennelGroup::DOG_ORD_BREED:
                return $query->select('dogs.*')->leftJoin('breeds', 'breeds.id', '=', 'dogs.breed_id')->orderBy('breeds.name', 'asc');

            case KennelGroup::DOG_ORD_ID:
                return $query->orderBy('dogs.id', 'asc');

            case KennelGroup::DOG_ORD_NAME:
                return $query->orderBy('dogs.name', 'asc');

            default:
                return $query;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the dog's owner.
     *
     * @return User
     */
    public function owner()
    {
        return $this->belongsTo('User', 'owner_id');
    }

    /**
     * Return the dog's breeder.
     *
     * @return User
     */
    public function breeder()
    {
        return $this->belongsTo('User', 'breeder_id');
    }

    /**
     * Return the dog's kennel group.
     *
     * @return User
     */
    public function kennelGroup()
    {
        return $this->belongsTo('KennelGroup', 'kennel_group_id');
    }

    /**
     * Return the dog's litter.
     *
     * @return Litter
     */
    public function litter()
    {
        return $this->belongsTo('Litter', 'litter_id');
    }

    /**
     * Return the dog's breed.
     *
     * @return Breed
     */
    public function breed()
    {
        return $this->belongsTo('Breed', 'breed_id');
    }

    /**
     * Return the dog's sex.
     *
     * @return Sex
     */
    public function sex()
    {
        return $this->belongsTo('Sex', 'sex_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Has One Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the dog's pedigree.
     *
     * @return Pedigree
     */
    public function pedigree()
    {
        return $this->hasOne('Pedigree', 'dog_id', 'id');
    }

    /**
     * Return the dog's lend request.
     *
     * @return LendRequest
     */
    public function lendRequest()
    {
        return $this->hasOne('LendRequest', 'dog_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Has Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All characteristics
     *
     * @return Collection of DogCharacteristics
     */
    public function characteristics()
    {
        return $this->hasMany('DogCharacteristic', 'dog_id', 'id');
    }

    /**
     * All diseases
     *
     * @return Collection of DogCharacteristics
     */
    public function diseases()
    {
        return $this->characteristics()->whereType(Characteristic::TYPE_IMMUNE_SYSTEM_DISEASE);
    }

    /**
     * All breeds this dog is an originator of 
     *
     * @return Collection of Breeds
     */
    public function originatedBreeds()
    {
        return $this->hasMany('Breed', 'originator_id', 'id');
    }

    /**
     * All breed drafts this dog has been assigned to
     *
     * @return Collection of BreedDrafts
     */
    public function breedDrafts()
    {
        return $this->hasMany('BreedDraft', 'dog_id', 'id');
    }

    /**
     * All challenges this dog has completed for a user
     *
     * @return Collection of Challenges
     */
    public function completedChallenges()
    {
        return $this->hasMany('Challenge', 'dog_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Has Many Through Relationships
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
        return $this->hasManyThrough('DogCharacteristicSymptom', 'DogCharacteristic', 'dog_id', 'dog_characteristic_id');
    }

    /**
     * All litters
     *
     * @return Collection of Litters
     */
    public function litters()
    {
        return $this->isFemale()
            ? $this->hasMany('Litter', 'dam_id', 'id')
            : $this->hasMany('Litter', 'sire_id', 'id');
    }

    /**
     * All contest entries
     *
     * @return Collection of ContestEntries
     */
    public function contestEntries()
    {
        return $this->hasMany('ContestEntry', 'dog_id', 'id');
    }

    /**
     * All community challenge entries
     *
     * @return Collection of CommunityChallengeEntries
     */
    public function communityChallengeEntries()
    {
        return $this->hasMany('CommunityChallengeEntry', 'dog_id', 'id');
    }

    /**
     * All offspring
     *
     * @return Collection of Dogs
     */
    public function offspring()
    {
        return Dog::select('dogs.*')
            ->join('litters', 'litters.id', '=', 'dogs.litter_id')
            ->where(function($query) 
            {
                $query->where('litters.dam_id', $this->id)->orWhere('litters.sire_id', $this->id);
            });
    }

    /**
     * Return the dog's blr.
     *
     * @return Collection of BeginnersLuckRequests
     */
    public function beginnersLuckRequests()
    {
        return $this->isFemale()
            ? $this->hasMany('BeginnersLuckRequest', 'bitch_id', 'id')
            : $this->hasMany('BeginnersLuckRequest', 'dog_id', 'id');
    }

    /**
     * Return the dog's stud requests.
     *
     * @return Collection of StudRequests
     */
    public function studRequests()
    {
        return $this->isFemale()
            ? $this->hasMany('StudRequest', 'bitch_id', 'id')
            : $this->hasMany('StudRequest', 'stud_id', 'id');
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
        return $this->belongsToMany('Genotype', 'dog_genotypes', 'dog_id', 'genotype_id');
    }

    /**
     * All phenotypes
     *
     * @return Collection of Phenotypes
     */
    public function phenotypes()
    {
        return $this->belongsToMany('Phenotype', 'dog_phenotypes', 'dog_id', 'phenotype_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public static function formatAgeInYearsAndMonths($ageInMonths, $abbreviate = false)
    {
        $years  = floor($ageInMonths / 12.00);
        $months = $ageInMonths % 12;

        return ($abbreviate)
            ? number_format($years).' '.Str::plural('Yr', $years).' '.number_format($months).' '.Str::plural('Mo', $months)
            : number_format($years).' '.Str::plural('Year', $years).' '.number_format($months).' '.Str::plural('Month', $months);
    }

    public static function studdingOptions()
    {
        return array(
            Dog::STUDDING_NONE      => 'None', 
            Dog::STUDDING_IMMEDIATE => 'Immediate', 
            Dog::STUDDING_REQUEST   => 'Request', 
        );
    }

    public static function displayImageOptions()
    {
        return array(
            Dog::DISP_IMAGE_NONE    => 'None', 
            Dog::DISP_IMAGE_DEFAULT => 'Default', 
            Dog::DISP_IMAGE_CUSTOM  => 'Custom', 
        );
    }

    public static function lendingOptions()
    {
        return array(
            'permanent'      => 'Permanently', 
            'one_turn'       => 'Lend for 1 Turn', 
            'five_turns'     => 'Lend for 5 Turns', 
            'fifteen_turns'  => 'Lend for 15 Turns', 
            'tonight'        => 'Lend until '.Carbon::now()->format('F j, Y').', 11:59pm Server Time', 
            'tomorrow_night' => 'Lend until '.Carbon::now()->addDay()->format('F j, Y').', 11:59pm Server Time', 
            'three_nights_from_now' => 'Lend until '.Carbon::now()->addDays(3)->format('F j, Y').', 11:59pm Server Time', 
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function hasSex()
    {
        return ( ! is_null($this->sex));
    }

    public function isFemale()
    {
        return $this->hasSex()
            ? $this->sex->isFemale()
            : false;
    }

    public function isMale()
    {
        return $this->hasSex()
            ? $this->sex->isMale()
            : false;
    }

    public function ownedByUser($user)
    {
        return ($this->owner_id == $user->id);
    }

    public function bredByUser($user)
    {
        return ($this->breeder_id == $user->id);
    }

    public function fullName()
    {
        return $this->hasKennelPrefix()
            ? $this->kennel_prefix.' '.$this->name
            : $this->name;
    }

    public function nameplate()
    {
        return $this->fullName().' (#'.$this->id.')';
    }

    public function linkedNameplate()
    {
        return HTML::link(URL::route('dog/profile', $this->id), e($this->nameplate()));
    }

    public function hasKennelPrefix()
    {
        return (strlen($this->kennel_prefix) > 0);
    }

    public function isWorked()
    {
        return $this->worked;
    }

    public function isAlive()
    {
        return is_null($this->deceased_at);
    }

    public function isDeceased()
    {
        return ( ! is_null($this->deceased_at));
    }

    public function isForStud()
    {
        return ($this->isForImmediateStud() or $this->isForRequestStud());
    }

    public function isForImmediateStud()
    {
        return ($this->studding == Dog::STUDDING_IMMEDIATE);
    }

    public function isForRequestStud()
    {
        return ($this->studding == Dog::STUDDING_REQUEST);
    }

    public function isInHeat()
    {
        return $this->heat;
    }

    public function isExpecting()
    {
        return ($this->litters()->unborn()->count() > 0);
    }

    public function isPregnant()
    {
        if ( ! $this->isFemale())
        {
            return false;
        }

        return $this->isExpecting();
    }

    public function displayNoImage()
    {
        return ($this->display_image == Dog::DISP_IMAGE_NONE);
    }

    public function displayDefaultImage()
    {
        return ($this->display_image == Dog::DISP_IMAGE_DEFAULT);
    }

    public function displayCustomImage()
    {
        return ($this->display_image == Dog::DISP_IMAGE_CUSTOM);
    }

    public function hasImage()
    {
        return ( ! is_null($this->getImageUrl()));
    }

    public function hasCustomImage()
    {
        return (strlen($this->image_url) > 0);
    }

    public function getImageUrl()
    {
        if ($this->displayNoImage())
        {
            return null;
        }

        if ($this->displayCustomImage() and $this->hasCustomImage())
        {
            return $this->image_url;
        }

        if ( ! $this->hasBreed())
        {
            return null;
        }

        return $this->breed->hasImage()
            ? asset($this->breed->getImageUrl())
            : null;
    }

    public function hasOwner()
    {
        return ( ! is_null($this->owner));
    }

    public function isPetHomed()
    {
        return is_null($this->owner);
    }

    public function hasBreeder()
    {
        return ( ! is_null($this->breeder));
    }

    public function isImported()
    {
        return is_null($this->breeder_id);
    }

    public function isCustomImported()
    {
        return $this->custom_import;
    }

    public function isBred()
    {
        return ( ! is_null($this->breeder_id));
    }

    public function getAgeInYearsAndMonths($abbreviate = false)
    {
        return Dog::formatAgeInYearsAndMonths($this->age, $abbreviate);
    }

    public function isRegistered()
    {
        return ( ! is_null($this->breed));
    }

    public function isActiveBreedMember()
    {
        // To be an active breed member it means that the dog must be worked (i.e. aged)
        return $this->active_breed_member;
    }

    public function hasBeginnersLuckRequests()
    {
        return ($this->beginnersLuckRequests()->count() > 0);
    }

    public function isPendingOwnership()
    {
        return ( ! is_null($this->lendRequest));
    }

    public function isLentOut()
    {
        return is_null($this->lendRequest)
            ?  false
            : ($this->lendRequest->receiver_id == $this->owner_id);
    }

    public function isPuppy()
    {
        return ($this->age <= Config::get('game.dog.max_puppy_age'));
    }

    public function isAdult()
    {
        return ($this->age > Config::get('game.dog.max_puppy_age'));
    }

    public function isComplete()
    {
        return ( ! is_null($this->completed_at));
    }

    public function isIncomplete()
    {
        return is_null($this->completed_at);
    }

    public function isPartiallyComplete()
    {
        return ($this->isIncomplete() and ($this->genotypes()->count() > 0 OR $this->phenotypes()->count() > 0 OR $this->characteristics()->count() > 0));
    }

    public function isPartiallyIncomplete()
    {
        return $this->isPartiallyComplete();
    }

    public function hasBreed()
    {
        return ( ! is_null($this->breed));
    }

    public function hasPedigree()
    {
        return ( ! is_null($this->pedigree));
    }

    public function hasLitter()
    {
        return ( ! is_null($this->litter));
    }

    public function hasLitters()
    {
        return ($this->litters()->count() > 0);
    }

    public function hasHadBreedChanged()
    {
        return $this->breed_changed;
    }

    public function getSexualMaturity()
    {
        return $this->characteristics()->whereType(Characteristic::TYPE_SEXUAL_MATURITY)->first();
    }

    public function ageToSexuallyMatureAt()
    {
        $sexualMaturity = $this->getSexualMaturity();

        if (is_null($sexualMaturity))
        {
            return 0;
        }

        return round($sexualMaturity->current_ranged_value);
    }

    public function checkSexualMaturity()
    {
        // If the dog is already sexually mature, it can't ever go back
        if ($this->isSexuallyMature())
        {
            return true;
        }

        $ageToMatureAt = $this->ageToSexuallyMatureAt();

        return ($this->age >= $ageToMatureAt);
    }

    public function getLitterSize()
    {
        return $this->characteristics()->whereType(Characteristic::TYPE_LITTER_SIZE)->first();
    }

    public function getFertilitySpan()
    {
        return $this->characteristics()->whereType(Characteristic::TYPE_FERTILITY_SPAN)->first();
    }

    public function getImmuneSystem()
    {
        return $this->characteristics()->whereType(Characteristic::TYPE_IMMUNE_SYSTEM)->first();
    }

    public function getLifeSpan()
    {
        return $this->characteristics()->whereType(Characteristic::TYPE_LIFE_SPAN)->first();
    }

    public function ageToBecomeInfertile()
    {
        // Find the dog's fertility span characteristic
        $fertilitySpan = $this->getFertilitySpan();

        $fertilitySpanCurrentRangedValue = is_null($fertilitySpan)
            ? 0
            : $fertilitySpan->current_ranged_value;

        // Find the dog's life span characteristic
        $lifeSpan = $this->getLifeSpan();

        $lifeSpanCurrentRangedValue = is_null($lifeSpan)
            ? 0
            : $lifeSpan->current_ranged_value;

        return round(($fertilitySpanCurrentRangedValue / 100.00) * $lifeSpanCurrentRangedValue);
    }

    public function checkInfertility()
    {
        // If the dog is already infertile, it can't ever go back
        if ($this->isInfertile())
        {
            return true;
        }

        $ageToBecomeInfertile = $this->ageToBecomeInfertile();

        return Floats::compare($this->age, $ageToBecomeInfertile, '>=');
    }

    public function checkFertility()
    {
        return ($this->checkSexualMaturity() and ! $this->checkInfertility());
    }

    public function isSexuallyMature()
    {
        return $this->sexually_mature;
    }

    public function isInSexualDecline()
    {
        return $this->sexual_decline;
    }

    public function isInfertile()
    {
        return $this->infertile;
    }

    public function isBreedable()
    {
        return ($this->isAlive() and $this->isComplete() and $this->isSexuallyMature() and ! $this->isInfertile());
    }

    public function canBeBredImmediately()
    {
        if ( ! $this->isBreedable())
        {
            return false;
        }

	//should check both for male and female
        if ($this->isMale())
        {
            return true;
        }

	return ($this->isInHeat() and ! $this->isExpecting() and ! $this->isWorked());
    }

    public function isUnhealthy()
    {
        return ($this->symptoms()->whereExpressed()->count() > 0);
    }

    public function isHealthy()
    {
        return ( ! $this->isUnhealthy());
    }

    public function getFertility()
    {
        return $this->characteristics()->whereType(Characteristic::TYPE_FERTILITY)->first();
    }

    public function getHeatCycle()
    {
        return $this->characteristics()->whereType(Characteristic::TYPE_HEAT_CYCLE)->first();
    }

    public function calculateLitterChance($mate)
    {
        $fertility     = $this->getFertility();
        $mateFertility = $mate->getFertility();

        $fertilityValue     = 0;
        $mateFertilityValue = 0;

        $fertilityValue = is_null($fertility)
            ? 0
            : $fertility->current_ranged_value;

        $mateFertilityValue = is_null($mateFertility)
            ? 0
            : $mateFertility->current_ranged_value;

        return round(($fertilityValue + $mateFertilityValue) / 2);
    }

    public function hasRequestedBeginnersLuckWith($dog)
    {
        if (is_null($dog))
        {
            return false;
        }

        return $this->isFemale()
            ? ($this->beginnersLuckRequests()->where('dog_id', $dog->id)->count() > 0)
            : ($this->beginnersLuckRequests()->where('bitch_id', $dog->id)->count() > 0);
    }

    public function hasRequestedBeginnersLuckWithDogOtherThan($dog)
    {
        if (is_null($dog))
        {
            return false;
        }

        return $this->isFemale()
            ? ($this->beginnersLuckRequests()->where('dog_id', '<>', $dog->id)->count() > 0)
            : ($this->beginnersLuckRequests()->where('bitch_id', '<>', $dog->id)->count() > 0);
    }

    public function hasRequestedBreedingWith($dog)
    {
        if (is_null($dog))
        {
            return false;
        }

        return $this->isFemale()
            ? ($this->studRequests()->where('stud_id', $dog->id)->whereWaiting()->count() > 0)
            : ($this->studRequests()->where('bitch_id', $dog->id)->whereWaiting()->count() > 0);
    }

    public function isBreedOriginator()
    {
        return ($this->originatedBreeds()->count() > 0);
    }

    public function isUndraftedBreedDraftOriginator()
    {
        return ($this->breedDrafts()->whereNotDraft()->count() > 0);
    }

    public function hasEnteredContests()
    {
        return ($this->contestEntries()->count() > 0);
    }

    public function hasCompleteAChallenge()
    {
        return ($this->completedChallenges()->count() > 0);
    }

    public function hasEnteredACommunityChallenge()
    {
        return ($this->communityChallengeEntries()->count() > 0);
    }

    public function canBeDeleted()
    {
        return (
            ! $this->hasLitters() and 
            ! $this->isBreedOriginator() and 
            ! $this->isUndraftedBreedDraftOriginator() and 
            ! $this->hasEnteredContests() and 
            ! $this->hasEnteredACommunityChallenge() and 
            ! $this->hasBeginnersLuckRequests()
        );
    }

    public function checkHeat()
    {
        if ($this->isMale() or ! $this->isBreedable())
        {
            return false;
        }

        $heatCycle = $this->getHeatCycle();

        if (is_null($heatCycle))
        {
            return false;
        }

        $ageToMatureAt = $this->ageToSexuallyMatureAt();
        $monthsBetweenHeats = round($heatCycle->current_ranged_value);

        // Get the remainder
        $remainder = ($this->age - $ageToMatureAt) % $monthsBetweenHeats;

        // Possible values
        $monthsToAge = Config::get('game.dog.months_to_age');
        $possibleValues = range(0, $monthsToAge - 1);

        // In heat
        return in_array($remainder, $possibleValues);
    }

    public function getFertilityDropOff()
    {
        return $this->characteristics()->whereType(Characteristic::TYPE_FERTILITY_DROP_OFF)->first();
    }

    public function ageToGoIntoSexualDecline()
    {
        // Find the dog's fertility drop off characteristic
        $fertilityDropOff = $this->getFertilityDropOff();

        if (is_null($fertilityDropOff))
        {
            return 0;
        }

        $ageToBecomeInfertile = $this->ageToBecomeInfertile();

        return round($ageToBecomeInfertile - (($fertilityDropOff->current_ranged_value / 100.00) * $ageToBecomeInfertile));
    }

    public function checkSexualDecline()
    {
        // If the dog is already in sexual decline, it can't ever go back
        if ($this->isInSexualDecline())
        {
            return true;
        }

        $ageToGoIntoSexualDecline = $this->ageToGoIntoSexualDecline();

        return Floats::compare($this->age, $ageToGoIntoSexualDecline, '>=');
    }

    public function addCharacteristic($characteristic)
    {
        if (is_null($characteristic))
        {
            return;
        }

        if ($this->characteristics->contains($characteristic->id))
        {
            return;
        }

        // Sort the dog's genotypes
        $genotypeIdsByLocusId = $this->genotypes()->lists('id', 'locus_id');

        // Do genetic components
        $ageToRevealGenotypes  = $characteristic->getRandomAgeToRevealGenotypes();
        $ageToRevealPhenotypes = $characteristic->getRandomAgeToRevealPhenotypes();

        // Do ranged components
        $finalRangedValue = $this->isFemale()
            ? $characteristic->getRandomRangedFemaleValue() 
            : $characteristic->getRandomRangedMaleValue();

        $ageToStopGrowing       = $characteristic->getRandomAgeToStopGrowing();
        $currentRangedValue     = DogCharacteristic::currentRangedValue($finalRangedValue, $ageToStopGrowing, $this->age);
        $ageToRevealRangedValue = $characteristic->getRandomAgeToRevealRangedValue();

        $filling = array(
            'dog_id'                     => $this->id, 
            'characteristic_id'          => $characteristic->id, 
            'hide'                       => $characteristic->hide, 

            // Do genetics
            'age_to_reveal_genotypes'    => $ageToRevealGenotypes, 
            'genotypes_revealed'         => ($characteristic->genotypesCanBeRevealed() and $this->age >= $ageToRevealGenotypes), 
            'age_to_reveal_phenotypes'   => $ageToRevealPhenotypes, 
            'phenotypes_revealed'        => ($characteristic->phenotypesCanBeRevealed() and $this->age >= $ageToRevealPhenotypes), 

            // Do ranged
            'final_ranged_value'         => $finalRangedValue, 
            'age_to_stop_growing'        => $ageToStopGrowing, 
            'current_ranged_value'       => $currentRangedValue, 
            'age_to_reveal_ranged_value' => $ageToRevealRangedValue, 
            'ranged_value_revealed'      => ($characteristic->rangedValueCanBeRevealed() and $this->age >= $ageToRevealRangedValue), 
        );

        $symptoms = [];

        // Grab a severity if eligible
        if ($characteristic->eligibleForSeverity($genotypeIdsByLocusId) and ! is_null($severity = $characteristic->getRandomSeverity(false, $this->age)))
        {
            $filling['characteristic_severity_id']   = (is_a($severity, 'CharacteristicSeverity') ? $severity->id : $severity->characteristic_severity_id);
            $filling['age_to_express_severity']      = $severity->getRandomAgeToExpress();
            $filling['severity_expressed']           = ($severity->canBeExpressed() and $this->age >= $filling['age_to_express_severity']);
            $filling['severity_value']               = $severity->getRandomValue();
            $filling['age_to_reveal_severity_value'] = $severity->getRandomAgeToRevealSeverityValue();
            $filling['severity_value_revealed']      = ($severity->valueCanBeRevealed() and $this->age >= $filling['age_to_reveal_severity_value']);

            // Check if there are any symptoms that need to be attached
            foreach($severity->symptoms as $symptom)
            {
                $ageToExpress = $symptom->getRandomAgeToExpress($filling['age_to_express_severity']);

                $dogCharacteristicSymptom = new DogCharacteristicSymptom;

                $dogCharacteristicSymptom->dog_characteristic_id = null;
                $dogCharacteristicSymptom->characteristic_severity_symptom_id = (is_a($symptom, 'CharacteristicSeveritySymptom') ? $symptom->id : $symptom->characteristic_severity_symptom_id);
                $dogCharacteristicSymptom->age_to_express = $ageToExpress;

                // Symptoms will always eventually be expressed
                $dogCharacteristicSymptom->expressed = ($this->age >= $ageToExpress);

                // Store the symptom to be added later
                $symptoms[] = $dogCharacteristicSymptom;
            }
        }

        // Create the dog characteristic
        $dogCharacteristic = DogCharacteristic::create($filling);

        // Check if the characteristic has loci
        $locusIds = $characteristic->loci()->lists('id', 'id');

        if ( ! empty($locusIds))
        {
            $attachedGenotypeIds = array_intersect_key($genotypeIdsByLocusId, $locusIds);

            // Attach genotypes
            if ( ! empty($attachedGenotypeIds))
            {
                $dogCharacteristic->genotypes()->attach($attachedGenotypeIds);
            }

            // Get the dog's phenotype IDs
            $phenotypeIds = $this->phenotypes()->lists('id');

            $attachedPhenotypeIds = [];

            // Go through each of the dog's phenotype ids
            foreach ($phenotypeIds as $phenotypeId)
            {
                // Grab genotype ids
                $required = DB::table('phenotypes_genotypes')
                    ->select('genotypes.locus_id', 'genotypes.id')
                    ->join('genotypes', 'genotypes.id', '=', 'phenotypes_genotypes.genotype_id')
                    ->join('loci', 'loci.id', '=', 'genotypes.locus_id')
                    ->where('phenotypes_genotypes.phenotype_id', $phenotypeId)
                    ->where('loci.active', true)
                    ->lists('locus_id', 'id');

                $requiredIds   = array_values(array_unique($required));
                $matchesNeeded = count($requiredIds);
                $matches       = count(array_intersect($requiredIds, $locusIds));

                if ($matches == $matchesNeeded)
                {
                    // Give the dog the phenotype
                    $attachedPhenotypeIds[] = $phenotypeId;
                }
            }

            // Attach phenotypes
            if ( ! empty($attachedPhenotypeIds))
            {
                $dogCharacteristic->phenotypes()->attach($attachedPhenotypeIds);
            }
        }

        // Save any symptoms
        foreach($symptoms as $symptom)
        {
            // Assign it to the characteristic
            $symptom->dog_characteristic_id = $dogCharacteristic->id;

            // Save it
            $symptom->save();

            if ($this->isAlive() and $symptom->isExpressed() and $symptom->isLethal())
            {
                // Kill the dog
                $symptom->killDog();
            }
        }

        return $dogCharacteristic;
    }

    public function kill()
    {
        $this->deceased_at = Carbon::now();

        if ( ! is_null($this->owner))
        {
            $cemetery = $this->owner->kennelGroups()->whereCemetery()->first();
            $this->kennel_group_id = $cemetery->id;
        }

        $this->studding = Dog::STUDDING_NONE;
        $this->heat = false;
        $this->active_breed_member = false;
        $this->worked = false;

        $this->save();

        // Remove all lend requests
        DB::table('lend_requests')->where('dog_id', $this->id)->delete();

        // Remove all stud requests
        DB::table('stud_requests')->where('bitch_id', $this->id)->orWhere('stud_id', $this->id)->delete();

        // Remove all blrs
        DB::table('beginners_luck_requests')->where('bitch_id', $this->id)->orWhere('dog_id', $this->id)->delete();
    }

    public function ageToDieOfOldAgeAt()
    {
        $lifeSpan = $this->getLifeSpan();

        if (is_null($lifeSpan))
        {
            return 0;
        }

        return round($lifeSpan->current_ranged_value);
    }

    public function checkLifeSpan()
    {
        // If the dog is already dead, it can't ever go back
        if ($this->isDeceased())
        {
            return true;
        }

        $ageToDieAt = $this->ageToDieOfOldAgeAt();

        // Dog should be dead
        return ($this->age >= $ageToDieAt);
    }

    public function complete($customizedCharacteristics = [])
    {
        // Remove characteristics
        DB::table('dog_characteristics')->where('dog_id', $this->id)->delete();

        // Remove genotypes
        DB::table('dog_genotypes')->where('dog_id', $this->id)->delete();

        // Remove phenotypes
        DB::table('dog_phenotypes')->where('dog_id', $this->id)->delete();

        // Remove pedigree
        DB::table('pedigrees')->where('dog_id', $this->id)->delete();

        if ($this->hasLitter())
        {
            $this->_completePuppy();
        }
        else if ($this->isCustomImported())
        {
            $this->_completeCustomImport($customizedCharacteristics);
        }
        else
        {
            $this->_completeImport();
        }

        $this->completed_at = Carbon::now();
        $this->save();
    }

    /**
     * @throws Dynasty\Pedigrees\Exceptions\NotSavedException
     */
    private function _completeImport()
    {
        // Store genotypes and phenotypes for later
        $genotypeIds   = [];
        $phenotypeIds  = [];
        $allBreedLoci  = [];
        $locusedBreeds = [];

        // Grab all active breeds
        $allBreeds = Breed::whereActive()->get();

        foreach($allBreeds as $allBreed)
        {
            // Grab the genotypes
            $genotypes = $this->isFemale()
                ? $allBreed->genotypes()->wherePivot('frequency', '>', 0)->whereActive()->whereAvailableToFemale()->get()->toArray()
                : $allBreed->genotypes()->wherePivot('frequency', '>', 0)->whereActive()->whereAvailableToMale()->get()->toArray();
            
            // We want to grab the genotypes from the active breeds so that there are no lethal genotypes grabbed
            $collected = array_collect($genotypes, 'locus_id');
            $allBreedLoci[$allBreed->id] = $collected;

            // We want to add the breed to the appropriate loci
            foreach($collected as $locusId => $genotypes)
            {
                if ( ! array_key_exists($locusId, $locusedBreeds))
                {
                    $locusedBreeds[$locusId] = [];
                }

                $locusedBreeds[$locusId][] = $allBreed->id;
            }
        }

        // Get all possible loci
        $loci = Locus::whereActive()->whereIn('id', array_keys($locusedBreeds))->get();

        // Save the possible genotypes
        $possibleGenotypes = [];

        // Make sure the breed has all the loci
        foreach($loci as $locus)
        {
            // Found it in the breed
            if (array_key_exists($locus->id, $allBreedLoci[$this->breed->id]))
            {
                $genotypes = $allBreedLoci[$this->breed->id][$locus->id];
            }
            // Not found in breed
            else
            {
                // Choose a random breed THAT HAS THIS LOCUS
                $index = mt_rand(0, count($locusedBreeds[$locus->id]) - 1);
                $randomBreedId = $locusedBreeds[$locus->id][$index];

                // Use its locus
                $genotypes = $allBreedLoci[$randomBreedId][$locus->id];
            }

            // Adjust for frequencies
            foreach($genotypes as $genotype)
            {
                for ($i = 0; $i < $genotype['pivot']['frequency']; $i++)
                {
                    $possibleGenotypes[$locus->id][] = $genotype;
                }
            }
        }

        foreach($possibleGenotypes as $locusId => $genotypes)
        {
            // Choose one
            $genotype = array_random($genotypes);

            // Add them to save later
            $genotypeIds[$genotype['locus_id']] = $genotype['id'];
        }

        // Give the dog the genotypes
        $this->genotypes()->sync($genotypeIds);

        // Get all the phenotypes
        $phenotypes = Phenotype::all();

        // Cycle through the phenotypes
        foreach($phenotypes as $phenotype)
        {
            // Grab genotype ids
            $required = DB::table('phenotypes_genotypes')
                ->select('genotypes.locus_id', 'genotypes.id')
                ->join('genotypes', 'genotypes.id', '=', 'phenotypes_genotypes.genotype_id')
                ->join('loci', 'loci.id', '=', 'genotypes.locus_id')
                ->where('phenotypes_genotypes.phenotype_id', $phenotype->id)
                ->where('loci.active', true)
                ->lists('locus_id', 'id');

            // Check if the phenotype matched all required genotypes with the dog's genotypes
            $requiredIds   = array_keys($required);
            $matchesNeeded = count(array_unique($required));
            $matches       = count(array_intersect($requiredIds, $genotypeIds));

            if ($matches == $matchesNeeded)
            {
                // Give the dog the phenotype
                $phenotypeIds[] = $phenotype->id;
            }
        }

        // Give the dog the phenotypes
        $this->phenotypes()->sync($phenotypeIds);

        // Store the characteristics given to be added in one query 
        $dogCharacteristics = [];
        
        // Store the symtpoms given to be added in one query
        $dogCharacteristicSymptoms = [];

        // Save them for later for the dependencies
        $allCharacteristics   = [];
        $breedCharacteristics = [];
        $usedCharacteristics  = [];

        // Grab all of the breed characteristics in advance
        foreach($this->breed->characteristics()->whereActive()->get() as $breedCharacteristic)
        {
            $breedCharacteristics[$breedCharacteristic->characteristic_id] = $breedCharacteristic;
        }

        // We need to grab all of the characteristics in case a breed is missing one
        $characteristics = Characteristic::whereActive()
            // Do not give IS diseases
            ->where('type_id', '<>', Characteristic::TYPE_IMMUNE_SYSTEM_DISEASE)
            // Do not give old age
            ->where('type_id', '<>', Characteristic::TYPE_OLD_AGE)
            ->orderBy('id', 'asc')
            ->get();

        // Go through all the potential characteristics
        foreach($characteristics as $characteristic)
        {
            // Store it for later
            $allCharacteristics[$characteristic->id] = $characteristic;

            $useableCharacteristic = array_key_exists($characteristic->id, $breedCharacteristics)
                // If the breed characteristic exists, we'll use that
                ? $breedCharacteristics[$characteristic->id]
                // Otherwise, we're going to use the umbrella characteristic
                : $useableCharacteristic = $characteristic;

            // Store it for later
            $usedCharacteristics[$characteristic->id] = $useableCharacteristic;

            // Do genetic components
            $ageToRevealGenotypes  = $useableCharacteristic->getRandomAgeToRevealGenotypes();
            $ageToRevealPhenotypes = $useableCharacteristic->getRandomAgeToRevealPhenotypes();

            // Do ranged components
            $finalRangedValue = $this->isFemale()
                ? $useableCharacteristic->getRandomRangedFemaleValue() 
                : $useableCharacteristic->getRandomRangedMaleValue();

            $ageToStopGrowing       = $useableCharacteristic->getRandomAgeToStopGrowing();
            $currentRangedValue     = DogCharacteristic::currentRangedValue($finalRangedValue, $ageToStopGrowing, $this->age);
            $ageToRevealRangedValue = $useableCharacteristic->getRandomAgeToRevealRangedValue();

            $filling = array(
                'characteristic_id'          => $characteristic->id, 
                'hide'                       => $useableCharacteristic->hide, 
                'in_summary'                 => false, 

                // Do genetics
                'age_to_reveal_genotypes'    => $ageToRevealGenotypes, 
                'genotypes_revealed'         => ($characteristic->genotypesCanBeRevealed() and $this->age >= $ageToRevealGenotypes), 
                'age_to_reveal_phenotypes'   => $ageToRevealPhenotypes, 
                'phenotypes_revealed'        => ($characteristic->phenotypesCanBeRevealed() and $this->age >= $ageToRevealPhenotypes), 

                // Do ranged
                'final_ranged_value'         => $finalRangedValue, 
                'age_to_stop_growing'        => $ageToStopGrowing, 
                'current_ranged_value'       => $currentRangedValue, 
                'age_to_reveal_ranged_value' => $ageToRevealRangedValue, 
                'ranged_value_revealed'      => ($characteristic->rangedValueCanBeRevealed() and $this->age >= $ageToRevealRangedValue), 
            );

            if ($characteristic->eligibleForSeverity($genotypeIds))
            {
                // Grab a nonlethal health severity if it exists
                if ( ! is_null($severity = $useableCharacteristic->getRandomSeverity(true, $this->age)))
                {
                    $filling['characteristic_severity_id']   = (is_a($severity, 'CharacteristicSeverity') ? $severity->id : $severity->characteristic_severity_id);
                    $filling['age_to_express_severity']      = $severity->getRandomAgeToExpress();
                    $filling['severity_expressed']           = ($severity->canBeExpressed() and $this->age >= $filling['age_to_express_severity']);
                    $filling['severity_value']               = $severity->getRandomValue();
                    $filling['age_to_reveal_severity_value'] = $severity->getRandomAgeToRevealSeverityValue();
                    $filling['severity_value_revealed']      = ($severity->valueCanBeRevealed() and $this->age >= $filling['age_to_reveal_severity_value']);

                    // Check if there are any symptoms that need to be attached
                    $orderedDogCharacteristicSymptoms[$filling['characteristic_severity_id']] = [];

                    foreach($severity->symptoms as $symptom)
                    {
                        $ageToExpress = $symptom->getRandomAgeToExpress($filling['age_to_express_severity']);

                        $dogCharacteristicSymptom = new DogCharacteristicSymptom;

                        $dogCharacteristicSymptom->dog_characteristic_id = null;
                        $dogCharacteristicSymptom->characteristic_severity_symptom_id = (is_a($symptom, 'CharacteristicSeveritySymptom') ? $symptom->id : $symptom->characteristic_severity_symptom_id);
                        $dogCharacteristicSymptom->age_to_express = $ageToExpress;

                        // Symptoms will always eventually be expressed
                        $dogCharacteristicSymptom->expressed = ($this->age >= $ageToExpress);

                        // !!! An imported dog should never be killed on completion
                        /*if ($dogCharacteristicSymptom->expressed and $symptom->isLethal())
                        {
                            // We need to kill the dog
                            $this->kill();

                            // Send a notification to the dog's owner
                            $params = array(
                                'symptom' => (is_a($symptom, 'CharacteristicSeveritySymptom') ? $symptom->name : $symptom->name), 
                                'dog'     => $this->nameplate(), 
                                'dogUrl'  => URL::route('dog/profile', $this->id), 
                                'pronoun' => ($this->isFemale() ? 'her' : 'his'), 
                            );

                            $body = Lang::get('notifications/dog.lethal_symptom.to_owner', array_map('htmlentities', array_dot($params)));
                            
                            $this->notify($body, UserNotification::TYPE_DANGER);
                        }*/

                        // Store the symptom to be added later
                        $orderedDogCharacteristicSymptoms[$filling['characteristic_severity_id']][] = $dogCharacteristicSymptom;
                    }
                }
            }

            // Fill the dog's characteristic and store it to be added later
            $dogCharacteristic = new DogCharacteristic;

            $dogCharacteristics[] = $dogCharacteristic->fill($filling);
        }

        // Attach the dog's characteristics
        $this->characteristics()->saveMany($dogCharacteristics);

        // We need to attach the genotypes, phenotypes, and symptoms to the newly saved dog characteristics
        $dogCharacteristics = $this->characteristics;

        // Store for saving later
        $dogCharacteristicSymptoms   = [];
        $dogCharacteristicGenotypes  = [];
        $dogCharacteristicPhenotypes = [];

        // Go through the dog's characteristics
        foreach($dogCharacteristics as $dogCharacteristic)
        {
            $characteristic = $allCharacteristics[$dogCharacteristic->characteristic_id];

            // Check if the characteristic has loci
            $locusIds = $characteristic->loci()->lists('id', 'id');

            if ( ! empty($locusIds))
            {
                $attachedPhenotypeIds = [];
                $attachedGenotypeIds = [];

                // Attach the genotypes to the dog characteristic
                $attachedGenotypeIds = array_intersect_key($genotypeIds, $locusIds);

                foreach($attachedGenotypeIds as $attachedGenotypeId)
                {
                    $dogCharacteristicGenotypes[] = array(
                        'dog_characteristic_id' => $dogCharacteristic->id, 
                        'genotype_id'           => $attachedGenotypeId, 
                    );
                }

                // Go through each of the dog's phenotype ids
                foreach ($phenotypeIds as $phenotypeId)
                {
                    // Grab genotype ids
                    $required = DB::table('phenotypes_genotypes')
                        ->select('genotypes.locus_id', 'genotypes.id')
                        ->join('genotypes', 'genotypes.id', '=', 'phenotypes_genotypes.genotype_id')
                        ->join('loci', 'loci.id', '=', 'genotypes.locus_id')
                        ->where('phenotypes_genotypes.phenotype_id', $phenotypeId)
                        ->where('loci.active', true)
                        ->lists('locus_id', 'id');

                    $requiredIds   = array_values(array_unique($required));
                    $matchesNeeded = count($requiredIds);
                    $matches       = count(array_intersect($requiredIds, $locusIds));

                    if ($matches == $matchesNeeded)
                    {
                        // Give the dog the phenotype
                        $dogCharacteristicPhenotypes[] = array(
                            'dog_characteristic_id' => $dogCharacteristic->id, 
                            'phenotype_id'          => $phenotypeId, 
                        );
                    }
                }
            }

            // Check if we need to attach a symptom
            if ( ! is_null($dogCharacteristic->characteristic_severity_id))
            {
                foreach($orderedDogCharacteristicSymptoms[$dogCharacteristic->characteristic_severity_id] as $dogCharacteristicSymptom)
                {
                    // Assign it to the characteristic
                    $dogCharacteristicSymptom->dog_characteristic_id = $dogCharacteristic->id;

                    // Save it back in the array to be saved
                    $dogCharacteristicSymptoms[] = $dogCharacteristicSymptom->toArray();
                }
            }
        }

        // Insert the dog's symptoms
        if ( ! empty($dogCharacteristicSymptoms))
        {
            DB::table('dog_characteristic_symptoms')->insert($dogCharacteristicSymptoms);
        }

        // Attach the genotypes to the characteristics
        if ( ! empty($dogCharacteristicGenotypes))
        {
            DB::table('dog_characteristic_genotypes')->insert($dogCharacteristicGenotypes);
        }

        // Attach the phenotypes to the characteristics
        if ( ! empty($dogCharacteristicPhenotypes))
        {
            DB::table('dog_characteristic_phenotypes')->insert($dogCharacteristicPhenotypes);
        }

        // Grab the dog's characteristics again
        // FASTER: Selecting everything again is quicker than lazy loading the genotypes, phenotypes and dependencies for the dog's characteristics
        $dogCharacteristics = $this
            ->load('characteristics.characteristic.dependencies', 'characteristics.genotypes', 'characteristics.phenotypes')
            ->characteristics()
            ->whereDependent()
            ->get();

        // We need to go back through the dog characteristics to do the dependency checks, but only on the dependent characteristics
        foreach($dogCharacteristics as $dogCharacteristic)
        {
            // SLOWER: Lazy load the genotypes, phenotypes and dependencies
            // $dogCharacteristic->load('genotypes', 'phenotypes', 'characteristic.dependencies');

            $characteristic = $dogCharacteristic->characteristic;

            foreach($characteristic->dependencies as $dependency)
            {
                if ($dependency->isActive())
                {
                    if ($dependency->takesInRanged())
                    {
                        // Get the independent characteristics range values for this dog
                        $independentRangedValues = DB::table('characteristic_dependency_ind_characteristics')
                            ->select('characteristic_dependency_ind_characteristics.independent_characteristic_id', 'dog_characteristics.final_ranged_value')
                            ->join('dog_characteristics', 'dog_characteristics.characteristic_id', '=', 'characteristic_dependency_ind_characteristics.independent_characteristic_id')
                            ->where('dog_characteristics.dog_id', $this->id)
                            ->where('characteristic_dependency_ind_characteristics.characteristic_dependency_id', $dependency->id)
                            ->lists('final_ranged_value', 'independent_characteristic_id');
                    }
                    else if ($dependency->takesInGenotypes())
                    {
                        // Get the independent characteristics genotypes for this dog
                        $independentGenotypeIds = DB::table('characteristic_dependency_ind_characteristics')
                            ->select('genotypes.locus_id', 'dog_characteristic_genotypes.genotype_id')
                            ->join('dog_characteristics', 'dog_characteristics.characteristic_id', '=', 'characteristic_dependency_ind_characteristics.independent_characteristic_id')
                            ->join('dog_characteristic_genotypes', 'dog_characteristic_genotypes.dog_characteristic_id', '=', 'dog_characteristics.id')
                            ->join('genotypes', 'genotypes.id', '=', 'dog_characteristic_genotypes.genotype_id')

                            ->where('dog_characteristics.dog_id', $this->id)
                            ->where('characteristic_dependency_ind_characteristics.characteristic_dependency_id', $dependency->id)
                            ->lists('genotype_id', 'locus_id');
                    }

                    if ($dependency->outputsRanged())
                    {
                        // Get this characteristics dependent value for this dog
                        $finalRangedValue = $dogCharacteristic->final_ranged_value;

                        $newRangedValue = $finalRangedValue;

                        // Do the dependencies
                        if ($dependency->isR2R())
                        {
                            $newRangedValue = $dependency->doR2R($finalRangedValue, $independentRangedValues);
                        }
                        else if ($dependency->isG2R())
                        {
                            $newRangedValue = $dependency->doG2R($finalRangedValue, $independentGenotypeIds);
                        }

                        // Only need to update and bind if it changed
                        if (Floats::compare($finalRangedValue, $newRangedValue, '!='))
                        {
                            // We need to bind to the used characteristic itself
                            $usedCharacteristic = $usedCharacteristics[$characteristic->id];

                            // Bind the value
                            $finalRangedValue = $this->isFemale()
                                ? $usedCharacteristic->bindRangedFemaleValue($newRangedValue) 
                                : $usedCharacteristic->bindRangedMaleValue($newRangedValue);

                            // Adjust for growth
                            $currentRangedValue = DogCharacteristic::currentRangedValue($finalRangedValue, $dogCharacteristic->age_to_stop_growing, $this->age);

                            // Save it back on the characteristic
                            $dogCharacteristic->final_ranged_value   = $finalRangedValue;
                            $dogCharacteristic->current_ranged_value = $currentRangedValue;

                            // Save it IMMEDIATELY
                            $dogCharacteristic->save();
                        }
                    }
                    else if ($dependency->outputsGenotypes())
                    {
                        // We no longer support X2G dependencies, but this check is here for legacy value
                    }
                }
            }
        }

        // Once the characteristics are assigned, need to check breedability
        $this->sexually_mature = $this->checkSexualMaturity();
        $this->sexual_decline  = $this->checkSexualDecline();
        $this->infertile       = $this->checkInfertility();

        // Give them an imported pedigree
        $pedigree = Pedigree::imported();

        $pedigree->dog_id = $this->id;

        if ( ! $pedigree->save())
        {
            throw new Dynasty\Pedigrees\Exceptions\NotSavedException;
        }

        // Calculate the COI of the pedigree
        $this->coi = $pedigree->calculateCoi();

        // Save everything
        $this->save();
    }

    /**
     * @throws Dynasty\Characteristics\Exceptions\NotFoundException
     * @throws Dynasty\Characteristics\Exceptions\InvalidException
     * @throws Dynasty\Characteristics\Exceptions\UniqueException
     * @throws Dynasty\Characteristics\Exceptions\IncompleteException
     * @throws Dynasty\DogCharacteristics\Exceptions\RangedValueOutOfBoundsException
     * @throws Dynasty\Breeds\Exceptions\GenotypeNotFoundException
     * @throws Dynasty\Characteristics\Exceptions\GenotypeNotFoundException
     * @throws Dynasty\DogCharacteristics\Exceptions\TooManyPhenotypesException
     * @throws Dynasty\Breeds\Exceptions\PhenotypeNotFoundException
     * @throws Dynasty\Characteristics\Exceptions\PhenotypeNotFoundException
     * @throws Dynasty\DogCharacteristics\Exceptions\InternalConflictException
     * @throws Dynasty\DogCharacteristics\Exceptions\ExternalConflictException
     * @throws Dynasty\DogCharacteristics\Exceptions\MultipleBasePhenotypesException
     * @throws Dynasty\DogCharacteristics\Exceptions\BasePhenotypeNotFoundException
     * @throws Dynasty\DogCharacteristics\Exceptions\IncompatibleException
     * @throws Dynasty\DogCharacteristics\Exceptions\UnresolvedException
     * @throws Dynasty\Pedigrees\Exceptions\NotSavedException
     */
    private function _completeCustomImport(array $customizedCharacteristics)
    {
        // We only need to complete a dog as a custom import if characteristics were specified
        if (empty($customizedCharacteristics))
        {
            return $this->_completeImport();
        }

        // Get all possible breed genotypes
        $breedGenotypes = $this->isFemale()
            ? $this->breed->genotypes()->whereAvailableToFemale()->whereActive()->wherePivot('frequency', '>', 0)->get()
            : $this->breed->genotypes()->whereAvailableToMale()->whereActive()->wherePivot('frequency', '>', 0)->get();

        // Sort the IDs by their locus
        $breedGenotypeIdsByLocusId = [];

        foreach($breedGenotypes as $genotype)
        {
            $breedGenotypeIdsByLocusId[$genotype->locus_id][] = $genotype->id;
        }

        // Get the genotype IDs
        $breedGenotypeIds = array_flatten($breedGenotypeIdsByLocusId);

        // Get all of the breed's characteristics
        $breedCharacteristics = $this->breed->characteristics()->whereActive()->get();

        // Sort the breed characteristics by characteristic ID
        $sortedBreedCharacteristics = [];

        foreach($breedCharacteristics as $breedCharacteristic)
        {
            $sortedBreedCharacteristics[$breedCharacteristic->characteristic_id] = $breedCharacteristic;
        }

        // Filter for the customizable ones
        $customizableBreedCharacteristics = $breedCharacteristics->filter(function($item)
            {
                return $item->isVisible();
            });

        // Get the IDs
        $customizableBreedCharacteristicIds = $customizableBreedCharacteristics->lists('characteristic_id');

        // Always add -1
        $customizableBreedCharacteristicIds[] = -1;

        // Get all customizable characteristics
        $customizableCharacteristics = Characteristic::whereActive()
            ->whereVisible()
            ->whereNotHealth()
            ->whereIn('characteristics.id', $customizableBreedCharacteristicIds)
            ->get();

        // Get the characteristic IDs
        $customizableCharacteristicIds = $customizableCharacteristics->lists('id');

        // Store the characteristics that were looked at to catch duplicates
        $originalSelectedCharacteristics = [];
        $selectedCharacteristics = [];

        // Keep track of what genotypes belong to which characteristic
        $genotypeIdbyLocusIdbyCharacteristicId = [];

        // Need to pool all of the future genotypes by loci
        $pooledGenotypeIdsByLocusId  = [];

        // Keep track of if a characteristic has had genotypes customized and/or phenotypes customized
        $genotypicCharacteristicIds  = [];
        $phenotypicCharacteristicIds = [];
        
        foreach($customizedCharacteristics as $index => $customizedCharacteristic)
        {
            // Need to check for conflicts
            $selfConflicted = false;
            $conflictedCharacteristics = [];

            // Make sure an id was provided
            if ( ! array_key_exists('id', $customizedCharacteristic))
            {
                throw new Dynasty\Characteristics\Exceptions\InvalidException;
            }

            // Grab the characteristic ID
            $characteristicId = $customizedCharacteristic['id'];

            // Left a characteristic dropdown blank
            if ( ! $characteristicId) // IDs are never 0, null or '' so this is safe
            {
                throw new Dynasty\Characteristics\Exceptions\NotFoundException;
            }

            // Make sure the characteristic ID could be customized
            if ( ! in_array($characteristicId, $customizableCharacteristicIds))
            {
                throw new Dynasty\Characteristics\Exceptions\InvalidException;
            }

            // Grab the breed characteristic
            $breedCharacteristic = $sortedBreedCharacteristics[$characteristicId];

            // Grab the characteristic
            $characteristic = $breedCharacteristic->characteristic;

            // Chosen twice
            if (array_key_exists($characteristic->id, $originalSelectedCharacteristics))
            {
                $params = array(
                    'characteristic' => $characteristic->name, 
                );

                throw new Dynasty\Characteristics\Exceptions\UniqueException(json_encode($params));
            }

            // Store the data by the characteristic ID
            $selectedCharacteristics[$characteristicId] = $customizedCharacteristic;
            $originalSelectedCharacteristics[$characteristicId] = $characteristicId;

            if ( ! array_key_exists('r', $customizedCharacteristic) and  ! array_key_exists('g', $customizedCharacteristic) and  ! array_key_exists('ph', $customizedCharacteristic))
            {
                $params = array(
                    'characteristic' => $characteristic->name, 
                );

                throw new Dynasty\Characteristics\Exceptions\IncompleteException(json_encode($params));
            }

            // Do ranged check
            if ($characteristic->isRanged())
            {
                if ( ! array_key_exists('r', $customizedCharacteristic))
                {
                    $params = array(
                        'characteristic' => $characteristic->name, 
                    );

                    throw new Dynasty\Characteristics\Exceptions\IncompleteException(json_encode($params));
                }

                $rangedKey = $this->isFemale() ? 'female' : 'male';
                $shortRangedKey = $rangedKey[0];

                if ( ! array_key_exists($shortRangedKey, $customizedCharacteristic['r']))
                {
                    $params = array(
                        'characteristic' => $characteristic->name, 
                    );
                    
                    throw new Dynasty\Characteristics\Exceptions\IncompleteException(json_encode($params));
                }

                // Get the ranged value
                $customizedRangedValue = $customizedCharacteristic['r'][$shortRangedKey];

                if ( ! $breedCharacteristic->isInRange($customizedRangedValue, $rangedKey))
                {
                    $params = array(
                        'characteristic' => $characteristic->name, 
                        'ranged_value'   => (int) $customizedRangedValue, 
                    );
                    
                    throw new Dynasty\DogCharacteristics\Exceptions\RangedValueOutOfBoundsException(json_encode($params));
                }
            } // End ranged check

            // Do genetic check
            if ($characteristic->isGenetic())
            {
                if ( ! array_key_exists('g', $customizedCharacteristic) and ! array_key_exists('ph', $customizedCharacteristic))
                {
                    $params = array(
                        'characteristic' => $characteristic->name, 
                    );

                    throw new Dynasty\Characteristics\Exceptions\IncompleteException(json_encode($params));
                }                     
                
                // Grab all the loci on the characteristic
                $characteristicLoci = $characteristic->loci()->with('genotypes')->whereActive()->get();

                // Get the genotype IDs and group them by their locus
                $characteristicGenotypeIdsByLocusId = [];

                foreach($characteristicLoci as $locus)
                {
                    $characteristicGenotypeIdsByLocusId[$locus->id] = $locus->genotypes->lists('id');
                }

                // Grab the characteristic's genotype IDs seperately
                $characteristicGenotypeIds = array_flatten($characteristicGenotypeIdsByLocusId);

                // Test on genotypes
                if ( ! $characteristic->hideGenotypes() and array_key_exists('g', $customizedCharacteristic))
                {
                    $selectedGenotypeIdsByLocusId = (array) $customizedCharacteristic['g'];

                    $filteredSelectedGenotypeIdsByLocusId = array_filter($selectedGenotypeIdsByLocusId);

                    // Could have chosen to just specify the phenotypes
                    if ( ! empty($filteredSelectedGenotypeIdsByLocusId))
                    {
                        // Make sure if they chose one, they chose all of them
                        if (count($filteredSelectedGenotypeIdsByLocusId) != count($selectedGenotypeIdsByLocusId))
                        {
                            $params = array(
                                'characteristic' => $characteristic->name, 
                            );

                            throw new Dynasty\Characteristics\Exceptions\IncompleteException(json_encode($params));
                        }

                        // Mark this characteristic as genotypic
                        $genotypicCharacteristicIds[] = $characteristic->id;

                        // Go through the loci and genotype IDs
                        foreach($selectedGenotypeIdsByLocusId as $locusId => $genotypeId)
                        {
                            // Make sure the genotype exists in the breed
                            if ( ! in_array($genotypeId, $breedGenotypeIds))
                            {
                                // Grab the genotype
                                $genotype = Genotype::find($genotypeId);

                                $params = array(
                                    'breed'    => $this->breed->name, 
                                    'genotype' => (is_null($genotype) ? '' : $genotype->toSymbol())
                                );

                                throw new Dynasty\Breeds\Exceptions\GenotypeNotFoundException(json_encode($params));
                            }

                            // Make sure the genotype exists in the characteristic
                            if ( ! in_array($genotypeId, $characteristicGenotypeIds))
                            {
                                // Grab the genotype
                                $genotype = Genotype::find($genotypeId);

                                $params = array(
                                    'characteristic' => $characteristic->name, 
                                    'genotype'       => (is_null($genotype) ? '' : $genotype->toSymbol())
                                );

                                throw new Dynasty\Characteristics\Exceptions\GenotypeNotFoundException(json_encode($params));
                            }

                            // Make sure there are no conflicts with other characteristics
                            foreach($genotypeIdbyLocusIdbyCharacteristicId as $characteristicId => $genotypeIdsByLocusId)
                            {
                                if (array_key_exists($locusId, $genotypeIdsByLocusId) and ! in_array($genotypeId, $genotypeIdsByLocusId[$locusId]))
                                {
                                    // Save the conflicted characteristic
                                    $conflictedCharacteristics[] = $sortedBreedCharacteristics[$characteristicId]->characteristic;
                                }
                            }

                            // Save the genotypes on their loci on the characteristic
                            $genotypeIdbyLocusIdbyCharacteristicId[$characteristic->id][$locusId] = [$genotypeId];
                        }
                    }
                } // End genotypes check

                // Test on phenotypes
                if (array_key_exists('ph', $customizedCharacteristic))
                {
                    // Check base issues
                    $baseConflict = false;
                    $baseChosen   = false;

                    // Grab the phenotype IDs
                    $selectedPhenotypeIds = (array) $customizedCharacteristic['ph'];

                    // Only Colour can have multiple phenotypes customized
                    if (count($selectedPhenotypeIds) > 1 and ! $characteristic->isType(Characteristic::TYPE_COLOUR))
                    {
                        $params = array(
                            'characteristic' => $characteristic->name, 
                        );

                        throw new Dynasty\DogCharacteristics\Exceptions\TooManyPhenotypesException(json_encode($params));
                    }

                    // Mark this characteristic as phenotypic
                    $phenotypicCharacteristicIds[] = $characteristic->id;

                    // Go through the phenotypes
                    foreach($selectedPhenotypeIds as $phenotypeId)
                    {
                        // Grab the phenotype
                        $phenotype = Phenotype::find($phenotypeId);
                                                                
                        // Get all of the phenotype's genotypes
                        $phenotypeGenotypes = $phenotype->genotypes()->whereActive()->get();

                        // Sort them by their locus
                        $phenotypeGenotypeIdsByLocusId = [];

                        foreach($phenotypeGenotypes as $genotype)
                        {
                            $phenotypeGenotypeIdsByLocusId[$genotype->locus_id][] = $genotype->id;
                        }

                        // Get the genotype IDs
                        $phenotypeGenotypeIds = array_flatten($phenotypeGenotypeIdsByLocusId);

                        // Verify that the phenotype is appropriate for the breed and the characteristic
                        foreach($phenotypeGenotypeIdsByLocusId as $locusId => $genotypeIds)
                        {
                            if ( ! array_key_exists($locusId, $breedGenotypeIdsByLocusId))
                            {
                                $params = array(
                                    'breed'     => $this->breed->name, 
                                    'phenotype' => $phenotype->name, 
                                );

                                throw new Dynasty\Breeds\Exceptions\PhenotypeNotFoundException(json_encode($params));
                            }

                            // Find common genotypes
                            $breedIntersect = array_intersect($genotypeIds, $breedGenotypeIdsByLocusId[$locusId]);

                            // Nothing in common
                            if (empty($breedIntersect))
                            {
                                $params = array(
                                    'breed'     => $this->breed->name, 
                                    'phenotype' => $phenotype->name, 
                                );

                                throw new Dynasty\Breeds\Exceptions\PhenotypeNotFoundException(json_encode($params));
                            }

                            if ( ! array_key_exists($locusId, $characteristicGenotypeIdsByLocusId))
                            {
                                $params = array(
                                    'characteristic' => $characteristic->name, 
                                    'phenotype'      => $phenotype->name, 
                                );

                                throw new Dynasty\Characteristics\Exceptions\PhenotypeNotFoundException(json_encode($params));
                            }

                            // Find common genotypes
                            $characteristicIntersect = array_intersect($genotypeIds, $characteristicGenotypeIdsByLocusId[$locusId]);

                            // Nothing in common
                            if (empty($characteristicIntersect))
                            {
                                $params = array(
                                    'characteristic' => $characteristic->name, 
                                    'phenotype'      => $phenotype->name, 
                                );

                                throw new Dynasty\Characteristics\Exceptions\PhenotypeNotFoundException(json_encode($params));
                            }
                        }

                        // Check for internal conflicts
                        if (array_key_exists($characteristic->id, $genotypeIdbyLocusIdbyCharacteristicId))
                        {
                            foreach($genotypeIdbyLocusIdbyCharacteristicId[$characteristic->id] as $locusId => $genotypeIds)
                            {
                                if (array_key_exists($locusId, $phenotypeGenotypeIdsByLocusId))
                                {
                                    $intersect = array_intersect($phenotypeGenotypeIdsByLocusId[$locusId], $genotypeIds);

                                    if (empty($intersect))
                                    {
                                        $params = array(
                                            'characteristic' => $characteristic->name, 
                                        );

                                        throw new Dynasty\DogCharacteristics\Exceptions\InternalConflictException(json_encode($params));
                                    }

                                    // Save the intersection
                                    $genotypeIdbyLocusIdbyCharacteristicId[$characteristic->id][$locusId] = $intersect;
                                }
                            }
                        }

                        // Check for external conflicts
                        foreach($phenotypeGenotypeIdsByLocusId as $locusId => $genotypeIds)
                        {
                            foreach($genotypeIdbyLocusIdbyCharacteristicId as $characteristicId => $genotypeIdsByLocusId)
                            {
                                if (array_key_exists($locusId, $genotypeIdsByLocusId))
                                {
                                    $intersect = array_intersect($genotypeIdsByLocusId[$locusId], $genotypeIds);

                                    if (empty($intersect))
                                    {
                                        // Save the conflicted characteristic
                                        $conflictedCharacteristics[] = $sortedBreedCharacteristics[$characteristicId]->characteristic;
                                    }

                                    // Save the intersection
                                    $genotypeIdbyLocusIdbyCharacteristicId[$characteristicId][$locusId] = $intersect;
                                }
                            }
                        }

                        // Store this phenotype's genotype IDs for later use
                        foreach($phenotypeGenotypeIdsByLocusId as $locusId => $genotypeIds)
                        {
                            $genotypeIdbyLocusIdbyCharacteristicId[$characteristic->id][$locusId] = (array_key_exists($characteristic->id, $genotypeIdbyLocusIdbyCharacteristicId) and array_key_exists($locusId, $genotypeIdbyLocusIdbyCharacteristicId[$characteristic->id]))
                                ? array_intersect($genotypeIdbyLocusIdbyCharacteristicId[$characteristic->id][$locusId], $genotypeIds)
                                : $genotypeIds;
                        }

                        // Do Colour checks
                        if ($characteristic->isType(Characteristic::TYPE_COLOUR) and ! starts_with($phenotype->name, 'and ') and ! starts_with($phenotype->name, 'with '))
                        {
                            // Check if a second base was chosen; which is not allowed
                            if ($baseChosen)
                            {
                                $params = array(
                                    'characteristic' => $characteristic->name, 
                                );

                                throw new Dynasty\DogCharacteristics\Exceptions\MultipleBasePhenotypesException(json_encode($params));
                            }

                            // Say that the base colour phenotype has been chosen
                            $baseChosen = true;
                        }
                    }

                    // Make sure a base was actually chosen if that characteristic is Colour
                    if ($characteristic->isType(Characteristic::TYPE_COLOUR) and ! $baseChosen)
                    {
                        $params = array(
                            'characteristic' => $characteristic->name, 
                        );

                        throw new Dynasty\DogCharacteristics\Exceptions\BasePhenotypeNotFoundException(json_encode($params));
                    }
                } // End phenotypes check

                if ( ! in_array($characteristic->id, $genotypicCharacteristicIds) and ! in_array($characteristic->id, $phenotypicCharacteristicIds))
                {
                    $params = array(
                        'characteristic' => $characteristic->name, 
                    );

                    throw new Dynasty\Characteristics\Exceptions\IncompleteException(json_encode($params));
                }
            } // End genetics check

            // Check if any external conflictions occurred between other characteristics already checked
            if ( ! empty($conflictedCharacteristics))
            {
                $conflictedCharacteristicNames = [];

                foreach($conflictedCharacteristics as $conflictedCharacteristic)
                {
                    $conflictedCharacteristicNames[] = $conflictedCharacteristic->name;
                }

                $params = array(
                    'characteristic' => $characteristic->name, 
                    'conflicted_characteristics' => implode(', ', $conflictedCharacteristicNames), 
                );

                throw new Dynasty\DogCharacteristics\Exceptions\ExternalConflictException(json_encode($params));
            }
        } // End characteristic check

        // Pool all genotypes together from the characteristics
        foreach($genotypeIdbyLocusIdbyCharacteristicId as $characteristicId => $genotypeIdsByLocusId)
        {
            foreach($genotypeIdsByLocusId as $locusId => $genotypeIds)
            {
                $pooledGenotypeIdsByLocusId[$locusId] = array_key_exists($locusId, $pooledGenotypeIdsByLocusId)
                    ? array_intersect($genotypeIds, $pooledGenotypeIdsByLocusId[$locusId])
                    : $genotypeIds;

                $pooledGenotypeIdsByLocusId[$locusId] = array_values(array_unique($pooledGenotypeIdsByLocusId[$locusId]));
            }
        }

        // We need to go through the customized characteristic data again to check for dependencies
        foreach($customizedCharacteristics as $index => $customizedCharacteristic)
        {
            // Grab the characteristic id
            $characteristicId = $customizedCharacteristic['id'];

            // Grab the breed characteristic
            $breedCharacteristic = $sortedBreedCharacteristics[$characteristicId];

            // Grab the characteristic
            $characteristic = $breedCharacteristic->characteristic;

            // Get all dependencies for this characteristics
            $dependencies = $characteristic->dependencies()->with('independentCharacteristics.characteristic')->whereActive()->get();

            foreach($dependencies as $dependency)
            {
                // Get the independent characteristic IDs
                $dependencyIndependentCharacteristicCharacteristicIds = $dependency->independentCharacteristics->lists('characteristic_id');

                // Number of times tried to resolve a dependency
                $dependencyResolutionTries = 0;

                do
                {
                    $continueTryingToResolveDependency = false;

                    ++$dependencyResolutionTries;

                    // Go through all of the independent characteristics
                    foreach($dependency->independentCharacteristics as $dependencyIndependentCharacteristic)
                    {

                        // Grab the characteristic
                        $independentCharacteristic = $dependencyIndependentCharacteristic->characteristic;

                        if ($dependency->takesInRanged())
                        {
                            // Get the independent characteristic range values from the 
                            if (array_key_exists($dependencyIndependentCharacteristic->id, $selectedCharacteristics))
                            {
                                $independentRangedValues[$independentCharacteristic->id] = $selectedCharacteristics[$independentCharacteristic->id]['r'][($this->isFemale() ? 'f' : 'm')];
                            }
                            else // Independent characteristic was not selected in customized characteristics
                            {
                                // Grab the breed characteristic equivalent
                                if (array_key_exists($independentCharacteristic->id, $sortedBreedCharacteristics))
                                {
                                    $breedCharacteristic = $sortedBreedCharacteristics[$independentCharacteristic->id];

                                    if ($post['sex_id'] == Model_Dog::FEMALE)
                                    {
                                        $minRangedValue = $breedCharacteristic->min_female_ranged_value;
                                        $maxRangedValue = $breedCharacteristic->max_female_ranged_value;
                                    }
                                    else
                                    {
                                        $minRangedValue = $breedCharacteristic->min_male_ranged_value;
                                        $maxRangedValue = $breedCharacteristic->max_male_ranged_value;
                                    }
                                }
                                else // Use the umbrella characteristic
                                {
                                    $minRangedValue = $dependencyIndependentCharacteristic->characterstic->min_ranged_value;
                                    $maxRangedValue = $dependencyIndependentCharacteristic->characterstic->max_ranged_value;
                                }

                                // We need to randomly generate one for future use
                                $independentRangedValues[$independentCharacteristic->id] = mt_rand($minRangedValue * 100, $maxRangedValue * 100) / 100.00;
                            
                                $continueTryingToResolveDependency = true;
                            }
                        }
                        else if ($dependency->takesInGenotypes())
                        {
                            // Get the independent characteristics required genotypes
                            $independentCharacteristicLoci = $independentCharacteristic->loci()->with('genotypes')->whereActive()->get();

                            // Sort the genotypes by their locus
                            $independentGenotypeIdsByLocusId = [];

                            foreach($independentCharacteristicLoci as $locus)
                            {
                                $possibleGenotypeIds = $locus->genotypes->lists('id');

                                if (array_key_exists($locus->id, $pooledGenotypeIdsByLocusId))
                                {
                                    $intersection = array_intersect($pooledGenotypeIdsByLocusId[$locus->id], $possibleGenotypeIds);

                                    $independentGenotypeIdsByLocusId[$locus->id] = $intersection;

                                    // Save the genotypes to affect future dependencies
                                    $pooledGenotypeIdsByLocusId[$locus->id] = $intersection;
                                }
                                // We need to randomly generate some genotypes at this locus for this independent char
                                else
                                {
                                    // Check if the breed has this locus
                                    if (array_key_exists($locus->id, $breedGenotypeIdsByLocusId))
                                    {
                                        // Base it on the breed genotypes
                                        $independentGenotypeIdsByLocusId[$locus->id] = array_intersect($breedGenotypeIdsByLocusId[$locus->id], $possibleGenotypeIds);
                                    }
                                    // If the breed doesn't have it, we need to select a genotype from a random breed
                                    else
                                    {
                                        $otherBreedGenotypeIds = DB::table('breed_genotypes')
                                            ->select('breed_genotypes.breed_id', 'genotypes.id')
                                            ->join('genotypes', 'genotypes.id', 'breed_genotypes.genotype_id')
                                            ->where('genotypes.locus_id', $locus->id)
                                            ->where('breed_genotypes.frequency', '>', 0)
                                            ->get();

                                        $possibleBreedGenotypesIdsByBreedId = [];

                                        foreach($otherBreedGenotypeIds as $row)
                                        {
                                            $possibleBreedGenotypesIdsByBreedId[$row['breed_id']][] = $row['genotype_id'];
                                        }

                                        // Grab the breed ids
                                        $possibleBreedIds = array_keys($possibleBreedGenotypesIdsByBreedId);

                                        // Choose a random breed
                                        $randomBreedId = mt_rand(0, count($possibleBreedIds) - 1);

                                        // Use the random breed's genotypes
                                        $independentGenotypeIdsByLocusId[$locus->id] = $possibleBreedGenotypesIdsByBreedId[$randomBreedId];
                                    }

                                    $continueTryingToResolveDependency = true;
                                }
                            }
                        }
                    }

                    // Check for violations
                    if ($dependency->outputsRanged())
                    {
                        // Get this characteristic's dependent value
                        $newRangedValue = $finalRangedValue = $selectedCharacteristics[$characteristic->id]['r'][($this->isFemale() ? 'f' : 'm')];

                        // Do the dependencies
                        if ($dependency->isR2R())
                        {
                            $newRangedValue = $dependency->doR2R($finalRangedValue, $independentRangedValues);
                        }
                        else if ($dependency->isG2R())
                        {
                            // We just want to use the genotype IDs
                            $independentGenotypeIds = array_flatten($independentGenotypeIdsByLocusId);

                            $newRangedValue = $dependency->doG2R($finalRangedValue, $independentGenotypeIds);
                        }


                        // Defies dependency
                        if (Floats::compare($finalRangedValue, $newRangedValue, '!='))
                        {
                            // Only throw errors if the indeps were not randomly generated
                            if ( ! $continueTryingToResolveDependency)
                            {
                                $params = array(
                                    'characteristic' => $characteristic->name, 
                                );

                                throw new Dynasty\DogCharacteristics\Exceptions\IncompatibleException(json_encode($params));
                            }

                            // Stop trying at a certain point
                            if ($tries >= 2000)
                            {
                                $params = array(
                                    'characteristic' => $characteristic->name, 
                                );

                                throw new Dynasty\DogCharacteristics\Exceptions\UnresolvedException(json_encode($params));
                            }
                        }
                        // Dependency respected
                        else
                        {
                            // If we were in the loop, get out of it
                            if ($continueTryingToResolveDependency)
                            {
                                // Save the independent characteristics since they passed
                                foreach($independentRangedValues as $characteristicId => $rangedValue)
                                {
                                    $selectedCharacteristics[$characteristicId]['r'][($this->isFemale() ? 'f' : 'm')] = $rangedValue;
                                }

                                // Same with the genotypes they passed with
                                foreach($independentGenotypeIdsByLocusId as $locusId => $genotypeIds)
                                {
                                    $pooledGenotypeIdsByLocusId[$locusId] = array_key_exists($locusId, $pooledGenotypeIdsByLocusId)
                                        ? array_intersect($genotypeIds, $pooledGenotypeIdsByLocusId[$locusId])
                                        : $genotypeIds;
                                }

                                $continueTryingToResolveDependency = false;
                            }
                        }
                    }
                    else if ($dependency->outputsGenotypes())
                    {
                        // We no longer support X2G dependencies, but this check is here for legacy value
                        $continueTryingToResolveDependency = false;
                    }
                } while($continueTryingToResolveDependency);
            } // End dependency checks
        } // End going through each customized characteristic again for dependency checks

        // Store genotypes and phenotypes for later
        $genotypeIds   = [];
        $phenotypeIds  = [];
        $allBreedLoci  = [];
        $locusedBreeds = [];

        // Grab all active breeds
        $allBreeds = Breed::whereActive()->get();

        foreach($allBreeds as $allBreed)
        {
            // Grab the genotypes
            $genotypes = $this->isFemale()
                ? $allBreed->genotypes()->wherePivot('frequency', '>', 0)->whereActive()->whereAvailableToFemale()->get()->toArray()
                : $allBreed->genotypes()->wherePivot('frequency', '>', 0)->whereActive()->whereAvailableToMale()->get()->toArray();
            
            // We want to grab the genotypes from the active breeds so that there are no lethal genotypes grabbed
            $collected = array_collect($genotypes, 'locus_id');
            $allBreedLoci[$allBreed->id] = $collected;

            // We want to add the breed to the appropriate loci
            foreach($collected as $locusId => $genotypes)
            {
                if ( ! array_key_exists($locusId, $locusedBreeds))
                {
                    $locusedBreeds[$locusId] = [];
                }

                $locusedBreeds[$locusId][] = $allBreed->id;
            }
        }

        // Get all possible loci
        $loci = Locus::whereActive()->whereIn('id', array_keys($locusedBreeds))->get();

        // Remove empty loci from the pool
        $pooledGenotypeIdsByLocusId = array_filter($pooledGenotypeIdsByLocusId);

        // Save the possible genotypes
        $possibleGenotypes = [];

        // Make sure the breed has all the loci
        foreach($loci as $locus)
        {
            // Use the breed
            if (array_key_exists($locus->id, $allBreedLoci[$this->breed->id]))
            {
                $genotypes = $allBreedLoci[$this->breed->id][$locus->id];
            }
            // Not found in breed
            else
            {
                // Choose a random breed THAT HAS THIS LOCUS
                $index = mt_rand(0, count($locusedBreeds[$locus->id]) - 1);
                $randomBreedId = $locusedBreeds[$locus->id][$index];

                // Use its locus
                $genotypes = $allBreedLoci[$randomBreedId][$locus->id];
            }

            // Check the pool for possible genotypes this dog can get
            if (array_key_exists($locus->id, $pooledGenotypeIdsByLocusId))
            {
                // Filter the genotypes
                foreach($genotypes as $index => $genotype)
                {
                    // Remove the genotype if a pool exists on this locus, but this genotype is not in it
                    if ( ! in_array($genotype['id'], $pooledGenotypeIdsByLocusId[$locus->id]))
                    {
                        unset($genotypes[$index]);
                    }
                }
            }

            // Rekey the genotypes
            $genotypes = array_values($genotypes);

            // Adjust for frequencies
            foreach($genotypes as $genotype)
            {
                for ($i = 0; $i < $genotype['pivot']['frequency']; $i++)
                {
                    $possibleGenotypes[$locus->id][] = $genotype;
                }
            }
        }

        foreach($possibleGenotypes as $locusId => $genotypes)
        {
            // Choose one
            $genotype = array_random($genotypes);

            // Add them to save later
            $genotypeIds[$genotype['locus_id']] = $genotype['id'];
        }

        // Give the dog the genotypes
        $this->genotypes()->sync($genotypeIds);

        // Get all the phenotypes
        $phenotypes = Phenotype::all();

        // Cycle through the phenotypes
        foreach($phenotypes as $phenotype)
        {
            // Grab genotype ids
            $required = DB::table('phenotypes_genotypes')
                ->select('genotypes.locus_id', 'genotypes.id')
                ->join('genotypes', 'genotypes.id', '=', 'phenotypes_genotypes.genotype_id')
                ->join('loci', 'loci.id', '=', 'genotypes.locus_id')
                ->where('phenotypes_genotypes.phenotype_id', $phenotype->id)
                ->where('loci.active', true)
                ->lists('locus_id', 'id');

            // Check if the phenotype matched all required genotypes with the dog's genotypes
            $requiredIds   = array_keys($required);
            $matchesNeeded = count(array_unique($required));
            $matches       = count(array_intersect($requiredIds, $genotypeIds));

            if ($matches == $matchesNeeded)
            {
                // Give the dog the phenotype
                $phenotypeIds[] = $phenotype->id;
            }
        }

        // Give the dog the phenotypes
        $this->phenotypes()->sync($phenotypeIds);

        // Store the characteristics given to be added in one query 
        $dogCharacteristics = [];
        
        // Store the symtpoms given to be added in one query
        $dogCharacteristicSymptoms = [];

        // Save them for later for the dependencies
        $allCharacteristics   = [];
        $breedCharacteristics = [];
        $usedCharacteristics  = [];

        // Grab all of the breed characteristics in advance
        foreach($this->breed->characteristics()->whereActive()->get() as $breedCharacteristic)
        {
            $breedCharacteristics[$breedCharacteristic->characteristic_id] = $breedCharacteristic;
        }

        // We need to grab all of the characteristics in case a breed is missing one
        $characteristics = Characteristic::whereActive()
            // Do not give IS diseases
            ->where('type_id', '<>', Characteristic::TYPE_IMMUNE_SYSTEM_DISEASE)
            // Do not give old age
            ->where('type_id', '<>', Characteristic::TYPE_OLD_AGE)
            ->orderBy('id', 'asc')
            ->get();

        // Go through all the potential characteristics
        foreach($characteristics as $characteristic)
        {
            // Store it for later
            $allCharacteristics[$characteristic->id] = $characteristic;

            $useableCharacteristic = array_key_exists($characteristic->id, $breedCharacteristics)
                // If the breed characteristic exists, we'll use that
                ? $breedCharacteristics[$characteristic->id]
                // Otherwise, we're going to use the umbrella characteristic
                : $useableCharacteristic = $characteristic;

            // Store it for later
            $usedCharacteristics[$characteristic->id] = $useableCharacteristic;

            // Do genetic components
            $ageToRevealGenotypes  = $useableCharacteristic->getRandomAgeToRevealGenotypes();
            $ageToRevealPhenotypes = $useableCharacteristic->getRandomAgeToRevealPhenotypes();

            // Do ranged components
            if (array_key_exists($characteristic->id, $selectedCharacteristics) and array_key_exists('r', $selectedCharacteristics[$characteristic->id]))
            {
                // Ranged value was customized
                $finalRangedValue = $this->isFemale()
                    ? $selectedCharacteristics[$characteristic->id]['r']['f']
                    : $selectedCharacteristics[$characteristic->id]['r']['m'];
            }
            else
            {
                // Randomly generate ranged value
                $finalRangedValue = $this->isFemale()
                    ? $useableCharacteristic->getRandomRangedFemaleValue() 
                    : $useableCharacteristic->getRandomRangedMaleValue();
            }

            $ageToStopGrowing       = $useableCharacteristic->getRandomAgeToStopGrowing();
            $currentRangedValue     = DogCharacteristic::currentRangedValue($finalRangedValue, $ageToStopGrowing, $this->age);
            $ageToRevealRangedValue = $useableCharacteristic->getRandomAgeToRevealRangedValue();

            $filling = array(
                'characteristic_id'          => $characteristic->id, 
                'hide'                       => $useableCharacteristic->hide, 
                'in_summary'                 => false, 

                // Do genetics
                'age_to_reveal_genotypes'    => $ageToRevealGenotypes, 
                'genotypes_revealed'         => (in_array($characteristic->id, $genotypicCharacteristicIds) or ($characteristic->genotypesCanBeRevealed() and $this->age >= $ageToRevealGenotypes)), 
                'age_to_reveal_phenotypes'   => $ageToRevealPhenotypes, 
                'phenotypes_revealed'        => (in_array($characteristic->id, $phenotypicCharacteristicIds) or ($characteristic->phenotypesCanBeRevealed() and $this->age >= $ageToRevealPhenotypes)), 

                // Do ranged
                'final_ranged_value'         => $finalRangedValue, 
                'age_to_stop_growing'        => $ageToStopGrowing, 
                'current_ranged_value'       => $currentRangedValue, 
                'age_to_reveal_ranged_value' => $ageToRevealRangedValue, 
                'ranged_value_revealed'      => (in_array($characteristic->id, $selectedCharacteristics) or ($characteristic->rangedValueCanBeRevealed() and $this->age >= $ageToRevealRangedValue)), 
            );

            if ($characteristic->eligibleForSeverity($genotypeIds))
            {
                // Grab a nonlethal health severity if it exists
                if ( ! is_null($severity = $useableCharacteristic->getRandomSeverity(true, $this->age)))
                {
                    $filling['characteristic_severity_id']   = (is_a($severity, 'CharacteristicSeverity') ? $severity->id : $severity->characteristic_severity_id);
                    $filling['age_to_express_severity']      = $severity->getRandomAgeToExpress();
                    $filling['severity_expressed']           = ($severity->canBeExpressed() and $this->age >= $filling['age_to_express_severity']);
                    $filling['severity_value']               = $severity->getRandomValue();
                    $filling['age_to_reveal_severity_value'] = $severity->getRandomAgeToRevealSeverityValue();
                    $filling['severity_value_revealed']      = ($severity->valueCanBeRevealed() and $this->age >= $filling['age_to_reveal_severity_value']);

                    // Check if there are any symptoms that need to be attached
                    $orderedDogCharacteristicSymptoms[$filling['characteristic_severity_id']] = [];

                    foreach($severity->symptoms as $symptom)
                    {
                        $ageToExpress = $symptom->getRandomAgeToExpress($filling['age_to_express_severity']);

                        $dogCharacteristicSymptom = new DogCharacteristicSymptom;

                        $dogCharacteristicSymptom->dog_characteristic_id = null;
                        $dogCharacteristicSymptom->characteristic_severity_symptom_id = (is_a($symptom, 'CharacteristicSeveritySymptom') ? $symptom->id : $symptom->characteristic_severity_symptom_id);
                        $dogCharacteristicSymptom->age_to_express = $ageToExpress;

                        // Symptoms will always eventually be expressed
                        $dogCharacteristicSymptom->expressed = ($this->age >= $ageToExpress);

                        // !!! An imported dog should never be killed on completion
                        /*if ($dogCharacteristicSymptom->expressed and $symptom->isLethal())
                        {
                            // We need to kill the dog
                            $this->kill();

                            // Send a notification to the dog's owner
                            $params = array(
                                'symptom' => (is_a($symptom, 'CharacteristicSeveritySymptom') ? $symptom->name : $symptom->name), 
                                'dog'     => $this->nameplate(), 
                                'dogUrl'  => URL::route('dog/profile', $this->id), 
                                'pronoun' => ($this->isFemale() ? 'her' : 'his'), 
                            );

                            $body = Lang::get('notifications/dog.lethal_symptom.to_owner', array_map('htmlentities', array_dot($params)));
                            
                            $this->notify($body, UserNotification::TYPE_DANGER);
                        }*/

                        // Store the symptom to be added later
                        $orderedDogCharacteristicSymptoms[$filling['characteristic_severity_id']][] = $dogCharacteristicSymptom;
                    }
                }
            }

            // Fill the dog's characteristic and store it to be added later
            $dogCharacteristic = new DogCharacteristic;

            $dogCharacteristics[] = $dogCharacteristic->fill($filling);
        }

        // Attach the dog's characteristics
        $this->characteristics()->saveMany($dogCharacteristics);

        // We need to attach the genotypes, phenotypes, and symptoms to the newly saved dog characteristics
        $dogCharacteristics = $this->characteristics;

        // Store for saving later
        $dogCharacteristicSymptoms   = [];
        $dogCharacteristicGenotypes  = [];
        $dogCharacteristicPhenotypes = [];

        // Go through the dog's characteristics
        foreach($dogCharacteristics as $dogCharacteristic)
        {
            $characteristic = $allCharacteristics[$dogCharacteristic->characteristic_id];

            // Check if the characteristic has loci
            $locusIds = $characteristic->loci()->lists('id', 'id');

            if ( ! empty($locusIds))
            {
                $attachedPhenotypeIds = [];
                $attachedGenotypeIds  = [];

                // Attach the genotypes to the dog characteristic
                $attachedGenotypeIds = array_intersect_key($genotypeIds, $locusIds);

                foreach($attachedGenotypeIds as $attachedGenotypeId)
                {
                    $dogCharacteristicGenotypes[] = array(
                        'dog_characteristic_id' => $dogCharacteristic->id, 
                        'genotype_id'           => $attachedGenotypeId, 
                    );
                }

                // Go through each of the dog's phenotype ids
                foreach ($phenotypeIds as $phenotypeId)
                {
                    // Grab genotype ids
                    $required = DB::table('phenotypes_genotypes')
                        ->select('genotypes.locus_id', 'genotypes.id')
                        ->join('genotypes', 'genotypes.id', '=', 'phenotypes_genotypes.genotype_id')
                        ->join('loci', 'loci.id', '=', 'genotypes.locus_id')
                        ->where('phenotypes_genotypes.phenotype_id', $phenotypeId)
                        ->where('loci.active', true)
                        ->lists('locus_id', 'id');

                    $requiredIds   = array_values(array_unique($required));
                    $matchesNeeded = count($requiredIds);
                    $matches       = count(array_intersect($requiredIds, $locusIds));

                    if ($matches == $matchesNeeded)
                    {
                        // Give the dog the phenotype
                        $dogCharacteristicPhenotypes[] = array(
                            'dog_characteristic_id' => $dogCharacteristic->id, 
                            'phenotype_id'          => $phenotypeId, 
                        );
                    }
                }
            }

            // Check if we need to attach a symptom
            if ( ! is_null($dogCharacteristic->characteristic_severity_id))
            {
                foreach($orderedDogCharacteristicSymptoms[$dogCharacteristic->characteristic_severity_id] as $dogCharacteristicSymptom)
                {
                    // Assign it to the characteristic
                    $dogCharacteristicSymptom->dog_characteristic_id = $dogCharacteristic->id;

                    // Save it back in the array to be saved
                    $dogCharacteristicSymptoms[] = $dogCharacteristicSymptom->toArray();
                }
            }
        }

        // Insert the dog's symptoms
        if ( ! empty($dogCharacteristicSymptoms))
        {
            DB::table('dog_characteristic_symptoms')->insert($dogCharacteristicSymptoms);
        }

        // Attach the genotypes to the characteristics
        if ( ! empty($dogCharacteristicGenotypes))
        {
            DB::table('dog_characteristic_genotypes')->insert($dogCharacteristicGenotypes);
        }

        // Attach the phenotypes to the characteristics
        if ( ! empty($dogCharacteristicPhenotypes))
        {
            DB::table('dog_characteristic_phenotypes')->insert($dogCharacteristicPhenotypes);
        }

        // Grab the dog's characteristics again
        // FASTER: Selecting everything again is quicker than lazy loading the genotypes, phenotypes and dependencies for the dog's characteristics
        $dogCharacteristics = $this
            ->load('characteristics.characteristic.dependencies', 'characteristics.genotypes', 'characteristics.phenotypes')
            ->characteristics()
            ->get();

        // We need to go back through the dog characteristics to do the dependency checks, but only on the dependent characteristics
        foreach($dogCharacteristics as $dogCharacteristic)
        {
            $characteristic = $dogCharacteristic->characteristic;

            // !!! DO NOT check on dependencies if this characteristic was involved in customization
            if ($characteristic->isDependent() and ! array_key_exists($characteristic->id, $selectedCharacteristics))
            {
                foreach($characteristic->dependencies as $dependency)
                {
                    if ($dependency->isActive())
                    {
                        if ($dependency->takesInRanged())
                        {
                            // Get the independent characteristics range values for this dog
                            $independentRangedValues = DB::table('characteristic_dependency_ind_characteristics')
                                ->select('characteristic_dependency_ind_characteristics.independent_characteristic_id', 'dog_characteristics.final_ranged_value')
                                ->join('dog_characteristics', 'dog_characteristics.characteristic_id', '=', 'characteristic_dependency_ind_characteristics.independent_characteristic_id')
                                ->where('dog_characteristics.dog_id', $this->id)
                                ->where('characteristic_dependency_ind_characteristics.characteristic_dependency_id', $dependency->id)
                                ->lists('final_ranged_value', 'independent_characteristic_id');
                        }
                        else if ($dependency->takesInGenotypes())
                        {
                            // Get the independent characteristics genotypes for this dog
                            $independentGenotypeIds = DB::table('characteristic_dependency_ind_characteristics')
                                ->select('genotypes.locus_id', 'dog_characteristic_genotypes.genotype_id')
                                ->join('dog_characteristics', 'dog_characteristics.characteristic_id', '=', 'characteristic_dependency_ind_characteristics.independent_characteristic_id')
                                ->join('dog_characteristic_genotypes', 'dog_characteristic_genotypes.dog_characteristic_id', '=', 'dog_characteristics.id')
                                ->join('genotypes', 'genotypes.id', '=', 'dog_characteristic_genotypes.genotype_id')

                                ->where('dog_characteristics.dog_id', $this->id)
                                ->where('characteristic_dependency_ind_characteristics.characteristic_dependency_id', $dependency->id)
                                ->lists('genotype_id', 'locus_id');
                        }

                        if ($dependency->outputsRanged())
                        {
                            // Get this characteristics dependent value for this dog
                            $finalRangedValue = $dogCharacteristic->final_ranged_value;

                            $newRangedValue = $finalRangedValue;

                            // Do the dependencies
                            if ($dependency->isR2R())
                            {
                                $newRangedValue = $dependency->doR2R($finalRangedValue, $independentRangedValues);
                            }
                            else if ($dependency->isG2R())
                            {
                                $newRangedValue = $dependency->doG2R($finalRangedValue, $independentGenotypeIds);
                            }

                            // Only need to update and bind if it changed
                            if (Floats::compare($finalRangedValue, $newRangedValue, '!='))
                            {
                                // We need to bind to the used characteristic itself
                                $usedCharacteristic = $usedCharacteristics[$characteristic->id];

                                // Bind the value
                                $finalRangedValue = $this->isFemale()
                                    ? $usedCharacteristic->bindRangedFemaleValue($newRangedValue) 
                                    : $usedCharacteristic->bindRangedMaleValue($newRangedValue);

                                // Adjust for growth
                                $currentRangedValue = DogCharacteristic::currentRangedValue($finalRangedValue, $dogCharacteristic->age_to_stop_growing, $this->age);

                                // Save it back on the characteristic
                                $dogCharacteristic->final_ranged_value   = $finalRangedValue;
                                $dogCharacteristic->current_ranged_value = $currentRangedValue;

                                // Save it IMMEDIATELY
                                $dogCharacteristic->save();
                            }
                        }
                        else if ($dependency->outputsGenotypes())
                        {
                            // We no longer support X2G dependencies, but this check is here for legacy value
                        }
                    }
                }
            }
            // The characteristic was customized, so perform all tests on it while ignoring the age restrictions
            else if (in_array($characteristic->id, $originalSelectedCharacteristics))
            {
                $tests = $characteristic->tests()->whereActive()->get();

                foreach($tests as $test)
                {
                    $test->performOnDogCharacteristic($dogCharacteristic);
                }
            }
        }

        // Once the characteristics are assigned, need to check breedability
        $this->sexually_mature = $this->checkSexualMaturity();
        $this->sexual_decline  = $this->checkSexualDecline();
        $this->infertile       = $this->checkInfertility();

        // Give them an imported pedigree
        $pedigree = Pedigree::imported();

        $pedigree->dog_id = $this->id;

        if ( ! $pedigree->save())
        {
            throw new Dynasty\Pedigrees\Exceptions\NotSavedException;
        }

        // Calculate the COI of the pedigree
        $this->coi = $pedigree->calculateCoi();

        // Save everything
        $this->save();
    }

    /**
     * @throws Dynasty\Dogs\Exceptions\ImportedException
     * @throws Dynasty\Pedigrees\Exceptions\NotSavedException
     */
    private function _completePuppy()
    {
        if (is_null($this->litter))
        {
            throw new Dynasty\Dogs\Exceptions\ImportedException;
        }

        // Grab the parents
        $dam  = $this->litter->dam;
        $sire = $this->litter->sire;

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

        // Get the genotypes from the dam
        $damGenotypes = $dam->genotypes;

        // Get the genotypes from the sire
        $sireGenotypes = $sire->genotypes;

        // Group by locus
        $groupedPossibleGenotypes = [];

        foreach($damGenotypes as $damGenotype)
        {
            $groupedPossibleGenotypes[$damGenotype->locus_id][] = $damGenotype;
        }

        foreach($sireGenotypes as $sireGenotype)
        {
            $groupedPossibleGenotypes[$sireGenotype->locus_id][] = $sireGenotype;
        }

        // Grab all active breeds genotypes in case a genotype fails to be added from the parents
        $activeBreedGenotypeIds = DB::table('breed_genotypes')->where('frequency', '>', 0)->lists('genotype_id');

        $activeBreedGenotypes = empty($activeBreedGenotypeIds)
            ? Genotype::whereActive()->get()
            : Genotype::whereActive()->whereNotIn('id', $activeBreedGenotypeIds)->get();

        // Group by locus
        $groupedActiveBreedGenotypes = [];

        foreach($activeBreedGenotypes as $activeBreedGenotype)
        {
            $groupedActiveBreedGenotypes[$activeBreedGenotype->locus_id][] = $activeBreedGenotype;
        }

        // Grab all possible locus IDs
        $locusIds = Locus::whereActive()->lists('id');

        // Get all the phenotypes
        $phenotypes = Phenotype::all();

        // Grab the dam's characteristic IDs
        $damCharacteristics = $dam->characteristics()->get();

        // Grab the sire's characteristics IDs
        $sireCharacteristics = $sire->characteristics()->get();

        // Merge them
        $parentCharacteristicIds = array_merge($damCharacteristics->lists('characteristic_id'), $sireCharacteristics->lists('characteristic_id'));

        // Always add -1
        $parentCharacteristicIds[] = -1;

        // Sort the parent characteristics by characteristic ID
        $sortedDamCharacteristics  = [];
        $sortedSireCharacteristics = [];

        foreach($damCharacteristics as $damCharacteristic)
        {
            $sortedDamCharacteristics[$damCharacteristic->characteristic_id] = $damCharacteristic;
        }

        foreach($sireCharacteristics as $sireCharacteristic)
        {
            $sortedSireCharacteristics[$sireCharacteristic->characteristic_id] = $sireCharacteristic;
        }

        // Get the visible dam characteristics
        $visibleDamCharacteristicIds = $damCharacteristics->filter(function($item)
            {
                return $item->isVisible();
            });

        // Get the visible sire characteristics
        $visibleSireCharacteristicIds = $sireCharacteristics->filter(function($item)
            {
                return $item->isVisible();
            });

        $visibleCharacteristicIds = array_merge($visibleDamCharacteristicIds->lists('characteristic_id'), $visibleSireCharacteristicIds->lists('characteristic_id'));

        // Store the summarized characteristic ids from the dam
        $damSummaryCharacteristicIds = $damCharacteristics->filter(function($item)
            {
                return $item->isInSummary();
            })
            ->lists('characteristic_id');

        // If there is a breed, use that to grab the characteristics
        if (is_null($this->breed))
        {
            // Find all possible characteristics the puppies could get
            $allCharacteristics = Characteristic::whereActive()
                    ->where('type_id', '<>', Characteristic::TYPE_OLD_AGE)
                    ->where('type_id', '<>', Characteristic::TYPE_IMMUNE_SYSTEM_DISEASE)
                    ->whereIn('id', $parentCharacteristicIds)
                    ->get();
        }
        else
        {
            // Find all possible characteristics the puppies could get
            $allCharacteristics = $this->breed->characteristics()->with('characteristic')
                    ->whereHas('characteristic', function($query)
                        {
                            $query
                                ->where('type_id', '<>', Characteristic::TYPE_OLD_AGE)
                                ->where('type_id', '<>', Characteristic::TYPE_IMMUNE_SYSTEM_DISEASE);
                        })
                    ->whereActive()
                    ->whereIn('characteristic_id', $parentCharacteristicIds)
                    ->get();
        }

        $validCharacteristics = $allCharacteristics->filter(function($item)
            {
                return $item->isValid();
            });

        // Go through the possible valid characteristics to get parent possibilities
        $parentCharacteristicData = [];

        foreach($validCharacteristics as $useableCharacteristic)
        {
            $characteristic = is_a($useableCharacteristic, 'BreedCharacteristic')
                ? $useableCharacteristic->characteristic
                : $useableCharacteristic;

            $damAgeToRevealGenotypes     = $sireAgeToRevealGenotypes     = null;
            $damAgeToRevealPhenotypes    = $sireAgeToRevealPhenotypes    = null;
            $damFinalRangedValue         = $sireFinalRangedValue         = null;
            $damAgeToStopGrowing         = $sireAgeToStopGrowing         = null;
            $damAgeToRevealRangedValue   = $sireAgeToRevealRangedValue   = null;
            $damAgeToExpressSeverity     = $sireAgeToExpressSeverity     = null;
            $damSeverityValue            = $sireSeverityValue            = null;
            $damAgeToRevealSeverityValue = $sireAgeToRevealSeverityValue = null;

            // Get the parent's characteristic data
            if (array_key_exists($characteristic->id, $sortedDamCharacteristics))
            {
                $damCharacteristic = $sortedDamCharacteristics[$characteristic->id];

                $damAgeToRevealGenotypes  = $damCharacteristic->age_to_reveal_genotypes;
                $damAgeToRevealPhenotypes = $damCharacteristic->age_to_reveal_phenotypes;

                $damFinalRangedValue       = $damCharacteristic->final_ranged_value;
                $damAgeToStopGrowing       = $damCharacteristic->age_to_stop_growing;
                $damAgeToRevealRangedValue = $damCharacteristic->age_to_reveal_ranged_value;

                $damAgeToExpressSeverity     = $damCharacteristic->age_to_express_severity;
                $damSeverityValue            = $damCharacteristic->severity_value;
                $damAgeToRevealSeverityValue = $damCharacteristic->age_to_reveal_severity_value;
            }
            else // If the dam doesn't have it, the sire must
            {
                // Need to use the sire characteristic's values
                $sireCharacteristic = $sortedSireCharacteristics[$characteristic->id];

                $damAgeToRevealGenotypes  = $sireCharacteristic->age_to_reveal_genotypes;
                $damAgeToRevealPhenotypes = $sireCharacteristic->age_to_reveal_phenotypes;

                $damFinalRangedValue       = $sireCharacteristic->final_ranged_value;
                $damAgeToStopGrowing       = $sireCharacteristic->age_to_stop_growing;
                $damAgeToRevealRangedValue = $sireCharacteristic->age_to_reveal_ranged_value;

                $damAgeToExpressSeverity     = $sireCharacteristic->age_to_express_severity;
                $damSeverityValue            = $sireCharacteristic->severity_value;
                $damAgeToRevealSeverityValue = $sireCharacteristic->age_to_reveal_severity_value;
            }

            if (array_key_exists($characteristic->id, $sortedSireCharacteristics))
            {
                $sireCharacteristic = $sortedSireCharacteristics[$characteristic->id];

                $sireAgeToRevealGenotypes  = $sireCharacteristic->age_to_reveal_genotypes;
                $sireAgeToRevealPhenotypes = $sireCharacteristic->age_to_reveal_phenotypes;

                $sireFinalRangedValue       = $sireCharacteristic->final_ranged_value;
                $sireAgeToStopGrowing       = $sireCharacteristic->age_to_stop_growing;
                $sireAgeToRevealRangedValue = $sireCharacteristic->age_to_reveal_ranged_value;

                $sireAgeToExpressSeverity     = $sireCharacteristic->age_to_express_severity;
                $sireSeverityValue            = $sireCharacteristic->severity_value;
                $sireAgeToRevealSeverityValue = $sireCharacteristic->age_to_reveal_severity_value;
            }
            else // If the sire doesn't have it, the dam must
            {
                // Need to use the dam characteristic's values
                $damCharacteristic = $sortedDamCharacteristics[$characteristic->id];

                $sireAgeToRevealGenotypes  = $damCharacteristic->age_to_reveal_genotypes;
                $sireAgeToRevealPhenotypes = $damCharacteristic->age_to_reveal_phenotypes;

                $sireFinalRangedValue       = $damCharacteristic->final_ranged_value;
                $sireAgeToStopGrowing       = $damCharacteristic->age_to_stop_growing;
                $sireAgeToRevealRangedValue = $damCharacteristic->age_to_reveal_ranged_value;

                $sireAgeToExpressSeverity     = $damCharacteristic->age_to_express_severity;
                $sireSeverityValue            = $damCharacteristic->severity_value;
                $sireAgeToRevealSeverityValue = $damCharacteristic->age_to_reveal_severity_value;
            }

            // Do genetic components
            list($minAgeToRevealGenotypes, $maxAgeToRevealGenotypes)   = Litter::calculateBredRanges($damAgeToRevealGenotypes, $damFertilityDropOffValue, $sireAgeToRevealGenotypes, $sireFertilityDropOffValue, $characteristic->min_age_to_reveal_genotypes, $characteristic->max_age_to_reveal_genotypes);
            list($minAgeToRevealPhenotypes, $maxAgeToRevealPhenotypes) = Litter::calculateBredRanges($damAgeToRevealPhenotypes, $damFertilityDropOffValue, $sireAgeToRevealPhenotypes, $sireFertilityDropOffValue, $characteristic->min_age_to_reveal_phenotypes, $characteristic->max_age_to_reveal_phenotypes);

            // Do ranged components
            list($minFinalRangedValue, $maxFinalRangedValue)             = Litter::calculateBredRanges($damFinalRangedValue, $damFertilityDropOffValue, $sireFinalRangedValue, $sireFertilityDropOffValue, $characteristic->min_ranged_value, $characteristic->max_ranged_value);
            list($minAgeToStopGrowing, $maxAgeToStopGrowing)             = Litter::calculateBredRanges($damAgeToStopGrowing, $damFertilityDropOffValue, $sireAgeToStopGrowing, $sireFertilityDropOffValue, $characteristic->min_age_to_stop_growing, $characteristic->max_age_to_stop_growing);
            list($minAgeToRevealRangedValue, $maxAgeToRevealRangedValue) = Litter::calculateBredRanges($damAgeToRevealRangedValue, $damFertilityDropOffValue, $sireAgeToRevealRangedValue, $sireFertilityDropOffValue, $characteristic->min_age_to_reveal_ranged_value, $characteristic->max_age_to_reveal_ranged_value);

            // Do health components
            list($minAgeToExpressSeverity, $maxAgeToExpressSeverity) = Litter::calculateBredRanges($damAgeToExpressSeverity, $damFertilityDropOffValue, $sireAgeToExpressSeverity, $sireFertilityDropOffValue, -99999, 99999);
            list($minSeverityValue, $maxSeverityValue) = Litter::calculateBredRanges($damSeverityValue, $damFertilityDropOffValue, $sireSeverityValue, $sireFertilityDropOffValue, -99999, 99999);
            list($minAgeToRevealSeverityValue, $maxAgeToRevealSeverityValue) = Litter::calculateBredRanges($damAgeToRevealSeverityValue, $damFertilityDropOffValue, $sireAgeToRevealSeverityValue, $sireFertilityDropOffValue, -99999, 99999);

            $parentCharacteristicData[$characteristic->id] = array(
                'age_to_reveal_genotypes' => array(
                    'min' => $minAgeToRevealGenotypes, 
                    'max' => $maxAgeToRevealGenotypes, 
                ), 
                'age_to_reveal_phenotypes' => array(
                    'min' => $minAgeToRevealPhenotypes, 
                    'max' => $maxAgeToRevealPhenotypes, 
                ), 
                'final_ranged_value' => array(
                    'min' => $minFinalRangedValue, 
                    'max' => $maxFinalRangedValue, 
                ), 
                'age_to_stop_growing' => array(
                    'min' => $minAgeToStopGrowing, 
                    'max' => $maxAgeToStopGrowing, 
                ), 
                'age_to_reveal_ranged_value' => array(
                    'min' => $minAgeToRevealRangedValue, 
                    'max' => $maxAgeToRevealRangedValue, 
                ),  
                'severity_id' => array(
                    'dam'  => $damCharacteristic->severity_id, 
                    'sire' => $sireCharacteristic->severity_id, 
                ),  
                'age_to_express_severity' => array(
                    'min' => $minAgeToExpressSeverity, 
                    'max' => $maxAgeToExpressSeverity, 
                ),  
                'severity_value' => array(
                    'min' => $minSeverityValue, 
                    'max' => $maxSeverityValue, 
                ),  
                'age_to_reveal_severity_value' => array(
                    'min' => $minAgeToRevealSeverityValue, 
                    'max' => $maxAgeToRevealSeverityValue, 
                ),  
            );
        }

        // Store genotypes and phenotypes for later
        $genotypeIds  = [];
        $phenotypeIds = [];

        // Go thorugh each locus
        foreach($locusIds as $locusId)
        {
            $genotypes = [];

            // Check if the locus was found in the parents
            if (array_key_exists($locusId, $groupedPossibleGenotypes))
            {
                // Grab the possible genotypes at the locus
                $possibleGenotypes = $groupedPossibleGenotypes[$locusId];

                // Only one parent had the locus
                if (count($possibleGenotypes) == 1)
                {
                    $possibleGenotype = $possibleGenotypes[0];

                    // Check if the dog's sex can have that genotype
                    if ($possibleGenotype->checkSex($this->sex))
                    {
                        $genotypes[] = $possibleGenotype;
                    }
                }
                else // Both parents had the locus
                {
                    // Grab the two genotypes used in the punnet square
                    $genotypeA = $possibleGenotypes[0];
                    $genotypeB = $possibleGenotypes[1];

                    // Perform punnet square
                    $punnetGenotypes = $genotypeA->punnetSquare($genotypeB);

                    // Filter them by sex
                    foreach($punnetGenotypes as $punnetGenotype)
                    {
                        if ($punnetGenotype->checkSex($this->sex))
                        {
                            $genotypes[] = $punnetGenotype;
                        }
                    }
                }
            }

            // None could be found on the parents
            if (empty($genotypes) and array_key_exists($locusId, $groupedActiveBreedGenotypes))
            {
                // Grab all possible genotypes from the active breeds
                $locusedActiveBreedGenotypes = $groupedActiveBreedGenotypes[$locusId];

                // Filter them by sex
                foreach($locusedActiveBreedGenotypes as $locusedActiveBreedGenotype)
                {
                    if ($locusedActiveBreedGenotype->checkSex($this->sex))
                    {
                        $genotypes[] = $locusedActiveBreedGenotype;
                    }
                }
            }

            // At least one viable genotype was found
            if ( ! empty($genotypes))
            {
                // Choose a random one
                $index    = mt_rand(0, count($genotypes) - 1);
                $genotype = $genotypes[$index];

                // Store it
                $genotypeIds[$locusId] = $genotype->id;
            }
        }

        // Give the puppy the genotypes
        $this->genotypes()->sync($genotypeIds);

        // Cycle through the phenotypes
        foreach($phenotypes as $phenotype)
        {
            // Grab genotype ids
            $required = DB::table('phenotypes_genotypes')
                ->select('genotypes.locus_id', 'genotypes.id')
                ->join('genotypes', 'genotypes.id', '=', 'phenotypes_genotypes.genotype_id')
                ->join('loci', 'loci.id', '=', 'genotypes.locus_id')
                ->where('phenotypes_genotypes.phenotype_id', $phenotype->id)
                ->where('loci.active', true)
                ->lists('locus_id', 'id');

            // Check if the phenotype matched all required genotypes with the puppy's genotypes
            $requiredIds   = array_keys($required);
            $matchesNeeded = count(array_unique($required));
            $matches       = count(array_intersect($requiredIds, $genotypeIds));

            if ($matches == $matchesNeeded)
            {
                // Give the puppy the phenotype
                $phenotypeIds[] = $phenotype->id;
            }
        }

        // Give the puppy the phenotypes
        $this->phenotypes()->sync($phenotypeIds);

        // Store the characteristics given to be added in one query 
        $puppyCharacteristics = [];

        // Save them for later for the dependencies
        $allCharacteristics  = [];
        $usedCharacteristics = [];

        // Go through the possible valid characteristics
        foreach($validCharacteristics as $useableCharacteristic)
        {
            $characteristic = is_a($useableCharacteristic, 'BreedCharacteristic')
                ? $useableCharacteristic->characteristic
                : $useableCharacteristic;

            // Store them for later
            $usedCharacteristics[$characteristic->id] = $useableCharacteristic;
            $allCharacteristics[$characteristic->id]  = $characteristic;

            // Get the ranges to use
            $parentCharacteristic = $parentCharacteristicData[$characteristic->id];

            // Do genetic components
            $ageToRevealGenotypes = is_null($parentCharacteristic['age_to_reveal_genotypes']['min'])
                ? null
                : mt_rand($parentCharacteristic['age_to_reveal_genotypes']['min'], $parentCharacteristic['age_to_reveal_genotypes']['max']);

            $ageToRevealPhenotypes = is_null($parentCharacteristic['age_to_reveal_phenotypes']['min'])
                ? null
                : mt_rand($parentCharacteristic['age_to_reveal_phenotypes']['min'], $parentCharacteristic['age_to_reveal_phenotypes']['max']);

            // Do ranged components
            $finalRangedValue = is_null($parentCharacteristic['final_ranged_value']['min'])
                ? null
                : mt_rand($parentCharacteristic['final_ranged_value']['min'] * 100, $parentCharacteristic['final_ranged_value']['max'] * 100) / 100.00;

            $ageToStopGrowing = is_null($parentCharacteristic['age_to_stop_growing']['min'])
                ? null
                : mt_rand($parentCharacteristic['age_to_stop_growing']['min'], $parentCharacteristic['age_to_stop_growing']['max']);

            $ageToRevealRangedValue = is_null($parentCharacteristic['age_to_reveal_ranged_value']['min'])
                ? null
                : mt_rand($parentCharacteristic['age_to_reveal_ranged_value']['min'], $parentCharacteristic['age_to_reveal_ranged_value']['max']);

            $currentRangedValue = DogCharacteristic::currentRangedValue($finalRangedValue, $ageToStopGrowing, $this->age);

            $filling = array(
                'characteristic_id'          => $characteristic->id, 
                'hide'                       => ( ! in_array($characteristic->id, $visibleCharacteristicIds)), 
                'in_summary'                 => in_array($characteristic->id, $damSummaryCharacteristicIds), 

                // Do genetics
                'age_to_reveal_genotypes'    => $ageToRevealGenotypes, 
                'genotypes_revealed'         => ($characteristic->genotypesCanBeRevealed() and $this->age >= $ageToRevealGenotypes), 
                'age_to_reveal_phenotypes'   => $ageToRevealPhenotypes, 
                'phenotypes_revealed'        => ($characteristic->phenotypesCanBeRevealed() and $this->age >= $ageToRevealPhenotypes), 

                // Do ranged
                'final_ranged_value'         => $finalRangedValue, 
                'age_to_stop_growing'        => $ageToStopGrowing, 
                'current_ranged_value'       => $currentRangedValue, 
                'age_to_reveal_ranged_value' => $ageToRevealRangedValue, 
                'ranged_value_revealed'      => ($characteristic->rangedValueCanBeRevealed() and $this->age >= $ageToRevealRangedValue), 
            );

            if ($characteristic->eligibleForSeverity($genotypeIds))
            {
                $severity = null;

                // Check if parents have a severity on this characteristic
                if (is_null($parentCharacteristic['severity_id']['dam']) and is_null($parentCharacteristic['severity_id']['sire']))
                {
                    // Grab a random severity
                    if ( ! is_null($severity = $useableCharacteristic->getRandomSeverity(false, $this->age)))
                    {
                        $severityValue = $severity->getRandomValue();
                        $ageToExpressSeverity     = $severity->getRandomAgeToExpress();
                        $ageToRevealSeverityValue = $severity->getRandomAgeToRevealSeverityValue();
                    }
                }
                else // At least one parent has one
                {
                    $severityValue = is_null($parentCharacteristic['severity_value']['min'])
                        ? null
                        : mt_rand($parentCharacteristic['severity_value']['min'] * 100, $parentCharacteristic['severity_value']['max'] * 100) / 100.00;

                    // Find the associated severities
                    $severities = CharacteristicSeverity::where('characteristic_id', $characteristic->id)
                        ->where('min_value', '<=', $severityValue)
                        ->where('max_value', '>=', $severityValue)
                        ->get();

                    // Get a random one
                    if ( ! is_null($severity = $severities->random()))
                    {
                        $ageToExpressSeverity = is_null($parentCharacteristic['age_to_express_severity']['min'])
                            ? null
                            : mt_rand($parentCharacteristic['age_to_express_severity']['min'], $parentCharacteristic['age_to_express_severity']['max']);

                        $ageToRevealSeverityValue = is_null($parentCharacteristic['age_to_reveal_severity_value']['min'])
                            ? null
                            : mt_rand($parentCharacteristic['age_to_reveal_severity_value']['min'], $parentCharacteristic['age_to_reveal_severity_value']['max']);

                        $minAgeToExpressSeverity = $severity->min_age_to_express;
                        $maxAgeToExpressSeverity = $severity->max_age_to_express;

                        $minAgeToRevealSeverityValue = $severity->min_age_to_reveal_value;
                        $maxAgeToRevealSeverityValue = $severity->max_age_to_reveal_value;

                        // Check if there is a breed equivalent for the severity
                        if (is_a($useableCharacteristic, 'BreedCharacteristic'))
                        {
                            $breedSeverity = $useableCharacteristic->severities()->where('characteristic_severity_id', $severity->id)->first();

                            if ( ! is_null($breedSeverity))
                            {
                                $minAgeToExpressSeverity = $breedSeverity->min_age_to_express;
                                $maxAgeToExpressSeverity = $breedSeverity->max_age_to_express;

                                $minAgeToRevealSeverityValue = $breedSeverity->min_age_to_reveal_value;
                                $maxAgeToRevealSeverityValue = $breedSeverity->max_age_to_reveal_value;
                            }
                        }

                        // Bind them
                        $ageToExpressSeverity     = min($maxAgeToExpressSeverity, max($minAgeToExpressSeverity, $ageToExpressSeverity));
                        $ageToRevealSeverityValue = min($maxAgeToRevealSeverityValue, max($minAgeToRevealSeverityValue, $ageToRevealSeverityValue));
                    }
                }

                if ( ! is_null($severity))
                {
                    $filling['characteristic_severity_id']   = (is_a($severity, 'CharacteristicSeverity') ? $severity->id : $severity->characteristic_severity_id);
                    $filling['age_to_express_severity']      = $ageToExpressSeverity;
                    $filling['severity_expressed']           = ($severity->canBeExpressed() and $this->age >= $ageToExpressSeverity);
                    $filling['severity_value']               = $severityValue;
                    $filling['age_to_reveal_severity_value'] = $ageToRevealSeverityValue;
                    $filling['severity_value_revealed']      = ($severity->valueCanBeRevealed() and $this->age >= $ageToRevealSeverityValue);

                    // Check if there are any symptoms that need to be attached
                    $orderedPuppyCharacteristicSymptoms[$filling['characteristic_severity_id']] = [];

                    foreach($severity->symptoms as $symptom)
                    {
                        $ageToExpress = $symptom->getRandomAgeToExpress($ageToExpressSeverity);

                        $puppyCharacteristicSymptom = new DogCharacteristicSymptom;

                        $puppyCharacteristicSymptom->dog_characteristic_id = null;
                        $puppyCharacteristicSymptom->characteristic_severity_symptom_id = (is_a($symptom, 'CharacteristicSeveritySymptom') ? $symptom->id : $symptom->characteristic_severity_symptom_id);
                        $puppyCharacteristicSymptom->age_to_express = $ageToExpress;

                        // Symptoms will always eventually be expressed
                        $puppyCharacteristicSymptom->expressed = ($this->age >= $ageToExpress);

                        if ($puppyCharacteristicSymptom->expressed and $symptom->isLethal())
                        {
                            // We need to kill the puppy
                            $this->kill();

                            if ( ! is_null($dam->owner))
                            {
                                // Send a notification to the dog's owner
                                $params = array(
                                    'symptom' => (is_a($symptom, 'CharacteristicSeveritySymptom') ? $symptom->symptom->name : $symptom->characteristicSeveritySymptom->symptom->name), 
                                    'dog'     => $this->nameplate(), 
                                    'dogUrl'  => URL::route('dog/profile', $this->id), 
                                    'pronoun' => ($this->isFemale() ? 'her' : 'his'), 
                                );

                                $body = Lang::get('notifications/dog.lethal_symptom.to_owner', array_map('htmlentities', array_dot($params)));
                                
                                $dam->owner->notify($body, UserNotification::TYPE_DANGER);
                            }

                            // Let's reload the puppy
                            $puppy = Dog::find($this->id);
                        }

                        // Store the symptom to be added later
                        $orderedPuppyCharacteristicSymptoms[$filling['characteristic_severity_id']][] = $puppyCharacteristicSymptom;
                    }
                }
            }

            // Fill the puppy's characteristic and store it to be added later
            $puppyCharacteristic = new DogCharacteristic;

            $puppyCharacteristics[] = $puppyCharacteristic->fill($filling);                    
        }

        // Attach the puppy's characteristics
        $this->characteristics()->saveMany($puppyCharacteristics);

        // We need to attach the genotypes, phenotypes, and symptoms to the newly saved puppy characteristics
        $puppyCharacteristics = $this->characteristics()->with('characteristic')->get();

        // Store for saving later
        $puppyCharacteristicSymptoms   = [];
        $puppyCharacteristicGenotypes  = [];
        $puppyCharacteristicPhenotypes = [];

        // Go through the puppy's characteristics
        foreach($puppyCharacteristics as $puppyCharacteristic)
        {
            $characteristic = $puppyCharacteristic->characteristic;

            // Check if the characteristic has loci
            $locusIds = $characteristic->loci()->lists('id', 'id');

            if ( ! empty($locusIds))
            {
                $attachedPhenotypeIds = [];
                $attachedGenotypeIds  = [];

                // Attach the genotypes to the puppy characteristic
                $attachedGenotypeIds = array_intersect_key($genotypeIds, $locusIds);

                foreach($attachedGenotypeIds as $attachedGenotypeId)
                {
                    $puppyCharacteristicGenotypes[] = array(
                        'dog_characteristic_id' => $puppyCharacteristic->id, 
                        'genotype_id'           => $attachedGenotypeId, 
                    );
                }

                // Go through each of the puppy's phenotype ids
                foreach ($phenotypeIds as $phenotypeId)
                {
                    // Grab genotype ids
                    $required = DB::table('phenotypes_genotypes')
                        ->select('genotypes.locus_id', 'genotypes.id')
                        ->join('genotypes', 'genotypes.id', '=', 'phenotypes_genotypes.genotype_id')
                        ->join('loci', 'loci.id', '=', 'genotypes.locus_id')
                        ->where('phenotypes_genotypes.phenotype_id', $phenotypeId)
                        ->where('loci.active', true)
                        ->lists('locus_id', 'id');

                    $requiredIds   = array_values(array_unique($required));
                    $matchesNeeded = count($requiredIds);
                    $matches       = count(array_intersect($requiredIds, $locusIds));

                    if ($matches == $matchesNeeded)
                    {
                        // Give the puppy the phenotype
                        $puppyCharacteristicPhenotypes[] = array(
                            'dog_characteristic_id' => $puppyCharacteristic->id, 
                            'phenotype_id'          => $phenotypeId, 
                        );
                    }
                }
            }

            // Check if we need to attach a symptom
            if ( ! is_null($puppyCharacteristic->characteristic_severity_id))
            {
                foreach($orderedPuppyCharacteristicSymptoms[$puppyCharacteristic->characteristic_severity_id] as $puppyCharacteristicSymptom)
                {
                    // Assign it to the characteristic
                    $puppyCharacteristicSymptom->dog_characteristic_id = $puppyCharacteristic->id;

                    // Save it back in the array to be saved
                    $puppyCharacteristicSymptoms[] = $puppyCharacteristicSymptom->toArray();
                }
            }
        }

        // Insert the puppy's symptoms
        if ( ! empty($puppyCharacteristicSymptoms))
        {
            DB::table('dog_characteristic_symptoms')->insert($puppyCharacteristicSymptoms);
        }

        // Attach the genotypes to the characteristics
        if ( ! empty($puppyCharacteristicGenotypes))
        {
            DB::table('dog_characteristic_genotypes')->insert($puppyCharacteristicGenotypes);
        }

        // Attach the phenotypes to the characteristics
        if ( ! empty($puppyCharacteristicPhenotypes))
        {
            DB::table('dog_characteristic_phenotypes')->insert($puppyCharacteristicPhenotypes);
        }

        // Need to adjust the immune system of the puppy
        $immuneSystem = $this->getImmuneSystem();

        if ( ! is_null($immuneSystem))
        {
            $basePoint        = $immuneSystem->current_ranged_value / 100.00;
            $newImmuneSystem  = $basePoint - (($damFertilityDropOffValue / 100.00) + ($sireFertilityDropOffValue / 100.00)) * ($basePoint / 2.00);
            $newImmuneSystem *= 100.00;

            $immuneSystem->final_ranged_value = $immuneSystem->current_ranged_value = $newImmuneSystem * 100.00;
            $immuneSystem->save();
        }

        // Grab the puppy's characteristics again
        // FASTER: Selecting everything again is quicker than lazy loading the genotypes, phenotypes and dependencies for the dog's characteristics
        $puppyCharacteristics = $this
            ->load('characteristics.characteristic.dependencies', 'characteristics.genotypes', 'characteristics.phenotypes')
            ->characteristics()
            ->whereDependent()
            ->get();

        // We need to go back through the puppy characteristics to do the dependency checks, but only on the dependent characteristics
        foreach($puppyCharacteristics as $puppyCharacteristic)
        {
            // SLOWER: Lazy load the genotypes, phenotypes and dependencies
            // $puppyCharacteristic->load('genotypes', 'phenotypes', 'characteristic.dependencies');

            $characteristic = $puppyCharacteristic->characteristic;

            foreach($characteristic->dependencies as $dependency)
            {
                if ($dependency->isActive())
                {
                    if ($dependency->takesInRanged())
                    {
                        // Get the independent characteristics range values for this puppy
                        $independentRangedValues = DB::table('characteristic_dependency_ind_characteristics')
                            ->select('characteristic_dependency_ind_characteristics.independent_characteristic_id', 'dog_characteristics.final_ranged_value')
                            ->join('dog_characteristics', 'dog_characteristics.characteristic_id', '=', 'characteristic_dependency_ind_characteristics.independent_characteristic_id')
                            ->where('dog_characteristics.dog_id', $this->id)
                            ->where('characteristic_dependency_ind_characteristics.characteristic_dependency_id', $dependency->id)
                            ->lists('final_ranged_value', 'independent_characteristic_id');
                    }
                    else if ($dependency->takesInGenotypes())
                    {
                        // Get the independent characteristics genotypes for this puppy
                        $independentGenotypeIds = DB::table('characteristic_dependency_ind_characteristics')
                            ->select('genotypes.locus_id', 'dog_characteristic_genotypes.genotype_id')
                            ->join('dog_characteristics', 'dog_characteristics.characteristic_id', '=', 'characteristic_dependency_ind_characteristics.independent_characteristic_id')
                            ->join('dog_characteristic_genotypes', 'dog_characteristic_genotypes.dog_characteristic_id', '=', 'dog_characteristics.id')
                            ->join('genotypes', 'genotypes.id', '=', 'dog_characteristic_genotypes.genotype_id')

                            ->where('dog_characteristics.dog_id', $this->id)
                            ->where('characteristic_dependency_ind_characteristics.characteristic_dependency_id', $dependency->id)
                            ->lists('genotype_id', 'locus_id');
                    }

                    if ($dependency->outputsRanged())
                    {
                        // Get this characteristics dependent value for this puppy
                        $finalRangedValue = $puppyCharacteristic->final_ranged_value;

                        $newRangedValue = $finalRangedValue;

                        // Do the dependencies
                        if ($dependency->isR2R())
                        {
                            $newRangedValue = $dependency->doR2R($finalRangedValue, $independentRangedValues);
                        }
                        else if ($dependency->isG2R())
                        {
                            $newRangedValue = $dependency->doG2R($finalRangedValue, $independentGenotypeIds);
                        }

                        // Only need to update and bind if it changed
                        if (Floats::compare($finalRangedValue, $newRangedValue, '!='))
                        {
                            // We need to bind to the used characteristic itself
                            $usedCharacteristic = $usedCharacteristics[$characteristic->id];

                            // Bind the value
                            $finalRangedValue = $this->isFemale()
                                ? $usedCharacteristic->bindRangedFemaleValue($newRangedValue) 
                                : $usedCharacteristic->bindRangedMaleValue($newRangedValue);

                            // Adjust for growth
                            $currentRangedValue = DogCharacteristic::currentRangedValue($finalRangedValue, $puppyCharacteristic->age_to_stop_growing, $this->age);

                            // Save it back on the characteristic
                            $puppyCharacteristic->final_ranged_value   = $finalRangedValue;
                            $puppyCharacteristic->current_ranged_value = $currentRangedValue;

                            // Save it IMMEDIATELY
                            $puppyCharacteristic->save();
                        }
                    }
                    else if ($dependency->outputsGenotypes())
                    {
                        // We no longer support X2G dependencies, but this check is here for legacy value
                    }
                }
            }
        }

        // Once the characteristics are assigned, need to check breedability
        $this->sexually_mature = $this->checkSexualMaturity();
        $this->sexual_decline  = $this->checkSexualDecline();
        $this->infertile       = $this->checkInfertility();

        // Build a pedigree for the puppy
        $pedigree = Pedigree::bred($dam, $sire);

        // Add the puppy
        $pedigree->dog_id = $this->id;

        // Save it
        if ( ! $pedigree->save())
        {
            throw new Dynasty\Pedigrees\Exceptions\NotSavedException;
        }

        // Give the COI of the pedigree
        $this->coi = $pedigree->calculateCoi();

        // Save everything
        $this->save();
    }

    public function countGenerations()
    {
        return is_null($this->pedigree)
            ? 0
            : $this->pedigree->getHeight(); // Imported dogs are first generations
    }

    public function petHome()
    {
        if ($this->canBeDeleted())
        {
            // If the dog is from a litter, check to see if they are the last dog in it
            if ($this->hasLitter() and $this->litter->dogs()->count() <= 1)
            {
                // Delete the litter too
                $this->litter->delete();
            }

            // Send a notification to the dog's owner
            if ( ! is_null($this->owner))
            {
                $params = array(
                    'dog' => $this->nameplate(), 
                );

                $body = Lang::get('notifications/dog.pet_homed_deleted.to_owner', array_map('htmlentities', array_dot($params)));
                
                $this->owner->notify($body, UserNotification::TYPE_DANGER, false, false);
            }

            // Delete the dog
            $this->delete();

            // Redirect to the kennel
            $deleted = true;
        }
        else
        {
            // Remove from breed drafts that are still in the draft stage
            DB::table('breed_drafts')->where('dog_id', $this->id)->where('status_id', BreedDraft::STATUS_DRAFT)->update([ 'dog_id' => null ]);

            // Remove all stud requests
            DB::table('stud_requests')->where('bitch_id', $this->id)->orWhere('stud_id', $this->id)->delete();

            // Remove all blrs
            DB::table('beginners_luck_requests')->where('bitch_id', $this->id)->orWhere('dog_id', $this->id)->delete();

            // Remove all lend requests
            DB::table('lend_requests')->where('dog_id', $this->id)->delete();

            // Check to see if we can remove the characteristics, genotypes, phenotypes and pedigree
            if ( ! $this->hasLitters() and ! $this->isBreedOriginator() and ! $this->isUndraftedBreedDraftOriginator() and ! $this->hasEnteredContests())
            {
                DB::table('dog_characteristics')->where('dog_id', $this->id)->delete();
                DB::table('dog_genotypes')->where('dog_id', $this->id)->delete();
                DB::table('dog_phenotypes')->where('dog_id', $this->id)->delete();
                DB::table('pedigrees')->where('dog_id', $this->id)->delete();
            }

            // Send a notification to the dog's owner
            if ( ! is_null($this->owner))
            {
                $params = array(
                    'dog'    => $this->nameplate(), 
                    'dogUrl' => URL::route('dog/profile', $this->id), 
                );

                $body = Lang::get('notifications/dog.pet_homed.to_owner', array_map('htmlentities', array_dot($params)));
                
                $this->owner->notify($body, UserNotification::TYPE_DANGER, false, false);
            }

            // Pet home the dog
            $this->owner_id = null;
            $this->kennel_group_id = null;
            $this->worked = false;
            $this->heat = false;
            $this->active_breed_member = false;
            $this->notes = '';
            $this->image_url = '';
            $this->studding = Dog::STUDDING_NONE;
            $this->display_image = Dog::DISP_IMAGE_DEFAULT;
            $this->save();

            $deleted = false;
        }

        return $deleted;
    }

}
