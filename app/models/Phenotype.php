<?php

class Phenotype extends Eloquent {

    public $timestamps = false;
    
    protected $guarded = array('id');

    /**
     * All genotypes
     *
     * @return Collection of Genotypes
     */
    public function genotypes()
    {
        return $this->belongsToMany('Genotype', 'phenotypes_genotypes', 'phenotype_id', 'genotype_id');
    }

    public function scopeMatchesLoci($query, $locusIds)
    {
        if (empty($locusIds))
        {
            return $query;
        }

        $occurrences = count($locusIds);

        // Grab all phenotypes that have loci that are not in the selected ones
        $invalidPhenotypeIds = DB::table('phenotypes_genotypes')
            ->select('phenotypes_genotypes.phenotype_id')
            ->join('genotypes', 'genotypes.id', '=', 'phenotypes_genotypes.genotype_id')
            ->whereNotIn('genotypes.locus_id', $locusIds)
            ->lists('phenotype_id');

        return empty($invalidPhenotypeIds)
            ? $query->select('phenotypes.*')
                ->join('phenotypes_genotypes as matechs_loci_phenotypes_genotypes', 'phenotypes.id', '=', 'matechs_loci_phenotypes_genotypes.phenotype_id')
                ->join('genotypes as matches_loci_genotypes', 'matches_loci_genotypes.id', '=', 'matechs_loci_phenotypes_genotypes.genotype_id')
                ->whereIn('matches_loci_genotypes.locus_id', $locusIds)
                ->groupBy('matechs_loci_phenotypes_genotypes.phenotype_id')
                ->having(DB::raw('COUNT(DISTINCT matches_loci_genotypes.locus_id)'), '=', $occurrences)
            : $query->select('phenotypes.*')
                ->join('phenotypes_genotypes as matechs_loci_phenotypes_genotypes', 'phenotypes.id', '=', 'matechs_loci_phenotypes_genotypes.phenotype_id')
                ->join('genotypes as matches_loci_genotypes', 'matches_loci_genotypes.id', '=', 'matechs_loci_phenotypes_genotypes.genotype_id')
                ->whereIn('matches_loci_genotypes.locus_id', $locusIds)
                ->whereNotIn('phenotypes.id', $invalidPhenotypeIds)
                ->groupBy('matechs_loci_phenotypes_genotypes.phenotype_id')
                ->having(DB::raw('COUNT(DISTINCT matches_loci_genotypes.locus_id)'), '=', $occurrences);
    }

    public function loci()
    {
        // Grab the genotype IDs
        $genotypeIds = $this->genotypes()->lists('id');

        // Always add -1
        $genotypeIds[] = -1;

        return Locus::with(array(
                    'genotypes' => function($query) use ($genotypeIds)
                        {
                            $query->whereIn('genotypes.id', $genotypeIds)->orderByAlleles();
                        }
                ))
            ->whereHas('genotypes', function($query) use ($genotypeIds)
                {
                    $query->whereIn('genotypes.id', $genotypeIds);
                })
            ->orderBy('name', 'asc');
    }

}
