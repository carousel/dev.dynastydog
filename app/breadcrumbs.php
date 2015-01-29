<?php

/*
|--------------------------------------------------------------------------
| Front-end Breadcrumbs
|--------------------------------------------------------------------------
|
|
*/

# Help

Breadcrumbs::register('help', function($breadcrumbs) {
    $breadcrumbs->push('Help', route('help'));
});

Breadcrumbs::register('help/category', function($breadcrumbs, $category) {
    $breadcrumbs->parent('help');

    // Get all categories and sub categories
    $parents = array($category);

    // Set the first parent
    $parent = $category->parent;

    while ( ! is_null($parent))
    {
        $parents[] = $parent;

        $parent = $parent->parent;
    }

    // Reverse the parents
    $parents = array_reverse($parents);

    foreach($parents as $parent)
    {
        $breadcrumbs->push($parent->title, route('help/category', $parent->id)); 
    }
});

Breadcrumbs::register('help/page', function($breadcrumbs, $page) {
    $breadcrumbs->parent('help');
    $breadcrumbs->push('Page');
    $breadcrumbs->push($page->title, route('help/page', $page->id)); 
});

# Forums

Breadcrumbs::register('forums', function($breadcrumbs) {
    $breadcrumbs->push('Forums', route('forums'));
});

Breadcrumbs::register('forums/topics/active', function($breadcrumbs) {
    $breadcrumbs->parent('forums');
    $breadcrumbs->push('Active Topics', route('forums/topics/active'));
});

Breadcrumbs::register('forums/topic/create', function($breadcrumbs) {
    $breadcrumbs->parent('forums');
    $breadcrumbs->push('New Topic', route('forums/topic/create'));
});

Breadcrumbs::register('forums/forum', function($breadcrumbs, $forum) {
    $breadcrumbs->parent('forums');
    $breadcrumbs->push(e($forum->title), route('forums/forum', $forum->id)); 
});

Breadcrumbs::register('forums/topic', function($breadcrumbs, $topic) {
    $breadcrumbs->parent('forums/forum', $topic->forum);
    $breadcrumbs->push(e($topic->title), route('forums/topic', $topic->id)); 
});

# Search

Breadcrumbs::register('search', function($breadcrumbs) {
    $breadcrumbs->push('Search', route('search'));
});

Breadcrumbs::register('search/forums', function($breadcrumbs) {
    $breadcrumbs->parent('search');
    $breadcrumbs->push('Forums', route('search/forums'));
});

Breadcrumbs::register('search/users', function($breadcrumbs) {
    $breadcrumbs->parent('search');
    $breadcrumbs->push('Players', route('search/users'));
});

Breadcrumbs::register('search/dogs', function($breadcrumbs) {
    $breadcrumbs->parent('search');
    $breadcrumbs->push('Dogs', route('search/dogs'));
});

# Contests

Breadcrumbs::register('contests', function($breadcrumbs) {
    $breadcrumbs->push('Contests', route('contests'));
});

Breadcrumbs::register('contests/manage', function($breadcrumbs) {
    $breadcrumbs->parent('contests');
    $breadcrumbs->push('Manage', route('contests/manage'));
});

Breadcrumbs::register('contests/type', function($breadcrumbs, $contestType) {
    $breadcrumbs->parent('contests/manage');
    $breadcrumbs->push('Types');
    $breadcrumbs->push(e($contestType->name), route('contests/type', $contestType->id));
});

# Goals

Breadcrumbs::register('goals/community/prizes', function($breadcrumbs) {
    $breadcrumbs->push('Goals', route('goals'));
    $breadcrumbs->push('Community Challenges', route('goals', ['tab' => 'community']));
    $breadcrumbs->push('Claim Your Prizes', route('goals/community/prizes'));
});

# Admin

Breadcrumbs::register('admin', function($breadcrumbs) {
    $breadcrumbs->push('<i class="fa fa-fw fa-home"></i>', route('home'));
    $breadcrumbs->push('Admin Panel', route('admin'));
});

Breadcrumbs::register('admin/alpha', function($breadcrumbs) {
    $breadcrumbs->parent('admin');
    $breadcrumbs->push('Alpha', route('admin/alpha'));
});

