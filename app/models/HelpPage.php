<?php

class HelpPage extends Eloquent {

    protected $guarded = array('id');


    /*
    |--------------------------------------------------------------------------
    | Many To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All categories this page is in
     *
     * @return Collection of HelpCategories
     */
    public function categories()
    {
        return $this->belongsToMany('HelpCategory', 'help_categories_help_pages', 'help_page_id', 'help_category_id');
    }

}
