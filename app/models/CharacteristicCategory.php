<?php

class CharacteristicCategory extends Eloquent {

    public $timestamps = false;
    
    protected $guarded = array('id');

    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    |
    |
    */

    public function setParentCategoryIdAttribute($parentCategoryId)
    {
        $this->attributes['parent_category_id'] = strlen($parentCategoryId) ? $parentCategoryId : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    |
    */

    public function scopeWhereNotHealth($query)
    {
        // Find all health
        $healthIds = DB::table('characteristic_categories')
            ->select('characteristic_categories.id as charid')
            ->leftJoin('characteristic_categories as parent', 'parent.id', '=', 'characteristic_categories.parent_category_id')
            ->where('characteristic_categories.name', 'Health')
            ->orWhere('parent.name', 'Health')
            ->lists('charid');

        return $query->whereNotIn('characteristic_categories.id', $healthIds);
    }

    public function scopeWhereHealth($query)
    {
        // Find all health
        $healthIds = DB::table('characteristic_categories')
            ->select('characteristic_categories.id as charid')
            ->leftJoin('characteristic_categories as parent', 'parent.id', '=', 'characteristic_categories.parent_category_id')
            ->where('characteristic_categories.name', 'Health')
            ->orWhere('parent.name', 'Health')
            ->lists('charid');

        return $query->whereIn('characteristic_categories.id', $healthIds);
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the parent
     *
     * @return CharacteristicCategory
     */
    public function parent()
    {
        return $this->belongsTo('CharacteristicCategory', 'parent_category_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Has Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All children
     *
     * @return Collection of CharacteristicCategory
     */
    public function children()
    {
        return $this->hasMany('CharacteristicCategory', 'parent_category_id', 'id');
    }

    /**
     * All characteristics
     *
     * @return Collection of Characteristic
     */
    public function characteristics()
    {
        return $this->hasMany('Characteristic', 'category_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function isHealth()
    {
        return ($this->name == 'Health' or ( ! is_null($this->parent) and $this->parent->name == 'Health'));
    }

}
