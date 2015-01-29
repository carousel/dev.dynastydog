<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ExpireLendRequestsCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'lendrequests:expire';

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
        // Get all lend requests set to expire today and ones that were supposed to expire previously, but failed to
        $lendRequests = LendRequest::whereNotNull('return_at')->where('return_at', '<=', $today)->get();

        foreach($lendRequests as $lendRequest)
        {
            try
            {
                DB::transaction(function () use ($lendRequest)
                {
                    // Transfer the ownership
                    $lendRequest->dog->owner_id = $lendRequest->sender->id;
                    
                    // Put the dog in its old kennel
                    $newKennelGroup = $lendRequest->sender->kennelGroups()->whereNotCemetery()->first();

                    $lendRequest->dog->kennel_group_id = is_null($newKennelGroup)
                        ? null
                        : $newKennelGroup->id;

                    // Save the dog
                    $lendRequest->dog->save();

                    // Notify the receiver
                    $params = array(
                        'sender'    => $lendRequest->sender->nameplate(), 
                        'senderUrl' => URL::route('user/profile', $lendRequest->sender->id), 
                        'dog'       => $lendRequest->dog->nameplate(), 
                        'dogUrl'    => URL::route('dog/profile', $lendRequest->dog->id), 
                    );

                    $body = Lang::get('notifications/user.expired_lend_request.to_receiver', array_map('htmlentities', array_dot($params)));
                    
                    $lendRequest->receiver->notify($body, UserNotification::TYPE_SUCCESS);

                    // Notify the sender
                    $params = array(
                        'receiver'    => $lendRequest->receiver->nameplate(), 
                        'receiverUrl' => URL::route('user/profile', $lendRequest->receiver->id), 
                        'dog'         => $lendRequest->dog->nameplate(), 
                        'dogUrl'      => URL::route('dog/profile', $lendRequest->dog->id), 
                    );

                    $body = Lang::get('notifications/user.expired_lend_request.to_sender', array_map('htmlentities', array_dot($params)));
                    
                    $lendRequest->sender->notify($body, UserNotification::TYPE_SUCCESS);

                    // Delete the request
                    $lendRequest->delete();
                });
            }
            catch(Exception $e)
            {
                // We want to log the exception
                Log::error('Lend request failed to expire', array('lend_request' => $lendRequest->toArray(), 'exception' => $e->getMessage()));
            }
        }
    }

}
