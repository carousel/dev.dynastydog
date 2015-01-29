<?php

class CharacteristicDependencyGroupIndependentCharacteristicGenotype extends Eloquent {

    public $table = 'characteristic_dependency_group_ind_characteristic_genotypes';

    public $timestamps = false;
    
    protected $guarded = array('id');

    /**
     * Return the group
     *
     * @return CharacteristicDependencyGroup
     */
    public function group()
    {
        return $this->belongsTo('CharacteristicDependencyGroup', 'characteristic_dependency_group_id');
    }

    /**
     * Return the independent characteristic
     *
     * @return Characteristic
     */
    public function characteristicDependencyIndependentCharacteristic()
    {
        return $this->belongsTo('CharacteristicDependencyIndependentCharacteristic', 'characteristic_dependency_ind_characteristic_id');
    }

}
