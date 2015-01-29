<?php

class CharacteristicDependencyGroupRange extends Eloquent {

    public $timestamps = false;
    
    protected $guarded = array('id');

    /**
     * Return the dependency
     *
     * @return CharacteristicDependency
     */
    public function group()
    {
        return $this->belongsTo('CharacteristicDependencyGroup', 'characteristic_dependency_group_id');
    }

}
