<?php

/*Route::get('sandbox', function()
{

    // foreach($dog->genotypes as $genotype)
    // {
    //     var_dump($genotype->locus->name.': '.$genotype->toSymbol());
    // }

    // $genotypeIds = $dog->genotypes->lists('id');


    // $characteristic = Characteristic::find(100);
    // $severity = BreedCharacteristic::find(422)->getRandomSeverity(true, $dog->age);


    //         var_dump($characteristic->eligibleForSeverity($genotypeIds));
    //         var_dump($severity->toArray());

    //         exit();



    $coll = new Illuminate\Support\Collection;

    $coll->push('aaa');
    $coll->push('bbb');
    $coll->push('ccc');

    var_dump($coll->random());
    exit();

});*/

/*
|--------------------------------------------------------------------------
| Route Models
|--------------------------------------------------------------------------
|
|
|
*/

require_once app_path().'/routes/models.php';


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
|
|
*/

require_once app_path().'/routes/admin.php';


/*
|--------------------------------------------------------------------------
| Characteristic Routes
|--------------------------------------------------------------------------
|
|
|
*/

require_once app_path().'/routes/characteristic.php';


/*
|--------------------------------------------------------------------------
| Dog Routes
|--------------------------------------------------------------------------
|
|
|
*/

require_once app_path().'/routes/dog.php';


/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
|
|
|
*/

require_once app_path().'/routes/user.php';


/*
|--------------------------------------------------------------------------
| Forums Routes
|--------------------------------------------------------------------------
|
|
|
*/

require_once app_path().'/routes/forums.php';


/*
|--------------------------------------------------------------------------
| Cash Shop Routes
|--------------------------------------------------------------------------
|
|
|
*/

Route::group(array('prefix' => 'cash_shop'), function()
{

    # Home
    Route::get('/', array('as' => 'cash_shop', 'uses' => 'CashShopController@getIndex'));

    Route::post('purchase_upgrade', array('as' => 'cash_shop/purchase_upgrade', 'uses' => 'CashShopController@postPurchaseUpgrade'));
    Route::post('purchase_turns', array('as' => 'cash_shop/purchase_turns', 'uses' => 'CashShopController@postPurchaseTurns'));
    Route::post('purchase_imports', array('as' => 'cash_shop/purchase_imports', 'uses' => 'CashShopController@postPurchaseImports'));
    Route::post('purchase_custom_imports', array('as' => 'cash_shop/purchase_custom_imports', 'uses' => 'CashShopController@postPurchaseCustomImports'));
    Route::post('gift_credits', array('as' => 'cash_shop/gift_credits', 'uses' => 'CashShopController@postGiftCredits'));
    Route::post('gift_upgrade', array('as' => 'cash_shop/gift_upgrade', 'uses' => 'CashShopController@postGiftUpgrade'));
    Route::post('gift_turns', array('as' => 'cash_shop/gift_turns', 'uses' => 'CashShopController@postGiftTurns'));

});


/*
|--------------------------------------------------------------------------
| Search Routes
|--------------------------------------------------------------------------
|
|
|
*/

Route::group(array('prefix' => 'search'), function()
{

    # Index
    Route::get('/', array('as' => 'search', 'uses' => 'SearchController@getIndex'));

    # Forums
    Route::get('forums', array('as' => 'search/forums', 'uses' => 'SearchController@getForums'));
    Route::get('users', array('as' => 'search/users', 'uses' => 'SearchController@getUsers'));
    Route::get('dogs', array('as' => 'search/dogs', 'uses' => 'SearchController@getDogs'));

});


/*
|--------------------------------------------------------------------------
| Payments Routes
|--------------------------------------------------------------------------
|
|
|
*/

Route::group(array('prefix' => 'imports'), function()
{

    # Index
    Route::get('/', array('as' => 'imports', 'uses' => 'ImportsController@getIndex'));

    # Import
    Route::post('import', array('as' => 'imports/import', 'uses' => 'ImportsController@postImport'));

    # Custom Import
    Route::post('custom_import', array('as' => 'imports/custom_import', 'uses' => 'ImportsController@postCustomImport'));

});


/*
|--------------------------------------------------------------------------
| Breed Registry Routes
|--------------------------------------------------------------------------
|
|
|
*/

