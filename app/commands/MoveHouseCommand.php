<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MoveHouseCommand extends Command {

    const NEW_DB = 'mysql';
    const OLD_DB = 'old_mysql';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'movehouse:kohana';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move all old data from the production database to the laravel database';

    protected $oldDB;
    protected $newDB;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->oldDB = DB::connection(self::OLD_DB);
        $this->newDB = DB::connection(self::NEW_DB);

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $tablesToMove = [];

        $tables = array_filter(explode(',', $this->option('tables')));

        if ($this->option('dev'))
        {
            $tablesToMove = array(
                'breeds', 
                'breed_characteristics', 
                'breed_characteristic_genetics', 
                'breed_characteristic_health', 
                'breed_characteristic_health_severities', 
                'breed_characteristic_health_severity_health_symptoms', 
                'breed_characteristic_ranges', 
                'breed_genotypes', 
                'challenge_levels', 
                'characteristics', 
                'characteristic_categories', 
                'characteristic_dependencies', 
                'characteristic_dependency_groups', 
                'characteristic_dependency_group_genotypes', 
                'characteristic_dependency_group_ind_characteristic_genotypes', 
                'characteristic_dependency_group_ind_characteristic_ranges', 
                'characteristic_dependency_group_ranges', 
                'characteristic_dependency_ind_characteristics', 
                'characteristic_dependency_ind_characteristic_withinperofrange', 
                'characteristic_genetics', 
                'characteristic_genetics_loci', 
                'characteristic_health', 
                'characteristic_health_genotypes', 
                'characteristic_health_severities', 
                'characteristic_health_severity_health_symptoms', 
                'characteristic_ranges', 
                'characteristic_range_labels', 
                'characteristic_tests', 
                'characteristic_test_genetics', 
                'characteristic_test_health', 
                'characteristic_test_ranges', 
                'credit_packages', 
                'forums', 
                'genotypes', 
                'gifter_levels', 
                'health_symptoms', 
                'loci', 
                'locus_alleles', 
                'phenotypes', 
                'phenotypes_genotypes', 
                'referral_levels', 
                'turn_packages', 
                'tutorial_stages', 
                'wiki_categories', 
                'wiki_categories_wiki_pages', 
                'wiki_pages', 
            );
        }
        else if ( ! empty($tables))
        {
            $tablesToMove = $tables;
        }
        else
        {
            $tablesToMove = array(
                'alpha_codes', 
                'banned_ips', 
                'beginners_luck_requests', 
                'blocks_users', 
                'blog_polls', 
                'blog_polls_blog_posts', 
                'blog_poll_answers', 
                'blog_poll_answer_votes', 
                'blog_posts', 
                'blog_post_comments', 
                'breeds', 
                'breeds_user_breed_drafts', 
                'breed_characteristics', 
                'breed_characteristic_genetics', 
                'breed_characteristic_health', 
                'breed_characteristic_health_severities', 
                'breed_characteristic_health_severity_health_symptoms', 
                'breed_characteristic_ranges', 
                'breed_genotypes', 
                'challenge_levels', 
                'characteristics', 
                'characteristic_categories', 
                'characteristic_dependencies', 
                'characteristic_dependency_groups', 
                'characteristic_dependency_group_genotypes', 
                'characteristic_dependency_group_ind_characteristic_genotypes', 
                'characteristic_dependency_group_ind_characteristic_ranges', 
                'characteristic_dependency_group_ranges', 
                'characteristic_dependency_ind_characteristics', 
                'characteristic_dependency_ind_characteristic_withinperofrange', 
                'characteristic_genetics', 
                'characteristic_genetics_loci', 
                'characteristic_health', 
                'characteristic_health_genotypes', 
                'characteristic_health_severities', 
                'characteristic_health_severity_health_symptoms', 
                'characteristic_ranges', 
                'characteristic_range_labels', 
                'characteristic_tests', 
                'characteristic_test_genetics', 
                'characteristic_test_health', 
                'characteristic_test_ranges', 
                'community_challenges', 
                'community_challenges_prize_winners', 
                'community_challenge_characteristics', 
                'community_challenge_characteristics_genotypes', 
                'community_challenge_characteristics_phenotypes', 
                'community_challenge_characteristic_ranges', 
                'community_challenge_entries', 
                'conversations', 
                'conversations_users', 
                'conversation_messages', 
                'credit_packages', 
                // 'dogs', 
                // 'dog_characteristics', 
                // 'dog_characteristic_genetics', 
                // 'dog_characteristic_genetic_genotypes', 
                // 'dog_characteristic_genetic_phenotypes', 
                // 'dog_characteristic_health', 
                // 'dog_characteristic_health_severities', 
                // 'dog_characteristic_health_severity_health_symptoms', 
                // 'dog_characteristic_ranges', 
                // 'dog_characteristic_tests', 
                // 'dog_genotypes', 
                // 'dog_pedigrees', 
                // 'dog_phenotypes', 
                'forums', 
                'forum_topics', 
                'forum_topic_posts', 
                'genotypes', 
                'gifter_levels', 
                'health_symptoms', 
                'lend_requests', 
                'litters', 
                'loci', 
                'locus_alleles', 
                'online_users', 
                'payments', 
                'payments_credit_packages', 
                'payments_users', 
                'phenotypes', 
                'phenotypes_genotypes', 
                'referral_levels', 
                'sessions', 
                'stud_requests', 
                'turn_packages', 
                'tutorial_stages', 
                'users', 
                'user_beginners_luck_requests', 
                'user_breed_drafts', 
                'user_breed_draft_characteristics', 
                'user_breed_draft_characteristic_genetics', 
                'user_breed_draft_characteristic_genetics_genotypes', 
                'user_breed_draft_characteristic_genetics_phenotypes', 
                'user_breed_draft_characteristic_ranges', 
                'user_challenges', 
                'user_challenge_characteristics', 
                'user_challenge_characteristic_genotypes', 
                'user_challenge_characteristic_phenotypes', 
                'user_challenge_characteristic_ranges', 
                'user_chat_messages', 
                'user_chat_turns', 
                'user_contests', 
                'user_contest_contest_types', 
                'user_contest_contest_type_prereqs', 
                'user_contest_contest_type_prereq_genetics', 
                'user_contest_contest_type_prereq_genetics_genotypes', 
                'user_contest_contest_type_prereq_genetics_phenotypes', 
                'user_contest_contest_type_prereq_ranges', 
                'user_contest_contest_type_reqs', 
                'user_contest_dogs', 
                'user_contest_types', 
                'user_contest_type_prereqs', 
                'user_contest_type_prereq_genetics', 
                'user_contest_type_prereq_genetics_genotypes', 
                'user_contest_type_prereq_genetics_phenotypes', 
                'user_contest_type_prereq_ranges', 
                'user_contest_type_reqs', 
                'user_credit_transactions', 
                'user_credit_transfers', 
                'user_goals', 
                'user_kennel_groups', 
                'user_notifications', 
                'user_tokens', 
                'user_tutorial_stages', 
                'wiki_categories', 
                'wiki_categories_wiki_pages', 
                'wiki_pages', 
            );
        }

        $this->newDB->transaction(function() use ($tablesToMove)
        {
            $this->newDB->statement('SET FOREIGN_KEY_CHECKS=0;');

            $this->info('Fired up');

            $this->truncateNewDatabase();

            $this->info('Moving tables...');

            $this->moveTables($tablesToMove);

            $this->info('Finished moving tables');

            $this->newDB->statement('SET FOREIGN_KEY_CHECKS=1;');
        });
    }

    public function truncateNewDatabase()
    {
        $this->info('Truncating laravel database...');

        $this->newDB->table('alpha_codes')->truncate();
        $this->newDB->table('banned_ips')->truncate();
        $this->newDB->table('beginners_luck_requests')->truncate();
        $this->newDB->table('breeds')->truncate();
        $this->newDB->table('breed_characteristics')->truncate();
        $this->newDB->table('breed_characteristic_severities')->truncate();
        $this->newDB->table('breed_characteristic_severity_symptoms')->truncate();
        $this->newDB->table('breed_drafts')->truncate();
        $this->newDB->table('breed_draft_characteristics')->truncate();
        $this->newDB->table('breed_draft_characteristic_genotypes')->truncate();
        $this->newDB->table('breed_draft_characteristic_phenotypes')->truncate();
        $this->newDB->table('breed_genotypes')->truncate();
        $this->newDB->table('challenges')->truncate();
        $this->newDB->table('challenge_characteristics')->truncate();
        $this->newDB->table('challenge_characteristic_genotypes')->truncate();
        $this->newDB->table('challenge_characteristic_phenotypes')->truncate();
        $this->newDB->table('challenge_levels')->truncate();
        $this->newDB->table('characteristics')->truncate();
        $this->newDB->table('characteristics_genotypes')->truncate();
        $this->newDB->table('characteristics_loci')->truncate();
        $this->newDB->table('characteristic_categories')->truncate();
        $this->newDB->table('characteristic_dependencies')->truncate();
        $this->newDB->table('characteristic_dependency_groups')->truncate();
        $this->newDB->table('characteristic_dependency_group_genotypes')->truncate();
        $this->newDB->table('characteristic_dependency_group_ind_characteristic_genotypes')->truncate();
        $this->newDB->table('characteristic_dependency_group_ind_characteristic_ranges')->truncate();
        $this->newDB->table('characteristic_dependency_group_ranges')->truncate();
        $this->newDB->table('characteristic_dependency_ind_characteristics')->truncate();
        $this->newDB->table('characteristic_dependency_ind_characteristic_withinperofrange')->truncate();
        $this->newDB->table('characteristic_labels')->truncate();
        $this->newDB->table('characteristic_severities')->truncate();
        $this->newDB->table('characteristic_severity_symptoms')->truncate();
        $this->newDB->table('characteristic_tests')->truncate();
        $this->newDB->table('chat_messages')->truncate();
        $this->newDB->table('chat_turns')->truncate();
        $this->newDB->table('community_challenges')->truncate();
        $this->newDB->table('community_challenge_characteristics')->truncate();
        $this->newDB->table('community_challenge_characteristic_genotypes')->truncate();
        $this->newDB->table('community_challenge_characteristic_phenotypes')->truncate();
        $this->newDB->table('community_challenge_entries')->truncate();
        $this->newDB->table('community_challenge_prize_winners')->truncate();
        $this->newDB->table('contests')->truncate();
        $this->newDB->table('contest_prerequisites')->truncate();
        $this->newDB->table('contest_prerequisite_genotypes')->truncate();
        $this->newDB->table('contest_prerequisite_phenotypes')->truncate();
        $this->newDB->table('contest_entries')->truncate();
        $this->newDB->table('contest_requirements')->truncate();
        $this->newDB->table('conversations')->truncate();
        $this->newDB->table('conversation_messages')->truncate();
        $this->newDB->table('credit_packages')->truncate();
        $this->newDB->table('credit_package_payments')->truncate();
        $this->newDB->table('dogs')->truncate();
        $this->newDB->table('dog_characteristics')->truncate();
        $this->newDB->table('dog_characteristic_genotypes')->truncate();
        $this->newDB->table('dog_characteristic_phenotypes')->truncate();
        $this->newDB->table('dog_characteristic_symptoms')->truncate();
        $this->newDB->table('dog_characteristic_tests')->truncate();
        $this->newDB->table('dog_genotypes')->truncate();
        $this->newDB->table('dog_phenotypes')->truncate();
        $this->newDB->table('forums')->truncate();
        $this->newDB->table('forum_posts')->truncate();
        $this->newDB->table('forum_topics')->truncate();
        $this->newDB->table('genotypes')->truncate();
        $this->newDB->table('gifter_levels')->truncate();
        $this->newDB->table('help_categories')->truncate();
        $this->newDB->table('help_categories_help_pages')->truncate();
        $this->newDB->table('help_pages')->truncate();
        $this->newDB->table('kennel_groups')->truncate();
        $this->newDB->table('lend_requests')->truncate();
        $this->newDB->table('litters')->truncate();
        $this->newDB->table('loci')->truncate();
        $this->newDB->table('locus_alleles')->truncate();
        $this->newDB->table('migrations')->truncate();
        $this->newDB->table('news_polls')->truncate();
        $this->newDB->table('news_poll_answers')->truncate();
        $this->newDB->table('news_poll_answer_votes')->truncate();
        $this->newDB->table('news_posts')->truncate();
        $this->newDB->table('news_posts_news_polls')->truncate();
        $this->newDB->table('news_post_comments')->truncate();
        $this->newDB->table('payments')->truncate();
        $this->newDB->table('pedigrees')->truncate();
        $this->newDB->table('phenotypes')->truncate();
        $this->newDB->table('phenotypes_genotypes')->truncate();
        $this->newDB->table('referral_levels')->truncate();
        $this->newDB->table('stud_requests')->truncate();
        $this->newDB->table('symptoms')->truncate();
        $this->newDB->table('turn_packages')->truncate();
        $this->newDB->table('tutorial_stages')->truncate();
        $this->newDB->table('users')->truncate();
        $this->newDB->table('user_blocks')->truncate();
        $this->newDB->table('user_contest_types')->truncate();
        $this->newDB->table('user_contest_type_prerequisites')->truncate();
        $this->newDB->table('user_contest_type_prerequisite_genotypes')->truncate();
        $this->newDB->table('user_contest_type_prerequisite_phenotypes')->truncate();
        $this->newDB->table('user_contest_type_requirements')->truncate();
        $this->newDB->table('user_conversations')->truncate();
        $this->newDB->table('user_credit_transactions')->truncate();
        $this->newDB->table('user_credit_transfers')->truncate();
        $this->newDB->table('user_goals')->truncate();
        $this->newDB->table('user_notifications')->truncate();
        $this->newDB->table('user_payments')->truncate();
        $this->newDB->table('user_tutorial_stages')->truncate();

        $this->info('Truncated laravel database.');
    }

    public function moveTables($tablesToMove)
    {
        foreach($tablesToMove as $table)
        {
            $methodName = Str::camel('move_'.$table);

            if (method_exists($this, $methodName))
            {
                $this->{$methodName}();
            }
        }
    }

    public function moveAll()
    {
        $this->moveAlphaCodes();
        $this->moveBannedIps();
        $this->moveBeginnersLuckRequests();
        $this->moveBlocksUsers();

        $this->moveBlogPolls();
        $this->moveBlogPollsBlogPosts();
        $this->moveBlogPollAnswers();
        $this->moveBlogPollAnswerVotes();
        $this->moveBlogPosts();
        $this->moveBlogPostComments();

        $this->moveBreeds();
        $this->moveBreedsUserBreedDrafts();
        $this->moveBreedCharacteristics();
        $this->moveBreedCharacteristicGenetics();
        $this->moveBreedCharacteristicHealth();
        $this->moveBreedCharacteristicHealthSeverities();
        $this->moveBreedCharacteristicHealthSeverityHealthSymptoms();
        $this->moveBreedCharacteristicRanges();
        $this->moveBreedGenotypes();

        $this->moveChallengeLevels();

        $this->moveCharacteristics();
        $this->moveCharacteristicCategories();
        $this->moveCharacteristicDependencies();
        $this->moveCharacteristicDependencyGroups();
        $this->moveCharacteristicDependencyGroupGenotypes();
        $this->moveCharacteristicDependencyGroupIndCharacteristicGenotypes();
        $this->moveCharacteristicDependencyGroupIndCharacteristicRanges();
        $this->moveCharacteristicDependencyGroupRanges();
        $this->moveCharacteristicDependencyIndCharacteristics();
        $this->moveCharacteristicDependencyIndCharacteristicsWithinperofrange();
        $this->moveCharacteristicGenetics();
        $this->moveCharacteristicGeneticsLoci();
        $this->moveCharacteristicHealth();
        $this->moveCharacteristicHealthGenotypes();
        $this->moveCharacteristicHealthSeverities();
        $this->moveCharacteristicHealthSeverityHealthSymptoms();
        $this->moveCharacteristicRanges();
        $this->moveCharacteristicRangeLabels();

        $this->moveCharacteristicTests();
        $this->moveCharacteristicTestGenetics();
        $this->moveCharacteristicTestHealth();
        $this->moveCharacteristicTestRanges();

        $this->moveCommunityChallenges();
        $this->moveCommunityChallengesPrizeWinners();
        $this->moveCommunityChallengeCharacteristics();
        $this->moveCommunityChallengeCharacteristicsGenotypes();
        $this->moveCommunityChallengeCharacteristicsPhenotypes();
        $this->moveCommunityChallengeCharacteristicRanges();
        $this->moveCommunityChallengeEntries();

        $this->moveConversations();
        $this->moveConversationsUsers();
        $this->moveConversationMessages();

        $this->moveCreditPackages();

        // $this->moveDogs();
        // $this->moveDogCharacteristics();
        // $this->moveDogCharacteristicGenetics();
        // $this->moveDogCharacteristicGeneticGenotypes();
        // $this->moveDogCharacteristicGeneticPhenotypes();
        // $this->moveDogCharacteristicHealth();
        // $this->moveDogCharacteristicHealthSeverities();
        // $this->moveDogCharacteristicHealthSeveritiesHealthSymptoms();
        // $this->moveDogCharacteristicRanges();
        // $this->moveDogCharacteristicTests();
        // $this->moveDogGenotypes();
        // $this->moveDogPedigrees();
        // $this->moveDogPhenotypes();

        $this->moveForums();
        $this->moveForumTopics();
        $this->moveForumTopicPosts();

        $this->moveGenotypes();
        $this->moveGifterLevels();
        $this->moveHealthSymptoms();
        $this->moveLendRequests();
        $this->moveLitters();
        $this->moveLoci();
        $this->moveLocusAlleles();
        $this->moveOnlineUsers();
        $this->movePayments();
        $this->movePaymentsCreditPackages();
        $this->movePaymentsUsers();
        $this->movePhenotypes();
        $this->movePhenotypesGenotypes();
        $this->moveReferralLevels();
        $this->moveStudRequests();
        $this->moveTurnPackages();
        $this->moveTutorialStages();

        $this->moveUsers();
        $this->moveUserBeginnersLuckRequests();

        $this->moveUserBreedDrafts();
        $this->moveUserBreedDraftCharacteristics();
        $this->moveUserBreedDraftCharacteristicGenetics();
        $this->moveUserBreedDraftCharacteristicGeneticsGenotypes();
        $this->moveUserBreedDraftCharacteristicGeneticsPhenotypes();
        $this->moveUserBreedDraftCharacteristicRanges();

        $this->moveUserChallenges();
        $this->moveUserChallengeCharacteristics();
        $this->moveUserChallengeCharacteristicGenotypes();
        $this->moveUserChallengeCharacteristicPhenotypes();
        $this->moveUserChallengeCharacteristicRanges();

        $this->moveUserChatMessages();
        $this->moveUserChatTurns();

        $this->moveUserContests();
        $this->moveUserContestContestTypes();
        $this->moveUserContestContestTypePrereqs();
        $this->moveUserContestContestTypePrereqGenetics();
        $this->moveUserContestContestTypePrereqGeneticsGenotypes();
        $this->moveUserContestContestTypePrereqGeneticsPhenotypes();
        $this->moveUserContestContestTypePrereqRanges();
        $this->moveUserContestContestTypeReqs();
        $this->moveUserContestDogs();

        $this->moveUserContestTypes();
        $this->moveUserContestTypePrereqs();
        $this->moveUserContestTypePrereqGenetics();
        $this->moveUserContestTypePrereqGeneticsGenotypes();
        $this->moveUserContestTypePrereqGeneticsPhenotypes();
        $this->moveUserContestTypePrereqRanges();
        $this->moveUserContestTypeReqs();

        $this->moveUserCreditTransactions();
        $this->moveUserCreditTransfers();
        $this->moveUserGoals();
        $this->moveUserKennelGroups();
        $this->moveUserNotifications();
        $this->moveUserTutorialStages();

        $this->moveWikiCategories();
        $this->moveWikiCategoriesWikiPages();
        $this->moveWikiPages();
    }

    public function moveAlphaCodes()
    {
        $records = $this->oldDB->table('alpha_codes')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'code' => $record->code, 
                'capacity' => $record->capacity, 
                'population' => $record->population, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => $this->formatDateTime($record->created), 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('alpha_codes')->insert($values);
        }

        $this->info('Moved table:alpha_codes '.count($values));
    }

    public function moveBannedIps()
    {
        $records = $this->oldDB->table('banned_ips')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'ip' => $record->ip, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => $this->formatDateTime($record->created), 
            );
        }
        
        if ( ! empty($values))
        {
            $this->newDB->table('banned_ips')->insert($values);
        }

        $this->info('Moved table:banned_ips '.count($values));
    }

    public function moveBeginnersLuckRequests()
    {
        $records = $this->oldDB->table('beginners_luck_requests')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'bitch_id' => $record->bitch_id, 
                'dog_id' => $record->dog_id, 
                'beginner_id' => $record->beginner_id, 
                'persistent_notification_id' => $record->persistent_notification_id, 
            );
        }
        
        if ( ! empty($values))
        {
            $this->newDB->table('beginners_luck_requests')->insert($values);
        }

        $this->info('Moved table:beginners_luck_requests '.count($values));
    }

    public function moveBlocksUsers()
    {
        $records = $this->oldDB->table('blocks_users')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'user_id' => $record->user_id, 
                'blocked_id' => $record->block_id, 
            );
        }
        
        if ( ! empty($values))
        {
            $this->newDB->table('user_blocks')->insert($values);
        }

        $this->info('Moved table:blocks_users '.count($values));
    }

    public function moveBlogPolls()
    {
        $records = $this->oldDB->table('blog_polls')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'question' => $record->question, 
                'reward' => $record->reward, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => $this->formatDateTime($record->updated), 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('news_polls')->insert($values);
        }

        $this->info('Moved table:blog_polls '.count($values));
    }

    public function moveBlogPollsBlogPosts()
    {
        $records = $this->oldDB->table('blog_polls_blog_posts')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'news_post_id' => $record->blog_post_id, 
                'news_poll_id' => $record->blog_poll_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('news_posts_news_polls')->insert($values);
        }

        $this->info('Moved table:blog_polls_blog_posts '.count($values));
    }

    public function moveBlogPollAnswers()
    {
        $records = $this->oldDB->table('blog_poll_answers')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'poll_id' => $record->blog_poll_id, 
                'body' => $record->body, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('news_poll_answers')->insert($values);
        }

        $this->info('Moved table:blog_poll_answers '.count($values));
    }

    public function moveBlogPollAnswerVotes()
    {
        $records = $this->oldDB->table('blog_poll_answer_votes')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'answer_id' => $record->blog_poll_answer_id, 
                'user_id' => $record->user_id, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => $this->formatDateTime($record->created), 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('news_poll_answer_votes')->insert($values);
        }

        $this->info('Moved table:blog_poll_answer_votes '.count($values));
    }

    public function moveBlogPosts()
    {
        $records = $this->oldDB->table('blog_posts')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'author_id' => $record->user_id, 
                'title' => $record->title, 
                'body' => $record->body, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => ( ! $record->updated ? $this->formatDateTime($record->created) : $this->formatDateTime($record->updated)), 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('news_posts')->insert($values);
        }

        $this->info('Moved table:blog_posts '.count($values));
    }

    public function moveBlogPostComments()
    {
        $records = $this->oldDB->table('blog_post_comments')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'news_post_id' => $record->blog_post_id, 
                'author_id' => $record->user_id, 
                'body' => $record->body, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => $this->formatDateTime($record->created), 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('news_post_comments')->insert($values);
        }

        $this->info('Moved table:blog_post_comments '.count($values));
    }

    public function moveBreeds()
    {
        $records = $this->oldDB->table('breeds')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'name' => $record->name, 
                'description' => $record->description, 
                'image_url' => $record->image_url, 
                'creator_id' => ($this->option('dev') ? null : $record->user_id), 
                'originator_id' => $record->dog_id, 
                'active' => $record->active, 
                'created_at' => ($record->created ? $this->formatDateTime($record->created) : Carbon::now()->toDateTimeString()), 
                'updated_at' => ($record->created ? $this->formatDateTime($record->created) : Carbon::now()->toDateTimeString()), 
                'importable' => $record->importable, 
                'extinctable' => $record->extinctable, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('breeds')->insert($values);
        }

        $this->info('Moved table:breeds '.count($values));
    }

    public function moveBreedsUserBreedDrafts()
    {
        $records = $this->oldDB->table('breeds_user_breed_drafts')->get();

        $count = 0;

        foreach($records as $record)
        {
            // Update the breed
            $this->newDB->table('breeds')
                ->where('id', $record->breed_id)
                ->update(array(
                    'draft_id' => $record->draft_id, 
                ));

            ++$count;
        }

        $this->info('Moved table:breeds_user_breed_drafts '.$count);
    }

    public function moveBreedCharacteristics()
    {
        $records = $this->oldDB->table('breed_characteristics')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'breed_id' => $record->breed_id, 
                'characteristic_id' => $record->characteristic_id, 
                'active' => $record->active, 
                'hide' => $record->hidden, 
                'min_age_to_reveal_genotypes' => null, 
                'max_age_to_reveal_genotypes' => null, 
                'min_age_to_reveal_phenotypes' => null, 
                'max_age_to_reveal_phenotypes' => null, 
                'min_female_ranged_value' => null, 
                'max_female_ranged_value' => null, 
                'min_male_ranged_value' => null, 
                'max_male_ranged_value' => null, 
                'min_age_to_reveal_ranged_value' => null, 
                'max_age_to_reveal_ranged_value' => null, 
                'min_age_to_stop_growing' => null, 
                'max_age_to_stop_growing' => null, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('breed_characteristics')->insert($values);
        }

        $this->info('Moved table:breed_characteristics '.count($values));
    }

    public function moveBreedCharacteristicGenetics()
    {
        $records = $this->oldDB->table('breed_characteristic_genetics')->get();

        $count = 0;

        foreach($records as $record)
        {
            // Update the breed
            $this->newDB->table('breed_characteristics')
                ->where('id', $record->breed_characteristic_id)
                ->update(array(
                    'min_age_to_reveal_genotypes' => $record->min_genotypes_can_be_known_age, 
                    'max_age_to_reveal_genotypes' => $record->max_genotypes_can_be_known_age,
                    'min_age_to_reveal_phenotypes' => $record->min_phenotypes_can_be_known_age, 
                    'max_age_to_reveal_phenotypes' => $record->max_phenotypes_can_be_known_age,  
                ));

            ++$count;
        }

        $this->info('Moved table:breed_characteristic_genetics '.$count);
    }

    public function moveBreedCharacteristicHealth()
    {
        // 
    }

    public function moveBreedCharacteristicHealthSeverities()
    {
        $records = $this->oldDB->table('breed_characteristic_health_severities')
            ->select('breed_characteristic_health_severities.*', 'breed_characteristic_health.breed_characteristic_id')
            ->join('breed_characteristic_health', 'breed_characteristic_health.id', '=', 'breed_characteristic_health_severities.breed_characteristic_health_id')
            ->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'breed_characteristic_id' => $record->breed_characteristic_id, 
                'characteristic_severity_id' => $record->characteristic_health_severity_id, 
                'min_age_to_express' => $record->min_onset_age, 
                'max_age_to_express' => $record->max_onset_age, 
                'min_age_to_reveal_value' => $record->min_value_can_be_known_age, 
                'max_age_to_reveal_value' => $record->max_value_can_be_known_age, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('breed_characteristic_severities')->insert($values);
        }

        $this->info('Moved table:breed_characteristic_health_severities '.count($values));
    }

    public function moveBreedCharacteristicHealthSeverityHealthSymptoms()
    {
        $records = $this->oldDB->table('breed_characteristic_health_severity_health_symptoms')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'breed_characteristic_severity_id' => $record->breed_characteristic_health_severity_id, 
                'characteristic_severity_symptom_id' => $record->characteristic_health_severity_health_symptom_id, 
                'min_offset_age_to_express' => $record->min_offset_onset_age, 
                'max_offset_age_to_express' => $record->max_offset_onset_age, 
                'lethal' => $record->lethal, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('breed_characteristic_severity_symptoms')->insert($values);
        }

        $this->info('Moved table:breed_characteristic_health_severity_health_symptoms '.count($values));
    }

    public function moveBreedCharacteristicRanges()
    {
        $records = $this->oldDB->table('breed_characteristic_ranges')->get();

        $count = 0;

        foreach($records as $record)
        {
            // Update the breed
            $this->newDB->table('breed_characteristics')
                ->where('id', $record->breed_characteristic_id)
                ->update(array(
                    'min_female_ranged_value' => $record->min_value_female, 
                    'max_female_ranged_value' => $record->max_value_female, 
                    'min_male_ranged_value' => $record->min_value_male, 
                    'max_male_ranged_value' => $record->max_value_male, 
                    'min_age_to_reveal_ranged_value' => $record->min_value_can_be_known_age, 
                    'max_age_to_reveal_ranged_value' => $record->max_value_can_be_known_age, 
                    'min_age_to_stop_growing' => $record->min_growth_age, 
                    'max_age_to_stop_growing' => $record->max_growth_age, 
                ));

            ++$count;
        }

        $this->info('Moved table:breed_characteristic_ranges '.$count);
    }

    public function moveBreedGenotypes()
    {
        $records = $this->oldDB->table('breed_genotypes')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'breed_id' => $record->breed_id, 
                'genotype_id' => $record->genotype_id, 
                'frequency' => $record->frequency, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('breed_genotypes')->insert($values);
        }

        $this->info('Moved table:breed_genotypes '.count($values));
    }

    public function moveChallengeLevels()
    {
        $records = $this->oldDB->table('challenge_levels')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'name' => $record->name, 
                'completed_challenges' => $record->completed_challenges, 
                'characteristics_generated' => $record->characteristics_generated, 
                'credit_prize' => $record->credit_prize, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('challenge_levels')->insert($values);
        }

        $this->info('Moved table:challenge_levels '.count($values));
    }

    public function moveCharacteristics()
    {
        $records = $this->oldDB->table('characteristics')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'category_id' => $record->characteristic_category_id, 
                'name' => $record->name, 
                'description' => $record->description, 
                'help_page_id' => (is_null($wikiPage = $this->oldDB->table('wiki_pages')->where('title', $record->wiki)->first()) ? null : $wikiPage->id), 
                'hide' => $record->hidden, 
                'active' => $record->active, 
                'type_id' => $record->type_id, 
                'ignorable' => $record->ignorable, 
                'hide_genotypes' => $record->hide_genotypes, 
                'genotypes_can_be_revealed' => null, 
                'min_age_to_reveal_genotypes' => null, 
                'max_age_to_reveal_genotypes' => null, 
                'phenotypes_can_be_revealed' => null, 
                'min_age_to_reveal_phenotypes' => null, 
                'max_age_to_reveal_phenotypes' => null, 
                'min_ranged_value' => null, 
                'max_ranged_value' => null, 
                'ranged_value_precision' => null, 
                'ranged_lower_boundary_label' => null, 
                'ranged_upper_boundary_label' => null, 
                'ranged_prefix_units' => null, 
                'ranged_suffix_units' => null, 
                'ranged_value_can_be_revealed' => null, 
                'min_age_to_reveal_ranged_value' => null, 
                'max_age_to_reveal_ranged_value' => null, 
                'ranged_value_can_grow' => null, 
                'min_age_to_stop_growing' => null, 
                'max_age_to_stop_growing' => null, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('characteristics')->insert($values);
        }

        $this->info('Moved table:characteristics '.count($values));
    }

    public function moveCharacteristicGenetics()
    {
        $records = $this->oldDB->table('characteristic_genetics')->get();

        $count = 0;

        foreach($records as $record)
        {
            // Update the breed
            $this->newDB->table('characteristics')
                ->where('id', $record->characteristic_id)
                ->update(array(
                    'genotypes_can_be_revealed' => $record->genotypes_can_be_known, 
                    'min_age_to_reveal_genotypes' => ($record->genotypes_can_be_known ? $record->min_genotypes_can_be_known_age : null), 
                    'max_age_to_reveal_genotypes' => ($record->genotypes_can_be_known ? $record->max_genotypes_can_be_known_age : null), 
                    'phenotypes_can_be_revealed' => $record->phenotypes_can_be_known, 
                    'min_age_to_reveal_phenotypes' => ($record->phenotypes_can_be_known ? $record->min_phenotypes_can_be_known_age : null), 
                    'max_age_to_reveal_phenotypes' => ($record->phenotypes_can_be_known ? $record->max_phenotypes_can_be_known_age : null), 
                ));

            ++$count;
        }

        $this->info('Moved table:characteristic_genetics '.$count);
    }

    public function moveCharacteristicCategories()
    {
        $records = $this->oldDB->table('characteristic_categories')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'parent_category_id' => $record->parent_characteristic_category_id, 
                'name' => $record->name, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('characteristic_categories')->insert($values);
        }

        $this->info('Moved table:characteristic_categories '.count($values));
    }

    public function moveCharacteristicDependencies()
    {
        $records = $this->oldDB->table('characteristic_dependencies')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'dependent_id' => $record->dependent_characteristic_id, 
                'type_id' => $record->type_id, 
                'active' => $record->active, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('characteristic_dependencies')->insert($values);
        }

        $this->info('Moved table:characteristic_dependencies '.count($values));
    }

    public function moveCharacteristicDependencyGroups()
    {
        $records = $this->oldDB->table('characteristic_dependency_groups')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'characteristic_dependency_id' => $record->characteristic_dependency_id, 
                'identifier' => $record->identifier, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('characteristic_dependency_groups')->insert($values);
        }

        $this->info('Moved table:characteristic_dependency_groups '.count($values));
    }

    public function moveCharacteristicDependencyGroupGenotypes()
    {
        $records = $this->oldDB->table('characteristic_dependency_group_genotypes')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'characteristic_dependency_group_id' => $record->characteristic_dependency_group_id, 
                'genotype_id' => $record->genotype_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('characteristic_dependency_group_genotypes')->insert($values);
        }

        $this->info('Moved table:characteristic_dependency_group_genotypes '.count($values));
    }

    public function moveCharacteristicDependencyGroupIndCharacteristicGenotypes()
    {
        $records = $this->oldDB->table('characteristic_dependency_group_ind_characteristic_genotypes')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'characteristic_dependency_group_id' => $record->characteristic_dependency_group_id, 
                'characteristic_dependency_ind_characteristic_id' => $record->characteristic_dependency_ind_characteristic_id, 
                'genotype_id' => $record->genotype_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('characteristic_dependency_group_ind_characteristic_genotypes')->insert($values);
        }

        $this->info('Moved table:characteristic_dependency_group_ind_characteristic_genotypes '.count($values));
    }

    public function moveCharacteristicDependencyGroupIndCharacteristicRanges()
    {
        $records = $this->oldDB->table('characteristic_dependency_group_ind_characteristic_ranges')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'characteristic_dependency_group_id' => $record->characteristic_dependency_group_id, 
                'characteristic_dependency_ind_characteristic_id' => $record->characteristic_dependency_ind_characteristic_id, 
                'min_value' => $record->min_value, 
                'max_value' => $record->max_value, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('characteristic_dependency_group_ind_characteristic_ranges')->insert($values);
        }

        $this->info('Moved table:characteristic_dependency_group_ind_characteristic_ranges '.count($values));
    }

    public function moveCharacteristicDependencyGroupRanges()
    {
        $records = $this->oldDB->table('characteristic_dependency_group_ranges')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'characteristic_dependency_group_id' => $record->characteristic_dependency_group_id, 
                'min_value' => $record->min_value, 
                'max_value' => $record->max_value, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('characteristic_dependency_group_ranges')->insert($values);
        }

        $this->info('Moved table:characteristic_dependency_group_ranges '.count($values));
    }

    public function moveCharacteristicDependencyIndCharacteristics()
    {
        $records = $this->oldDB->table('characteristic_dependency_ind_characteristics')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'characteristic_dependency_id' => $record->characteristic_dependency_id, 
                'independent_characteristic_id' => $record->independent_characteristic_id, 
                'min_percent' => null, 
                'max_percent' => null, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('characteristic_dependency_ind_characteristics')->insert($values);
        }

        $this->info('Moved table:characteristic_dependency_ind_characteristics '.count($values));
    }

    public function moveCharacteristicDependencyIndCharacteristicsWithinperofrange()
    {
        $records = $this->oldDB->table('characteristic_dependency_ind_characteristic_withinperofrange')->get();

        $count = 0;

        foreach($records as $record)
        {
            // Update the breed
            $this->newDB->table('characteristic_dependency_ind_characteristics')
                ->where('id', $record->characteristic_dependency_ind_characteristic_id)
                ->update(array(
                    'min_percent' => $record->min_value, 
                    'max_percent' => $record->max_value, 
                ));

            ++$count;
        }

        $this->info('Moved table:characteristic_dependency_ind_characteristic_withinperofrange '.$count);
    }

    public function moveCharacteristicGeneticsLoci()
    {
        $records = $this->oldDB->table('characteristic_genetics_loci')
            ->select('characteristic_genetics_loci.*', 'characteristic_genetics.characteristic_id')
            ->join('characteristic_genetics', 'characteristic_genetics.id', '=', 'characteristic_genetics_loci.characteristic_genetic_id')
            ->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'characteristic_id' => $record->characteristic_id, 
                'locus_id' => $record->locus_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('characteristics_loci')->insert($values);
        }

        $this->info('Moved table:characteristic_genetics_loci '.count($values));
    }

    public function moveCharacteristicHealth()
    {
        // 
    }

    public function moveCharacteristicHealthGenotypes()
    {
        $records = $this->oldDB->table('characteristic_health_genotypes')
            ->select('characteristic_health_genotypes.*', 'characteristic_health.characteristic_id')
            ->join('characteristic_health', 'characteristic_health.id', '=', 'characteristic_health_genotypes.characteristic_health_id')
            ->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'characteristic_id' => $record->characteristic_id, 
                'genotype_id' => $record->genotype_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('characteristics_genotypes')->insert($values);
        }

        $this->info('Moved table:characteristic_health_genotypes '.count($values));
    }

    public function moveCharacteristicHealthSeverities()
    {
        $records = $this->oldDB->table('characteristic_health_severities')
            ->select('characteristic_health_severities.*', 'characteristic_health.characteristic_id')
            ->join('characteristic_health', 'characteristic_health.id', '=', 'characteristic_health_severities.characteristic_health_id')
            ->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'characteristic_id' => $record->characteristic_id, 
                'can_be_expressed' => 1, 
                'min_age_to_express' => $record->min_onset_age, 
                'max_age_to_express' => $record->max_onset_age, 
                'min_value' => $record->min_value, 
                'max_value' => $record->max_value, 
                'value_can_be_revealed' => $record->value_can_be_known, 
                'min_age_to_reveal_value' => ($record->value_can_be_known ? $record->min_value_can_be_known_age : null), 
                'max_age_to_reveal_value' => ($record->value_can_be_known ? $record->max_value_can_be_known_age : null), 
                'prefix_units' => $record->prefix_units, 
                'suffix_units' => $record->suffix_units, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('characteristic_severities')->insert($values);
        }

        $this->info('Moved table:characteristic_health_severities '.count($values));
    }

    public function moveCharacteristicHealthSeverityHealthSymptoms()
    {
        $records = $this->oldDB->table('characteristic_health_severity_health_symptoms')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'severity_id' => $record->characteristic_health_severity_id, 
                'symptom_id' => $record->health_symptom_id, 
                'min_offset_age_to_express' => $record->min_offset_onset_age, 
                'max_offset_age_to_express' => $record->max_offset_onset_age, 
                'lethal' => $record->lethal, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('characteristic_severity_symptoms')->insert($values);
        }

        $this->info('Moved table:characteristic_health_severity_health_symptoms '.count($values));
    }

    public function moveCharacteristicRanges()
    {
        $records = $this->oldDB->table('characteristic_ranges')->get();

        $count = 0;

        foreach($records as $record)
        {
            // Update the breed
            $this->newDB->table('characteristics')
                ->where('id', $record->characteristic_id)
                ->update(array(
                    'min_ranged_value' => $record->min_value, 
                    'max_ranged_value' => $record->max_value, 
                    'ranged_value_precision' => $record->decimal_points, 
                    'ranged_value_can_be_revealed' => $record->value_can_be_known, 
                    'min_age_to_reveal_ranged_value' => ($record->value_can_be_known ? $record->min_value_can_be_known_age : null), 
                    'max_age_to_reveal_ranged_value' => ($record->value_can_be_known ? $record->max_value_can_be_known_age : null), 
                    'ranged_lower_boundary_label' => $record->lower_boundary_label, 
                    'ranged_upper_boundary_label' => $record->upper_boundary_label, 
                    'ranged_prefix_units' => $record->prefix_units, 
                    'ranged_suffix_units' => $record->suffix_units, 
                    'ranged_value_can_grow' => $record->growth, 
                    'min_age_to_stop_growing' => ($record->growth ? $record->min_growth_age : null), 
                    'max_age_to_stop_growing' => ($record->growth ? $record->max_growth_age : null), 
                ));

            ++$count;
        }

        $this->info('Moved table:characteristic_ranges '.$count);
    }

    public function moveCharacteristicRangeLabels()
    {
        $records = $this->oldDB->table('characteristic_range_labels')
            ->select('characteristic_range_labels.*', 'characteristic_ranges.characteristic_id')
            ->join('characteristic_ranges', 'characteristic_ranges.id', '=', 'characteristic_range_labels.characteristic_range_id')
            ->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'characteristic_id' => $record->characteristic_id, 
                'name' => $record->name, 
                'min_ranged_value' => $record->min_value, 
                'max_ranged_value' => $record->max_value, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('characteristic_labels')->insert($values);
        }

        $this->info('Moved table:characteristic_range_labels '.count($values));
    }

    public function moveCharacteristicTests()
    {
        $records = $this->oldDB->table('characteristic_tests')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'characteristic_id' => $record->characteristic_id, 
                'name' => $record->name, 
                'min_age' => $record->min_age, 
                'max_age' => $record->max_age, 
                'active' => $record->active, 
                'type_id' => 0, 
                'reveal_genotypes' => 0, 
                'reveal_phenotypes' => 0, 
                'reveal_ranged_value' => 0, 
                'reveal_severity_value' => 0, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('characteristic_tests')->insert($values);
        }

        $this->info('Moved table:characteristic_tests '.count($values));
    }

    public function moveCharacteristicTestGenetics()
    {
        $records = $this->oldDB->table('characteristic_test_genetics')->get();

        $count = 0;

        foreach($records as $record)
        {
            $this->newDB->table('characteristic_tests')
                ->where('id', $record->characteristic_test_id)
                ->update(array(
                    'reveal_genotypes' => $record->mark_genotypes_as_known, 
                    'reveal_phenotypes' => $record->mark_phenotypes_as_known, 
                ));

            ++$count;
        }

        $this->info('Moved table:characteristic_test_genetics '.$count);
    }

    public function moveCharacteristicTestHealth()
    {
        $records = $this->oldDB->table('characteristic_test_health')->get();
        $count = 0;

        foreach($records as $record)
        {
            $this->newDB->table('characteristic_tests')
                ->where('id', $record->characteristic_test_id)
                ->update(array(
                    'type_id' => ($record->type_id + 1), 
                    'reveal_severity_value' => $record->mark_severity_as_known, 
                ));

            ++$count;
        }

        $this->info('Moved table:characteristic_test_health '.$count);
    }

    public function moveCharacteristicTestRanges()
    {
        $records = $this->oldDB->table('characteristic_test_ranges')->get();
        $count = 0;

        foreach($records as $record)
        {
            $this->newDB->table('characteristic_tests')
                ->where('id', $record->characteristic_test_id)
                ->update(array(
                    'reveal_ranged_value' => $record->mark_value_as_known, 
                ));

            ++$count;
        }

        $this->info('Moved table:characteristic_test_ranges '.$count);
    }

    public function moveCommunityChallenges()
    {
        $records = $this->oldDB->table('community_challenges')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'num_characteristics' => $record->num_characteristics, 
                'start_date' => $this->formatDateTime($record->start_date), 
                'end_date' => $this->formatDateTime($record->end_date), 
                'healthy' => $record->healthy, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => $this->formatDateTime($record->created), 
                'judged' => $record->judged, 
                'winners' => $record->winners, 
                'credit_payout' => $record->credit_payout, 
                'breeders_prize_payout' => $record->breeders_prize_payout, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('community_challenges')->insert($values);
        }

        $this->info('Moved table:community_challenges '.count($values));
    }

    public function moveCommunityChallengesPrizeWinners()
    {
        $records = $this->oldDB->table('community_challenges_prize_winners')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'community_challenge_id' => $record->community_challenge_id, 
                'user_id' => $record->user_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('community_challenge_prize_winners')->insert($values);
        }

        $this->info('Moved table:community_challenges_prize_winners '.count($values));
    }

    public function moveCommunityChallengeCharacteristics()
    {
        $records = $this->oldDB->table('community_challenge_characteristics')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'community_challenge_id' => $record->community_challenge_id, 
                'characteristic_id' => $record->characteristic_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('community_challenge_characteristics')->insert($values);
        }

        $this->info('Moved table:community_challenge_characteristics '.count($values));
    }

    public function moveCommunityChallengeCharacteristicsGenotypes()
    {
        $records = $this->oldDB->table('community_challenge_characteristics_genotypes')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'community_challenge_characteristic_id' => $record->community_challenge_characteristic_id, 
                'genotype_id' => $record->genotype_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('community_challenge_characteristic_genotypes')->insert($values);
        }

        $this->info('Moved table:community_challenge_characteristics_genotypes '.count($values));
    }

    public function moveCommunityChallengeCharacteristicsPhenotypes()
    {
        $records = $this->oldDB->table('community_challenge_characteristics_phenotypes')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'community_challenge_characteristic_id' => $record->community_challenge_characteristic_id, 
                'phenotype_id' => $record->phenotype_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('community_challenge_characteristic_phenotypes')->insert($values);
        }

        $this->info('Moved table:community_challenge_characteristics_phenotypes '.count($values));
    }

    public function moveCommunityChallengeCharacteristicRanges()
    {
        $records = $this->oldDB->table('community_challenge_characteristic_ranges')->get();

        $count = 0;

        foreach($records as $record)
        {
            $this->newDB->table('community_challenge_characteristics')
                ->where('id', $record->community_challenge_characteristic_id)
                ->update(array(
                    'ranged_value' => $record->value, 
                ));

            ++$count;
        }

        $this->info('Moved table:community_challenge_characteristic_ranges '.$count);
    }

    public function moveCommunityChallengeEntries()
    {
        $records = $this->oldDB->table('community_challenge_entries')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'community_challenge_id' => $record->community_challenge_id, 
                'dog_id' => $record->dog_id, 
                'num_breeders' => $record->num_breeders, 
                'winner' => $record->winner, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('community_challenge_entries')->insert($values);
        }

        $this->info('Moved table:community_challenge_entries '.count($values));
    }

    public function moveConversations()
    {
        $records = $this->oldDB->table('conversations')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'sender_id' => $record->sender_id, 
                'receiver_id' => $record->receiver_id, 
                'subject' => $record->title, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => ($record->updated ? $this->formatDateTime($record->updated) : $this->formatDateTime($record->created)), 
                'replies' => $record->replies, 
                'deleted_at' => ($record->deleted ? date('Y-m-d H:i:s') : null), 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('conversations')->insert($values);
        }

        $this->info('Moved table:conversations '.count($values));
    }

    public function moveConversationsUsers()
    {
        $records = $this->oldDB->table('conversations_users')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'user_id' => $record->user_id, 
                'conversation_id' => $record->conversation_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('user_conversations')->insert($values);
        }

        $this->info('Moved table:conversations_users '.count($values));
    }

    public function moveConversationMessages()
    {
        $records = $this->oldDB->table('conversation_messages')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'conversation_id' => $record->conversation_id, 
                'user_id' => $record->user_id, 
                'body' => $record->body, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => $this->formatDateTime($record->created), 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('conversation_messages')->insert($values);
        }

        $this->info('Moved table:conversation_messages '.count($values));
    }

    public function moveCreditPackages()
    {
        $records = $this->oldDB->table('credit_packages')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'name' => $record->name, 
                'credit_amount' => $record->amount, 
                'cost' => $record->cost, 
                'item_name' => $record->item_name, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('credit_packages')->insert($values);
        }

        $this->info('Moved table:credit_packages '.count($values));
    }

    public function moveDogs()
    {
        $records = $this->oldDB->table('dogs')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'owner_id' => $record->user_id, 
                'breeder_id' => $record->breeder_id, 
                'kennel_prefix' => $record->kennel_prefix, 
                'kennel_group_id' => $record->kennel_group_id, 
                'name' => $record->name, 
                'display_image' => $record->display_image, 
                'image_url' => $record->image_url, 
                'notes' => $record->notes, 
                'studding' => $record->studding, 
                'breed_id' => $record->breed_id, 
                'breed_changed' => $record->breed_changed, 
                'litter_id' => $record->litter_id, 
                'sex_id' => $record->sex_id, 
                'age' => $record->age, 
                'coi' => $record->coi, 
                'active_breed_member' => $record->active_breed_member, 
                'worked' => $record->worked, 
                'heat' => $record->heat, 
                'small_contest_wins' => $record->small_contest_wins, 
                'medium_contest_wins' => $record->medium_contest_wins, 
                'large_contest_wins' => $record->large_contest_wins, 
                'custom_import' => $record->custom_import, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => $this->formatDateTime($record->created), 
                'completed_at' => ($record->completed ? $this->formatDateTime($record->completed) : null), 
                'deceased_at' => ($record->deceased ? $this->formatDateTime($record->deceased) : null), 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('dogs')->insert($values);
        }

        $this->info('Moved table:dogs '.count($values));
    }

    public function moveDogCharacteristics()
    {
        // Too big to do this view PHP
    }

    public function moveDogCharacteristicGenetics()
    {
        // Too big to do this view PHP
    }

    public function moveDogCharacteristicGeneticGenotypes()
    {
        // Too big to do this view PHP
    }

    public function moveDogCharacteristicGeneticPhenotypes()
    {
        // Too big to do this view PHP
    }

    public function moveDogCharacteristicHealth()
    {
        // Too big to do this view PHP
    }

    public function moveDogCharacteristicHealthSeverities()
    {
        // Too big to do this view PHP
    }

    public function moveDogCharacteristicHealthSeveritiesHealthSymptoms()
    {
        // Too big to do this view PHP
    }

    public function moveDogCharacteristicRanges()
    {
        // Too big to do this view PHP
    }

    public function moveDogCharacteristicTests()
    {
        // Too big to do this view PHP
        $records = $this->oldDB->table('dog_characteristic_tests')
            ->select('dog_characteristics.id', 'dog_characteristic_tests.characteristic_test_id')
            ->join('characteristic_tests', 'characteristic_tests.id', '=', 'dog_characteristic_tests.characteristic_test_id')
            ->join('dog_characteristics', 'dog_characteristics.id', '=', 'characteristic_tests.characteristic_id')
            ->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'dog_characteristic_id' => $record->id, 
                'test_id' => $record->characteristic_test_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('dog_characteristic_tests')->insert($values);
        }

        $this->info('Moved table:dog_characteristic_tests '.count($values));
    }

    public function moveDogGenotypes()
    {
        // Too big to do this view PHP
    }

    public function moveDogPedigrees()
    {
        $records = $this->oldDB->table('dog_pedigrees')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'dog_id' => $record->dog_id, 
                'dam' => $record->dam, 
                'sire' => $record->sire, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('pedigrees')->insert($values);
        }

        $this->info('Moved table:dog_pedigrees '.count($values));
    }

    public function moveDogPhenotypes()
    {
        // Too big to do this view PHP
    }

    public function moveForums()
    {
        $records = $this->oldDB->table('forums')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'title' => $record->title, 
                'description' => $record->description, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('forums')->insert($values);
        }

        $this->info('Moved table:forums '.count($values));
    }

    public function moveForumTopics()
    {
        $records = $this->oldDB->table('forum_topics')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'forum_id' => $record->forum_id, 
                'author_id' => $record->user_id, 
                'title' => $record->title, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => ($record->edited ? $this->formatDateTime($record->edited) : $this->formatDateTime($record->created)), 
                'views' => $record->views, 
                'replies' => $record->replies, 
                'last_activity_at' => ($record->edited ? $this->formatDateTime($record->edited) : $this->formatDateTime($record->created)), 
                'editor_id' => $record->editor_id, 
                'stickied' => $record->stickied, 
                'locked' => $record->locked, 
                'deleted_at' => ($record->deleted ? date('Y-m-d H:i:s') : null), 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('forum_topics')->insert($values);
        }

        $this->info('Moved table:forum_topics '.count($values));
    }

    public function moveForumTopicPosts()
    {
        $records = $this->oldDB->table('forum_topic_posts')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'topic_id' => $record->forum_topic_id, 
                'author_id' => $record->user_id, 
                'body' => $record->body, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => ($record->edited ? $this->formatDateTime($record->edited) : $this->formatDateTime($record->created)), 
                'editor_id' => $record->editor_id, 
                'deleted_at' => ($record->deleted ? date('Y-m-d H:i:s') : null), 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('forum_posts')->insert($values);
        }

        $this->info('Moved table:forum_topic_posts '.count($values));
    }

    public function moveGenotypes()
    {
        $records = $this->oldDB->table('genotypes')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'locus_id' => $record->locus_id, 
                'locus_allele_id_a' => $record->locus_allele_id_a, 
                'locus_allele_id_b' => $record->locus_allele_id_b, 
                'available_to_female' => $record->available_to_female, 
                'available_to_male' => $record->available_to_male, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('genotypes')->insert($values);
        }

        $this->info('Moved table:genotypes '.count($values));
    }

    public function moveGifterLevels()
    {
        $records = $this->oldDB->table('gifter_levels')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'title' => $record->title, 
                'min' => $record->min, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('gifter_levels')->insert($values);
        }

        $this->info('Moved table:gifter_levels '.count($values));
    }

    public function moveHealthSymptoms()
    {
        $records = $this->oldDB->table('health_symptoms')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'name' => $record->name, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('symptoms')->insert($values);
        }

        $this->info('Moved table:health_symptoms '.count($values));
    }

    public function moveLendRequests()
    {
        $records = $this->oldDB->table('lend_requests')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'dog_id' => $record->dog_id, 
                'sender_id' => $record->user_id, 
                'receiver_id' => $record->receiver_id, 
                'permanent' => $record->permanent, 
                'turns_left' => $record->turns_left, 
                'turns_left' => ($record->turns_left ? $record->turns_left : null), 
                'return_at' => ($record->return_time ? $this->formatDate($record->return_time) : null), 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => $this->formatDateTime($record->created), 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('lend_requests')->insert($values);
        }

        $this->info('Moved table:lend_requests '.count($values));
    }

    public function moveLitters()
    {
        $records = $this->oldDB->table('litters')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'breeder_id' => $record->breeder_id, 
                'sire_id' => $record->sire_id, 
                'dam_id' => $record->dam_id, 
                'litter_chance' => $record->litter_chance, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => $this->formatDateTime($record->created), 
                'born' => $record->born, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('litters')->insert($values);
        }

        $this->info('Moved table:litters '.count($values));
    }

    public function moveLoci()
    {
        $records = $this->oldDB->table('loci')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'name' => $record->name, 
                'active' => $record->active, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('loci')->insert($values);
        }

        $this->info('Moved table:loci '.count($values));
    }

    public function moveLocusAlleles()
    {
        $records = $this->oldDB->table('locus_alleles')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'locus_id' => $record->locus_id, 
                'symbol' => $record->symbol, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('locus_alleles')->insert($values);
        }

        $this->info('Moved table:locus_alleles '.count($values));
    }

    public function moveOnlineUsers()
    {
        $records = $this->oldDB->table('online_users')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'created_at' => $this->formatDateTime($record->logged), 
                'total' => $record->total, 
                'updated_at' => $this->formatDateTime($record->logged), 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('online_users_logs')->insert($values);
        }

        $this->info('Moved table:online_users '.count($values));
    }

    public function movePayments()
    {
        $records = $this->oldDB->table('payments')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->payment_id, 
                'transaction_id' => $record->transaction_id, 
                'payment_status' => $record->payment_status, 
                'payment_gross' => $record->gross, 
                'mc_gross' => $record->gross, 
                'mc_currency' => $record->currency, 
                'payer_id' => $record->payer_id, 
                'payer_email' => $record->payer_email, 
                'payer_name' => $record->payer_name, 
                'payment_date' => $record->payment_date, 
                'ipn_message' => $record->info, 
                'created_at' => $record->payment_date, 
                'updated_at' => $record->payment_date, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('payments')->insert($values);
        }

        $this->info('Moved table:payments '.count($values));
    }

    public function movePaymentsCreditPackages()
    {
        $records = $this->oldDB->table('payments_credit_packages')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'credit_package_id' => $record->credit_package_id, 
                'payment_id' => $record->payment_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('credit_package_payments')->insert($values);
        }

        $this->info('Moved table:payments_credit_packages '.count($values));
    }

    public function movePaymentsUsers()
    {
        $records = $this->oldDB->table('payments_users')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'user_id' => $record->user_id, 
                'payment_id' => $record->payment_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('user_payments')->insert($values);
        }

        $this->info('Moved table:payments_users '.count($values));
    }

    public function movePhenotypes()
    {
        $records = $this->oldDB->table('phenotypes')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'name' => $record->name, 
                'priority' => $record->priority, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('phenotypes')->insert($values);
        }

        $this->info('Moved table:phenotypes '.count($values));
    }

    public function movePhenotypesGenotypes()
    {
        $records = $this->oldDB->table('phenotypes_genotypes')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'phenotype_id' => $record->phenotype_id, 
                'genotype_id' => $record->genotype_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('phenotypes_genotypes')->insert($values);
        }

        $this->info('Moved table:phenotypes_genotypes '.count($values));
    }

    public function moveReferralLevels()
    {
        $records = $this->oldDB->table('referral_levels')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'referred_users' => $record->referred_users, 
                'points_per_referral' => $record->points_per_referral, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('referral_levels')->insert($values);
        }

        $this->info('Moved table:referral_levels '.count($values));
    }

    public function moveStudRequests()
    {
        $records = $this->oldDB->table('stud_requests')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'stud_id' => $record->stud_id, 
                'bitch_id' => $record->bitch_id, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => $this->formatDateTime($record->created), 
                'accepted' => $record->accepted, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('stud_requests')->insert($values);
        }

        $this->info('Moved table:stud_requests '.count($values));
    }

    public function moveTurnPackages()
    {
        $records = $this->oldDB->table('turn_packages')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'credit_cost' => $record->cost, 
                'amount' => $record->amount, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('turn_packages')->insert($values);
        }

        $this->info('Moved table:turn_packages '.count($values));
    }

    public function moveTutorialStages()
    {
        $records = $this->oldDB->table('tutorial_stages')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'number' => $record->number, 
                'slug' => $record->slug, 
                'uri' => $record->uri, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('tutorial_stages')->insert($values);
        }

        $this->info('Moved table:tutorial_stages '.count($values));
    }

    public function moveUsers()
    {
        $records = $this->oldDB->table('users')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'display_name' => $record->display_name, 
                'credits' => $record->site_credit, 
                'banked_credits' => $record->banked_site_credit, 
                'kennel_prefix' => $record->kennel_prefix, 
                'kennel_name' => $record->kennel_name, 
                'kennel_description' => $record->kennel_description, 
                'turns' => $record->turns_left, 
                'imports' => $record->imports_left, 
                'custom_imports' => $record->custom_imports_left, 
                'profile_html' => $record->profile_html, 
                'email' => $record->email, 
                'username' => $record->username, 
                'password' => $record->password, 
                'activated' => ( ! $record->activation_code), 
                'activated_at' => ( ! $record->activation_code ? $this->formatDateTime($record->created) : null), 
                'activation_code' => ($record->activation_code ? $record->activation_code : null), 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => ($record->upgraded_until ? $this->formatDateTime($record->last_action) : $this->formatDateTime($record->created)), 
                'created_ip' => $record->created_ip, 
                'ip_banned' => $record->ip_banned, 
                'campaign_code' => $record->campaign_code, 
                'registered_alpha_code' => $record->registered_alpha_code, 
                'referred_by_id' => $record->referred_by_id, 
                'logins' => $record->logins, 
                'gifter_level_id' => $record->gifter_level_id, 
                'gifts_given' => $record->gifts_given, 
                'show_gifter_level' => $record->show_gifter_level, 
                'last_login' => ($record->last_login ? $this->formatDateTime($record->last_login) : null), 
                'last_login_ip' => $record->last_login_ip, 
                'last_uri' => $record->last_uri, 
                'last_action_at' => ($record->last_action ? $this->formatDateTime($record->last_action) : null), 
                'upgraded_until' => ($record->upgraded_until ? $this->formatDateTime($record->upgraded_until) : null), 
                'avatar' => $record->avatar, 
                'challenge_level_id' => $record->challenge_level_id, 
                'total_completed_challenges' => $record->individual_challenges_completed, 
                'referral_level_id' => $record->referral_level_id, 
                'total_referrals' => $record->total_referrals, 
                'referral_points' => $record->referral_points, 
                'breeders_prize_until' => ($record->breeders_prize ? $this->formatDateTime($record->breeders_prize) : null), 
                'banned_until' => ($record->banned_until ? $this->formatDateTime($record->banned_until) : null), 
                'ban_reason' => $record->ban_reason, 
                'chat_banned_until' => ($record->chat_banned_until ? $this->formatDateTime($record->chat_banned_until) : null), 
                'chat_ban_reason' => $record->chat_ban_reason, 
                'allow_marketing_emails' => $record->allow_marketing_emails, 
                'read_community_rules' => $record->read_community_rules, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('users')->insert($values);
        }

        $this->info('Moved table:users '.count($values));
    }

    public function moveUserBeginnersLuckRequests()
    {
        $records = $this->oldDB->table('user_beginners_luck_requests')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'user_id' => $record->user_id, 
                'bitch_id' => $record->bitch_id, 
                'dog_id' => $record->dog_id, 
                'beginner_id' => $record->beginner_id, 
                'persistent_notification_id' => $record->persistent_notification_id, 
                'accepted' => $record->accepted, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('beginners_luck_requests')->insert($values);
        }

        $this->info('Moved table:user_beginners_luck_requests '.count($values));
    }

    public function moveUserBreedDrafts()
    {
        $records = $this->oldDB->table('user_breed_drafts')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'user_id' => $record->user_id, 
                'official' => $record->official, 
                'name' => $record->name, 
                'description' => $record->description, 
                'health_disorders' => $record->health_disorders, 
                'dog_id' => $record->dog_id, 
                'status_id' => $record->status_id, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => ($record->edited ? $this->formatDateTime($record->edited) : $this->formatDateTime($record->created)), 
                'edited_at' => ($record->edited ? $this->formatDateTime($record->edited) : null), 
                'submitted_at' => ($record->submitted ? $this->formatDateTime($record->submitted) : null), 
                'rejection_reasons' => $record->rejection_reasons, 
                'accepted_at' => ($record->submitted ? $this->formatDateTime($record->submitted) : null), 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('breed_drafts')->insert($values);
        }

        $this->info('Moved table:user_breed_drafts '.count($values));
    }

    public function moveUserBreedDraftCharacteristics()
    {
        $records = $this->oldDB->table('user_breed_draft_characteristics')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'breed_draft_id' => $record->draft_id, 
                'characteristic_id' => $record->characteristic_id, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => ($record->updated ? $this->formatDateTime($record->updated) : $this->formatDateTime($record->created)), 
                'ignored' => $record->ignore, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('breed_draft_characteristics')->insert($values);
        }

        $this->info('Moved table:user_breed_draft_characteristics '.count($values));
    }

    public function moveUserBreedDraftCharacteristicGenetics()
    {
        //
    }

    public function moveUserBreedDraftCharacteristicGeneticsGenotypes()
    {
        $records = $this->oldDB->table('user_breed_draft_characteristic_genetics_genotypes')
            ->select('user_breed_draft_characteristic_genetics_genotypes.*', 'user_breed_draft_characteristic_genetics.draft_characteristic_id')
            ->join('user_breed_draft_characteristic_genetics', 'user_breed_draft_characteristic_genetics.id', '=', 'user_breed_draft_characteristic_genetics_genotypes.draft_characteristic_genetic_id')
            ->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'genotype_id' => $record->genotype_id, 
                'breed_draft_characteristic_id' => $record->draft_characteristic_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('breed_draft_characteristic_genotypes')->insert($values);
        }

        $this->info('Moved table:user_breed_draft_characteristic_genetics_genotypes '.count($values));
    }

    public function moveUserBreedDraftCharacteristicGeneticsPhenotypes()
    {
        $records = $this->oldDB->table('user_breed_draft_characteristic_genetics_phenotypes')
            ->select('user_breed_draft_characteristic_genetics_phenotypes.*', 'user_breed_draft_characteristic_genetics.draft_characteristic_id')
            ->join('user_breed_draft_characteristic_genetics', 'user_breed_draft_characteristic_genetics.id', '=', 'user_breed_draft_characteristic_genetics_phenotypes.draft_characteristic_genetic_id')
            ->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'phenotype_id' => $record->phenotype_id, 
                'breed_draft_characteristic_id' => $record->draft_characteristic_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('breed_draft_characteristic_phenotypes')->insert($values);
        }

        $this->info('Moved table:user_breed_draft_characteristic_genetics_phenotypes '.count($values));
    }

    public function moveUserBreedDraftCharacteristicRanges()
    {
        $records = $this->oldDB->table('user_breed_draft_characteristic_ranges')->get();

        $count = 0;

        foreach($records as $record)
        {
            $this->newDB->table('breed_draft_characteristics')
                ->where('id', $record->draft_characteristic_id)
                ->update(array(
                    'min_female_ranged_value' => $record->min_value_female, 
                    'max_female_ranged_value' => $record->max_value_female, 
                    'min_male_ranged_value' => $record->min_value_male, 
                    'max_male_ranged_value' => $record->max_value_male, 
                ));

            ++$count;
        }

        $this->info('Moved table:user_breed_draft_characteristic_ranges '.$count);
    }

    public function moveUserChallenges()
    {
        $records = $this->oldDB->table('user_challenges')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'user_id' => $record->user_id, 
                'level_id' => $record->challenge_level_id, 
                'dog_id' => $record->dog_id, 
                'credit_payout' => $record->credit_payout, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => $this->formatDateTime($record->created), 
                'completed_at' => ($record->completed ? $this->formatDateTime($record->created) : null), 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('challenges')->insert($values);
        }

        $this->info('Moved table:user_challenges '.count($values));
    }

    public function moveUserChallengeCharacteristics()
    {
        $records = $this->oldDB->table('user_challenge_characteristics')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'challenge_id' => $record->user_challenge_id, 
                'characteristic_id' => $record->characteristic_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('challenge_characteristics')->insert($values);
        }

        $this->info('Moved table:user_challenge_characteristics '.count($values));
    }

    public function moveUserChallengeCharacteristicGenotypes()
    {
        $records = $this->oldDB->table('user_challenge_characteristic_genotypes')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'challenge_characteristic_id' => $record->user_challenge_characteristic_id, 
                'genotype_id' => $record->genotype_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('challenge_characteristic_genotypes')->insert($values);
        }

        $this->info('Moved table:user_challenge_characteristic_genotypes '.count($values));
    }

    public function moveUserChallengeCharacteristicPhenotypes()
    {
        $records = $this->oldDB->table('user_challenge_characteristic_phenotypes')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'challenge_characteristic_id' => $record->user_challenge_characteristic_id, 
                'phenotype_id' => $record->phenotype_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('challenge_characteristic_phenotypes')->insert($values);
        }

        $this->info('Moved table:user_challenge_characteristic_phenotypes '.count($values));
    }

    public function moveUserChallengeCharacteristicRanges()
    {
        $records = $this->oldDB->table('user_challenge_characteristic_ranges')->get();

        $count = 0;

        foreach($records as $record)
        {
            $this->newDB->table('challenge_characteristics')
                ->where('id', $record->user_challenge_characteristic_id)
                ->update(array(
                    'ranged_value' => $record->value, 
                ));

            ++$count;
        }

        $this->info('Moved table:user_challenge_characteristic_ranges '.$count);
    }

    public function moveUserChatMessages()
    {
        $records = $this->oldDB->table('user_chat_messages')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'author_id' => $record->user_id, 
                'body' => $record->body, 
                'hex' => $record->hex, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => $this->formatDateTime($record->created), 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('chat_messages')->insert($values);
        }

        $this->info('Moved table:user_chat_messages '.count($values));
    }

    public function moveUserChatTurns()
    {
        $records = $this->oldDB->table('user_chat_turns')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'user_id' => $record->user_id, 
                'amount' => $record->amount, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => $this->formatDateTime($record->created), 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('chat_turns')->insert($values);
        }

        $this->info('Moved table:user_chat_turns '.count($values));
    }

    public function moveUserContests()
    {
        $records = $this->oldDB->table('user_contests')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'user_id' => $record->user_id, 
                'name' => $record->name, 
                'run_on' => $record->run_date, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => $this->formatDateTime($record->created), 
                'has_run' => $record->has_run, 
                'total_entries' => $record->total_entries, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('contests')->insert($values);
        }

        $this->info('Moved table:user_contests '.count($values));
    }

    public function moveUserContestContestTypes()
    {
        $records = $this->oldDB->table('user_contest_contest_types')->get();

        $count = 0;

        foreach($records as $record)
        {
            // Update the breed
            $this->newDB->table('contests')
                ->where('id', $record->contest_id)
                ->update(array(
                    'type_name' => $record->name, 
                    'type_description' => $record->description, 
                ));

            ++$count;
        }

        $this->info('Moved table:user_contest_contest_types '.$count);
    }

    public function moveUserContestContestTypePrereqs()
    {
        $records = $this->oldDB->table('user_contest_contest_type_prereqs')
            ->select('user_contest_contest_type_prereqs.*', 'user_contest_contest_types.contest_id')
            ->join('user_contest_contest_types', 'user_contest_contest_types.id', '=', 'user_contest_contest_type_prereqs.contest_contest_type_id')
            ->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'contest_id' => $record->contest_id, 
                'characteristic_id' => $record->characteristic_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('contest_prerequisites')->insert($values);
        }

        $this->info('Moved table:user_contest_contest_type_prereqs '.count($values));
    }

    public function moveUserContestContestTypePrereqGenetics()
    {
        //
    }

    public function moveUserContestContestTypePrereqGeneticsGenotypes()
    {
        $records = $this->oldDB->table('user_contest_contest_type_prereq_genetics_genotypes')
            ->select('user_contest_contest_type_prereq_genetics_genotypes.*', 'user_contest_contest_type_prereq_genetics.contest_contest_type_prereq_id')
            ->join('user_contest_contest_type_prereq_genetics', 'user_contest_contest_type_prereq_genetics.id', '=', 'user_contest_contest_type_prereq_genetics_genotypes.contest_contest_type_prereq_genetic_id')
            ->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'genotype_id' => $record->genotype_id, 
                'contest_prerequisite_id' => $record->contest_contest_type_prereq_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('contest_prerequisite_genotypes')->insert($values);
        }

        $this->info('Moved table:user_contest_contest_type_prereq_genetics_genotypes '.count($values));
    }

    public function moveUserContestContestTypePrereqGeneticsPhenotypes()
    {
        $records = $this->oldDB->table('user_contest_contest_type_prereq_genetics_phenotypes')
            ->select('user_contest_contest_type_prereq_genetics_phenotypes.*', 'user_contest_contest_type_prereq_genetics.contest_contest_type_prereq_id')
            ->join('user_contest_contest_type_prereq_genetics', 'user_contest_contest_type_prereq_genetics.id', '=', 'user_contest_contest_type_prereq_genetics_phenotypes.contest_contest_type_prereq_genetic_id')
            ->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'phenotype_id' => $record->phenotype_id, 
                'contest_prerequisite_id' => $record->contest_contest_type_prereq_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('contest_prerequisite_phenotypes')->insert($values);
        }

        $this->info('Moved table:user_contest_contest_type_prereq_genetics_phenotypes '.count($values));
    }

    public function moveUserContestContestTypePrereqRanges()
    {
        $records = $this->oldDB->table('user_contest_contest_type_prereq_ranges')->get();

        $count = 0;

        foreach($records as $record)
        {
            $this->newDB->table('contest_prerequisites')
                ->where('id', $record->contest_contest_type_prereq_id)
                ->update(array(
                    'min_ranged_value' => $record->min_value, 
                    'max_ranged_value' => $record->max_value, 
                ));

            ++$count;
        }

        $this->info('Moved table:user_contest_contest_type_prereq_ranges '.$count);
    }

    public function moveUserContestContestTypeReqs()
    {
        $records = $this->oldDB->table('user_contest_contest_type_reqs')
            ->select('user_contest_contest_type_reqs.*', 'user_contest_contest_types.contest_id', 'characteristic_ranges.characteristic_id')
            ->join('user_contest_contest_types', 'user_contest_contest_types.id', '=', 'user_contest_contest_type_reqs.contest_contest_type_id')
            ->join('characteristic_ranges', 'characteristic_ranges.id', '=', 'user_contest_contest_type_reqs.characteristic_range_id')
            ->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'contest_id' => $record->contest_id, 
                'characteristic_id' => $record->characteristic_id, 
                'type_id' => $record->range_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('contest_requirements')->insert($values);
        }

        $this->info('Moved table:user_contest_contest_type_reqs '.count($values));
    }

    public function moveUserContestDogs()
    {
        $records = $this->oldDB->table('user_contest_dogs')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'dog_id' => $record->dog_id, 
                'contest_id' => $record->contest_id, 
                'score' => $record->score, 
                'rank' => $record->rank, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('contest_entries')->insert($values);
        }

        $this->info('Moved table:user_contest_dogs '.count($values));
    }
    
    public function moveUserContestTypes()
    {
        $records = $this->oldDB->table('user_contest_types')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'user_id' => $record->user_id, 
                'name' => $record->name, 
                'description' => $record->description, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => $this->formatDateTime($record->created), 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('user_contest_types')->insert($values);
        }

        $this->info('Moved table:user_contest_types '.count($values));
    }
    
    public function moveUserContestTypePrereqs()
    {
        $records = $this->oldDB->table('user_contest_type_prereqs')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'contest_type_id' => $record->contest_type_id, 
                'characteristic_id' => $record->characteristic_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('user_contest_type_prerequisites')->insert($values);
        }

        $this->info('Moved table:user_contest_type_prereqs '.count($values));
    }

    public function moveUserContestTypePrereqGenetics()
    {
        //
    }

    public function moveUserContestTypePrereqGeneticsGenotypes()
    {
        $records = $this->oldDB->table('user_contest_type_prereq_genetics_genotypes')
            ->select('user_contest_type_prereq_genetics_genotypes.*', 'user_contest_type_prereq_genetics.contest_type_prereq_id')
            ->join('user_contest_type_prereq_genetics', 'user_contest_type_prereq_genetics.id', '=', 'user_contest_type_prereq_genetics_genotypes.contest_type_prereq_genetic_id')
            ->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'genotype_id' => $record->genotype_id, 
                'contest_type_prerequisite_id' => $record->contest_type_prereq_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('user_contest_type_prerequisite_genotypes')->insert($values);
        }

        $this->info('Moved table:user_contest_type_prereq_genetics_genotypes '.count($values));
    }

    public function moveUserContestTypePrereqGeneticsPhenotypes()
    {
        $records = $this->oldDB->table('user_contest_type_prereq_genetics_phenotypes')
            ->select('user_contest_type_prereq_genetics_phenotypes.*', 'user_contest_type_prereq_genetics.contest_type_prereq_id')
            ->join('user_contest_type_prereq_genetics', 'user_contest_type_prereq_genetics.id', '=', 'user_contest_type_prereq_genetics_phenotypes.contest_type_prereq_genetic_id')
            ->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'phenotype_id' => $record->phenotype_id, 
                'contest_type_prerequisite_id' => $record->contest_type_prereq_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('user_contest_type_prerequisite_phenotypes')->insert($values);
        }

        $this->info('Moved table:user_contest_type_prereq_genetics_phenotypes '.count($values));
    }

    public function moveUserContestTypePrereqRanges()
    {
        $records = $this->oldDB->table('user_contest_type_prereq_ranges')->get();

        $count = 0;

        foreach($records as $record)
        {
            $this->newDB->table('user_contest_type_prerequisites')
                ->where('id', $record->contest_type_prereq_id)
                ->update(array(
                    'min_ranged_value' => $record->min_value, 
                    'max_ranged_value' => $record->max_value, 
                ));

            ++$count;
        }

        $this->info('Moved table:user_contest_type_prereq_ranges '.$count);
    }

    public function moveUserContestTypeReqs()
    {
        $records = $this->oldDB->table('user_contest_type_reqs')
            ->select('user_contest_type_reqs.*', 'characteristic_ranges.characteristic_id')
            ->join('characteristic_ranges', 'characteristic_ranges.id', '=', 'user_contest_type_reqs.characteristic_range_id')
            ->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'contest_type_id' => $record->contest_type_id, 
                'characteristic_id' => $record->characteristic_id, 
                'type_id' => $record->range_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('user_contest_type_requirements')->insert($values);
        }

        $this->info('Moved table:user_contest_type_reqs '.count($values));
    }

    public function moveUserCreditTransactions()
    {
        $records = $this->oldDB->table('user_credit_transactions')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'user_id' => $record->user_id, 
                'type' => $record->type, 
                'amount' => $record->amount, 
                'cost' => $record->cost, 
                'gross' => $record->gross, 
                'info' => $record->info, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => $this->formatDateTime($record->created), 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('user_credit_transactions')->insert($values);
        }

        $this->info('Moved table:user_credit_transactions '.count($values));
    }

    public function moveUserCreditTransfers()
    {
        $records = $this->oldDB->table('user_credit_transfers')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'sender_id' => $record->sender_id, 
                'receiver_id' => $record->receiver_id, 
                'amount' => $record->amount, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => $this->formatDateTime($record->created), 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('user_credit_transfers')->insert($values);
        }

        $this->info('Moved table:user_credit_transfers '.count($values));
    }

    public function moveUserGoals()
    {
        $records = $this->oldDB->table('user_goals')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'user_id' => $record->user_id, 
                'body' => $record->body, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => $this->formatDateTime($record->created), 
                'completed_at' => ($record->completed ? $this->formatDateTime($record->completed) : null), 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('user_goals')->insert($values);
        }

        $this->info('Moved table:user_goals '.count($values));
    }

    public function moveUserKennelGroups()
    {
        $records = $this->oldDB->table('user_kennel_groups')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'user_id' => $record->user_id, 
                'name' => $record->name, 
                'description' => $record->description, 
                'type_id' => $record->type_id, 
                'dog_order_id' => $record->dog_order_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('kennel_groups')->insert($values);
        }

        $this->info('Moved table:user_kennel_groups '.count($values));
    }

    public function moveUserNotifications()
    {
        $records = $this->oldDB->table('user_notifications')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'user_id' => $record->user_id, 
                'type' => $record->type, 
                'body' => $record->body, 
                'persistent' => $record->persistent, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => $this->formatDateTime($record->created), 
                'unread' => ( ! $record->read), 
                'unseen' => ( ! $record->seen), 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('user_notifications')->insert($values);
        }

        $this->info('Moved table:user_notifications '.count($values));
    }

    public function moveUserTutorialStages()
    {
        $records = $this->oldDB->table('user_tutorial_stages')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'user_id' => $record->user_id, 
                'tutorial_stage_number' => $record->tutorial_stage_number, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => $this->formatDateTime($record->created), 
                'data' => $record->data, 
                'seen' => $record->seen, 
                'completed_at' => ($record->completed ? $this->formatDateTime($record->completed) : null), 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('user_tutorial_stages')->insert($values);
        }

        $this->info('Moved table:user_tutorial_stages '.count($values));
    }

    public function moveWikiCategories()
    {
        $records = $this->oldDB->table('wiki_categories')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'parent_id' => $record->parent_id, 
                'title' => $record->title, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('help_categories')->insert($values);
        }

        $this->info('Moved table:wiki_categories '.count($values));
    }

    public function moveWikiCategoriesWikiPages()
    {
        $records = $this->oldDB->table('wiki_categories_wiki_pages')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'help_category_id' => $record->wiki_category_id, 
                'help_page_id' => $record->wiki_page_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('help_categories_help_pages')->insert($values);
        }

        $this->info('Moved table:wiki_categories_wiki_pages '.count($values));
    }

    public function moveWikiPages()
    {
        $records = $this->oldDB->table('wiki_pages')->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'title' => $record->title, 
                'content' => $record->content, 
                'created_at' => $this->formatDateTime($record->created), 
                'updated_at' => ( ! $record->updated ? $this->formatDateTime($record->created) : $this->formatDateTime($record->updated)), 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('help_pages')->insert($values);
        }

        $this->info('Moved table:wiki_pages '.count($values));
    }

    public function chunk($values, $table, $size = 200)
    {
        // Chunk the values
        $chunks = [];

        foreach($values as $index => $value)
        {
            $chunks[$index%$size][] = $value;
        }

        foreach($chunks as $chunk)
        {
            $this->newDB->table($table)->insert($chunk);
        }
    }

    public function formatDateTime($string)
    {
        return date('Y-m-d H:i:s', $string);
    }

    public function formatDate($string)
    {
        return date('Y-m-d', $string);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('all', null, InputOption::VALUE_NONE, 'Move all tables', null),
            array('dev', null, InputOption::VALUE_NONE, 'Move only tables required for dev', null),
            array('tables', null, InputOption::VALUE_OPTIONAL, 'Move individual tables', null),
        );
    }

}