Breadcrumbs::register('admin/alpha/code/create', function($breadcrumbs) {
    $breadcrumbs->parent('admin/alpha');
    $breadcrumbs->push('Create Alpha Code', route('admin/alpha/code/create'));
});

Breadcrumbs::register('admin/alpha/code/edit', function($breadcrumbs, $alphaCode) {
    $breadcrumbs->parent('admin/alpha');
    $breadcrumbs->push('Edit Alpha Code', route('admin/alpha/code/edit', $alphaCode->code));
});

Breadcrumbs::register('admin/news', function($breadcrumbs) {
    $breadcrumbs->parent('admin');
    $breadcrumbs->push('News', route('admin/news'));
});

Breadcrumbs::register('admin/news/post/create', function($breadcrumbs) {
    $breadcrumbs->parent('admin/news');
    $breadcrumbs->push('Create News Post', route('admin/news/post/create'));
});

Breadcrumbs::register('admin/news/post/edit', function($breadcrumbs, $newsPost) {
    $breadcrumbs->parent('admin/news');
    $breadcrumbs->push('Edit News Post', route('admin/news/post/edit', $newsPost->id));
});

Breadcrumbs::register('admin/news/post/comments', function($breadcrumbs) {
    $breadcrumbs->parent('admin/news');
    $breadcrumbs->push('News Post Comments', route('admin/news/post/comments'));
});

Breadcrumbs::register('admin/news/post/comment/edit', function($breadcrumbs, $newsPostComment) {
    $breadcrumbs->parent('admin/news/post/comments');
    $breadcrumbs->push('Edit News Post Comment', route('admin/news/post/comment/edit', $newsPostComment->id));
});

Breadcrumbs::register('admin/news/polls', function($breadcrumbs) {
    $breadcrumbs->parent('admin/news');
    $breadcrumbs->push('News Polls', route('admin/news/polls'));
});

Breadcrumbs::register('admin/news/poll/create', function($breadcrumbs) {
    $breadcrumbs->parent('admin/news/polls');
    $breadcrumbs->push('Create News Poll', route('admin/news/poll/create'));
});

Breadcrumbs::register('admin/news/poll/edit', function($breadcrumbs, $newsPoll) {
    $breadcrumbs->parent('admin/news/polls');
    $breadcrumbs->push('Edit News Poll', route('admin/news/poll/edit', $newsPoll->id));
});

Breadcrumbs::register('admin/health', function($breadcrumbs) {
    $breadcrumbs->parent('admin');
    $breadcrumbs->push('Health', route('admin/health'));
});

Breadcrumbs::register('admin/health/symptom/create', function($breadcrumbs) {
    $breadcrumbs->parent('admin/health');
    $breadcrumbs->push('Create Symptom', route('admin/health/symptom/create'));
});

Breadcrumbs::register('admin/health/symptom/edit', function($breadcrumbs, $symptom) {
    $breadcrumbs->parent('admin/health');
    $breadcrumbs->push('Edit Symptom', route('admin/health/symptom/edit', $symptom->id));
});

Breadcrumbs::register('admin/characteristics', function($breadcrumbs) {
    $breadcrumbs->parent('admin');
    $breadcrumbs->push('Characteristics', route('admin/characteristics'));
});

Breadcrumbs::register('admin/characteristics/characteristic/create', function($breadcrumbs) {
    $breadcrumbs->parent('admin/characteristics');
    $breadcrumbs->push('Create Characteristic', route('admin/characteristics/characteristic/create'));
});

Breadcrumbs::register('admin/characteristics/characteristic/edit', function($breadcrumbs, $characteristic) {
    $breadcrumbs->parent('admin/characteristics');
    $breadcrumbs->push('Edit Characteristic', route('admin/characteristics/characteristic/edit', $characteristic->id));
});

Breadcrumbs::register('admin/characteristics/characteristic/severity/edit', function($breadcrumbs, $characteristicSeverity) {
    $breadcrumbs->parent('admin/characteristics/characteristic/edit', $characteristicSeverity->characteristic);
    $breadcrumbs->push('Edit Characteristic Severity', route('admin/characteristics/characteristic/severity/edit', $characteristicSeverity->id));
});

Breadcrumbs::register('admin/characteristics/categories', function($breadcrumbs) {
    $breadcrumbs->parent('admin/characteristics');
    $breadcrumbs->push('Characteristic Categories', route('admin/characteristics/categories'));
});

