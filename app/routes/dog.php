<?php

/*
|--------------------------------------------------------------------------
| Dog Routes
|--------------------------------------------------------------------------
|
|
|
*/

Route::group(array('prefix' => 'dog'), function()
{

    # Profile
    Route::get('{dog}', array('as' => 'dog/profile', 'uses' => 'Controllers\Dog\ProfileController@getIndex'));
    Route::post('{dog}/profile/change_name', array('as' => 'dog/profile/change_name', 'uses' => 'Controllers\Dog\ProfileController@postChangeName'));
    Route::post('{dog}/profile/save_notes', array('as' => 'dog/profile/save_notes', 'uses' => 'Controllers\Dog\ProfileController@postSaveNotes'));
    Route::post('{dog}/profile/change_image', array('as' => 'dog/profile/change_image', 'uses' => 'Controllers\Dog\ProfileController@postChangeImage'));
    Route::post('{dog}/profile/change_breed', array('as' => 'dog/profile/change_breed', 'uses' => 'Controllers\Dog\ProfileController@postChangeBreed'));
    Route::get('{dog}/profile/add_prefix', array('as' => 'dog/profile/add_prefix', 'uses' => 'Controllers\Dog\ProfileController@getAddPrefix'));
    Route::post('{dog}/profile/manage_studding', array('as' => 'dog/profile/manage_studding', 'uses' => 'Controllers\Dog\ProfileController@postManageStudding'));

    Route::get('{dog}/profile/complete', array('as' => 'dog/profile/complete', 'uses' => 'Controllers\Dog\ProfileController@getComplete'));

    Route::get('{dog}/pet_home', array('as' => 'dog/pet_home', 'uses' => 'Controllers\Dog\ProfileController@getPetHome'));

    Route::post('{dog}/lend', array('as' => 'dog/lend', 'uses' => 'Controllers\Dog\ProfileController@postLend'));
    Route::get('lend/{lendRequest}/accept', array('as' => 'dog/lend/accept', 'uses' => 'Controllers\Dog\ProfileController@getAcceptLendRequest'));
    Route::get('lend/{lendRequest}/reject', array('as' => 'dog/lend/reject', 'uses' => 'Controllers\Dog\ProfileController@getRejectLendRequest'));
    Route::get('lend/{lendRequest}/revoke', array('as' => 'dog/lend/revoke', 'uses' => 'Controllers\Dog\ProfileController@getRevokeLendRequest'));
    Route::get('lend/{lendRequest}/return', array('as' => 'dog/lend/return', 'uses' => 'Controllers\Dog\ProfileController@getReturnLendRequest'));

    Route::post('{dog}/profile/summary/add', array('as' => 'dog/profile/summary/add', 'uses' => 'Controllers\Dog\ProfileController@postAddToSummary'));
    Route::get('dog/profile/summary/{dogCharacteristic}/remove', array('as' => 'dog/profile/summary/remove', 'uses' => 'Controllers\Dog\ProfileController@getRemoveFromSummary'));

    Route::post('test/perform', array('as' => 'dog/test/perform', 'uses' => 'Controllers\Dog\TestController@postPerform'));

    Route::post('{dog}/breeding/request', array('as' => 'dog/breed/request', 'uses' => 'Controllers\Dog\ProfileController@postRequestBreeding'));
    Route::get('{dog}/breed_to/{bitch}', array('as' => 'dog/breed', 'uses' => 'Controllers\Dog\ProfileController@getBreedDogs'));

    Route::get('{dog}/blr/{bitch}', array('as' => 'dog/blr', 'uses' => 'Controllers\Dog\BeginnersLuckController@getIndex'));
    Route::get('{dog}/blr/{bitch}/from/{user}', array('as' => 'dog/blr/request', 'uses' => 'Controllers\Dog\BeginnersLuckController@getRequest'));

    Route::get('blr/{beginnersLuckRequest}/accept', array('as' => 'dog/blr/accept', 'uses' => 'Controllers\Dog\BeginnersLuckController@getAccept'));
    Route::get('blr/{beginnersLuckRequest}/reject', array('as' => 'dog/blr/reject', 'uses' => 'Controllers\Dog\BeginnersLuckController@getReject'));
    Route::get('blr/{beginnersLuckRequest}/revoke', array('as' => 'dog/blr/revoke', 'uses' => 'Controllers\Dog\BeginnersLuckController@getRevoke'));

});
