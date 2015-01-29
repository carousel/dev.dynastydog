<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RunContestsCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'contests:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rank entries in contests that run today';

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
        // Get all contests set to run today
        $contests = Contest::where('run_on', '<=', $today)->where('has_run', false)->get();

        foreach($contests as $contest)
        {
            try
            {
                $contest->run();
            }
            catch(Exception $e)
            {
                // We want to log the exception
                Log::error('Contest failed to run', array('contest' => $contest->toArray(), 'exception' => $e->getMessage()));
            }
        }
    }

}
