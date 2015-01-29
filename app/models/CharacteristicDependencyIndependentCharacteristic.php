<?php

class CharacteristicDependencyIndependentCharacteristic extends Eloquent {

    public $table = 'characteristic_dependency_ind_characteristics';

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
     * Return the characteristic
     *
     * @return Characteristic
     */
    public function characteristic()
    {
        return $this->belongsTo('Characteristic', 'independent_characteristic_id');
    }

    /**
     * All genotypes in ranges
     *
     * @return Collection of CharacteristicDependencyGroupIndependentCharacteristicRange
     */
    public function ranges()
    {
        return $this->hasMany('CharacteristicDependencyGroupIndependentCharacteristicRange', 'characteristic_dependency_ind_characteristic_id', 'id');
    }

    /**
     * All genotypes in groups
     *
     * @return Collection of CharacteristicDependencyGroupIndependentCharacteristicGenotype
     */
    public function genotypes()
    {
        return $this->hasMany('CharacteristicDependencyGroupIndependentCharacteristicGenotype', 'characteristic_dependency_ind_characteristic_id', 'id');
    }

}