Breadcrumbs::register('admin/characteristics/category/create', function($breadcrumbs) {
    $breadcrumbs->parent('admin/characteristics/categories');
    $breadcrumbs->push('Create Characteristic Category', route('admin/characteristics/category/create'));
});

Breadcrumbs::register('admin/characteristics/category/edit', function($breadcrumbs, $characteristicCategory) {
    $breadcrumbs->parent('admin/characteristics/categories');
    $breadcrumbs->push('Edit Characteristic Category', route('admin/characteristics/category/edit', $characteristicCategory->id));
});

Breadcrumbs::register('admin/characteristics/dependencies', function($breadcrumbs) {
    $breadcrumbs->parent('admin/characteristics');
    $breadcrumbs->push('Characteristic Dependencies', route('admin/characteristics/dependencies'));
});

Breadcrumbs::register('admin/characteristics/dependency/create', function($breadcrumbs) {
    $breadcrumbs->parent('admin/characteristics/dependencies');
    $breadcrumbs->push('Create Characteristic Dependency', route('admin/characteristics/dependency/create'));
});

Breadcrumbs::register('admin/characteristics/dependency/edit', function($breadcrumbs, $characteristicDependency) {
    $breadcrumbs->parent('admin/characteristics/dependencies');
    $breadcrumbs->push('Edit Characteristic Dependency', route('admin/characteristics/dependency/edit', $characteristicDependency->id));
});

Breadcrumbs::register('admin/characteristics/tests', function($breadcrumbs) {
    $breadcrumbs->parent('admin/characteristics');
    $breadcrumbs->push('Characteristic Tests', route('admin/characteristics/tests'));
});

Breadcrumbs::register('admin/characteristics/test/create', function($breadcrumbs) {
    $breadcrumbs->parent('admin/characteristics/tests');
    $breadcrumbs->push('Create Characteristic Test', route('admin/characteristics/test/create'));
});

Breadcrumbs::register('admin/characteristics/test/edit', function($breadcrumbs, $characteristicTest) {
    $breadcrumbs->parent('admin/characteristics/tests');
    $breadcrumbs->push('Edit Characteristic Test', route('admin/characteristics/test/edit', $characteristicTest->id));
});

Breadcrumbs::register('admin/forums', function($breadcrumbs) {
    $breadcrumbs->parent('admin');
    $breadcrumbs->push('Forums', route('admin/forums'));
});

Breadcrumbs::register('admin/forums/forum/create', function($breadcrumbs) {
    $breadcrumbs->parent('admin/forums');
    $breadcrumbs->push('Create Forum', route('admin/forums/forum/create'));
});

Breadcrumbs::register('admin/forums/forum/edit', function($breadcrumbs, $forum) {
    $breadcrumbs->parent('admin/forums');
    $breadcrumbs->push('Edit Forum', route('admin/forums/forum/edit', $forum->id));
});

Breadcrumbs::register('admin/forums/forum/topics', function($breadcrumbs) {
    $breadcrumbs->parent('admin/forums');
    $breadcrumbs->push('Forum Topics', route('admin/forums/forum/topics'));
});

Breadcrumbs::register('admin/forums/forum/topic/edit', function($breadcrumbs, $forumTopic) {
    $breadcrumbs->parent('admin/forums/forum/topics');
    $breadcrumbs->push('Edit Forum Topic', route('admin/forums/forum/topic/edit', $forumTopic->id));
});

Breadcrumbs::register('admin/forums/forum/posts', function($breadcrumbs) {
    $breadcrumbs->parent('admin/forums');
    $breadcrumbs->push('Forum Posts', route('admin/forums/forum/posts'));
});

Breadcrumbs::register('admin/forums/forum/post/edit', function($breadcrumbs, $forumPost) {
    $breadcrumbs->parent('admin/forums/forum/posts');
    $breadcrumbs->push('Edit Forum Post', route('admin/forums/forum/post/edit', $forumPost->id));
});

Breadcrumbs::register('admin/help', function($breadcrumbs) {
    $breadcrumbs->parent('admin');
    $breadcrumbs->push('Help', route('admin/help'));
});

Breadcrumbs::register('admin/help/help/category/create', function($breadcrumbs) {
    $breadcrumbs->parent('admin/help');
    $breadcrumbs->push('Create Help Category', route('admin/forums/help/category/create'));
});

