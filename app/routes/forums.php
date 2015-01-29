<?php

/*
|--------------------------------------------------------------------------
| Forums Routes
|--------------------------------------------------------------------------
|
|
|
*/

Route::group(array('prefix' => 'forums'), function()
{

    # Index
    Route::get('/', array('as' => 'forums', 'uses' => 'ForumsController@getIndex'));

    # Forums
    Route::get('forum/{forum}', array('as' => 'forums/forum', 'uses' => 'ForumsController@getForum'));

    # Topic
    Route::get('topic/{forumTopic}', array('as' => 'forums/topic', 'uses' => 'ForumsController@getTopic'));
    Route::post('topic/{forumTopic}', array('uses' => 'ForumsController@postTopic'));
    
    Route::post('topic/{forumTopic}/reply', array('as' => 'forums/topic/reply', 'uses' => 'ForumsController@postReplyToTopic'));
    Route::get('topic/{forumTopic}/bump', array('as' => 'forums/topic/bump', 'uses' => 'ForumsController@getBumpTopic'));
    Route::post('topic/{forumTopic}/preview', array('as' => 'forums/topic/preview', 'uses' => 'ForumsController@postPreviewReply'));

    Route::get('topic/create', array('as' => 'forums/topic/create', 'uses' => 'ForumsController@getCreateTopic'));
    Route::post('topic/create', array('uses' => 'ForumsController@postCreateTopic'));

    # Topics
    Route::get('topics/active', array('as' => 'forums/topics/active', 'uses' => 'ForumsController@getActiveTopics'));
    Route::get('topics/user', array('as' => 'forums/topics/user', 'uses' => 'ForumsController@getUserTopics'));

    # Community Guidelines
    Route::post('agree', array('as' => 'forums/agree_to_community_guidelines', 'uses' => 'ForumsController@postAgreeToCommunityGuidelines'));

});

