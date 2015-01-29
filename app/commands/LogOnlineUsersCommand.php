<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class LogOnlineUsersCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'users:log_online';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Log online users';

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
        $now = Carbon::now()->toDateTimeString();

        try
        {
            $totalUsersOnline = User::whereOnline()->count();

            // Log the total
            DB::table('online_users_logs')
                ->insert(array(
                    'total'      => $totalUsersOnline, 
                    'created_at' => $now, 
                    'updated_at' => $now, 
                ));
        }
        catch(Exception $e)
        {
            // We want to log the exception
            Log::error('Online users could not be logged', array('exception' => $e->getMessage()));
        }
    }

}
