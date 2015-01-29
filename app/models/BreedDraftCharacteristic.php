<?php

class BreedDraftCharacteristic extends Eloquent {

    protected $guarded = array('id');

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    |
    */

    public function scopeWhereIgnored($query)
    {
        return $query->where('breed_draft_characteristics.ignored', true);
    }

    public function scopeWhereNotIgnored($query)
    {
        return $query->where('breed_draft_characteristics.ignored', false);
    }

    public function scopeOrderByCharacteristic($query)
    {
        return $query->select('breed_draft_characteristics.*')
            ->leftJoin('characteristics', 'characteristics.id', '=', 'breed_draft_characteristics.characteristic_id')
            ->orderBy('characteristics.name', 'asc');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    |
    |
    */

    public function getIgnoredAttribute($ignored)
    {
        return (bool) $ignored;
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the breed draft
     *
     * @return BreedDraft
     */
    public function breedDraft()
    {
        return $this->belongsTo('BreedDraft', 'breed_draft_id');
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
        return $this->belongsToMany('Genotype', 'breed_draft_characteristic_genotypes', 'breed_draft_characteristic_id', 'genotype_id');
    }

    /**
     * All phenotypes
     *
     * @return Collection of Phenotypes
     */
    public function phenotypes()
    {
        return $this->belongsToMany('Phenotype', 'breed_draft_characteristic_phenotypes', 'breed_draft_characteristic_id', 'phenotype_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function isIgnored()
    {
        return $this->ignored;
    }

    public function wasSaved()
    {
        return ($this->created_at != $this->updated_at);
    }

    public function formatRangedValue($value, $allowLabel = true)
    {
        return $this->characteristic->formatRangedValue($value, $allowLabel);
    }

    public function getPossibleGenotypeIdsByLocusId()
    {
        // Get all genotypes attached
        $genotypes = $this->genotypes()->whereActive()->get();

        // Get all phenotypes attached
        $phenotypes = $this->phenotypes()->with(array(
            'genotypes' => function($query)
            {
                $query->whereActive();
            }
        ))->get();

        // Sort the ids by locus
        $genotypeIdsByLocusId = [];

        foreach($genotypes as $genotype)
        {
            $genotypeIdsByLocusId[$genotype->locus_id][] = $genotype->id;
        }

        // Get all genotypes in the phenotypes
        $phenotypeGenotypeIdsByLocusId = array();

        foreach($phenotypes as $phenotype)
        {
            $phenotypeGenotypes = $phenotype->genotypes;

            foreach($phenotypeGenotypes as $genotype)
            {
                // Use all genotypes for the phenotypes
                $phenotypeGenotypeIdsByLocusId[$genotype->locus_id][] = $genotype->id;
            }
        }

        // Need to check that genotypes respect phenotypes for each charateristic
        foreach($phenotypeGenotypeIdsByLocusId as $locusId => $genotypeIds)
        {
            $genotypeIdsByLocusId[$locusId] = array_key_exists($locusId,  $genotypeIdsByLocusId)
                ? array_intersect($genotypeIds, $genotypeIdsByLocusId[$locusId]) // Proves internal conflicts
                : $genotypeIds;
        }

        return array_filter($genotypeIdsByLocusId);
    }

    public function possiblePhenotypes()
    {
        // Sort the ids by locus
        $genotypeIdsByLocusId = $this->getPossibleGenotypeIdsByLocusId();

        // Get all phenotypes from the characteristic
        $phenotypes = $this->characteristic->queryPhenotypes()->with('genotypes')->get();

        $matchedPhenotypeIds = [ -1 ];

        // Make sure the genotypes match for all of them
        foreach($phenotypes as $phenotype)
        {
            // Grab the phenotype's genotypes
            $phenotypeGenotypes = $phenotype->genotypes()->whereActive()->get();

            // Get the locus IDs
            $phenotypeLocusIds = $phenotypeGenotypes->lists('locus_id');

            // Get the genotype IDs
            $phenotypeGenotypeIds = $phenotypeGenotypes->lists('id');

            // Make sure the characteristic has at least one genotype in all of the phenotype's loci
            $failed = false;

            foreach($phenotypeLocusIds as $locusId)
            {
                if ( ! array_key_exists($locusId, $genotypeIdsByLocusId))
                {
                    $failed = true;

                    continue;
                }

                $same = array_intersect($phenotypeGenotypeIds, $genotypeIdsByLocusId[$locusId]);

                // None were the same
                if (empty($same))
                {
                    $failed = true;
                }
            }

            if ( ! $failed)
            {
                $matchedPhenotypeIds[] = $phenotype->id;
            }
        }

        return Phenotype::whereIn('id', $matchedPhenotypeIds);
    }

    public function possibleLociWithGenotypes()
    {
        // Sort the ids by locus
        $genotypeIdsByLocusId = $this->getPossibleGenotypeIdsByLocusId();

        // Get only the genotype Ids
        $genotypeIds = array_flatten($genotypeIdsByLocusId);

        // Get that characteristic loci
        $characteristicLocusIds = $this->characteristic->loci()->lists('id');

        // Always add -1 in
        $genotypeIds[] = -1;
        $characteristicLocusIds[] = -1;

        return Locus::with(array(
                'genotypes' => function($query) use ($genotypeIds)
                {
                    $query->whereIn('genotypes.id', $genotypeIds)->orderByAlleles();
                }
            ))
            ->whereActive()
            ->whereIn('id', $characteristicLocusIds);
    }

    public function isInRange($value, $sex = 'female')
    {
        return ($sex == 'female')
            ? $this->isInFemaleRange($value)
            : $this->isInMaleRange($value);
    }

    public function isInFemaleRange($value)
    {
        return (Floats::compare($value, $this->min_female_ranged_value, '>=') and Floats::compare($value, $this->max_female_ranged_value, '<='));
    }

    public function isInMaleRange($value)
    {
        return (Floats::compare($value, $this->min_male_ranged_value, '>=') and Floats::compare($value, $this->max_male_ranged_value, '<='));
    }

}
