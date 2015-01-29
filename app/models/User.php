<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

use Cartalyst\Sentry\Users\Eloquent\User as SentryUserModel;

class User extends SentryUserModel implements UserInterface, RemindableInterface {

    use UserTrait, RemindableTrait, SoftDeletingTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hide = array('password', 'remember_token');

    protected $dates = ['deleted_at', 'last_login', 'activated_at', 'last_action_at', 'upgraded_until', 'banned_until', 'chat_banned_until', 'breeders_prize_until'];

    public function getReadCommunityRulesAttribute($readCommunityRules)
    {
        return (bool) $readCommunityRules;
    }

    public function getShowGifterLevelAttribute($showGifterLevel)
    {
        return (bool) $showGifterLevel;
    }

    public function getIpBannedAttribute($ipBanned)
    {
        return (bool) $ipBanned;
    }

    public function getPasswordResetRequiredAttribute($passwordResetRequired)
    {
        return (bool) $passwordResetRequired;
    }

    public function setKennelDescriptionAttribute($kennelDescription)
    {
        $this->attributes['kennel_description'] = Purifier::clean($kennelDescription);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    |
    */

    public function scopeWhereOnline($query)
    {
        return $query
            ->where(function($q)
            {
                $onlineThreshold = Config::get('game.user.online_threshold');
                $logoutURI = ltrim(parse_url(URL::route('auth/logout'), PHP_URL_PATH), '/');
                $threshold = Carbon::now()->subMinutes($onlineThreshold)->toDateTimeString();

                $q->whereNotNull('last_action_at')->where('last_action_at', '>=', $threshold)->where('last_uri', '<>', $logoutURI);
            });
    }

    public function scopeWhereActive($query)
    {
        return $query
            ->where(function($q)
            {
                $twoDaysAgo = Carbon::now()->subDays(2)->toDateTimeString();

                $q->whereNotNull('last_action_at')->where('last_action_at', '>=', $twoDaysAgo);
            });
    }

    public function scopeSameCreatedIp($query, $createdIp)
    {
        return $query->where('created_ip', $createdIp);
    }

    public function scopeWhereSameCreatedIp($query, $createdIp)
    {
        return $query->scopeSameCreatedIp($query, $createdIp);
    }

    public function scopeWhereBeginner($query)
    {
        $oneWeekAgo = Carbon::now()->subDays(7)->toDateTimeString();
        return $query->where('created_at', '>=', $oneWeekAgo);
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the referrer
     *
     * @return User
     */
    public function referrer()
    {
        return $this->belongsTo('User', 'referred_by_id', 'id');
    }

    /**
     * Return the referral level
     *
     * @return ReferralLevel
     */
    public function referralLevel()
    {
        return $this->belongsTo('ReferralLevel', 'referral_level_id', 'id');
    }

    /**
     * Return the gifter level
     *
     * @return GifterLevel
     */
    public function gifterLevel()
    {
        return $this->belongsTo('GifterLevel', 'gifter_level_id', 'id');
    }

    /**
     * Return the challenge level
     *
     * @return ChallengeLevel
     */
    public function challengeLevel()
    {
        return $this->belongsTo('ChallengeLevel', 'challenge_level_id', 'id');
    }


    /*
    |--------------------------------------------------------------------------
    | One To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All dogs owned by this user
     *
     * @return Collection of Dogs
     */
    public function dogs()
    {
        return $this->hasMany('Dog', 'owner_id', 'id');
    }

    /**
     * All users referred
     *
     * @return Collection of Users
     */
    public function referred()
    {
        return $this->hasMany('User', 'referred_by_id', 'id');
    }

    /**
     * All kennel groups owned by this user
     *
     * @return Collection of KennelGroups
     */
    public function kennelGroups()
    {
        return $this->hasMany('KennelGroup', 'user_id', 'id');
    }

    /**
     * All notifications
     *
     * @return Collection of UserNotifications
     */
    public function notifications()
    {
        return $this->hasMany('UserNotification', 'user_id', 'id');
    }

    /**
     * All conversations started by this user
     *
     * @return Collection of Conversations
     */
    public function sentConversations()
    {
        return $this->hasMany('Conversation', 'sender_id', 'id');
    }

    /**
     * All conversations received by this user
     *
     * @return Collection of Conversations
     */
    public function receivedConversations()
    {
        return $this->hasMany('Conversation', 'receiver_id', 'id');
    }

    /**
     * All conversations this user is taking part in
     *
     * @return Collection of Conversations
     */
    public function conversations()
    {
        return Conversation::where(function($query)
            {
                $query->where('sender_id', $this->id)->orWhere('receiver_id', $this->id);
            });
    }

    /**
     * All conversation messages sent by this user
     *
     * @return Collection of ConversationMessages
     */
    public function conversationMessages()
    {
        return $this->hasMany('ConversationMessage', 'user_id', 'id');
    }

    /**
     * All chat messages sent by this user
     *
     * @return Collection of ChatMessages
     */
    public function chatMessages()
    {
        return $this->hasMany('ChatMessage', 'author_id', 'id');
    }

    /**
     * All chat turns thrown by this user
     *
     * @return Collection of ChatTurns
     */
    public function chatTurns()
    {
        return $this->hasMany('ChatTurn', 'user_id', 'id');
    }

    /**
     * All breeds created by this user
     *
     * @return Collection of Breeds
     */
    public function breeds()
    {
        return $this->hasMany('Breed', 'creator_id', 'id');
    }

    /**
     * All breed drafts created by this user
     *
     * @return Collection of BreedDrafts
     */
    public function breedDrafts()
    {
        return $this->hasMany('BreedDraft', 'user_id', 'id');
    }

    /**
     * All tutorial steps attempted
     *
     * @return Collection of UserTutorialStages
     */
    public function tutorialStages()
    {
        return $this->hasMany('UserTutorialStage', 'user_id', 'id');
    }

    /**
     * All authored forum topics
     *
     * @return Collection of ForumTopics
     */
    public function forumTopics()
    {
        return $this->hasMany('ForumTopic', 'author_id', 'id');
    }

    /**
     * All authored forum posts
     *
     * @return Collection of ForumPosts
     */
    public function forumPosts()
    {
        return $this->hasMany('ForumPost', 'author_id', 'id');
    }

    /**
     * All authored news post comments
     *
     * @return Collection of NewsPostComments
     */
    public function newsPostComments()
    {
        return $this->hasMany('NewsPostComment', 'author_id', 'id');
    }

    /**
     * All authored news post comments
     *
     * @return Collection of NewsPollAnswerVotes
     */
    public function newsPollAnswerVotes()
    {
        return $this->hasMany('NewsPollAnswerVote', 'user_id', 'id');
    }

    /**
     * All contests
     *
     * @return Collection of Contests
     */
    public function contests()
    {
        return $this->hasMany('Contest', 'user_id', 'id');
    }

    /**
     * All contest types
     *
     * @return Collection of UserContestTypes
     */
    public function contestTypes()
    {
        return $this->hasMany('UserContestType', 'user_id', 'id');
    }

    /**
     * All challenges
     *
     * @return Collection of Challenges
     */
    public function challenges()
    {
        return $this->hasMany('Challenge', 'user_id', 'id');
    }

    /**
     * All personal goals
     *
     * @return Collection of UserGoals
     */
    public function personalGoals()
    {
        return $this->hasMany('UserGoal', 'user_id', 'id');
    }

    /**
     * All BLRs this user has requested
     *
     * @return Collection of BeginnersLuckRequests
     */
    public function sentBeginnersLuckRequests()
    {
        return $this->hasMany('BeginnersLuckRequest', 'user_id', 'id');
    }

    /**
     * All BLRs this user has received
     *
     * @return Collection of BeginnersLuckRequests
     */
    public function receivedBeginnersLuckRequests()
    {
        return $this->hasMany('BeginnersLuckRequest', 'beginner_id', 'id');
    }

    /**
     * All beginners luck requests this user is taking part in
     *
     * @return Collection of BeginnersLuckRequests
     */
    public function beginnersLuckRequests()
    {
        return BeginnersLuckRequest::where(function($query)
            {
                $query->where('user_id', $this->id)->orWhere('beginner_id', $this->id);
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All users this user has blocked
     *
     * @return Collection of Users
     */
    public function blocked()
    {
        return $this->belongsToMany('User', 'user_blocks', 'user_id', 'blocked_id');
    }

    /**
     * All users that has this user blocked
     *
     * @return Collection of Users
     */
    public function blockedBy()
    {
        return $this->belongsToMany('User', 'user_blocks', 'blocked_id', 'user_id');
    }

    /**
     * All conversations this user has in their inbox
     *
     * @return Collection of Conversations
     */
    public function inbox()
    {
        return $this->belongsToMany('Conversation', 'user_conversations', 'user_id', 'conversation_id');
    }

    /**
     * All PayPal payments by this user
     *
     * @return Collection of Payments
     */
    public function payments()
    {
        return $this->belongsToMany('Payment', 'user_payments', 'user_id', 'payment_id');
    }

    /**
     * All unclaimed community challenge prizes for this user
     *
     * @return Collection of CommunityChallenges
     */
    public function unclaimedCommunityChallengePrizes()
    {
        return $this->belongsToMany('CommunityChallenge', 'community_challenge_prize_winners', 'user_id', 'community_challenge_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    public function receivedStudRequests()
    {
        return $this->hasManyThrough('StudRequest', 'Dog', 'owner_id', 'stud_id')->select('stud_requests.*');
    }

    public function sentStudRequests()
    {
        return $this->hasManyThrough('StudRequest', 'Dog', 'owner_id', 'bitch_id')->select('stud_requests.*');
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function nameplate()
    {
        return $this->display_name.' (#'.$this->id.')';
    }

    public function linkedNameplate()
    {
        return HTML::link(URL::route('user/profile', $this->id), e($this->nameplate()));
    }

    public function isAdministrator()
    {
        // Find the Administrator group
        try
        {
            $admin = Sentry::findGroupByName('Administrator');
        }
        catch (Exception $e)
        {
            return false;
        }

        // Check if the user is in the administrator group
        return $this->inGroup($admin);
    }

    public function isUpgraded()
    {
        return is_null($this->upgraded_until)
            ? false
            : ( ! $this->upgraded_until->isPast());
    }

    public function hasAvatar()
    {
        return $this->isUpgraded()
            ? (strlen($this->avatar) > 0)
            : false;
    }

    public function nextTurnIn()
    {
        return ($this->turns < 5 ? (30 * 60) - (time() % (30 * 60)) : '');
    }

    public function canAffordCredits($amount)
    {
        return ($this->credits >= $amount);
    }

    public function canAffordTurns($amount)
    {
        return ($this->turns >= $amount);
    }

    public function canAffordImports($amount)
    {
        return ($this->imports >= $amount);
    }

    public function canAffordCustomImports($amount)
    {
        return ($this->custom_imports >= $amount);
    }

    public function canAffordReferralPoints($amount)
    {
        return ($this->referral_points >= $amount);
    }

    public function isIpBanned()
    {
        return $this->ip_banned;
    }

    public function isBannedFromChat()
    {
        return is_null($this->chat_banned_until)
            ? false
            : $this->chat_banned_until->isFuture();
    }

    public function isBanned()
    {
        return is_null($this->banned_until)
            ? false
            : $this->banned_until->isFuture();
    }

    public function hasCompletedTutorial()
    {
        $totalIncomplete = $this->tutorialStages()->where(function($query)
            {
                $query->whereIncomplete()->orWhere(function($q)
                    {
                        $q->whereUnseen();
                    });
            })
            ->count();

        return ($totalIncomplete <= 0);
    }

    public function hasCompletedTutorialStage($slug)
    {
        $stage = $this->tutorialStages()->complete()
            ->select('user_tutorial_stages.*')
            ->join('tutorial_stages', 'tutorial_stages.number', '=', 'user_tutorial_stages.tutorial_stage_number')
            ->where('tutorial_stages.slug', $slug)
            ->first();

        return ( ! is_null($stage));
    }

    public function isOnTutorialStage($slug, $incomplete = true)
    {
        $currentStage = $this->tutorialStages()->current()->with('tutorialStage')->first();

        return (is_null($currentStage) or ($incomplete and $currentStage->isComplete()))
            ? false
            : ($currentStage->tutorialStage->slug == $slug);
    }

    public function inTutorial()
    {
        return ( ! $this->hasCompletedTutorial());
    }

    /**
     * @throws Dynasty\Dogs\Exceptions\NotSavedException
     */
    public function importDog($name, $breed, $sex, $age, $custom = false, $customizedCharacteristics = [])
    {
        // Start the new dog
        $dog = new Dog;

        $dog->fill(array(
            'name'          => $name, 
            'breed_id'      => $breed->id, 
            'sex_id'        => $sex->id, 
            'age'           => $age, 
            'custom_import' => $custom, 
            # Defaults
            'display_image' => Dog::DISP_IMAGE_DEFAULT, 
            'studding'      => Dog::STUDDING_NONE, 
            'active_breed_member' => false, 
        ));

        // Assign the dog to its owners first kennel group
        $kennelGroup = $this->kennelGroups()->whereNotCemetery()->first();

        // Shouldn't ever be null, but we should check for it regardless
        if ( ! is_null($kennelGroup))
        {
            $dog->kennel_group_id = $kennelGroup->id;
        }

        // Create the dog
        $dog = $this->dogs()->save($dog);

        if ( ! $dog)
        {
            throw new Dynasty\Dogs\Exceptions\NotSavedException;
        }

        // Complete the rest of the dog
        $dog->complete($customizedCharacteristics);

        // Return the newly created dog
        return $dog;
    }

    public function completeTutorialStage($slug, $data = [], $seen = false, $completed = false)
    {
        return $this->isOnTutorialStage($slug)
            ? $this->advanceTutorial($data, $seen, $completed)
            : null;
    }

    public function advanceTutorial($data = null, $seen = false, $completed = false)
    {
        $currentStage = $this->tutorialStages()->current()->first();

        $mergedData = [];
        
        if (is_null($currentStage))
        {
            $nextStage = TutorialStage::find(1);
        }
        else
        {
            // Mark the last stage as seen, always
            $currentStage->seen = true;
            $currentStage->completed_at = Carbon::now();
            $currentStage->save();

            // Give them the next stage
            $nextStage = $currentStage->tutorialStage->getNextTutorialStage();

            if (is_array($currentStage->data))
            {
                $mergedData += $currentStage->data;
            }
        }

        if (is_null($nextStage))
        {
            return null;
        }

        if (is_array($data))
        {
            $mergedData = $data + $mergedData;
        }

        $completedAt = ($completed) ? Carbon::now() : null;

        // Give them the tutorial
        $currentStage = UserTutorialStage::create(array(
            'user_id'      => $this->id, 
            'tutorial_stage_number' => $nextStage->number, 
            'seen'         => $seen, 
            'completed_at' => $completedAt, 
            'data'         => $mergedData, 
        ));

        return $currentStage;
    }

    public function referralLevelCompletionPercent()
    {
        // Get all past requirements
        $pastNeeded = DB::table('referral_levels')
            ->where('referred_users', '<=', $this->referralLevel->referred_users)
            ->sum('referred_users');

        if (is_null($pastNeeded))
        {
            $pastNeeded = 0;
        }

        $referralsForThisLevel = $this->total_referrals - $pastNeeded;

        // Get the next level requirements
        $nextLevelNeeded = DB::table('referral_levels')
            ->select('referred_users')
            ->where('referred_users', '>', $this->referralLevel->referred_users)
            ->orderBy('referred_users', 'asc')
            ->take(1)
            ->pluck('referred_users');

        return ( ! $nextLevelNeeded)
            ? 100
            : (($referralsForThisLevel / $nextLevelNeeded) * 100);
    }

    public function referralLevelProgress()
    {
        // Get all past requirements
        $pastNeeded = DB::table('referral_levels')
            ->where('referred_users', '<=', $this->referralLevel->referred_users)
            ->sum('referred_users');

        if (is_null($pastNeeded))
        {
            $pastNeeded = 0;
        }

        $referralsForThisLevel = $this->total_referrals - $pastNeeded;

        // Get the next level requirements
        $nextLevelNeeded = DB::table('referral_levels')
            ->select('referred_users')
            ->where('referred_users', '>', $this->referralLevel->referred_users)
            ->orderBy('referred_users', 'asc')
            ->take(1)
            ->pluck('referred_users');

        return ( ! $nextLevelNeeded)
            ? '&infin;'
            : $referralsForThisLevel.'/'.$nextLevelNeeded;
    }

    public function challengeLevelCompletionPercent()
    {
        // Get all past requirements
        $pastNeeded = DB::table('challenge_levels')
            ->where('completed_challenges', '<=', $this->challengeLevel->completed_challenges)
            ->sum('completed_challenges');

        if (is_null($pastNeeded))
        {
            $pastNeeded = 0;
        }

        $challengesForThisLevel = $this->total_completed_challenges - $pastNeeded;

        // Get the next level requirements
        $nextLevelNeeded = DB::table('challenge_levels')
            ->select('completed_challenges')
            ->where('completed_challenges', '>', $this->challengeLevel->completed_challenges)
            ->orderBy('completed_challenges', 'asc')
            ->take(1)
            ->pluck('completed_challenges');

        return ( ! $nextLevelNeeded)
            ? 100
            : (($challengesForThisLevel / $nextLevelNeeded) * 100);
    }

    public function challengeLevelProgress()
    {
        // Get all past requirements
        $pastNeeded = DB::table('challenge_levels')
            ->where('completed_challenges', '<=', $this->challengeLevel->completed_challenges)
            ->sum('completed_challenges');

        if (is_null($pastNeeded))
        {
            $pastNeeded = 0;
        }

        $challengesForThisLevel = $this->total_completed_challenges - $pastNeeded;

        // Get the next level requirements
        $nextLevelNeeded = DB::table('challenge_levels')
            ->select('completed_challenges')
            ->where('completed_challenges', '>', $this->challengeLevel->completed_challenges)
            ->orderBy('completed_challenges', 'asc')
            ->take(1)
            ->pluck('completed_challenges');

        return ( ! $nextLevelNeeded)
            ? '&infin;'
            : $challengesForThisLevel.'/'.$nextLevelNeeded;
    }

    public function ownsDog($dog)
    {
        return ($this->id == $dog->owner_id);
    }

    public function hasBlocked($user)
    {
        return ( ! is_null($this->blocked()->where('id', $user->id)->first()));
    }

    public function canBeBlocked()
    {
        return ( ! $this->isAdministrator() and ! $this->isSuperUser());
    }

    public function showGifterLevel()
    {
        return $this->show_gifter_level;
    }

    public function next_gifter_level()
    {
        $user = $this->auth->get_user();

        $next_level = $user->gifter_level->next_level();

        if ( ! $next_level->loaded())
        {
            return FALSE;
        }

        $difference = $next_level->min - $user->gifts_given;

        return array(
            'title'             => $next_level->title, 
            'points_difference' => $difference, 
        );
    }

    public function isOnline()
    {
        if (is_null($this->last_action_at))
        {
            return false;
        }

        $onlineThreshold = Config::get('game.user.online_threshold');
        $logoutURI = ltrim(parse_url(URL::route('auth/logout'), PHP_URL_PATH), '/');
        
        return ($this->last_uri != $logoutURI and Carbon::now()->diffInMinutes($this->last_action_at, false) >= ($onlineThreshold * -1));
    }

    public function logCreditTransaction($type, $amount, $cost, $gross, array $info = null)
    {
        // Create the transaction
        return UserCreditTransaction::create(array(
            'user_id' => $this->id, 
            'type'    => $type, 
            'amount'  => $amount, 
            'cost'    => $cost, 
            'gross'   => $gross, 
            'info'    => $info, 
        ));
    }

    public function logCreditTransfer($receiverId, $amount)
    {
        // Create the transfer record
        return UserCreditTransfer::create(array(
            'sender_id'   => $this->id, 
            'receiver_id' => $receiverId, 
            'amount'      => $amount, 
        ));
    }

    public function gifted($numberOfGifts = 1)
    {
        $this->gifts_given += $numberOfGifts;

        // Check if they went up a level
        $currentGifterLevel = GifterLevel::where('min', '<=', $this->gifts_given)->orderBy('min', 'desc')->first();

        if ( ! is_null($currentGifterLevel) and $currentGifterLevel->id != $this->gifter_level_id)
        {
            // Went up a level
            $this->gifter_level_id = $currentGifterLevel->id;

            $leveledUp = true;
        }
        else
        {
            $leveledUp = false;
        }

        $this->save();

        return $leveledUp;
    }

    public static function notifyAll($body, $type = UserNotification::TYPE_INFO, $unread = true, $unseen = true, $persistent = false, $except = [])
    {
        // Get all users
        $users = empty($except)
            ? User::all()
            : User::whereNotIn('id', $except)->get();

        foreach($users as $user)
        {
            $user->notify($body, $type, $unread, $unseen, $persistent);
        }

        return $users->count();
    }

    public static function notifyOnly($only = [], $body, $type = UserNotification::TYPE_INFO, $unread = true, $unseen = true, $persistent = false, $except = [])
    {
        if (empty($only))
        {
            return 0;
        }

        // Get all users
        $users = empty($except)
            ? User::whereIn('id', $only)->get()
            : User::whereIn('id', $only)->whereNotIn('id', $except)->get();

        foreach($users as $user)
        {
            $user->notify($body, $type, $unread, $unseen, $persistent);
        }

        return $users->count();
    }

    public function notify($body, $type = UserNotification::TYPE_INFO, $unread = true, $unseen = true, $persistent = false)
    {
        $notification = $this->notifications()->save(new UserNotification(array(
            'body'       => $body, 
            'type'       => $type, 
            'unread'     => $unread, 
            'unseen'     => $unseen, 
            'persistent' => $persistent, 
        )));

        $notification->body = str_ireplace('<<read_querystring>>', $notification->querystring(), $notification->body);
        $notification->save();

        return $notification;
    }

    public function getNotifications()
    {
        $notifications = $this->notifications()
            ->whereUnseen()
            ->whereUnread()
            ->orderBy('persistent', 'desc')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Collect the ids
        $notificationIds = $notifications->lists('id');

        if ( ! empty($notificationIds))
        {
            DB::table('user_notifications')
                ->whereIn('id', $notificationIds)
                ->update(array(
                    'unseen' => false, 
                ));
        }

        return $notifications;
    }

    public function isInInbox($conversation)
    {
        return ( ! is_null($this->inbox()->where('id', $conversation->id)->first()));
    }

    public function agreedToCommunityGuidelines()
    {
        return $this->read_community_rules;
    }

    public function hasBreedersPrize()
    {
        return is_null($this->breeders_prize_until)
            ? false
            : $this->breeders_prize_until->isFuture();
    }

    public function getMaxIncompleteChallenges()
    {
        return $this->hasCompletedTutorialStage('visit-first-goals') 
            ? Config::get('game.challenge.max_rolled') 
            : 1;
    }

    public function canRerollChallenges()
    {
        return ($this->hasCompletedTutorialStage('visit-first-goals'));
    }

    public function hasUnclaimedCommunityChallengePrize()
    {
        return ($this->unclaimedCommunityChallengePrizes()->count() > 0);
    }

    public function canAddNewKennelGroup()
    {
        $startingTotal = Config::get('game.user.starting_kennel_groups');

        $totalKennelGroups = $this->kennelGroups()->whereNotCemetery()->count();

        if ($totalKennelGroups < $startingTotal)
        {
            return true;
        }

        if ( ! $this->isUpgraded())
        {
            return false;
        }

        $maxTotal = Config::get('game.user.max_kennel_groups');

        return ($totalKennelGroups < $maxTotal);
    }

    public function isBeginner()
    {
        return (Carbon::now()->diffInDays($this->created_at, false) >= -7);
    }

    public function breedDogsWithBeginnersLuck($dog, $bitch)
    {
        // Give the beginner a turn
        $this->turns += 1;
        $this->save();

        return $this->breedDogs($dog, $bitch, true);
    }

    public function breedDogs($dog, $bitch, $userBreedersLuck = false)
    {
        // Work the bitch
        $bitch->worked = true;
        $bitch->save();

        // The breeder is always the dam's owner
        $breeder = $bitch->owner;

        // Potentially guarantee the breeding
        $guarantee = ($userBreedersLuck or $breeder->isOnTutorialStage('first-heat'));

        // Get the ltiter's chance of being successful
        $litterChance = ( ! $guarantee)
            ? $bitch->calculateLitterChance($dog)
            : 100;

        // Create the litter
        $litter = Litter::create(array(
            'breeder_id' => $breeder->id, 
            'sire_id'    => $dog->id, 
            'dam_id'     => $bitch->id, 
            'litter_chance' => $litterChance, 
            'born' => false, 
        ));

        // Remove all stud requests the bitch has with this dog (if any)
        DB::table('stud_requests')->where('bitch_id', $bitch->id)->where('stud_id', $dog->id)->delete();

        // Remove all blrs
        DB::table('beginners_luck_requests')->where('bitch_id', $bitch->id)->delete();

        // If the bitch's owner does not also own the dog, notify the dog's owner
        if ( ! $breeder->ownsDog($bitch) and ! is_null($dog->owner))
        {
            $params = array(
                'breeder'    => $breeder->nameplate(), 
                'breederUrl' => URL::route('user/profile', $breeder->id), 
                'dog'        => $dog->nameplate(), 
                'dogUrl'     => URL::route('dog/profile', $dog->id), 
                'bitch'      => $bitch->nameplate(), 
                'bitchUrl'   => URL::route('dog/profile', $bitch->id), 
            );

            $body = Lang::get('notifications/user.breed_dogs.to_dog_owner', array_map('htmlentities', array_dot($params)));
            
            $dog->owner->notify($body, UserNotification::TYPE_SUCCESS);
        }

        // @TUTORIAL: complete first-heat
        $breeder->completeTutorialStage('first-heat', array('bred_bitch_id' => $bitch->id)); 

        return $litter;
    }

    public function hasKennelPrefix()
    {
        return (strlen($this->kennel_prefix) > 0);
    }

    public function hasRequestedBeginnersLuck()
    {
        return ($this->sentBeginnersLuckRequests->count() > 0);
    }

    public function removeSocialPresence()
    {
        // Delete forum posts
        $this->forumPosts()->delete();

        // Delete forum topics
        $this->forumTopics()->delete();

        // Delete chat messages
        $this->chatMessages()->delete();
        
        // Delete chat turns
        $this->chatTurns()->delete();
        
        // Delete conversations
        $this->conversations()->delete();
        
        // Delete conversation messages
        $this->conversationMessages()->delete();

        // Delete news poll answer votes
        $this->newsPollAnswerVotes()->delete();

        // Delete news post comments
        $this->newsPostComments()->delete();

        // Delete beginners luck requests
        $this->beginnersLuckRequests()->delete();
    }

    public function hasLoggedIn()
    {
        return ( ! is_null($this->last_login));
    }

    public function passwordResetRequired()
    {
        return $this->password_reset_required;
    }

}
