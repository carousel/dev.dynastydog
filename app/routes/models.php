<?php

/*
|--------------------------------------------------------------------------
| Route Models
|--------------------------------------------------------------------------
|
|
|
*/

Route::pattern('user', '[0-9]+');
Route::model('user', 'User', function()
{
    App::abort('404', 'User not found!');
});

Route::pattern('kennelGroup', '[0-9]+');
Route::model('kennelGroup', 'KennelGroup', function()
{
    App::abort('404', 'Kennel tab not found!');
});

Route::pattern('dog', '[0-9]+');
Route::model('dog', 'Dog', function()
{
    App::abort('404', 'Dog not found!');
});

Route::pattern('bitch', '[0-9]+');
Route::model('bitch', 'Dog', function()
{
    App::abort('404', 'Bitch not found!');
});

Route::pattern('breed', '[0-9]+');
Route::model('breed', 'Breed', function()
{
    App::abort('404', 'Breed not found!');
});

Route::pattern('breedCharacteristic', '[0-9]+');
Route::model('breedCharacteristic', 'BreedCharacteristic', function()
{
    App::abort('404', 'Characteristic not found!');
});

Route::pattern('breedDraft', '[0-9]+');
Route::model('breedDraft', 'BreedDraft', function()
{
    App::abort('404', 'Draft not found!');
});

Route::pattern('breedDraftCharacteristic', '[0-9]+');
Route::model('breedDraftCharacteristic', 'BreedDraftCharacteristic', function()
{
    App::abort('404', 'Characteristic not found!');
});

Route::pattern('dogCharacteristic', '[0-9]+');
Route::model('dogCharacteristic', 'DogCharacteristic', function()
{
    App::abort('404', 'Characteristic not found!');
});

Route::pattern('conversation', '[0-9]+');
Route::model('conversation', 'Conversation', function()
{
    App::abort('404', 'Conversation not found!');
});

Route::pattern('forum', '[0-9]+');
Route::model('forum', 'Forum', function()
{
    App::abort('404', 'Forum not found!');
});

Route::pattern('forumTopic', '[0-9]+');
Route::model('forumTopic', 'ForumTopic', function()
{
    App::abort('404', 'Forum topic not found!');
});

Route::pattern('forumPost', '[0-9]+');
Route::model('forumPost', 'ForumPost', function()
{
    App::abort('404', 'Forum post not found!');
});

Route::pattern('newsPost', '[0-9]+');
Route::model('newsPost', 'NewsPost', function()
{
    App::abort('404', 'News post not found!');
});

Route::pattern('newsPostComment', '[0-9]+');
Route::model('newsPostComment', 'NewsPostComment', function()
{
    App::abort('404', 'News post comment not found!');
});

Route::pattern('newsPoll', '[0-9]+');
Route::model('newsPoll', 'NewsPoll', function()
{
    App::abort('404', 'News poll not found!');
});

Route::pattern('newsPollAnswer', '[0-9]+');
Route::model('newsPollAnswer', 'NewsPollAnswer', function()
{
    App::abort('404', 'News poll answer not found!');
});

Route::pattern('helpCategory', '[0-9]+');
Route::model('helpCategory', 'HelpCategory', function()
{
    App::abort('404', 'Help category not found!');
});

Route::pattern('helpPage', '[0-9]+');
Route::model('helpPage', 'HelpPage', function()
{
    App::abort('404', 'Help page not found!');
});

Route::pattern('chatTurn', '[0-9]+');
Route::model('chatTurn', 'ChatTurn', function()
{
    App::abort('404', 'Chat turns not found!');
});

Route::pattern('contest', '[0-9]+');
Route::model('contest', 'Contest', function()
{
    App::abort('404', 'Contest not found!');
});

Route::pattern('contestType', '[0-9]+');
Route::model('contestType', 'UserContestType', function()
{
    App::abort('404', 'Contest type not found!');
});

Route::pattern('userContestType', '[0-9]+');
Route::model('userContestType', 'UserContestType', function()
{
    App::abort('404', 'Contest type not found!');
});

Route::pattern('userContestTypePrerequisite', '[0-9]+');
Route::model('userContestTypePrerequisite', 'UserContestTypePrerequisite', function()
{
    App::abort('404', 'Prerequisite not found!');
});

Route::pattern('userContestTypeRequirement', '[0-9]+');
Route::model('userContestTypeRequirement', 'UserContestTypeRequirement', function()
{
    App::abort('404', 'Judging requirement not found!');
});

