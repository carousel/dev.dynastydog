<?php

/*
|--------------------------------------------------------------------------
| Characteristic Routes
|--------------------------------------------------------------------------
|
|
|
*/

Route::group(array('prefix' => 'characteristics'), function()
{

    # AJAX
    Route::post('dropdown', array(
        'as'     => 'characteristics/dropdown', 
        'uses'   => 'CharacteristicController@postDropdown', 
        'before' => 'ajax', 
    ));

    Route::post('profiles', array(
        'as'     => 'characteristics/profiles', 
        'uses'   => 'CharacteristicController@postProfiles', 
        'before' => 'ajax', 
    ));

    Route::post('custom_import_dropdown', array(
        'as'     => 'characteristics/custom_import_dropdown', 
        'uses'   => 'CharacteristicController@postCustomImportDropdown', 
        'before' => 'ajax', 
    ));

    Route::post('custom_import_profiles', array(
        'as'     => 'characteristics/custom_import_profiles', 
        'uses'   => 'CharacteristicController@postCustomImportProfiles', 
        'before' => 'ajax', 
    ));

});
