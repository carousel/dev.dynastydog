<?php

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
|
|
*/

Route::group(array('prefix' => 'admin'), function()
{

    # Alpha Code Management
    Route::group(array('prefix' => 'alphacodes'), function()
    {


    });

    # Alpha Page
    Route::group(array('prefix' => 'alpha'), function()
    {

        Route::get('/', array('as' => 'admin/alpha', 'uses' => 'Controllers\Admin\AlphaController@getIndex'));

        Route::get('code/create', array('as' => 'admin/alpha/code/create', 'uses' => 'Controllers\Admin\AlphaController@getCreateAlphaCode'));
        Route::post('code/create', array('uses' => 'Controllers\Admin\AlphaController@postCreateAlphaCode'));

        Route::get('code/{alphaCode}/edit', array('as' => 'admin/alpha/code/edit', 'uses' => 'Controllers\Admin\AlphaController@getEditAlphaCode'));
        Route::post('code/{alphaCode}/edit', array('uses' => 'Controllers\Admin\AlphaController@postEditAlphaCode'));

        Route::get('code/{alphaCode}/delete', array('as' => 'admin/alpha/code/delete', 'uses' => 'Controllers\Admin\AlphaController@getDeleteAlphaCode'));

    });

    # Breeds Page
    Route::group(array('prefix' => 'breeds'), function()
    {

        # Breeds
        Route::get('/', array('as' => 'admin/breeds', 'uses' => 'Controllers\Admin\BreedsController@getIndex'));
        Route::get('manage', array('as' => 'admin/breeds/manage', 'uses' => 'Controllers\Admin\BreedsController@getManageBreeds'));
        Route::post('manage/add_characteristics', array('as' => 'admin/breeds/manage/add_characteristics', 'uses' => 'Controllers\Admin\BreedsController@postAddCharacteristicsToBreeds'));
        Route::post('manage/add_genotypes', array('as' => 'admin/breeds/manage/add_genotypes', 'uses' => 'Controllers\Admin\BreedsController@postAddGenotypesToBreeds'));

        Route::get('breed/create', array('as' => 'admin/breeds/breed/create', 'uses' => 'Controllers\Admin\BreedsController@getCreateBreed'));
        Route::post('breed/create', array('uses' => 'Controllers\Admin\BreedsController@postCreateBreed'));

        Route::get('breed/{breed}/edit', array('as' => 'admin/breeds/breed/edit', 'uses' => 'Controllers\Admin\BreedsController@getEditBreed'));
        Route::post('breed/{breed}/edit', array('uses' => 'Controllers\Admin\BreedsController@postEditBreed'));

        Route::get('breed/{breed}/delete', array('as' => 'admin/breeds/breed/delete', 'uses' => 'Controllers\Admin\BreedsController@getDeleteBreed'));
        Route::get('breed/{breed}/clone', array('as' => 'admin/breeds/breed/clone', 'uses' => 'Controllers\Admin\BreedsController@getCloneBreed'));

        Route::post('breed/{breed}/genotypes/update', array('as' => 'admin/breeds/breed/genotypes/update', 'uses' => 'Controllers\Admin\BreedsController@postUpdateBreedGenotypes'));

        Route::post('breed/{breed}/characteristic/create', array('as' => 'admin/breeds/breed/characteristic/create', 'uses' => 'Controllers\Admin\BreedsController@postCreateBreedCharacteristic'));
        Route::post('breed/characteristic/{breedCharacteristic}/update', array('as' => 'admin/breeds/breed/characteristic/update', 'uses' => 'Controllers\Admin\BreedsController@postUpdateBreedCharacteristic'));
        Route::get('breed/characteristic/{breedCharacteristic}/delete', array('as' => 'admin/breeds/breed/characteristic/delete', 'uses' => 'Controllers\Admin\BreedsController@getDeleteBreedCharacteristic'));


        # Breed Drafts
        Route::get('drafts', array('as' => 'admin/breeds/breed/drafts', 'uses' => 'Controllers\Admin\BreedsController@getBreedDrafts'));

        Route::get('breed/draft/{breedDraft}/edit', array('as' => 'admin/breeds/breed/draft/edit', 'uses' => 'Controllers\Admin\BreedsController@getEditBreedDraft'));
        Route::post('breed/draft/{breedDraft}/edit', array('uses' => 'Controllers\Admin\BreedsController@postEditBreedDraft'));

        Route::get('breed/draft/{breedDraft}/approve', array('as' => 'admin/breeds/breed/draft/approve', 'uses' => 'Controllers\Admin\BreedsController@getApproveBreedDraft'));
        Route::post('breed/draft/{breedDraft}/reject', array('as' => 'admin/breeds/breed/draft/reject', 'uses' => 'Controllers\Admin\BreedsController@postRejectBreedDraft'));

    });

    # Characteristics Page
    Route::group(array('prefix' => 'characteristics'), function()
    {

        # Characteristics
        Route::get('/', array('as' => 'admin/characteristics', 'uses' => 'Controllers\Admin\CharacteristicsController@getIndex'));

        Route::get('characteristic/create', array('as' => 'admin/characteristics/characteristic/create', 'uses' => 'Controllers\Admin\CharacteristicsController@getCreateCharacteristic'));
        Route::post('characteristic/create', array('uses' => 'Controllers\Admin\CharacteristicsController@postCreateCharacteristic'));

        Route::get('characteristic/{characteristic}/edit', array('as' => 'admin/characteristics/characteristic/edit', 'uses' => 'Controllers\Admin\CharacteristicsController@getEditCharacteristic'));
        Route::post('characteristic/{characteristic}/edit', array('uses' => 'Controllers\Admin\CharacteristicsController@postEditCharacteristic'));

        Route::get('characteristic/{characteristic}/delete', array('as' => 'admin/characteristics/characteristic/delete', 'uses' => 'Controllers\Admin\CharacteristicsController@getDeleteCharacteristic'));

        Route::post('characteristic/{characteristic}/range/update', array('as' => 'admin/characteristics/characteristic/range/update', 'uses' => 'Controllers\Admin\CharacteristicsController@postUpdateCharacteristicRange'));
        Route::get('characteristic/{characteristic}/range/remove', array('as' => 'admin/characteristics/characteristic/range/remove', 'uses' => 'Controllers\Admin\CharacteristicsController@getRemoveCharacteristicRange'));

        Route::post('characteristic/{characteristic}/label/add', array('as' => 'admin/characteristics/characteristic/label/add', 'uses' => 'Controllers\Admin\CharacteristicsController@postAddLabelToCharacteristic'));
        Route::get('characteristic/label/{characteristicLabel}/delete', array('as' => 'admin/characteristics/characteristic/label/delete', 'uses' => 'Controllers\Admin\CharacteristicsController@getRemoveLabelFromCharacteristic'));

        Route::post('characteristic/{characteristic}/genetics/update', array('as' => 'admin/characteristics/characteristic/genetics/update', 'uses' => 'Controllers\Admin\CharacteristicsController@postUpdateCharacteristicGenetics'));
        Route::get('characteristic/{characteristic}/genetics/remove', array('as' => 'admin/characteristics/characteristic/genetics/remove', 'uses' => 'Controllers\Admin\CharacteristicsController@getRemoveCharacteristicGenetics'));

        Route::post('characteristic/{characteristic}/health/update', array('as' => 'admin/characteristics/characteristic/health/update', 'uses' => 'Controllers\Admin\CharacteristicsController@postUpdateCharacteristicHealth'));
        Route::get('characteristic/{characteristic}/health/remove', array('as' => 'admin/characteristics/characteristic/health/remove', 'uses' => 'Controllers\Admin\CharacteristicsController@getRemoveCharacteristicHealth'));

        Route::post('characteristic/{characteristic}/severity/add', array('as' => 'admin/characteristics/characteristic/severity/add', 'uses' => 'Controllers\Admin\CharacteristicsController@postAddSeverityToCharacteristic'));

        # Characteristic Severities
        Route::get('characteristic/severity/{characteristicSeverity}/edit', array('as' => 'admin/characteristics/characteristic/severity/edit', 'uses' => 'Controllers\Admin\CharacteristicsController@getEditCharacteristicSeverity'));
        Route::post('characteristic/severity/{characteristicSeverity}/update', array('as' => 'admin/characteristics/characteristic/severity/update', 'uses' => 'Controllers\Admin\CharacteristicsController@postUpdateCharacteristicSeverity'));

        Route::get('characteristic/severity/{characteristicSeverity}/delete', array('as' => 'admin/characteristics/characteristic/severity/delete', 'uses' => 'Controllers\Admin\CharacteristicsController@getRemoveSeverityFromCharacteristic'));
        
        Route::post('characteristic/severity/{characteristicSeverity}/symptom/add', array('as' => 'admin/characteristics/characteristic/severity/symptom/add', 'uses' => 'Controllers\Admin\CharacteristicsController@postAddSymptomToCharacteristicSeverity'));
        Route::post('characteristic/severity/symptom/{characteristicSeveritySymptom}/update', array('as' => 'admin/characteristics/characteristic/severity/symptom/update', 'uses' => 'Controllers\Admin\CharacteristicsController@postUpdateCharacteristicSeveritySymptom'));
        Route::get('characteristic/severity/symptom/{characteristicSeveritySymptom}/delete', array('as' => 'admin/characteristics/characteristic/severity/symptom/delete', 'uses' => 'Controllers\Admin\CharacteristicsController@getRemoveSymptomFromCharacteristicSeverity'));

        # Characteristic Categories
        Route::get('categories', array('as' => 'admin/characteristics/categories', 'uses' => 'Controllers\Admin\CharacteristicsController@getCharacteristicCategories'));

        Route::get('category/create', array('as' => 'admin/characteristics/category/create', 'uses' => 'Controllers\Admin\CharacteristicsController@getCreateCharacteristicCategory'));
        Route::post('category/create', array('uses' => 'Controllers\Admin\CharacteristicsController@postCreateCharacteristicCategory'));

        Route::get('category/{characteristicCategory}/edit', array('as' => 'admin/characteristics/category/edit', 'uses' => 'Controllers\Admin\CharacteristicsController@getEditCharacteristicCategory'));
        Route::post('category/{characteristicCategory}/edit', array('uses' => 'Controllers\Admin\CharacteristicsController@postEditCharacteristicCategory'));

        Route::get('category/{characteristicCategory}/delete', array('as' => 'admin/characteristics/category/delete', 'uses' => 'Controllers\Admin\CharacteristicsController@getDeleteCharacteristicCategory'));

        Route::get('category/{characteristicCategory}/remove_child_category/{childCharacteristicCategory}', array('as' => 'admin/characteristics/category/remove_child_category', 'uses' => 'Controllers\Admin\CharacteristicsController@getRemoveChildCharacteristicCategoryFromParentCharacteristicCategory'));
        Route::get('category/{characteristicCategory}/remove_characteristic/{characteristic}', array('as' => 'admin/characteristics/category/remove_characteristic', 'uses' => 'Controllers\Admin\CharacteristicsController@getRemoveCharacteristicFromCharacteristicCategory'));

        # Characteristic Tests
        Route::get('tests', array('as' => 'admin/characteristics/tests', 'uses' => 'Controllers\Admin\CharacteristicsController@getCharacteristicTests'));

        Route::get('test/create', array('as' => 'admin/characteristics/test/create', 'uses' => 'Controllers\Admin\CharacteristicsController@getCreateCharacteristicTest'));
        Route::post('test/create', array('uses' => 'Controllers\Admin\CharacteristicsController@postCreateCharacteristicTest'));

        Route::get('test/{characteristicTest}/edit', array('as' => 'admin/characteristics/test/edit', 'uses' => 'Controllers\Admin\CharacteristicsController@getEditCharacteristicTest'));
        Route::post('test/{characteristicTest}/edit', array('uses' => 'Controllers\Admin\CharacteristicsController@postEditCharacteristicTest'));

        Route::get('test/{characteristicTest}/delete', array('as' => 'admin/characteristics/test/delete', 'uses' => 'Controllers\Admin\CharacteristicsController@getDeleteCharacteristicTest'));

        # Characteristic Dependencies
        Route::get('dependencies', array('as' => 'admin/characteristics/dependencies', 'uses' => 'Controllers\Admin\CharacteristicsController@getCharacteristicDependencies'));

        Route::get('dependency/create', array('as' => 'admin/characteristics/dependency/create', 'uses' => 'Controllers\Admin\CharacteristicsController@getCreateCharacteristicDependency'));
        Route::post('dependency/create', array('uses' => 'Controllers\Admin\CharacteristicsController@postCreateCharacteristicDependency'));

        Route::get('dependency/{characteristicDependency}/edit', array('as' => 'admin/characteristics/dependency/edit', 'uses' => 'Controllers\Admin\CharacteristicsController@getEditCharacteristicDependency'));
        Route::post('dependency/{characteristicDependency}/edit', array('uses' => 'Controllers\Admin\CharacteristicsController@postEditCharacteristicDependency'));

        Route::get('dependency/{characteristicDependency}/delete', array('as' => 'admin/characteristics/dependency/delete', 'uses' => 'Controllers\Admin\CharacteristicsController@getDeleteCharacteristicDependency'));
        
        Route::post('dependency/{characteristicDependency}/add_independents', array('as' => 'admin/characteristics/dependendency/add_independents', 'uses' => 'Controllers\Admin\CharacteristicsController@postAddIndependentCharacteristicToCharacteristicDependency'));
        Route::get('dependency/independent/{charDepIndChar}/remove', array('as' => 'admin/characteristics/dependency/independent/remove', 'uses' => 'Controllers\Admin\CharacteristicsController@getRemoveCharacteristicDependencyIndependentCharacteristic'));

        Route::post('dependency/independent/{charDepIndChar}/update_percents', array('as' => 'admin/characteristics/dependency/independent/update_percents', 'uses' => 'Controllers\Admin\CharacteristicsController@postUpdateCharacteristicDependendencyIndependentCharacteristicPercents'));

        Route::post('dependency/{characteristicDependency}/dependency/group/g2r/create', array('as' => 'admin/characteristics/dependency/group/g2r/create', 'uses' => 'Controllers\Admin\CharacteristicsController@postCreateG2RCharacteristicDependencyGroup'));
        Route::post('dependency/{characteristicDependency}/dependency/group/x2g/create', array('as' => 'admin/characteristics/dependency/group/x2g/create', 'uses' => 'Controllers\Admin\CharacteristicsController@postCreateX2GCharacteristicDependencyGroup'));

        Route::post('dependency/dependency/group/{characteristicDependencyGroup}/g2x/update', array('as' => 'admin/characteristics/dependency/group/g2x/update', 'uses' => 'Controllers\Admin\CharacteristicsController@postUpdateG2XCharacteristicDependencyGroup'));
        Route::post('dependency/dependency/group/{characteristicDependencyGroup}/x2g/update', array('as' => 'admin/characteristics/dependency/group/x2g/update', 'uses' => 'Controllers\Admin\CharacteristicsController@postUpdateX2GCharacteristicDependencyGroup'));

        Route::get('dependency/dependency/group/{characteristicDependencyGroup}/delete', array('as' => 'admin/characteristics/dependency/group/delete', 'uses' => 'Controllers\Admin\CharacteristicsController@geteDeleteCharacteristicDependencyGroup'));

        Route::post('dependency/dependency/group/{characteristicDependencyGroup}/range/add', array('as' => 'admin/characteristics/dependency/group/range/add', 'uses' => 'Controllers\Admin\CharacteristicsController@postAddRangeToCharacteristicDependencyGroup'));
        Route::post('dependency/dependency/group/{characteristicDependencyGroup}/genotypes/add', array('as' => 'admin/characteristics/dependency/group/genotypes/add', 'uses' => 'Controllers\Admin\CharacteristicsController@postAddGenotypesToCharacteristicDependencyGroup'));

        Route::post('dependency/dependency/group/{characteristicDependencyGroup}/independent/range/add', array('as' => 'admin/characteristics/dependency/group/independent/range/add', 'uses' => 'Controllers\Admin\CharacteristicsController@postAddIndependentRangeToCharacteristicDependencyGroup'));
        Route::get('dependency/dependency/group/{charDepGroupIndCharRange}/independent/range/remove', array('as' => 'admin/characteristics/dependency/group/independent/range/remove', 'uses' => 'Controllers\Admin\CharacteristicsController@getRemoveIndependentRangeFromCharacteristicDependencyGroup'));

        Route::get('dependency/dependency/group/range/{charDepGroupRange}/remove', array('as' => 'admin/characteristics/dependency/group/range/remove', 'uses' => 'Controllers\Admin\CharacteristicsController@getRemoveRangeFromCharacteristicDependencyGroup'));

    });

    # Dogs Page
    Route::group(array('prefix' => 'dogs'), function()
    {

        Route::get('/', array('as' => 'admin/dogs', 'uses' => 'Controllers\Admin\DogsController@getIndex'));
        Route::get('manage', array('as' => 'admin/dogs/manage', 'uses' => 'Controllers\Admin\DogsController@getManageDogs'));
        Route::post('manage/age_dogs', array('as' => 'admin/dogs/manage/age_dogs', 'uses' => 'Controllers\Admin\DogsController@postAgeDogs'));

        Route::post('dog/find', array('as' => 'admin/dogs/dog/find', 'uses' => 'Controllers\Admin\DogsController@postFindDog'));

        Route::get('dog/{dog}/edit', array('as' => 'admin/dogs/dog/edit', 'uses' => 'Controllers\Admin\DogsController@getEditDog'));
        Route::post('dog/{dog}/edit', array('uses' => 'Controllers\Admin\DogsController@postEditDog'));

        Route::get('dog/{dog}/delete', array('as' => 'admin/dogs/dog/delete', 'uses' => 'Controllers\Admin\DogsController@getDeleteDog'));
        Route::get('dog/{dog}/recomplete', array('as' => 'admin/dogs/dog/recomplete', 'uses' => 'Controllers\Admin\DogsController@getRecompleteDog'));
        Route::get('dog/{dog}/refresh_phenotypes', array('as' => 'admin/dogs/dog/refresh_phenotypes', 'uses' => 'Controllers\Admin\DogsController@getRefreshPhenotypesForDog'));

    });

    # Forums Management
    Route::group(array('prefix' => 'forums'), function()
    {
        # Forums
        Route::get('/', array('as' => 'admin/forums', 'uses' => 'Controllers\Admin\ForumsController@getIndex'));

        Route::get('forum/create', array('as' => 'admin/forums/forum/create', 'uses' => 'Controllers\Admin\ForumsController@getCreateForum'));
        Route::post('forum/create', array('uses' => 'Controllers\Admin\ForumsController@postCreateForum'));

        Route::get('forum/{forum}/edit', array('as' => 'admin/forums/forum/edit', 'uses' => 'Controllers\Admin\ForumsController@getEditForum'));
        Route::post('forum/{forum}/edit', array('uses' => 'Controllers\Admin\ForumsController@postEditForum'));

        Route::get('forum/{forum}/delete', array('as' => 'admin/forums/forum/delete', 'uses' => 'Controllers\Admin\ForumsController@getDeleteForum'));

        # Forum Topics
        Route::get('forum/topics', array('as' => 'admin/forums/forum/topics', 'uses' => 'Controllers\Admin\ForumsController@getForumTopics'));
        Route::post('forum/{forum}/topics/delete', array('as' => 'admin/forums/forum/topics/delete', 'uses' => 'Controllers\Admin\ForumsController@postDeleteForumTopics'));

        Route::get('forum/topic/{forumTopic}/delete', array('as' => 'admin/forums/forum/topic/delete', 'uses' => 'Controllers\Admin\ForumsController@getDeleteForumTopic'));
        Route::get('forum/topic/{forumTopicId}/delete/permanent', array('as' => 'admin/forums/forum/topic/delete/permanent', 'uses' => 'Controllers\Admin\ForumsController@getPermanentlyDeleteForumTopic'));
        Route::get('forum/topic/{forumTopicId}/restore', array('as' => 'admin/forums/forum/topic/restore', 'uses' => 'Controllers\Admin\ForumsController@getRestoreForumTopic'));

        Route::get('forum/topic/{forumTopic}/edit', array('as' => 'admin/forums/forum/topic/edit', 'uses' => 'Controllers\Admin\ForumsController@getEditForumTopic'));
        Route::post('forum/topic/{forumTopic}/update', array('as' => 'admin/forums/forum/topic/update', 'uses' => 'Controllers\Admin\ForumsController@postUpdateForumTopic'));

        Route::get('forum/topic/{forumTopic}/sticky', array('as' => 'admin/forums/forum/topic/sticky', 'uses' => 'Controllers\Admin\ForumsController@getStickyForumTopic'));
        Route::get('forum/topic/{forumTopic}/lock', array('as' => 'admin/forums/forum/topic/lock', 'uses' => 'Controllers\Admin\ForumsController@getLockForumTopic'));
        Route::post('forum/topic/{forumTopic}/move', array('as' => 'admin/forums/forum/topic/move', 'uses' => 'Controllers\Admin\ForumsController@postMoveForumTopic'));

        # Forum Posts
        Route::get('forum/posts', array('as' => 'admin/forums/forum/posts', 'uses' => 'Controllers\Admin\ForumsController@getForumPosts'));

        Route::get('forum/post/{forumPost}/delete', array('as' => 'admin/forums/forum/post/delete', 'uses' => 'Controllers\Admin\ForumsController@getDeleteForumPost'));
        Route::get('forum/post/{forumPostId}/delete/permanent', array('as' => 'admin/forums/forum/post/delete/permanent', 'uses' => 'Controllers\Admin\ForumsController@getPermanentlyDeleteForumPost'));
        Route::get('forum/post/{forumPostId}/restore', array('as' => 'admin/forums/forum/post/restore', 'uses' => 'Controllers\Admin\ForumsController@getRestoreForumPost'));

        Route::get('forum/post/{forumPost}/edit', array('as' => 'admin/forums/forum/post/edit', 'uses' => 'Controllers\Admin\ForumsController@getEditForumPost'));
        Route::post('forum/post/{forumPost}/update', array('as' => 'admin/forums/forum/post/update', 'uses' => 'Controllers\Admin\ForumsController@postUpdateForumPost'));

    });

    # Genetics Page
    Route::group(array('prefix' => 'genetics'), function()
    {

        # Loci
        Route::get('/', array('as' => 'admin/genetics', 'uses' => 'Controllers\Admin\GeneticsController@getIndex'));

        Route::get('locus/create', array('as' => 'admin/genetics/locus/create', 'uses' => 'Controllers\Admin\GeneticsController@getCreateLocus'));
        Route::post('locus/create', array('uses' => 'Controllers\Admin\GeneticsController@postCreateLocus'));

        Route::get('locus/{locus}/edit', array('as' => 'admin/genetics/locus/edit', 'uses' => 'Controllers\Admin\GeneticsController@getEditLocus'));
        Route::post('locus/{locus}/edit', array('uses' => 'Controllers\Admin\GeneticsController@postEditLocus'));

        Route::get('locus/{locus}/delete', array('as' => 'admin/genetics/locus/delete', 'uses' => 'Controllers\Admin\GeneticsController@getDeleteLocus'));

        # Locus Alleles
        Route::get('locus/alleles', array('as' => 'admin/genetics/locus/alleles', 'uses' => 'Controllers\Admin\GeneticsController@getLocusAlleles'));

        Route::get('locus/allele/create', array('as' => 'admin/genetics/locus/allele/create', 'uses' => 'Controllers\Admin\GeneticsController@getCreateLocusAllele'));
        Route::post('locus/allele/create', array('uses' => 'Controllers\Admin\GeneticsController@postCreateLocusAllele'));

        Route::get('locus/allele/{locusAllele}/edit', array('as' => 'admin/genetics/locus/allele/edit', 'uses' => 'Controllers\Admin\GeneticsController@getEditLocusAllele'));
        Route::post('locus/allele/{locusAllele}/edit', array('uses' => 'Controllers\Admin\GeneticsController@postEditLocusAllele'));

        Route::get('locus/allele/{locusAllele}/delete', array('as' => 'admin/genetics/locus/allele/delete', 'uses' => 'Controllers\Admin\GeneticsController@getDeleteLocusAllele'));

        # Genotypes
        Route::get('genotypes', array('as' => 'admin/genetics/genotypes', 'uses' => 'Controllers\Admin\GeneticsController@getGenotypes'));

        Route::get('genotype/create', array('as' => 'admin/genetics/genotype/create', 'uses' => 'Controllers\Admin\GeneticsController@getCreateGenotype'));
        Route::post('genotype/create', array('uses' => 'Controllers\Admin\GeneticsController@postCreateGenotype'));

        Route::get('genotype/{genotype}/edit', array('as' => 'admin/genetics/genotype/edit', 'uses' => 'Controllers\Admin\GeneticsController@getEditGenotype'));
        Route::post('genotype/{genotype}/edit', array('uses' => 'Controllers\Admin\GeneticsController@postEditGenotype'));

        Route::get('genotype/{genotype}/delete', array('as' => 'admin/genetics/genotype/delete', 'uses' => 'Controllers\Admin\GeneticsController@getDeleteGenotype'));

        # Phenotypes
        Route::get('phenotypes', array('as' => 'admin/genetics/phenotypes', 'uses' => 'Controllers\Admin\GeneticsController@getPhenotypes'));

        Route::get('phenotype/create', array('as' => 'admin/genetics/phenotype/create', 'uses' => 'Controllers\Admin\GeneticsController@getCreatePhenotype'));
        Route::post('phenotype/create', array('uses' => 'Controllers\Admin\GeneticsController@postCreatePhenotype'));

        Route::get('phenotype/{phenotype}/edit', array('as' => 'admin/genetics/phenotype/edit', 'uses' => 'Controllers\Admin\GeneticsController@getEditPhenotype'));
        Route::post('phenotype/{phenotype}/edit', array('uses' => 'Controllers\Admin\GeneticsController@postEditPhenotype'));

        Route::get('phenotype/{phenotype}/delete', array('as' => 'admin/genetics/phenotype/delete', 'uses' => 'Controllers\Admin\GeneticsController@getDeletePhenotype'));
        Route::get('phenotype/{phenotype}/clone', array('as' => 'admin/genetics/phenotype/clone', 'uses' => 'Controllers\Admin\GeneticsController@getClonePhenotype'));

    });

    # Goals Page
    Route::group(array('prefix' => 'goals'), function()
    {

        Route::get('/', array('as' => 'admin/goals', 'uses' => 'Controllers\Admin\GoalsController@getIndex'));

        Route::get('community/challenge/create', array('as' => 'admin/goals/community/challenge/create', 'uses' => 'Controllers\Admin\GoalsController@getCreateCommunityChallenge'));
        Route::post('community/challenge/create', array('uses' => 'Controllers\Admin\GoalsController@postCreateCommunityChallenge'));

        Route::get('community/challenge/{communityChallenge}/edit', array('as' => 'admin/goals/community/challenge/edit', 'uses' => 'Controllers\Admin\GoalsController@getEditCommunityChallenge'));
        Route::post('community/challenge/{communityChallenge}/edit', array('uses' => 'Controllers\Admin\GoalsController@postEditCommunityChallenge'));

    });

    # Health Page
    Route::group(array('prefix' => 'health'), function()
    {

        Route::get('/', array('as' => 'admin/health', 'uses' => 'Controllers\Admin\HealthController@getIndex'));

        Route::get('symptom/create', array('as' => 'admin/health/symptom/create', 'uses' => 'Controllers\Admin\HealthController@getCreateSymptom'));
        Route::post('symptom/create', array('uses' => 'Controllers\Admin\HealthController@postCreateSymptom'));

        Route::get('symptom/{symptom}/edit', array('as' => 'admin/health/symptom/edit', 'uses' => 'Controllers\Admin\HealthController@getEditSymptom'));
        Route::post('symptom/{symptom}/edit', array('uses' => 'Controllers\Admin\HealthController@postEditSymptom'));

        Route::get('symptom/{symptom}/delete', array('as' => 'admin/health/symptom/delete', 'uses' => 'Controllers\Admin\HealthController@getDeleteSymptom'));

        Route::get('symptom/{symptom}/characteristic/{characteristic}/remove_from_all_severities', array('as' => 'admin/health/symptom/characteristic/remove_from_all_severities', 'uses' => 'Controllers\Admin\HealthController@getRemoveSymptomFromCharacteristic'));

    });

    # Help Page
    Route::group(array('prefix' => 'help'), function()
    {

        # Help Categories
        Route::get('/', array('as' => 'admin/help', 'uses' => 'Controllers\Admin\HelpController@getIndex'));

        Route::get('category/create', array('as' => 'admin/help/help/category/create', 'uses' => 'Controllers\Admin\HelpController@getCreateHelpCategory'));
        Route::post('category/create', array('uses' => 'Controllers\Admin\HelpController@postCreateHelpCategory'));


        Route::get('category/{helpCategory}/edit', array('as' => 'admin/help/help/category/edit', 'uses' => 'Controllers\Admin\HelpController@getEditHelpCategory'));
        Route::post('category/{helpCategory}/edit', array('uses' => 'Controllers\Admin\HelpController@postEditHelpCategory'));

        Route::get('category/{helpCategory}/delete', array('as' => 'admin/help/help/category/delete', 'uses' => 'Controllers\Admin\HelpController@getDeleteHelpCategory'));

        # Help Pages
        Route::get('pages', array('as' => 'admin/help/help/pages', 'uses' => 'Controllers\Admin\HelpController@getHelpPages'));

        Route::get('page/create', array('as' => 'admin/help/help/page/create', 'uses' => 'Controllers\Admin\HelpController@getCreateHelpPage'));
        Route::post('page/create', array('uses' => 'Controllers\Admin\HelpController@postCreateHelpPage'));


        Route::get('page/{helpPage}/edit', array('as' => 'admin/help/help/page/edit', 'uses' => 'Controllers\Admin\HelpController@getEditHelpPage'));
        Route::post('page/{helpPage}/edit', array('uses' => 'Controllers\Admin\HelpController@postEditHelpPage'));

        Route::get('page/{helpPage}/delete', array('as' => 'admin/help/help/page/delete', 'uses' => 'Controllers\Admin\HelpController@getDeleteHelpPage'));
        
    });

    # News Page
    Route::group(array('prefix' => 'news'), function()
    {

        # News Posts
        Route::get('/', array('as' => 'admin/news', 'uses' => 'Controllers\Admin\NewsController@getIndex'));

        Route::get('post/create', array('as' => 'admin/news/post/create', 'uses' => 'Controllers\Admin\NewsController@getCreateNewsPost'));
        Route::post('post/create', array('uses' => 'Controllers\Admin\NewsController@postCreateNewsPost'));

        Route::get('post/{newsPost}/edit', array('as' => 'admin/news/post/edit', 'uses' => 'Controllers\Admin\NewsController@getEditNewsPost'));
        Route::post('post/{newsPost}/edit', array('uses' => 'Controllers\Admin\NewsController@postEditNewsPost'));

        Route::get('post/{newsPost}/delete', array('as' => 'admin/news/post/delete', 'uses' => 'Controllers\Admin\NewsController@getDeleteNewsPost'));
        Route::get('post/{newsPost}/poll/{newsPoll}/add', array('as' => 'admin/news/post/poll/add', 'uses' => 'Controllers\Admin\NewsController@getAddNewsPollToNewsPost'));
        Route::get('post/{newsPost}/poll/{newsPoll}/remove', array('as' => 'admin/news/post/poll/remove', 'uses' => 'Controllers\Admin\NewsController@getRemoveNewsPollFromNewsPost'));

        # News Post Comments
        Route::get('post/comments', array('as' => 'admin/news/post/comments', 'uses' => 'Controllers\Admin\NewsController@getNewsPostComments'));

        Route::get('post/comment/{newsPostComment}/edit', array('as' => 'admin/news/post/comment/edit', 'uses' => 'Controllers\Admin\NewsController@getEditNewsPostComment'));
        Route::post('post/comment/{newsPostComment}/edit', array('uses' => 'Controllers\Admin\NewsController@postEditNewsPostComment'));

        Route::get('post/comment/{newsPostComment}/delete', array('as' => 'admin/news/post/comment/delete', 'uses' => 'Controllers\Admin\NewsController@getDeleteNewsPostComment'));

        # News Polls
        Route::get('polls', array('as' => 'admin/news/polls', 'uses' => 'Controllers\Admin\NewsController@getNewsPolls'));

        Route::get('poll/create', array('as' => 'admin/news/poll/create', 'uses' => 'Controllers\Admin\NewsController@getCreateNewsPoll'));
        Route::post('poll/create', array('uses' => 'Controllers\Admin\NewsController@postCreateNewsPoll'));

        Route::get('poll/{newsPoll}/edit', array('as' => 'admin/news/poll/edit', 'uses' => 'Controllers\Admin\NewsController@getEditNewsPoll'));
        Route::post('poll/{newsPoll}/edit', array('uses' => 'Controllers\Admin\NewsController@postEditNewsPoll'));

        Route::get('poll/{newsPoll}/delete', array('as' => 'admin/news/poll/delete', 'uses' => 'Controllers\Admin\NewsController@getDeleteNewsPoll'));

        Route::post('poll/{newsPoll}/answer/create', array('as' => 'admin/news/poll/answer/create', 'uses' => 'Controllers\Admin\NewsController@postCreateNewsPollAnswer'));
        Route::post('poll/answer/{newsPollAnswer}/edit', array('as' => 'admin/news/poll/answer/edit', 'uses' => 'Controllers\Admin\NewsController@postEditNewsPollAnswer'));
        Route::get('poll/answer/{newsPollAnswer}/delete', array('as' => 'admin/news/poll/answer/delete', 'uses' => 'Controllers\Admin\NewsController@getDeleteNewsPollAnswer'));

    });

    # Users Page
    Route::group(array('prefix' => 'users'), function()
    {

        # Users
        Route::get('/', array('as' => 'admin/users', 'uses' => 'Controllers\Admin\UsersController@getIndex'));
        Route::get('manage', array('as' => 'admin/users/manage', 'uses' => 'Controllers\Admin\UsersController@getManageUsers'));

        Route::post('manage/give_currency', array('as' => 'admin/users/manage/give_currency', 'uses' => 'Controllers\Admin\UsersController@postGiveCurrency'));
        Route::post('manage/ban_ip', array('as' => 'admin/users/manage/ban_ip', 'uses' => 'Controllers\Admin\UsersController@postBanIp'));
        Route::get('manage/unban_ip/{bannedIp}', array('as' => 'admin/users/manage/unban_ip', 'uses' => 'Controllers\Admin\UsersController@getUnbanIp'));

        Route::post('user/find', array('as' => 'admin/users/user/find', 'uses' => 'Controllers\Admin\UsersController@postFindUser'));

        Route::get('user/{user}/edit', array('as' => 'admin/users/user/edit', 'uses' => 'Controllers\Admin\UsersController@getEditUser'));
        Route::post('user/{user}/edit', array('uses' => 'Controllers\Admin\UsersController@postEditUser'));

        Route::post('user/{user}/ban', array('as' => 'admin/users/user/ban', 'uses' => 'Controllers\Admin\UsersController@postBanUser'));
        Route::get('user/{user}/unban', array('as' => 'admin/users/user/unban', 'uses' => 'Controllers\Admin\UsersController@getUnbanUser'));
        Route::get('user/{user}/unban_chat', array('as' => 'admin/users/user/unban_chat', 'uses' => 'Controllers\Admin\UsersController@getUnbanChatUser'));

        Route::get('user/{user}/delete', array('as' => 'admin/users/user/delete', 'uses' => 'Controllers\Admin\UsersController@getDeleteUser'));
        Route::get('user/{userId}/delete/permanent', array('as' => 'admin/users/user/delete/permanent', 'uses' => 'Controllers\Admin\UsersController@getPermanentlyDeleteUser'));
        Route::get('user/{userId}/restore', array('as' => 'admin/users/user/restore', 'uses' => 'Controllers\Admin\UsersController@getRestoreUser'));

        # Kennel Groups
        Route::post('kennel_group/{kennelGroup}/update', array('as' => 'admin/users/kennel_group/update', 'uses' => 'Controllers\Admin\UsersController@postUpdateKennelGroup'));

        # Contests
        Route::get('contests', array('as' => 'admin/users/contests', 'uses' => 'Controllers\Admin\UsersController@getContests'));

        Route::get('contest/{contest}/edit', array('as' => 'admin/users/contest/edit', 'uses' => 'Controllers\Admin\UsersController@getEditContest'));
        Route::post('contest/{contest}/edit', array('uses' => 'Controllers\Admin\UsersController@postEditContest'));

        Route::get('contest/{contest}/delete', array('as' => 'admin/users/contest/delete', 'uses' => 'Controllers\Admin\UsersController@getDeleteContest'));

        # Contest Types
        Route::get('contest/types', array('as' => 'admin/users/contest/types', 'uses' => 'Controllers\Admin\UsersController@getContestTypes'));

        Route::get('contest/type/{contestType}/edit', array('as' => 'admin/users/contest/type/edit', 'uses' => 'Controllers\Admin\UsersController@getEditContestType'));
        Route::post('contest/type/{contestType}/edit', array('uses' => 'Controllers\Admin\UsersController@postEditContestType'));

        Route::get('contest/type/{contestType}/delete', array('as' => 'admin/users/contest/type/delete', 'uses' => 'Controllers\Admin\UsersController@getDeleteContestType'));

    });

    # Dashboard
    Route::get('/', array(
        'as' => 'admin', 
        'uses' => 'Controllers\Admin\DashboardController@getIndex', 
    ));

});
