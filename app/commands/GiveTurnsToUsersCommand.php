<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class GiveTurnsToUsersCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'users:give_turns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Give turns to users';

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

        try
        {
            $increment = $this->option('increment');
            $max       = $this->option('max');

            // Give the turns
            DB::table('users')
                ->where('turns', '<', $max)
                ->increment('turns', $increment);
        }
        catch(Exception $e)
        {
            // We want to log the exception
            Log::error('Turns could not be given to users', array('exception' => $e->getMessage()));
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('increment', null, InputOption::VALUE_OPTIONAL, 'The number of turns to add if a user has less than the max', 1),
            array('max', null, InputOption::VALUE_OPTIONAL, 'The max number of turns a user can have', 5),
        );
    }

}
