<?php

class UserContestTypePrerequisite extends Eloquent {

    public $timestamps = false;

    protected $guarded = array('id');

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    |
    */

    public function scopeOrderByCharacteristic($query)
    {
        return $query->select('user_contest_type_prerequisites.*')
            ->leftJoin('characteristics', 'characteristics.id', '=', 'user_contest_type_prerequisites.characteristic_id')
            ->orderBy('characteristics.name', 'asc');
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the contest type.
     *
     * @return UserContestType
     */
    public function contestType()
    {
        return $this->belongsTo('UserContestType', 'contest_type_id', 'id');
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
        return $this->belongsToMany('Genotype', 'user_contest_type_prerequisite_genotypes', 'contest_type_prerequisite_id', 'genotype_id');
    }

    /**
     * All phenotypes
     *
     * @return Collection of Phenotypes
     */
    public function phenotypes()
    {
        return $this->belongsToMany('Phenotype', 'user_contest_type_prerequisite_phenotypes', 'contest_type_prerequisite_id', 'phenotype_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function isComplete()
    {
        // Must either have ranged data or genetic data attached
        return ( ! is_null($this->min_ranged_value) or $this->genotypes()->count() > 0 or $this->phenotypes()->count() > 0);
    }

}
