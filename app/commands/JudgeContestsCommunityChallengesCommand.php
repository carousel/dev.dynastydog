<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class JudgeContestsCommunityChallengesCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'communitychallenges:judge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Judge entries in community challenges that end today';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $today = Carbon::today()->format('Y-m-d');

        // 11:59 PM
        // Get all community challenges set to end today that haven't been judged yet
        $communityChallenges = CommunityChallenge::whereUnjudged()->where('end_date', '<=', $today)->get();

        foreach($communityChallenges as $communityChallenge)
        {
            try
            {
                // Judge the community challenge
                $communityChallenge->judge();
            }
            catch(Exception $e)
            {
                // We want to log the exception
                Log::error('Community challenge failed to be judged', array('community_challenge' => $communityChallenge->toArray(), 'exception' => $e->getMessage()));
            }
        }
    }

}
