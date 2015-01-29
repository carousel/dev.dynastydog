<?php

/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
|
|
|
*/

Route::group(array('prefix' => 'user'), function()
{

    # Profile
    Route::get('profile/{user?}', array('as' => 'user/profile', 'uses' => 'Controllers\User\ProfileController@getIndex'));

    # Kennel
    Route::group(array('prefix' => 'kennel'), function()
    {

        # Index
        Route::get('{user?}', array('as' => 'user/kennel', 'uses' => 'Controllers\User\KennelController@getIndex'));

        # Kennel Groups
        Route::get('group/add', array('as' => 'user/kennel/group/add', 'uses' => 'Controllers\User\KennelController@getAddKennelGroup'));
        Route::get('group/{kennelGroup}/delete', array('as' => 'user/kennel/group/delete', 'uses' => 'Controllers\User\KennelController@getDeleteKennelGroup'));
        Route::post('group/{kennelGroup}/update', array('as' => 'user/kennel/group/update', 'uses' => 'Controllers\User\KennelController@postUpdateKennelGroup'));

        # Dogs
        Route::post('dogs/test', array('as' => 'user/kennel/dogs/test', 'uses' => 'Controllers\User\KennelController@postTestDogs'));
        Route::post('dogs/compare', array('as' => 'user/kennel/dogs/compare', 'uses' => 'Controllers\User\KennelController@postCompareDogs'));
        Route::post('dogs/breed/request', array('as' => 'user/kennel/dogs/breed/request', 'uses' => 'Controllers\User\KennelController@postRequestToBreedDogs'));
        Route::post('dogs/breed', array('as' => 'user/kennel/dogs/breed', 'uses' => 'Controllers\User\KennelController@postBreedDogs'));
        Route::post('dogs/move', array('as' => 'user/kennel/dogs/move', 'uses' => 'Controllers\User\KennelController@postMoveDogs'));
        Route::post('dogs/stud', array('as' => 'user/kennel/dogs/stud', 'uses' => 'Controllers\User\KennelController@postStudDogs'));
        Route::post('dogs/summary', array('as' => 'user/kennel/dogs/summary', 'uses' => 'Controllers\User\KennelController@postCopyDogSummary'));
        Route::post('dogs/pethome', array('as' => 'user/kennel/dogs/pethome', 'uses' => 'Controllers\User\KennelController@postPetHomeDogs'));

        # Stud Requests
        Route::post('stud_request/manage', array('as' => 'user/kennel/stud_request/manage', 'uses' => 'Controllers\User\KennelController@postManageStudRequest'));
        
    });

    # Notifications
    Route::get('notifications', array('as' => 'user/notifications', 'uses' => 'Controllers\User\NotificationsController@getIndex'));
    Route::post('notifications/read', array('as' => 'user/notifications/read', 'uses' => 'Controllers\User\NotificationsController@postRead'));
    Route::post('notifications/read_all', array('as' => 'user/notifications/read_all', 'uses' => 'Controllers\User\NotificationsController@postReadAll'));

    # Inbox
    Route::group(array('prefix' => 'inbox'), function()
    {

        # Index
        Route::get('/', array('as' => 'user/inbox', 'uses' => 'Controllers\User\InboxController@getIndex'));

        # Conversation
        Route::get('conversation/{conversation}', array('as' => 'user/inbox/conversation', 'uses' => 'Controllers\User\InboxController@getConversation'));
        Route::post('conversation/create', array('as' => 'user/inbox/conversation/create', 'uses' => 'Controllers\User\InboxController@postCreateConversation'));
        Route::post('conversation/{conversation}/reply', array('as' => 'user/inbox/conversation/reply', 'uses' => 'Controllers\User\InboxController@postReplyToConversation'));
        Route::post('delete_conversations', array('as' => 'user/inbox/delete_conversations', 'uses' => 'Controllers\User\InboxController@postDeleteConversations'));

    });

    # Settings
    Route::get('settings', array('as' => 'user/settings', 'uses' => 'Controllers\User\SettingsController@getIndex'));
    Route::post('settings/update_basic', array('as' => 'user/settings/update_basic', 'uses' => 'Controllers\User\SettingsController@postUpdateBasic'));
    Route::post('settings/change_password', array('as' => 'user/settings/change_password', 'uses' => 'Controllers\User\SettingsController@postChangePassword'));
    Route::post('settings/block', array('as' => 'user/settings/block', 'uses' => 'Controllers\User\SettingsController@postBlock'));
    Route::post('settings/unblock', array('as' => 'user/settings/unblock', 'uses' => 'Controllers\User\SettingsController@postUnblock'));
    Route::post('settings/change_email', array('as' => 'user/settings/change_email', 'uses' => 'Controllers\User\SettingsController@postChangeEmail'));
    Route::post('settings/update_kennel_description', array('as' => 'user/settings/update_kennel_description', 'uses' => 'Controllers\User\SettingsController@postUpdateKennelDescription'));

    # Referrals
    Route::get('referrals', array('as' => 'user/referrals', 'uses' => 'Controllers\User\ReferralsController@getIndex'));
    Route::post('referrals/reset_status', array('as' => 'user/referrals/reset_status', 'uses' => 'Controllers\User\ReferralsController@postResetStatus'));
    Route::post('referrals/exchange', array('as' => 'user/referrals/exchange', 'uses' => 'Controllers\User\ReferralsController@postExchange'));


    # Advance Turn
    Route::get('advance_turn', array('as' => 'user/advance_turn', 'uses' => 'Controllers\User\ProfileController@getAdvanceTurn'));

});
