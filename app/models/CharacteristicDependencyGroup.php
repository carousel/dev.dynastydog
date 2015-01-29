<?php

class CharacteristicDependencyGroup extends Eloquent {

    public $timestamps = false;
    
    protected $guarded = array('id');

    /**
     * Return the dependency
     *
     * @return CharacteristicDependency
     */
    public function dependency()
    {
        return $this->belongsTo('CharacteristicDependency', 'characteristic_dependency_id');
    }

    /**
     * All genotypes belonging to independents in this group
     *
     * @return Collection of CharacteristicDependencyGroupIndependentCharacteristicGenotype
     */
    public function independentCharacteristicGenotypes()
    {
        return $this->hasMany('CharacteristicDependencyGroupIndependentCharacteristicGenotype', 'characteristic_dependency_group_id', 'id');
    }

    /**
     * All ranges belonging to independents in this group
     *
     * @return Collection of CharacteristicDependencyGroupIndependentCharacteristicRange
     */
    public function independentCharacteristicRanges()
    {
        return $this->hasMany('CharacteristicDependencyGroupIndependentCharacteristicRange', 'characteristic_dependency_group_id', 'id');
    }

    /**
     * All genotypes
     *
     * @return Collection of Genotypes
     */
    public function genotypes()
    {
        return $this->belongsToMany('Genotype', 'characteristic_dependency_group_genotypes', 'characteristic_dependency_group_id', 'genotype_id');
    }

    /**
     * All ranges
     *
     * @return Collection of CharacteristicDependencyGroupRange
     */
    public function ranges()
    {
        return $this->hasMany('CharacteristicDependencyGroupRange', 'characteristic_dependency_group_id', 'id');
    }

}
