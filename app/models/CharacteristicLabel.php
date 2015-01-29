<?php

class CharacteristicLabel extends Eloquent {

    public $timestamps = false;
    
    protected $guarded = array('id');

    public function scopeWhereInRange($query, $minRangedValue, $maxRangedValue)
    {
        return $query->where(function($q) use ($minRangedValue, $maxRangedValue)
            {
                $q->where('min_ranged_value', '<=', $minRangedValue)->where('max_ranged_value', '>=', $maxRangedValue);
            });
    }

    /**
     * Return the characteristic
     *
     * @return Characteristic
     */
    public function characteristic()
    {
        return $this->belongsTo('Characteristic', 'characteristic_id');
    }

}