Route::pattern('challenge', '[0-9]+');
Route::model('challenge', 'Challenge', function()
{
    App::abort('404', 'Challenge not found!');
});

Route::pattern('communityChallenge', '[0-9]+');
Route::model('communityChallenge', 'CommunityChallenge', function()
{
    App::abort('404', 'Challenge not found!');
});

Route::pattern('userGoal', '[0-9]+');
Route::model('userGoal', 'UserGoal', function()
{
    App::abort('404', 'Personal goal not found!');
});

Route::pattern('beginnersLuckRequest', '[0-9]+');
Route::model('beginnersLuckRequest', 'BeginnersLuckRequest', function()
{
    App::abort('404', 'Beginners Luck Request not found!');
});

Route::pattern('lendRequest', '[0-9]+');
Route::model('lendRequest', 'LendRequest', function()
{
    App::abort('404', 'Lend request not found!');
});

Route::model('alphaCode', 'AlphaCode', function()
{
    App::abort('404', 'Alpha code not found!');
});

Route::pattern('symptom', '[0-9]+');
Route::model('symptom', 'Symptom', function()
{
    App::abort('404', 'Symptom not found!');
});

Route::pattern('characteristic', '[0-9]+');
Route::model('characteristic', 'Characteristic', function()
{
    App::abort('404', 'Characteristic not found!');
});

Route::pattern('characteristicLabel', '[0-9]+');
Route::model('characteristicLabel', 'CharacteristicLabel', function()
{
    App::abort('404', 'Characteristic label not found!');
});

Route::pattern('characteristicCategory', '[0-9]+');
Route::model('characteristicCategory', 'CharacteristicCategory', function()
{
    App::abort('404', 'Characteristic category not found!');
});

Route::pattern('childCharacteristicCategory', '[0-9]+');
Route::model('childCharacteristicCategory', 'CharacteristicCategory', function()
{
    App::abort('404', 'Child characteristic category not found!');
});

Route::pattern('characteristicSeverity', '[0-9]+');
Route::model('characteristicSeverity', 'CharacteristicSeverity', function()
{
    App::abort('404', 'Characteristic severity not found!');
});

Route::pattern('characteristicSeveritySymptom', '[0-9]+');
Route::model('characteristicSeveritySymptom', 'CharacteristicSeveritySymptom', function()
{
    App::abort('404', 'Characteristic severity symptom not found!');
});

Route::pattern('characteristicTest', '[0-9]+');
Route::model('characteristicTest', 'CharacteristicTest', function()
{
    App::abort('404', 'Characteristic test not found!');
});

Route::pattern('characteristicDependency', '[0-9]+');
Route::model('characteristicDependency', 'CharacteristicDependency', function()
{
    App::abort('404', 'Characteristic dependency not found!');
});

Route::pattern('charDepIndChar', '[0-9]+');
Route::model('charDepIndChar', 'CharacteristicDependencyIndependentCharacteristic', function()
{
    App::abort('404', 'Characteristic dependency independent characteristic not found!');
});

Route::pattern('characteristicDependencyGroup', '[0-9]+');
Route::model('characteristicDependencyGroup', 'CharacteristicDependencyGroup', function()
{
    App::abort('404', 'Characteristic dependency group not found!');
});

Route::pattern('charDepGroupRange', '[0-9]+');
Route::model('charDepGroupRange', 'CharacteristicDependencyGroupRange', function()
{
    App::abort('404', 'Characteristic dependency group range not found!');
});

Route::pattern('charDepGroupIndCharRange', '[0-9]+');
Route::model('charDepGroupIndCharRange', 'CharacteristicDependencyGroupIndependentCharacteristicRange', function()
{
    App::abort('404', 'Characteristic dependency group independent characteristic range not found!');
});

Route::model('bannedIp', 'BannedIp', function()
{
    App::abort('404', 'Banned IP not found!');
});

Route::pattern('locus', '[0-9]+');
Route::model('locus', 'Locus', function()
{
    App::abort('404', 'Locus not found!');
});

Route::pattern('locusAllele', '[0-9]+');
Route::model('locusAllele', 'LocusAllele', function()
{
    App::abort('404', 'Locus allele not found!');
});

Route::pattern('genotype', '[0-9]+');
Route::model('genotype', 'Genotype', function()
{
    App::abort('404', 'Genotype not found!');
});

Route::pattern('phenotype', '[0-9]+');
Route::model('phenotype', 'Phenotype', function()
{
    App::abort('404', 'Phenotype not found!');
});