Route::group(array('prefix' => 'breed_registry'), function()
{

    # Home
    Route::get('/', array('as' => 'breed_registry', 'uses' => 'Controllers\BreedRegistry\SearchController@getIndex'));
    Route::get('manage', array('as' => 'breed_registry/manage', 'uses' => 'Controllers\BreedRegistry\DraftsController@getManage'));

    Route::get('breed/{breed}', array('as' => 'breed_registry/breed', 'uses' => 'Controllers\BreedRegistry\BreedController@getIndex'));
    Route::get('breed/characteristic/{breedCharacteristic}', array('as' => 'breed_registry/breed/characteristic', 'uses' => 'Controllers\BreedRegistry\BreedController@getCharacteristic'));

    Route::get('drafts/official', array('as' => 'breed_registry/drafts/official', 'uses' => 'Controllers\BreedRegistry\DraftsController@getOfficial'));
    Route::get('drafts/new', array('as' => 'breed_registry/drafts/new', 'uses' => 'Controllers\BreedRegistry\DraftsController@getNewDraft'));
    Route::post('drafts/new', array('uses' => 'Controllers\BreedRegistry\DraftsController@postNewDraft'));
    Route::get('drafts/official/new', array('as' => 'breed_registry/drafts/official/new', 'uses' => 'Controllers\BreedRegistry\DraftsController@getNewOfficial'));
    Route::post('drafts/official/new', array('uses' => 'Controllers\BreedRegistry\DraftsController@postNewOfficial'));

    Route::get('draft/{breedDraft}', array('as' => 'breed_registry/draft/form', 'uses' => 'Controllers\BreedRegistry\DraftController@getForm'));
    Route::post('draft/{breedDraft}', array('uses' => 'Controllers\BreedRegistry\DraftController@postForm'));
    Route::get('draft/{breedDraft}/submit', array('as' => 'breed_registry/draft/form/submit', 'uses' => 'Controllers\BreedRegistry\DraftController@getSubmitForm'));

    Route::post('draft/{breedDraft}/characteristic/add', array('as' => 'breed_registry/draft/form/characteristic/add', 'uses' => 'Controllers\BreedRegistry\DraftController@postFormAddCharacteristic'));
    Route::get('draft/characteristic/{breedDraftCharacteristic}', array('as' => 'breed_registry/draft/form/characteristic', 'uses' => 'Controllers\BreedRegistry\DraftController@getFormCharacteristic'));
    Route::post('draft/characteristic/{breedDraftCharacteristic}/save', array('as' => 'breed_registry/draft/form/characteristic/save', 'uses' => 'Controllers\BreedRegistry\DraftController@postSaveFormCharacteristic'));
    Route::get('draft/characteristic/{breedDraftCharacteristic}/remove', array('as' => 'breed_registry/draft/form/characteristic/remove', 'uses' => 'Controllers\BreedRegistry\DraftController@getRemoveFormCharacteristic'));

    Route::get('draft/submitted/{breedDraft}', array('as' => 'breed_registry/draft/submitted', 'uses' => 'Controllers\BreedRegistry\DraftController@getSubmitted'));
    Route::get('draft/submitted/{breedDraft}/revert', array('as' => 'breed_registry/draft/submitted/revert', 'uses' => 'Controllers\BreedRegistry\DraftController@getRevertSubmittedToDraft'));
    Route::post('draft/submitted/{breedDraft}/resubmit', array('as' => 'breed_registry/draft/submitted/resubmit', 'uses' => 'Controllers\BreedRegistry\DraftController@postResubmitExtinct'));
    Route::get('draft/submitted/characteristic/{breedDraftCharacteristic}', array('as' => 'breed_registry/draft/submitted/characteristic', 'uses' => 'Controllers\BreedRegistry\DraftController@getSubmittedCharacteristic'));

    Route::get('draft/{breedDraft}/delete', array('as' => 'breed_registry/draft/delete', 'uses' => 'Controllers\BreedRegistry\DraftController@getDelete'));

});


/*
|--------------------------------------------------------------------------
| Contest Routes
|--------------------------------------------------------------------------
|
|
|
*/

