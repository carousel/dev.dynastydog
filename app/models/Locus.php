<?php

class Locus extends Eloquent {

    public $table = 'loci';

    public $timestamps = false;
    
    protected $guarded = array('id');

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    |
    |
    */

    public function getActiveAttribute($active)
    {
        return (bool) $active;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    |
    */

    public function scopeWhereActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeWhereInactive($query)
    {
        return $query->where('active', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Has Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All alleles
     *
     * @return Collection of Alleles
     */
    public function alleles()
    {
        return $this->hasMany('LocusAllele', 'locus_id', 'id');
    }

    /**
     * All genotypes
     *
     * @return Collection of Genotypes
     */
    public function genotypes()
    {
        return $this->hasMany('Genotype', 'locus_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function isActive()
    {
        return $this->active;
    }

}
