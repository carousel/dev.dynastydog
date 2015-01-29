<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ExtinctBreedsCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'breeds:extinct';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check whether or not breeds should become extinct or endangered';

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
        // Sunday 11:59 PM
        $gracePeriod      = Config::get('game.breed.grace_period');
        $activeThreshold  = Config::get('game.breed.active_threshold');
        $activeExtinction = Config::get('game.breed.active_extinction');

        $graceDaysAgo = Carbon::now()->subDays($gracePeriod)->toDateTimeString();

        // Find all user breeds that are extinctable
        $breeds = Breed::with(array(
            'dogs' => function($query)
                {
                    $query->whereAlive()->whereActiveBreedMember();
                }, 
            ))
            ->whereActive()
            ->whereExtinctable()
            ->wherePlayerBreed()
            ->where('created_at', '>=', $graceDaysAgo)
            ->get();

        foreach($breeds as $breed)
        {
            try
            {
                $totalActiveMembers = $breed->dogs->count();

                if ($totalActiveMembers < $activeExtinction) // Breed went extinct
                {
                    if ( ! is_null($breed->creator))
                    {
                        // Notify the breed creator
                        $params = array(
                            'manageBreedDraftsUrl' => URL::route('breed_registry/manage'), 
                            'breed'       => $breed->name,
                            'active_dogs' => number_format($activeExtinction),
                        );

                        $body = Lang::get('notifications/breed_registry.breed_extinct.to_creator', array_map('htmlentities', array_dot($params)));
                        
                        $breed->creator->notify($body, UserNotification::TYPE_DANGER);
                    }

                    // Mark the breed as inactive
                    $breed->active = false;
                    $breed->save();

                    // Revert it back to a draft
                    if ( ! is_null($breed->draft))
                    {
                        $breed->draft->status_id = BreedDraft::STATUS_EXTINCT;
                        $breed->draft->save();

                        // Move the image if it exists
                        if ($breed->hasImage())
                        {
                            // Get the image
                            $oldPath = $breed->getImagePath();

                            // Get the new image path
                            $newPath = $breed->draft->getImagePath();

                            File::move($oldPath, $newPath);
                        }
                    }
                }
                else if ($totalActiveMembers < $activeThreshold) // Breed numbers are low, but not extinct
                {
                    $params = array(
                        'manageBreedDraftsUrl' => URL::route('breed_registry/manage'), 
                        'breedUrl'    => URL::route('breed_registry/breed', $breed->id), 
                        'breed'       => $breed->name,
                        'active_dogs' => number_format($activeExtinction).' active breed '.Str::plural('member', $activeExtinction),
                        'required_active_dogs' => number_format($activeExtinction),
                    );

                    // Notify the breed creator
                    $body = Lang::get('notifications/breed_registry.breed_endangered.to_creator', array_map('htmlentities', array_dot($params)));
                    
                    $breed->creator->notify($body, UserNotification::TYPE_DANGER);

                    // Get all dog owner IDs that are endangered
                    $ownerIds = Dog::whereAlive()->where('breed_id', $breed->id)->where('owner_id', '<>', $breed->creator_id)->lists('owner_id');

                    // Notify the dog owners
                    $body = Lang::get('notifications/breed_registry.breed_endangered.to_owner', array_map('htmlentities', array_dot($params)));

                    User::notifyOnly($ownerIds, $body, UserNotification::TYPE_DANGER);
                }
            }
            catch(Exception $e)
            {
                // We want to log the exception
                Log::error('Breed failed to be checked for extinction', array('breed' => $breed->toArray(), 'exception' => $e->getMessage()));
            }
        }

        try
        {
            // All dogs are no longer active breed members
            DB::table('dogs')->update(array(
                'active_breed_member' => false, 
            ));
        }
        catch(Exception $e)
        {
            // We want to log the exception
            Log::error('Active breed members failed to become inactive', array('exception' => $e->getMessage()));
        }
    }

}