Route::group(array('prefix' => 'contests'), function()
{

    # Home
    Route::get('/', array('as' => 'contests', 'uses' => 'ContestsController@getIndex'));
    Route::get('enter/{dog}/{contest}', array('as' => 'contests/enter', 'uses' => 'ContestsController@getEnterDogInContest'));

    # Manage
    Route::get('manage', array('as' => 'contests/manage', 'uses' => 'ContestsController@getManage'));
    Route::post('create', array('as' => 'contests/create', 'uses' => 'ContestsController@postCreateContest'));

    # Types
    Route::post('type/create', array('as' => 'contests/type/create', 'uses' => 'ContestsController@postCreateContestType'));
    Route::get('type/{userContestType}', array('as' => 'contests/type', 'uses' => 'ContestsController@getType'));
    Route::get('type/{userContestType}/delete', array('as' => 'contests/type/delete', 'uses' => 'ContestsController@getDeleteContestType'));
    Route::post('type/{userContestType}/update', array('as' => 'contests/type/update', 'uses' => 'ContestsController@postUpdateContestType'));
    Route::post('type/{userContestType}/add_prerequisites', array('as' => 'contests/type/add_prerequisites', 'uses' => 'ContestsController@postAddPrerequisites'));
    Route::post('type/{userContestType}/add_requirements', array('as' => 'contests/type/add_requirements', 'uses' => 'ContestsController@postAddRequirements'));
    Route::get('prerequisite/{userContestTypePrerequisite}/delete', array('as' => 'contests/type/delete_prerequisite', 'uses' => 'ContestsController@getDeletePrerequisite'));
    Route::get('requirement/{userContestTypeRequirement}/delete', array('as' => 'contests/type/delete_requirement', 'uses' => 'ContestsController@getDeleteRequirement'));
    Route::post('type/{userContestType}/update_prerequisite', array('as' => 'contests/type/update_prerequisite', 'uses' => 'ContestsController@postUpdatePrerequisite'));
    Route::post('type/{userContestType}/update_requirement', array('as' => 'contests/type/update_requirement', 'uses' => 'ContestsController@postUpdateRequirement'));

});


/*
|--------------------------------------------------------------------------
| Goal Routes
|--------------------------------------------------------------------------
|
|
|
*/

Route::group(array('prefix' => 'goals'), function()
{

    # Home
    Route::get('/', array('as' => 'goals', 'uses' => 'GoalsController@getIndex'));

    # Challenges
    Route::get('challenge/roll', array('as' => 'goals/challenge/roll', 'uses' => 'GoalsController@getRollChallenge'));
    Route::post('challenge/{challenge}/complete', array('as' => 'goals/challenge/complete', 'uses' => 'GoalsController@postCompleteChallenge'));
    Route::get('challenge/{challenge}/reroll', array('as' => 'goals/challenge/reroll', 'uses' => 'GoalsController@getRerollChallenge'));

    # Community Challenges
    Route::get('community/prizes', array('as' => 'goals/community/prizes', 'uses' => 'GoalsController@getCommunityChallengePrizes'));
    Route::get('community/{communityChallenge}/claim/credits', array('as' => 'goals/community/claim/credits', 'uses' => 'GoalsController@getClaimCommunityChallengeCreditPrize'));
    Route::get('community/{communityChallenge}/claim/breeders', array('as' => 'goals/community/claim/breeders', 'uses' => 'GoalsController@getClaimCommunityChallengeBreedersPrize'));
    Route::post('community/{communityChallenge}/enter', array('as' => 'goals/community/enter', 'uses' => 'GoalsController@postEnterCommunityChallenge'));

    # Personal Goals
    Route::post('personal/create', array('as' => 'goals/personal/create', 'uses' => 'GoalsController@postCreatePersonalGoal'));
    Route::post('personal/{userGoal}/update', array('as' => 'goals/personal/update', 'uses' => 'GoalsController@postUpdatePersonalGoal'));
    Route::get('personal/{userGoal}/complete', array('as' => 'goals/personal/complete', 'uses' => 'GoalsController@getCompletePersonalGoal'));
    Route::get('personal/{userGoal}/delete', array('as' => 'goals/personal/delete', 'uses' => 'GoalsController@getDeletePersonalGoal'));

});


/*
|--------------------------------------------------------------------------
| News Routes
|--------------------------------------------------------------------------
|
|
|
*/

Route::group(array('prefix' => 'news'), function()
{

    # Index
    Route::get('/', array('as' => 'news', 'uses' => 'NewsController@getIndex'));

    # News Post
    Route::get('/{newsPost}', array('as' => 'news/post', 'uses' => 'NewsController@getPost'));
    Route::post('/{newsPost}/comment', array('as' => 'news/post/comment', 'uses' => 'NewsController@postCommentOnPost'));

    # News Poll
    Route::post('/{newsPoll}/vote', array('as' => 'news/poll/vote', 'uses' => 'NewsController@postVoteOnPoll'));

});