Breadcrumbs::register('admin/help/help/category/edit', function($breadcrumbs, $helpCategory) {
    $breadcrumbs->parent('admin/help');
    $breadcrumbs->push('Edit Help Category', route('admin/help/help/category/edit', $helpCategory->id));
});

Breadcrumbs::register('admin/help/help/pages', function($breadcrumbs) {
    $breadcrumbs->parent('admin/help');
    $breadcrumbs->push('Help Pages', route('admin/help/help/pages'));
});

Breadcrumbs::register('admin/help/help/page/create', function($breadcrumbs) {
    $breadcrumbs->parent('admin/help/help/pages');
    $breadcrumbs->push('Create Help Page', route('admin/help/help/page/create'));
});

Breadcrumbs::register('admin/help/help/page/edit', function($breadcrumbs, $helpPage) {
    $breadcrumbs->parent('admin/help/help/pages');
    $breadcrumbs->push('Edit Help Page', route('admin/help/help/page/edit', $helpPage->id));
});

Breadcrumbs::register('admin/users', function($breadcrumbs) {
    $breadcrumbs->parent('admin');
    $breadcrumbs->push('Users', route('admin/users'));
});

Breadcrumbs::register('admin/users/manage', function($breadcrumbs) {
    $breadcrumbs->parent('admin/users');
    $breadcrumbs->push('Manage Users', route('admin/users/manage'));
});

Breadcrumbs::register('admin/users/user/edit', function($breadcrumbs, $user) {
    $breadcrumbs->parent('admin/users');
    $breadcrumbs->push('Edit User', route('admin/users/user/edit', $user->id));
});

Breadcrumbs::register('admin/users/contests', function($breadcrumbs) {
    $breadcrumbs->parent('admin/users');
    $breadcrumbs->push('Contests', route('admin/users/contests'));
});

Breadcrumbs::register('admin/users/contest/edit', function($breadcrumbs, $contest) {
    $breadcrumbs->parent('admin/users/contests');
    $breadcrumbs->push('Edit Contest', route('admin/users/contest/edit', $contest->id));
});

Breadcrumbs::register('admin/users/contest/types', function($breadcrumbs) {
    $breadcrumbs->parent('admin/users');
    $breadcrumbs->push('Contest Types', route('admin/users/contest/types'));
});

Breadcrumbs::register('admin/users/contest/type/edit', function($breadcrumbs, $contestType) {
    $breadcrumbs->parent('admin/users/contest/types');
    $breadcrumbs->push('Edit Contest Type', route('admin/users/contest/type/edit', $contestType->id));
});

Breadcrumbs::register('admin/breeds', function($breadcrumbs) {
    $breadcrumbs->parent('admin');
    $breadcrumbs->push('Breeds', route('admin/breeds'));
});

Breadcrumbs::register('admin/breeds/manage', function($breadcrumbs) {
    $breadcrumbs->parent('admin/breeds');
    $breadcrumbs->push('Manage Breeds', route('admin/breeds/manage'));
});

Breadcrumbs::register('admin/breeds/breed/create', function($breadcrumbs) {
    $breadcrumbs->parent('admin/breeds');
    $breadcrumbs->push('Create Breed', route('admin/breeds/breed/create'));
});

Breadcrumbs::register('admin/breeds/breed/edit', function($breadcrumbs, $breed) {
    $breadcrumbs->parent('admin/breeds');
    $breadcrumbs->push('Edit Breed', route('admin/breeds/breed/edit', $breed->id));
});

Breadcrumbs::register('admin/breeds/breed/drafts', function($breadcrumbs) {
    $breadcrumbs->parent('admin/breeds');
    $breadcrumbs->push('Breed Drafts', route('admin/breeds/breed/drafts'));
});

Breadcrumbs::register('admin/breeds/breed/draft/edit', function($breadcrumbs, $breedDraft) {
    $breadcrumbs->parent('admin/breeds');
    $breadcrumbs->push('Edit Breed Draft', route('admin/breeds/breed/draft/edit', $breedDraft->id));
});

Breadcrumbs::register('admin/genetics', function($breadcrumbs) {
    $breadcrumbs->parent('admin');
    $breadcrumbs->push('Genetics', route('admin/genetics'));
});

