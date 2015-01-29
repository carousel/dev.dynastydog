<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RemoveContestsCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'contests:remove';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove old contests';

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
        $thirtyDaysAgo = Carbon::today()->subDays(30)->format('Y-m-d');

        DB::table('contests')->where('run_on', '<=', $thirtyDaysAgo)->where('has_run', true)->delete();
    }

}
