<?php

class ContestPrerequisite extends Eloquent {

    public $timestamps = false;

    protected $guarded = array('id');

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the contest.
     *
     * @return Contest
     */
    public function contest()
    {
        return $this->belongsTo('Contest', 'contest_id', 'id');
    }

    /**
     * Return the characteristic.
     *
     * @return Characteristic
     */
    public function characteristic()
    {
        return $this->belongsTo('Characteristic', 'characteristic_id', 'id');
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
        return $this->belongsToMany('Genotype', 'contest_prerequisite_genotypes', 'contest_prerequisite_id', 'genotype_id');
    }

    /**
     * All phenotypes
     *
     * @return Collection of Phenotypes
     */
    public function phenotypes()
    {
        return $this->belongsToMany('Phenotype', 'contest_prerequisite_phenotypes', 'contest_prerequisite_id', 'phenotype_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function checkDogCharacteristic($dogCharacteristic)
    {
        // Grab the characteristic
        $characteristic = $this->characteristic;

        // Check if it matches on range
        if ( ! is_null($this->min_ranged_value))
        {
            if ( ! $dogCharacteristic->rangedValueIsRevealed())
            {
                return false;
            }

            // Round it to match the prerequisite value precision
            $roundedFinalRangedValue = round($dogCharacteristic->final_ranged_value);

            // Cehck if out of bounds
            if ($roundedFinalRangedValue < $this->min_ranged_value or $roundedFinalRangedValue > $this->max_ranged_value)
            {
                return false;
            }
        }

        $requiredGenotypeIdsByLocus = [];

        // Check if there are any genotypes
        $genotypes = $this->genotypes()->get();

        foreach($genotypes as $genotype)
        {
            if ( ! array_key_exists($genotype->locus_id, $requiredGenotypeIdsByLocus))
            {
                $requiredGenotypeIdsByLocus[$genotype->locus_id] = [];
            }

            $requiredGenotypeIdsByLocus[$genotype->locus_id][] = $genotype->id;
        }

        // Grab the phenotypes
        $phenotypes = $this->phenotypes()->get();

        foreach($phenotypes as $phenotype)
        {
            $phenotypeGenotypes = $phenotype->genotypes;

            foreach($phenotypeGenotypes as $genotype)
            {
                if ( ! array_key_exists($genotype->locus_id, $requiredGenotypeIdsByLocus))
                {
                    $requiredGenotypeIdsByLocus[$genotype->locus_id] = [];
                }

                $requiredGenotypeIdsByLocus[$genotype->locus_id][] = $genotype->id;
            }
        }

        // Only need to check dog's genetic data if the prerequisite has genotypes or phenotypes on it
        if ( ! empty($requiredGenotypeIdsByLocus))
        {
            // Grab the genotypes on the dog's characteristic
            $dogCharacteristicGenotypeIdsByLocusId = [];

            // Only compare against known genotypes and phenotypes
            if ($dogCharacteristic->genotypesAreRevealed() or $dogCharacteristic->phenotypesAreRevealed())
            {
                $dogCharacteristicGenotypes = $dogCharacteristic->genotypes;

                foreach($dogCharacteristicGenotypes as $genotype)
                {
                    $dogCharacteristicGenotypeIdsByLocusId[$genotype->locus_id][] = $genotype->id;
                }
            }

            // The dog must have a match at all loci given for the prerequisite
            foreach($requiredGenotypeIdsByLocus as $locusId => $genotypeIds)
            {
                // Dog's characteristic doesn't have this locus for some reason
                if ( ! array_key_exists($locusId, $dogCharacteristicGenotypeIdsByLocusId))
                {
                    return false;
                }

                // Check if any are the same
                $sameGenotypeIds = array_intersect($genotypeIds, $dogCharacteristicGenotypeIdsByLocusId[$locusId]);

                // None matched
                if (empty($sameGenotypeIds))
                {
                    return false;
                }
            }
        }

        return true;
    }

}
