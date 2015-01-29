<?php

class LocusAllele extends Eloquent {

    const NULL_SYMBOL = '_';

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
     * Return the genotype's locus.
     *
     * @return Breed
     */
    public function locus()
    {
        return $this->belongsTo('Locus', 'locus_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Has Many Relationships
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
        return Genotype::where(function($query)
            {
                $query->where('locus_allele_id_a', $this->id)->orWhere('locus_allele_id_b', $this->id);
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function isUniquePair($locusAllele, $except = [])
    {
        $genotypes = Genotype::where(function($query) use ($locusAllele)
            {
                $query->where(function($q) use ($locusAllele)
                    {
                        $q->where('locus_allele_id_a', $this->id)->where('locus_allele_id_b', $locusAllele->id);
                    })
                    ->orWhere(function($q) use ($locusAllele)
                    {
                        $q->where('locus_allele_id_a', $locusAllele->id)->where('locus_allele_id_b', $this->id);
                    });
            });

        if ( ! empty($except))
        {
            $genotypes = $genotypes->whereNotIn('id', $except);
        }

        $totalPairsFound = $genotypes->count();

        return ($totalPairsFound <= 0);
    }

    public function hasGenotypes()
    {
        return ($this->genotypes()->count() > 0);
    }

}