Breadcrumbs::register('admin/genetics/locus/create', function($breadcrumbs) {
    $breadcrumbs->parent('admin/genetics');
    $breadcrumbs->push('Create Locus', route('admin/genetics/locus/create'));
});

Breadcrumbs::register('admin/genetics/locus/edit', function($breadcrumbs, $locus) {
    $breadcrumbs->parent('admin/genetics');
    $breadcrumbs->push('Edit Locus', route('admin/genetics/locus/edit', $locus->id));
});

Breadcrumbs::register('admin/genetics/locus/alleles', function($breadcrumbs) {
    $breadcrumbs->parent('admin/genetics');
    $breadcrumbs->push('Locus Alleles', route('admin/genetics/locus/alleles'));
});

Breadcrumbs::register('admin/genetics/locus/allele/create', function($breadcrumbs) {
    $breadcrumbs->parent('admin/genetics/locus/alleles');
    $breadcrumbs->push('Create Locus Allele', route('admin/genetics/locus/allele/create'));
});

Breadcrumbs::register('admin/genetics/locus/allele/edit', function($breadcrumbs, $locusAllele) {
    $breadcrumbs->parent('admin/genetics/locus/alleles');
    $breadcrumbs->push('Edit Locus Allele', route('admin/genetics/locus/allele/edit', $locusAllele->id));
});

Breadcrumbs::register('admin/genetics/genotypes', function($breadcrumbs) {
    $breadcrumbs->parent('admin/genetics');
    $breadcrumbs->push('Genotypes', route('admin/genetics/genotypes'));
});

Breadcrumbs::register('admin/genetics/genotype/create', function($breadcrumbs) {
    $breadcrumbs->parent('admin/genetics/genotypes');
    $breadcrumbs->push('Create Genotype', route('admin/genetics/genotype/create'));
});

Breadcrumbs::register('admin/genetics/genotype/edit', function($breadcrumbs, $genotype) {
    $breadcrumbs->parent('admin/genetics/genotypes');
    $breadcrumbs->push('Edit Genotype', route('admin/genetics/genotype/edit', $genotype->id));
});

Breadcrumbs::register('admin/genetics/phenotypes', function($breadcrumbs) {
    $breadcrumbs->parent('admin/genetics');
    $breadcrumbs->push('Phenotypes', route('admin/genetics/phenotypes'));
});

Breadcrumbs::register('admin/genetics/phenotype/create', function($breadcrumbs) {
    $breadcrumbs->parent('admin/genetics/phenotypes');
    $breadcrumbs->push('Create Phenotype', route('admin/genetics/phenotype/create'));
});

Breadcrumbs::register('admin/genetics/phenotype/edit', function($breadcrumbs, $phenotype) {
    $breadcrumbs->parent('admin/genetics/phenotypes');
    $breadcrumbs->push('Edit Phenotype', route('admin/genetics/phenotype/edit', $phenotype->id));
});

Breadcrumbs::register('admin/dogs', function($breadcrumbs) {
    $breadcrumbs->parent('admin');
    $breadcrumbs->push('Dogs', route('admin/dogs'));
});

Breadcrumbs::register('admin/dogs/manage', function($breadcrumbs) {
    $breadcrumbs->parent('admin/dogs');
    $breadcrumbs->push('Manage Dogs', route('admin/dogs/manage'));
});

Breadcrumbs::register('admin/dogs/dog/edit', function($breadcrumbs, $dog) {
    $breadcrumbs->parent('admin/dogs');
    $breadcrumbs->push('Edit Dog', route('admin/dogs/dog/edit', $dog->id));
});

Breadcrumbs::register('admin/goals', function($breadcrumbs) {
    $breadcrumbs->parent('admin');
    $breadcrumbs->push('Goals', route('admin/goals'));
});

Breadcrumbs::register('admin/goals/community/challenge/create', function($breadcrumbs) {
    $breadcrumbs->parent('admin/goals');
    $breadcrumbs->push('Create Community Challenge', route('admin/goals/community/challenge/create'));
});

Breadcrumbs::register('admin/goals/community/challenge/edit', function($breadcrumbs, $communityChallenge) {
    $breadcrumbs->parent('admin/goals');
    $breadcrumbs->push('Edit Community Challenge', route('admin/goals/community/challenge/edit', $communityChallenge->id));
});