/*
|--------------------------------------------------------------------------
| Help Routes
|--------------------------------------------------------------------------
|
|
|
*/

Route::group(array('prefix' => 'help'), function()
{

    # Index
    Route::get('/', array('as' => 'help', 'uses' => 'HelpController@getIndex'));

    # Help Category
    Route::get('category/{helpCategory}', array('as' => 'help/category', 'uses' => 'HelpController@getCategory'));

    # Help Post
    Route::get('page/{helpPage}', array('as' => 'help/page', 'uses' => 'HelpController@getPage'));

});


/*
|--------------------------------------------------------------------------
| Authentication and Authorization Routes
|--------------------------------------------------------------------------
|
|
|
*/

Route::group(array('prefix' => 'auth'), function()
{

    # Login
    Route::post('login', array('as' => 'auth/login', 'uses' => 'AuthController@postLogin'));

    # Register
    Route::get('register', array('as' => 'auth/register', 'uses' => 'AuthController@getRegister'));
    Route::post('register', 'AuthController@postRegister');

    # Account Activation
    Route::get('activate', array('as' => 'auth/activate', 'uses' => 'AuthController@getActivate'));
    Route::post('activate', array('uses' => 'AuthController@postActivate'));

    # Forgot Password
    Route::get('forgot_password', array('as' => 'auth/forgot_password', 'uses' => 'AuthController@getForgotPassword'));
    Route::post('forgot_password', 'AuthController@postForgotPassword');

    # Forgot Password Confirmation
    Route::get('forgot_password/{passwordResetCode}', array('as' => 'auth/forgot_password_confirm', 'uses' => 'AuthController@getForgotPasswordConfirm'));
    Route::post('forgot_password/{passwordResetCode}', 'AuthController@postForgotPasswordConfirm');

    # Logout
    Route::get('logout', array('as' => 'auth/logout', 'uses' => 'AuthController@getLogout'));

});

/*
|--------------------------------------------------------------------------
| Chat Routes
|--------------------------------------------------------------------------
|
*/

Route::group(array('prefix' => 'chat'), function()
{

    # Messages
    Route::get('/', array('as' => 'chat/messages', 'uses' => 'ChatController@getMessages'));

    # Create
    Route::post('create', array('as' => 'chat/create', 'before' => 'auth', 'uses' => 'ChatController@postCreate'));

    # Delete
    Route::post('delete', array('as' => 'chat/delete', 'before' => 'auth', 'uses' => 'ChatController@postDelete'));

    # Give Turns
    Route::post('give_turns', array('as' => 'chat/give_turns', 'before' => 'auth', 'uses' => 'ChatController@postGiveTurns'));

    # Claim Turns
    Route::get('claim_turns/{chatTurn}', array('as' => 'chat/claim_turns', 'before' => 'auth', 'uses' => 'ChatController@getClaimTurns'));

});


/*
|--------------------------------------------------------------------------
| Payments Routes
|--------------------------------------------------------------------------
|
|
|
*/

Route::group(array('prefix' => 'payments'), function()
{

    # PayPal IPN
    Route::post('ipn', array('as' => 'payments/paypal-ipn', 'uses' => 'PaymentsController@postPaypalIpn'));

});


/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get("/brain",function(){
    return View::make("brain.index");
});
Route::get('goals', array('as' => 'goals', 'uses' => 'GoalsController@getIndex'));
Route::get('online', array('as' => 'online', 'uses' => 'OnlineController@getIndex'));
Route::get('tos', array('as' => 'tos', 'uses' => 'HomeController@getTermsOfService'));
Route::get('privacy', array('as' => 'privacy', 'uses' => 'HomeController@getPrivacyPolicy'));

Route::get('staff', array('as' => 'staff', 'uses' => 'HomeController@getStaff'));
Route::post('staff', array('uses' => 'HomeController@postStaff'));

Route::get('community_guidelines', array('as' => 'community_guidelines', 'uses' => 'HomeController@getCommunityGuidelines'));

Route::get('/', array('as' => 'home', 'before' => 'guest', 'uses' => 'HomeController@getIndex'));
