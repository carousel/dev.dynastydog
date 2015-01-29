<?php

class BreedDraft extends Eloquent {

    const STATUS_DRAFT    = 0;
    const STATUS_PENDING  = 1;
    const STATUS_ACCEPTED = 2;
    const STATUS_REJECTED = 3;
    const STATUS_EXTINCT  = 4;

    public $timestamps = true;

    protected $guarded = array('id');

    protected $dates = ['created_at', 'updated_at', 'edited_at', 'submitted_at', 'accepted_at'];

    protected $imageDirectory = 'assets/img/breeds/drafts';
    protected $imageExtension = 'png';

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    |
    |
    */

    public function getOfficialAttribute($official)
    {
        return (bool) $official;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    |
    */

    public function scopeWhereOfficial($query)
    {
        return $query->where('official', true);
    }

    public function scopeWhereUnofficial($query)
    {
        return $query->where('official', false);
    }

    public function scopeWhereDraft($query)
    {
        return $query->where('status_id', BreedDraft::STATUS_DRAFT);
    }

    public function scopeWherePending($query)
    {
        return $query->where('status_id', BreedDraft::STATUS_PENDING);
    }

    public function scopeWhereAccepted($query)
    {
        return $query->where('status_id', BreedDraft::STATUS_ACCEPTED);
    }

    public function scopeWhereRejected($query)
    {
        return $query->where('status_id', BreedDraft::STATUS_REJECTED);
    }

    public function scopeWhereExtinct($query)
    {
        return $query->where('status_id', BreedDraft::STATUS_EXTINCT);
    }

    public function scopeWhereNotDraft($query)
    {
        return $query->where('status_id', '<>', BreedDraft::STATUS_DRAFT);
    }

    public function scopeWhereNotAccepted($query)
    {
        return $query->where('status_id', '<>', BreedDraft::STATUS_ACCEPTED);
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the user who started the draft
     *
     * @return User
     */
    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id');
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
    | Has One Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * The breed this draft made
     *
     * @return Breed
     */
    public function breed()
    {
        return $this->hasOne('Breed', 'draft_id', 'id');
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
     * @return Collection of BreedDraftCharacteristics
     */
    public function characteristics()
    {
        return $this->hasMany('BreedDraftCharacteristic', 'breed_draft_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public static function getStates()
    {
        return array(
            BreedDraft::STATUS_ACCEPTED => 'Accepted', 
            BreedDraft::STATUS_DRAFT    => 'Draft', 
            BreedDraft::STATUS_EXTINCT  => 'Extinct', 
            BreedDraft::STATUS_PENDING  => 'Pending', 
            BreedDraft::STATUS_REJECTED => 'Rejected', 
        );
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

    public function getStatus()
    {
        $states = BreedDraft::getStates();

        return $states[$this->status_id];
    }

    public function isInState($stateId)
    {
        return ($this->status_id == $stateId);
    }

    public function hasReasonsForRejection()
    {
        return (strlen($this->rejection_reasons) > 0);
    }

    public function hasDescription()
    {
        return (strlen($this->description) > 0);
    }

    public function hasHealthDisorders()
    {
        return (strlen($this->health_disorders) > 0);
    }

    public function isOfficial()
    {
        return $this->official;
    }

    public function isUnofficial()
    {
        return ( ! $this->official);
    }

    public function isDraft()
    {
        return ($this->status_id == BreedDraft::STATUS_DRAFT);
    }

    public function isPending()
    {
        return ($this->status_id == BreedDraft::STATUS_PENDING);
    }

    public function isAccepted()
    {
        return ($this->status_id == BreedDraft::STATUS_ACCEPTED);
    }

    public function isRejected()
    {
        return ($this->status_id == BreedDraft::STATUS_REJECTED);
    }

    public function isExtinct()
    {
        return ($this->status_id == BreedDraft::STATUS_EXTINCT);
    }

    public function isEditable()
    {
        return $this->isDraft();
    }

    public function hasImage()
    {
        return File::exists($this->getImagePath());
    }

    public function getImageUrl()
    {
        return implode(DIRECTORY_SEPARATOR, [$this->getImageDirectory(), $this->id.'.'.$this->getImageExtension()]);
    }

    public function getImagePath()
    {
        return public_path($this->getImageUrl());
    }

    public function hasCharacteristic($characteristicId)
    {
        return ( ! $this->characteristics()->where('characteristic_id', $characteristicId)->get()->isEmpty());
    }

    public function checkOriginator()
    {
        if ( ! $this->isOfficial())
        {
            $dog = $this->dog;

            if (is_null($dog))
            {
                throw new Dynasty\BreedDrafts\Exceptions\MissingDogException;
            }

            // Make sure the user owns the dog
            if ( ! $this->user->ownsDog($dog))
            {
                throw new Dynasty\User\Exceptions\DoesNotOwnDogException;
            }

            // A dog must be alive
            if ( ! $dog->isAlive())
            {
                throw new Dynasty\Dogs\Exceptions\DeceasedException;
            }

            // A dog must be completed
            if ( ! $dog->isComplete())
            {
                throw new Dynasty\Dogs\Exceptions\IncompleteException;
            }

            // Dog must be atleast a Nth generation
            // Example: 5 generations 
            //          1: dog, 2: sire 3: gsire 4: ggsire 5:gggsire
            $generationsNeeded = Config::get('game.breed.generations_needed');

            if ($dog->countGenerations() < $generationsNeeded)
            {
                throw new Dynasty\Dogs\Exceptions\NotEnoughGenerationsException;
            }

            // Only check if not originator if a breed does not already exist for this draft
            $breed = $this->breed;

            if ((is_null($breed) or $breed->originator_id != $dog->id) and $dog->isBreedOriginator())
            {
                throw new Dynasty\Dogs\Exceptions\BreedOriginatorException;
            }
        }
    }

    public function checkCharacteristics()
    {
        $breedDraftCharacteristics = $this->characteristics;

        if ($breedDraftCharacteristics->isEmpty())
        {
            throw new Dynasty\BreedDrafts\Exceptions\MissingCharacteristicException;
        }

        $allGenotypeIdsByLocusId = [];

        foreach($breedDraftCharacteristics as $breedDraftCharacteristic)
        {
            // We can skip checks on ignorable characteristics
            if ( ! $this->isOfficial() or ! $breedDraftCharacteristic->isIgnored())
            {
                // Grab the characteristic
                $characteristic = $breedDraftCharacteristic->characteristic;

                // Do range check first because it's the quickest
                if ($characteristic->isRanged())
                {
                    // Check that ranges obey bounds
                    if ($breedDraftCharacteristic->min_female_ranged_value < $characteristic->min_ranged_value or $breedDraftCharacteristic->max_female_ranged_value > $characteristic->max_ranged_value)
                    {
                        throw new Dynasty\BreedDraftCharacteristics\Exceptions\FemaleRangedValueOutOfBoundsException($characteristic->name);
                    }

                    if ($breedDraftCharacteristic->min_male_ranged_value < $characteristic->min_ranged_value or $breedDraftCharacteristic->max_male_ranged_value > $characteristic->max_ranged_value)
                    {
                        throw new Dynasty\BreedDraftCharacteristics\Exceptions\MaleRangedValueOutOfBoundsException($characteristic->name);
                    }
                }

                // Do genetic check
                if ($characteristic->isGenetic())
                {
                    // Get all genotypes and phenotypese attached
                    $attachedGenotypes  = $breedDraftCharacteristic->genotypes;
                    $attachedPhenotypes = $breedDraftCharacteristic->phenotypes()->with(array(
                        'genotypes' => function($query)
                            {
                                $query->whereActive();
                            }
                    ))->get();

                    if ($attachedGenotypes->isEmpty() and $attachedPhenotypes->isEmpty())
                    {
                        throw new Dynasty\BreedDraftCharacteristics\Exceptions\IncompleteException($characteristic->name);
                    }

                    // Get the characteristic's loci
                    $loci = $characteristic->loci()->with('genotypes')->get();

                    // Group the genotypes
                    $characteristicGenotypeIdsByLocusId = [];
                    $attachedGenotypeIdsByLocusId       = [];

                    foreach($loci as $locus)
                    {
                        $characteristicGenotypeIdsByLocusId[$locus->id] = $locus->genotypes->lists('id');
                    }

                    foreach($attachedGenotypes as $genotype)
                    {
                        $attachedGenotypeIdsByLocusId[$genotype->locus_id][] = $genotype->id;
                    }

                    // Make sure the genotypes selected exist for this characteristic
                    foreach($attachedGenotypeIdsByLocusId as $locusId => $genotypeIds)
                    {
                        $foundCharacteristicLocusGenotypeIds = array_key_exists($locusId, $characteristicGenotypeIdsByLocusId)
                            ? $characteristicGenotypeIdsByLocusId[$locusId]
                            : [];

                        $intersect = array_intersect($genotypeIds, $foundCharacteristicLocusGenotypeIds);

                        // Nothing in common
                        if (empty($intersect))
                        {
                            $symbols = [];

                            foreach($intersect as $genotypeId)
                            {
                                $genotype = Genotype::find($genotypeId);

                                $symbols[] = $genotype->toSymbol();
                            }

                            $params = array(
                                'characteristic' => $characteristic->name, 
                                'genotypes'      => implode(', ', $symbols), 
                            );

                            throw new Dynasty\BreedDraftCharacteristics\Exceptions\GenotypesNotFoundInCharacteristicException(json_encode($params));
                        }
                    }

                    // Get all of the possible phenotypes available in the draft's characteristic
                    $possiblePhenotypeIds = $breedDraftCharacteristic->possiblePhenotypes()->lists('id');

                    // Get all genotypes in the selected phenotypes
                    $attachedPhenotypeGenotypeIdsByLocusId = [];

                    foreach($attachedPhenotypes as $phenotype)
                    {
                        // Prove internal conflict
                        if ( ! in_array($phenotype->id, $possiblePhenotypeIds))
                        {
                            throw new Dynasty\BreedDraftCharacteristics\Exceptions\InternalConflictException($characteristic->name);
                        }

                        $phenotypeGenotypes = $phenotype->genotypes;

                        foreach($phenotypeGenotypes as $genotype)
                        {
                            // Use all genotypes for the phenotypes
                            $attachedPhenotypeGenotypeIdsByLocusId[$genotype->locus_id][] = $genotype->id;
                        }
                    }

                    // Need to check that genotypes respect phenotypes for each charateristic
                    foreach($attachedPhenotypeGenotypeIdsByLocusId as $locusId => $genotypeIds)
                    {
                        $foundCharacteristicLocusGenotypeIds = array_key_exists($locusId, $characteristicGenotypeIdsByLocusId)
                            ? $characteristicGenotypeIdsByLocusId[$locusId]
                            : [];

                        // Make sure this phenotype exists for this characteristic
                        $intersect = array_intersect($genotypeIds, $foundCharacteristicLocusGenotypeIds);

                        // Nothing in common
                        if (empty($intersect))
                        {
                            throw new Dynasty\BreedDraftCharacteristics\Exceptions\PhenotypeNotFoundInCharacteristicException(array(
                                'characteristic' => $characteristic->name, 
                                'phenotype'      => $phenotype->name, 
                            ));
                        }
                    }

                    // Get all of the genotypes available in the draft's characteristic
                    $possibleGenotypeIdsByLocusId = $breedDraftCharacteristic->getPossibleGenotypeIdsByLocusId();

                    // Add all of this char's possible genotypes to the draft's total
                    foreach($possibleGenotypeIdsByLocusId as $locusId => $genotypeIds)
                    {
                        // External conflicts
                        if (array_key_exists($locusId, $allGenotypeIdsByLocusId))
                        {
                            $intersect = array_intersect($genotypeIds, $allGenotypeIdsByLocusId[$locusId]);

                            $totalPossibleGenotypes = count($genotypeIds);
                            $totalGenotypesInLocus  = count($allGenotypeIdsByLocusId[$locusId]);
                            $totalSameGenotypes     = count($intersect);

                            // Check for external conflicts
                            if ($totalPossibleGenotypes !== $totalGenotypesInLocus or $totalSameGenotypes !== $totalGenotypesInLocus)
                            {
                                throw new Dynasty\BreedDraftCharacteristics\Exceptions\ExternalConflictException($characteristic->name);
                            }

                            $allGenotypeIdsByLocusId[$locusId] = $intersect;
                        }
                        else
                        {
                            $allGenotypeIdsByLocusId[$locusId] = $genotypeIds;
                        }
                    }

                }
            }
        }
    }

    public function checkOriginatorCharacteristics()
    {
        if ( ! $this->isOfficial())
        {
            // Make sure the dog fits into the breed
            $failedCharacteristics = $this->checkDog($this->dog);

            if ( ! empty($failedCharacteristics))
            {
                $failedCharacteristicNames = [];

                foreach($failedCharacteristics as $failedCharacteristic)
                {
                    $failedCharacteristicNames[] = $failedCharacteristic->name;
                }

                throw new Dynasty\BreedDrafts\Exceptions\DogDoesNotMeetRequirementsException(implode(', ', $failedCharacteristicNames));
            }

            // All dogs within 5 generations must fit into the new breed
            $generationsNeeded = Config::get('game.breed.generations_needed');

            if ( ! $this->dog->hasPedigree())
            {
                throw new Dynasty\Dogs\Exceptions\NotEnoughGenerationsException;
            }

            $mergedPedigree = $this->dog->pedigree->merged();

            $ancestors = [];

            foreach($mergedPedigree as $key => $ancestor)
            {
                $ancestors[$key] = $ancestor[Pedigree::ANCESTOR];
            }

            $ancestors = array_unique(array_filter($ancestor));

            foreach($ancestors as $key => $ancestorId)
            {
                if (strlen($key) <= $generationsNeeded)
                {
                    $ancestorDog = Dog::find($ancestorId);

                    if ( ! is_null($ancestorDog))
                    {
                        // Make sure the ancestor fits into the breed
                        $failedCharacteristics = $this->checkDog($ancestorDog);

                        if ( ! empty($failedCharacteristics))
                        {
                            $failedCharacteristicNames = [];

                            foreach($failedCharacteristics as $failedCharacteristic)
                            {
                                $failedCharacteristicNames[] = $failedCharacteristic->name;
                            }

                            throw new Dynasty\BreedDrafts\Exceptions\AncestorDoesNotMeetRequirementsException(implode(', ', $failedCharacteristicNames));
                        }
                    }
                }
            }
        }
    }

    public function checkDog($dog)
    {
        // Store the failed characteristics here
        $failedCharacteristics = [];

        $breedDraftCharacteristics = $this->isOfficial()
            ? $this->characteristics()->whereNotIgnored()->get()
            : $this->characteristics()->get();

        foreach($breedDraftCharacteristics as $breedDraftCharacteristic)
        {
            // Grab the umbrella characteristic
            $characteristic = $breedDraftCharacteristic->characteristic;

            // Get the dog's equivalent
            $dogCharacteristic = $dog->characteristics()->whereVisible()->whereCharacteristic($characteristic->id)->first();

            if (is_null($dogCharacteristic))
            {
                // Log the fail
                $failedCharacteristics[] = $characteristic;

                // Continue to the next breed draft characteristic
                continue;
            }

            // Do ranged check
            if ($characteristic->isRanged())
            {
                $sex = $dog->isFemale() ? 'female' : 'male';

                if ( ! $breedDraftCharacteristic->isInRange($dogCharacteristic->final_ranged_value, $sex))
                {
                    // Log the fail
                    $failedCharacteristics[] = $characteristic;

                    // Continue to the next breed draft characteristic
                    continue;
                }
            }

            // Do genetic check
            if ($characteristic->isGenetic())
            {
                // Get all genotypes on the breed draft characteristic
                $breedDraftCharacteristicGenotypeIds = array_flatten($breedDraftCharacteristic->getPossibleGenotypeIdsByLocusId());

                // Get all genotypes attached to this dog's characteristic
                $dogCharacteristicGenotypeIds = $dogCharacteristic->genotypes()->whereActive()->lists('id');

                // Make sure the breed draft characteristic has all of them
                $difference = array_diff($dogCharacteristicGenotypeIds, $breedDraftCharacteristicGenotypeIds);

                if ( ! empty($difference))
                {
                    // Log the fail
                    $failedCharacteristics[] = $characteristic;

                    // Continue to the next breed draft characteristic
                    continue;
                }
            }
        }

        return $failedCharacteristics;
    }

}
