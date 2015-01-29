<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class GiveImportsToUsersCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'users:give_imports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Give imports to users';

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
            $max = $this->option('max');

            // Give the imports
            DB::table('users')
                ->where('imports', '<', $max)
                ->update(array(
                    'imports' => $max, 
                ));
        }
        catch(Exception $e)
        {
            // We want to log the exception
            Log::error('Imports could not be given to users', array('exception' => $e->getMessage()));
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
            array('max', null, InputOption::VALUE_OPTIONAL, 'The max number of imports a user can have', 2),
        );
    }

}
