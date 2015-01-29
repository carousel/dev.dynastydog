<?php

class HelpCategory extends Eloquent {

    protected $guarded = array('id');

    public $timestamps = false;

    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    |
    |
    */

    public function setParentIdAttribute($parentId)
    {
        $this->attributes['parent_id'] = strlen($parentId) 
            ? $parentId 
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the parent.
     *
     * @return HelpCategory
     */
    public function parent()
    {
        return $this->belongsTo('HelpCategory', 'parent_id');
    }

    /*
    |--------------------------------------------------------------------------
    | One To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All sub-categories
     *
     * @return Collection of HelpCategories
     */
    public function subCategories()
    {
        return $this->hasMany('HelpCategory', 'parent_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Many To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All pages in this category
     *
     * @return Collection of HelpPages
     */
    public function pages()
    {
        return $this->belongsToMany('HelpPage', 'help_categories_help_pages', 'help_category_id', 'help_page_id');
    }

}
