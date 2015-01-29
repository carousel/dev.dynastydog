<?php

class Genotype extends Eloquent {

    public $timestamps = false;
    
    protected $guarded = array('id');

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    |
    */

    public function scopeWhereActive($query)
    {
        return $query->whereHas('locus', function($query)
        {
            $query->whereActive();
        });
    }

    public function scopeWhereInactive($query)
    {
        return $query->whereHas('locus', function($query)
        {
            $query->whereInactive();
        });
    }

    public function scopeWhereAvailableToFemale($query)
    {
        return $query->where('genotypes.available_to_female', true);
    }

    public function scopeWhereAvailableToMale($query)
    {
        return $query->where('genotypes.available_to_male', true);
    }

    public function scopeWhereAvailableToAll($query)
    {
        return $query->where(function($q)
            {
                $q->where('genotypes.available_to_male', true)->where('genotypes.available_to_female', true);
            });
    }

    public function scopeOrderByAlleles($query)
    {
        return $query
            ->select('genotypes.*')
            ->leftJoin('locus_alleles as allele_a', 'allele_a.id', '=', 'genotypes.locus_allele_id_a')
            ->leftJoin('locus_alleles as allele_b', 'allele_b.id', '=', 'genotypes.locus_allele_id_b')
            ->orderBy('allele_a.symbol', 'asc')
            ->orderBy('allele_b.symbol', 'asc');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    |
    |
    */

    public function getAvailableToFemaleAttribute($availableToFemale)
    {
        return (bool) $availableToFemale;
    }

    public function getAvailableToMaleAttribute($availableToMale)
    {
        return (bool) $availableToMale;
    }

    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    |
    |
    */

    public function setLocusAlleleIdAAttribute($locusAlleleIdA)
    {
        $this->attributes['locus_allele_id_a'] = strlen($locusAlleleIdA) ? $locusAlleleIdA : null;
    }

    public function setLocusAlleleIdBAttribute($locusAlleleIdB)
    {
        $this->attributes['locus_allele_id_b'] = strlen($locusAlleleIdB) ? $locusAlleleIdB : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the genotype's locus.
     *
     * @return Breed
     */
    public function locus()
    {
        return $this->belongsTo('Locus', 'locus_id');
    }

    /**
     * Return the genotype's first allele.
     *
     * @return LocusAllele
     */
    public function firstAllele()
    {
        return $this->belongsTo('LocusAllele', 'locus_allele_id_a');
    }

    /**
     * Return the genotype's second allele.
     *
     * @return LocusAllele
     */
    public function secondAllele()
    {
        return $this->belongsTo('LocusAllele', 'locus_allele_id_b');
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All phenotypes
     *
     * @return Collection of Phenotypes
     */
    public function phenotypes()
    {
        return $this->belongsToMany('Phenotype', 'phenotypes_genotypes', 'genotype_id', 'phenotype_id');
    }

    /**
     * All breeds
     *
     * @return Collection of Breeds
     */
    public function breeds()
    {
        return $this->belongsToMany('Breed', 'breed_genotypes', 'genotype_id', 'breed_id')->withPivot('frequency');
    }

    /**
     * All dogs
     *
     * @return Collection of Dogs
     */
    public function dogs()
    {
        return $this->belongsToMany('Dog', 'dog_genotypes', 'genotype_id', 'dog_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function toSymbol()
    {
        $symbols = [];

        $symbols[0] = is_null($this->firstAllele)
            ? LocusAllele::NULL_SYMBOL
            : $this->firstAllele->symbol;

        $symbols[1] = is_null($this->secondAllele)
            ? LocusAllele::NULL_SYMBOL
            : $this->secondAllele->symbol;

        return implode('', $symbols);
    }

    public function isAvailableToFemales()
    {
        return $this->available_to_female;
    }

    public function isAvailableToMales()
    {
        return $this->available_to_male;
    }

    public function checkSex($sex)
    {
        return (($sex->isFemale() and $this->isAvailableToFemales()) or ($sex->isMale() and $this->isAvailableToMales()));
    }

    public function punnetSquare($genotypeB)
    {
        $genotypeA = $this;

        // Get the alleles
        $genotypeAAlleleIds = [ $genotypeA->locus_allele_id_a, $genotypeA->locus_allele_id_b ];
        $genotypeBAlleleIds = [ $genotypeB->locus_allele_id_a, $genotypeB->locus_allele_id_b ];

        // Get all possibilities
        $lookup  = [];
        $results = [];

        foreach($genotypeAAlleleIds as $genotypeAAlleleId)
        {
            foreach($genotypeBAlleleIds as $genotypeBAlleleId)
            {
                $key = $genotypeAAlleleId.$genotypeBAlleleId;

                if (isset($lookup[$key]))
                {
                    $genotype = $lookup[$key];
                }
                else
                {
                    $genotype = Genotype::whereActive()->where(function($query) use ($genotypeAAlleleId, $genotypeBAlleleId)
                    {
                        $query
                            ->where(function($q) use ($genotypeAAlleleId, $genotypeBAlleleId)
                            {
                                if (is_null($genotypeAAlleleId))
                                {
                                    $q->whereNull('locus_allele_id_a');
                                }
                                else
                                {
                                    $q->where('locus_allele_id_a', $genotypeAAlleleId);
                                }

                                if (is_null($genotypeBAlleleId))
                                {
                                    $q->whereNull('locus_allele_id_b');
                                }
                                else
                                {
                                    $q->where('locus_allele_id_b', $genotypeBAlleleId);
                                }
                            })
                            ->orWhere(function($q) use ($genotypeAAlleleId, $genotypeBAlleleId)
                            {
                                if (is_null($genotypeBAlleleId))
                                {
                                    $q->whereNull('locus_allele_id_a');
                                }
                                else
                                {
                                    $q->where('locus_allele_id_a', $genotypeBAlleleId);
                                }
                                
                                if (is_null($genotypeAAlleleId))
                                {
                                    $q->whereNull('locus_allele_id_b');
                                }
                                else
                                {
                                    $q->where('locus_allele_id_b', $genotypeAAlleleId);
                                }
                            });
                    })
                    ->first();

                    $lookup[$key] = $genotype;
                }

                if ( ! is_null($genotype))
                {
                    $results[] = $genotype;
                }
            }
        }

        return $results;
    }

}
