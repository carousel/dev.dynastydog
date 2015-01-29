<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CleanChatCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'chat:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove old chat messages';

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
            $limit = $this->option('limit');

            // Do not delete the last N messages
            $savedIds = DB::table('chat_messages')->orderBy('id', 'desc')->take($limit)->lists('id');

            // Always add -1
            $savedIds[] = -1;

            // Delete the messages
            DB::table('chat_messages')->whereNotIn('id', $savedIds)->delete();
        }
        catch(Exception $e)
        {
            // We want to log the exception
            Log::error('Chat messages could not be cleaned', array('exception' => $e->getMessage()));
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
            array('limit', null, InputOption::VALUE_OPTIONAL, 'The number of most recent chat messages to save', 50),
        );
    }

}
