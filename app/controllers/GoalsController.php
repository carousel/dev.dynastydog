<?php

class GoalsController extends AuthorizedController {

    public function getIndex()
    {
        // @TUTORIAL: complete first-advance-turn
        if ($this->currentUser->isOnTutorialStage('first-advance-turn'))
        {
            $currentStage = $this->currentUser->tutorialStages()->current()->first();

            // Check if the challenge has already been completed
            $challengeId = $currentStage->data['challenge_id'];

            // Find the challenge
            $challenge = Challenge::find($challengeId);

            if ($challenge->isComplete())
            {
                // Completed already
                $this->currentUser->advanceTutorial(array('skipped' => true), true, true);

                // So we're skipping to the next stage
                $this->currentUser->advanceTutorial();
            }
            else
            {
                $this->currentUser->advanceTutorial(array('skipped' => false));
            }
        }

        // Get the current user's kennel groups
        $kennelGroups = $this->currentUser->kennelGroups()->whereNotCemetery()
            ->whereHas('dogs', function($query)
                {
                    $query->whereComplete()->whereAlive();
                })
            ->with(array(
                'dogs' => function($query)
                {
                    $query->whereComplete()->whereAlive()->orderBy('id', 'asc');
                }))
            ->orderBy('id', 'asc')->get();

        // Individual Challenges
        $incompleteChallenges = $this->currentUser->challenges()->whereIncomplete()->with(array(
                'dog', 
                'level', 
                'characteristics' => function($query)
                {
                    $query->with('characteristic')->orderByCharacteristic('name', 'asc');
                }
            ))
            ->select('challenges.*', DB::raw("IF(completed_at IS NULL, 0, 1) as completed"))
            ->orderBy('completed', 'asc')
            ->orderBy('completed_at', 'desc')
            ->orderBy('id', 'asc')
            ->take(Config::get('game.challenge.max_rolled'))
            ->get();

        $completedChallenges  = $this->currentUser->challenges()->whereComplete()->with(array(
                'dog', 
                'level', 
                'characteristics' => function($query)
                {
                    $query->with('characteristic')->orderByCharacteristic('name', 'asc');
                }
            ))
            ->orderBy('completed_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $maxRolled = $this->currentUser->getMaxIncompleteChallenges();

        $displayedChallenges = [];

        // Show the incomplete challenges first
        foreach ($incompleteChallenges as $incompleteChallenge)
        {
            $displayedChallenges[] = $incompleteChallenge;
        }

        // Then show the completed challenges
        $totalIncompleteChallenges = $incompleteChallenges->count();

        if ($totalIncompleteChallenges < $maxRolled)
        {
            $diff    = $maxRolled - $totalIncompleteChallenges;
            $chosen = $completedChallenges->take(min($completedChallenges->count(), $diff));

            foreach ($chosen as $completedChallenge)
            {
                $displayedChallenges[] = $completedChallenge;
            }
        }

        $counter = 0;
        $totalEmptySlots = $maxRolled - count($displayedChallenges);

        // Community Challenges
        $communityChallenge = CommunityChallenge::with(array(
                'entries.dog', 
                'characteristics' => function($query)
                {
                    $query->with('characteristic')->orderByCharacteristic('name', 'asc');
                }
            ))
            ->whereOpen()
            ->orderBy('id', 'desc')
            ->first();

        // Personal Goals
        $incompletePersonalGoals = $this->currentUser->personalGoals()->whereIncomplete()->orderBy('id', 'asc')->get();
        $completedPersonalGoals  = $this->currentUser->personalGoals()->whereComplete()->orderBy('completed_at', 'desc')->orderBy('id', 'asc')->get();

        // Show the page
        return View::make('frontend/goals/index', compact(
            'displayedChallenges', 'completedChallenges', 'kennelGroups', 
            'maxRolled', 'counter', 'totalEmptySlots', 
            'communityChallenge', 
            'incompletePersonalGoals', 'completedPersonalGoals'
        ));
    }

    public function getCommunityChallengePrizes()
    {
        // Grab all of the user's unclaimed prizes
        $communityChallenges = $this->currentUser->unclaimedCommunityChallengePrizes()
            ->with(array(
                'entries' => function($query)
                {
                    $query->whereWinner()->with('dog')->orderBy('dog_id', 'asc');
                }
            ))
            ->orderBy('end_date', 'asc')
            ->get();

        // Get the credit prize
        $creditPrize = Config::get('game.community_challenge.credit_prize');

        // Show the page
        return View::make('frontend/goals/prizes', compact('communityChallenges', 'creditPrize'));
    }

    public function getRollChallenge()
    {
        try
        {
            // Make sure they aren't at their max
            $totalIncompleteChallenges = $this->currentUser->challenges()->whereIncomplete()->count();
            $maxRolled = $this->currentUser->getMaxIncompleteChallenges();

            if ($totalIncompleteChallenges >= $maxRolled)
            {
                throw new Dynasty\Challenges\Exceptions\TooManyRolledException;
            }

            DB::transaction(function()
            {
                $challenge = Challenge::rollForUser($this->currentUser);

                // @TUTORIAL: complete start-tutorial
                $this->currentUser->completeTutorialStage('start-tutorial', array(
                    'challenge_id' => $challenge->id, 
                ));                
            });

            $success = Lang::get('forms/user.roll_challenge.success');

            return Redirect::route('goals', ['tab' => 'individual'])->with('success', $success);
        }
        catch (Dynasty\Challenges\Exceptions\TooManyRolledException $e)
        {
            $error = Lang::get('forms/user.roll_challenge.too_many_incomplete');
        }
        catch (Dynasty\UserTutorials\Exceptions\CannotContinueException $e)
        {
            $error = Lang::get('forms/user.roll_challenge.cannot_continue_tutorial');
        }
        catch (Dynasty\Challenges\Exceptions\NoTestableCharacteristicsException $e)
        {
            $error = Lang::get('forms/user.roll_challenge.no_testable_characteristics_found');
        }
        catch (Dynasty\Challenges\Exceptions\NoTestableDogCharacteristicsException $e)
        {
            $error = Lang::get('forms/user.roll_challenge.no_testable_dog_characteristics_found');
        }
        catch (Dynasty\Challenges\Exceptions\NotEnoughCharacteristicsToGenerateException $e)
        {
            $error = Lang::get('forms/user.roll_challenge.not_enough_characteristics_generated');
        }
        catch (Exception $e)
        {
            $error = Lang::get('forms/user.roll_challenge.error');
        }

        return Redirect::route('goals', ['tab' => 'individual'])->with('error', $error);
    }

    public function postCompleteChallenge($challenge)
    {
        try
        {
            // Make sure the challenge belongs to this user
            if ($this->currentUser->id != $challenge->user_id or $challenge->isComplete())
            {
                App::abort('404', 'Challenge not found!');
            }

            // Declare the rules for the form validation
            $rules = array(
                'dog' => 'required|exists:dogs,id,owner_id,'.$this->currentUser->id,
            );

            // Create a new validator instance from our validation rules
            $validator = Validator::make(Input::all(), $rules);

            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::route('goals', ['tab' => 'individual'])->withInput()->with('error', $validator->errors()->first());
            }

            $dog = Dog::find(Input::get('dog'));

            // The dog must be alive
            if ( ! $dog->isAlive())
            {
                throw new Dynasty\Dogs\Exceptions\DeceasedException;
            }

            // The dog must be completed
            if ( ! $dog->isComplete())
            {
                throw new Dynasty\Dogs\Exceptions\IncompleteException;
            }

            // Check the dog
            if ( ! $challenge->checkDog($dog))
            {
                throw new Dynasty\Challenges\Exceptions\CharacteristicRequirementsUnmetException;
            }

            $newChallengeLevel = null;

            DB::transaction(function () use($challenge, $dog, &$newChallengeLevel)
            {
                // Get the prize credits
                $payout = $challenge->level->credit_prize;

                // Complete the challenge
                $challenge->dog_id = $dog->id;
                $challenge->credit_payout = $payout;
                $challenge->completed_at = Carbon::now();
                $challenge->save();

                // Give the prize the the user
                $this->currentUser->credits += $payout;

                // Track their win
                ++$this->currentUser->total_completed_challenges;

                // Check if they went up a level
                $challengeLevel = $this->currentUser->challengeLevel;

                if ($challengeLevel->challengesNeededUntilNextLevel($this->currentUser->total_completed_challenges) < 1)
                {
                    // Level them up
                    if ( ! is_null($nextLevel = $challengeLevel->getNextChallengeLevel()))
                    {
                        $this->currentUser->challenge_level_id = $nextLevel->id;

                        $newChallengeLevel = $nextLevel;
                    }
                }

                // Save the user
                $this->currentUser->save();

                // @TUTORIAL: complete visit-first-goals
                $this->currentUser->completeTutorialStage('visit-first-goals', array(
                    'credits' => $challenge->credit_payout, 
                ));
            });

            $success = Lang::get('forms/user.complete_challenge.success');

            return Redirect::route('goals', ['tab' => 'individual'])
                ->with('success', $success)
                ->with('challengeJustCompleted', $challenge)
                ->with('newChallengeLevel', $newChallengeLevel);
        }
        catch(Dynasty\Dogs\Exceptions\DeceasedException $e)
        {
            $error = Lang::get('forms/user.complete_challenge.invalid_dog');
        }
        catch(Dynasty\Dogs\Exceptions\IncompleteException $e)
        {
            $error = Lang::get('forms/user.complete_challenge.invalid_dog');
        }
        catch(Dynasty\Challenges\Exceptions\CharacteristicRequirementsUnmetException $e)
        {
            $error = Lang::get('forms/user.complete_challenge.characteristics_unmet');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.complete_challenge.error');
        }

        return Redirect::route('goals', ['tab' => 'individual'])->withInput()->with('error', $error);
    }

    public function getRerollChallenge($challenge)
    {
        try
        {
            // Make sure the challenge belongs to this user
            if ($this->currentUser->id != $challenge->user_id or $challenge->isComplete())
            {
                App::abort('404', 'Challenge not found!');
            }

            // Make sure the user can afford it
            $creditCostToReroll = Config::get('game.challenge.reroll_cost');

            if ( ! $this->currentUser->canAffordCredits($creditCostToReroll))
            {
                throw new Dynasty\Users\Exceptions\NotEnoughCreditsException;
            }

            if ( ! $challenge->canBeRerolled())
            {
                throw new Dynasty\Challenges\Exceptions\CannotBeRerolledException;
            }

            DB::transaction(function() use ($challenge, $creditCostToReroll)
            {
                // Take away the credits
                $this->currentUser->credits -= $creditCostToReroll;
                $this->currentUser->save();

                // Reroll the characteristics
                $challenge->rerollCharacteristics();

                // Log the transaction
                $this->currentUser->logCreditTransaction(UserCreditTransaction::CHALLENGE_REROLL, 1, $creditCostToReroll, $creditCostToReroll, array('level' => $challenge->level->name));
            });

            $success    = Lang::get('forms/user.reroll_challenge.success', array_dot([ 'credits' => Dynasty::credits($creditCostToReroll) ]));

            return Redirect::route('goals', ['tab' => 'individual'])->with('success', $success);
        }
        catch (Dynasty\Users\Exceptions\NotEnoughCreditsException $e)
        {
            $error = Lang::get('forms/user.reroll_challenge.not_enough_credits');
        }
        catch (Dynasty\Challenges\Exceptions\CannotBeRerolledException $e)
        {
            $error = Lang::get('forms/user.reroll_challenge.cannot_be_rerolled');
        }
        catch (Dynasty\UserTutorials\Exceptions\CannotContinueException $e)
        {
            $error = Lang::get('forms/user.reroll_challenge.cannot_continue_tutorial');
        }
        catch (Dynasty\Challenges\Exceptions\NoTestableCharacteristicsException $e)
        {
            $error = Lang::get('forms/user.reroll_challenge.no_testable_characteristics_found');
        }
        catch (Dynasty\Challenges\Exceptions\NoTestableDogCharacteristicsException $e)
        {
            $error = Lang::get('forms/user.reroll_challenge.no_testable_dog_characteristics_found');
        }
        catch (Dynasty\Challenges\Exceptions\NotEnoughCharacteristicsToGenerateException $e)
        {
            $error = Lang::get('forms/user.reroll_challenge.not_enough_characteristics_generated');
        }
        catch (Exception $e)
        {
            $error = Lang::get('forms/user.reroll_challenge.error');
        }

        return Redirect::route('goals', ['tab' => 'individual'])->withInput()->with('error', $error);
    }

    public function postEnterCommunityChallenge($communityChallenge)
    {
        try
        {
            // Make sure the challenge is accepting entries
            if ( ! $communityChallenge->isOpen())
            {
                App::abort('404', 'Challenge not found!');
            }

            // Declare the rules for the form validation
            $rules = array(
                'dog' => 'required|exists:dogs,id,owner_id,'.$this->currentUser->id,
            );

            // Create a new validator instance from our validation rules
            $validator = Validator::make(Input::all(), $rules);

            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::route('goals', ['tab' => 'community'])->withInput()->with('error', $validator->errors()->first());
            }

            $dog = Dog::find(Input::get('dog'));

            // Check if the dog is already entered
            if ($communityChallenge->dogHasBeenEntered($dog))
            {
                throw new Dynasty\CommunityChallenges\Exceptions\DogHasAlreadyBeenEnteredException;
            }

            // The dog must be alive
            if ( ! $dog->isAlive())
            {
                throw new Dynasty\Dogs\Exceptions\DeceasedException;
            }

            // The dog must be completed
            if ( ! $dog->isComplete())
            {
                throw new Dynasty\Dogs\Exceptions\IncompleteException;
            }

            // Check the dog
            if ( ! $communityChallenge->checkDog($dog))
            {
                throw new Dynasty\CommunityChallenges\Exceptions\RequirementsUnmetException;
            }

            $entry = CommunityChallengeEntry::create(array(
                'community_challenge_id' => $communityChallenge->id, 
                'dog_id' => $dog->id, 
                'winner' => false, 
            ));

            $success = Lang::get('forms/user.enter_dog_in_community_challenge.success');

            return Redirect::route('goals', ['tab' => 'community'])->with('success', $success);
        }
        catch(Dynasty\CommunityChallenges\Exceptions\DogHasAlreadyBeenEnteredException $e)
        {
            $error = Lang::get('forms/user.enter_dog_in_community_challenge.dog_already_entered');
        }
        catch(Dynasty\Dogs\Exceptions\DeceasedException $e)
        {
            $error = Lang::get('forms/user.enter_dog_in_community_challenge.invalid_dog');
        }
        catch(Dynasty\Dogs\Exceptions\IncompleteException $e)
        {
            $error = Lang::get('forms/user.enter_dog_in_community_challenge.invalid_dog');
        }
        catch(Dynasty\CommunityChallenges\Exceptions\RequirementsUnmetException $e)
        {
            $error = Lang::get('forms/user.enter_dog_in_community_challenge.requirements_unmet');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.enter_dog_in_community_challenge.error');
        }

        return Redirect::route('goals', ['tab' => 'community'])->withInput()->with('error', $error);
    }

    public function getClaimCommunityChallengeCreditPrize($communityChallenge)
    {
        // Make sure the user has an unclaimed prize for this community challenge
        if (is_null($this->currentUser->unclaimedCommunityChallengePrizes()->where('community_challenge_id', $communityChallenge->id)->first()))
        {
            App::abort('404', 'Community challenge not found!');
        }

        try
        {
            // Get the credit prize
            $creditPrize = Config::get('game.community_challenge.credit_prize');

            DB::transaction(function() use ($communityChallenge, $creditPrize)
            {
                // Give the user the credits
                $this->currentUser->credits += $creditPrize;
                $this->currentUser->save();

                // Log the payout
                $communityChallenge->credit_payout += $creditPrize;
                $communityChallenge->save();

                // Remove the unclaimed prize record
                $this->currentUser->unclaimedCommunityChallengePrizes()->detach($communityChallenge->id);
            });

            $params = array(
                'credits' => Dynasty::credits($creditPrize), 
            );

            $success = Lang::get('forms/user.claimed_community_challenge_credit_prize.success', $params);

            return Redirect::route('goals/community/prizes')->with('success', $success);
        }
        catch (Exception $e)
        {
            $error = Lang::get('forms/user.claimed_community_challenge_credit_prize.error');
        }

        return Redirect::route('goals/community/prizes')->withInput()->with('error', $error);
    }

    public function getClaimCommunityChallengeBreedersPrize($communityChallenge)
    {
        // Make sure the user has an unclaimed prize for this community challenge
        if (is_null($this->currentUser->unclaimedCommunityChallengePrizes()->where('community_challenge_id', $communityChallenge->id)->first()))
        {
            App::abort('404', 'Community challenge not found!');
        }

        try
        {
            // Get the breeder's prize duration in days
            $breedersPrizeDuration = Config::get('game.community_challenge.breeders_prize_duration');

            DB::transaction(function() use ($communityChallenge, $breedersPrizeDuration)
            {
                // Give the user the breeder's prize
                $this->currentUser->breeders_prize_until = is_null($this->currentUser->breeders_prize_until)
                    ? Carbon::now()->addDays($breedersPrizeDuration)
                    : $this->currentUser->breeders_prize_until->addDays($breedersPrizeDuration);
                $this->currentUser->save();

                // Log the payout
                $communityChallenge->breeders_prize_payout += 1;
                $communityChallenge->save();

                // Remove the unclaimed prize record
                $this->currentUser->unclaimedCommunityChallengePrizes()->detach($communityChallenge->id);
            });

            $params = array(
                'duration' => number_format($breedersPrizeDuration).' '.Str::plural('day', $breedersPrizeDuration), 
            );

            $success = Lang::get('forms/user.claimed_community_challenge_breeders_prize.success', $params);

            return Redirect::route('goals/community/prizes')->with('success', $success);
        }
        catch (Exception $e)
        {
            $error = Lang::get('forms/user.claimed_community_challenge_breeders_prize.error');
        }

        return Redirect::route('goals/community/prizes')->withInput()->with('error', $error);
    }

    public function postCreatePersonalGoal()
    {
        // Declare the rules for the form validation
        $rules = array(
            'new_personal_goal' => 'required|max:1024',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('goals', ['tab' => 'personal'])->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            // Create the goal
            $goal = UserGoal::create(array(
                'user_id' => $this->currentUser->id, 
                'body'    => Input::get('new_personal_goal'), 
            ));

            $success = Lang::get('forms/user.create_personal_goal.success');

            return Redirect::route('goals', ['tab' => 'personal'])->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.create_personal_goal.error');
        }

        return Redirect::route('goals', ['tab' => 'personal'])->withInput()->with('error', $error);
    }

    public function getDeletePersonalGoal($userGoal)
    {
        try
        {
            // Make sure the goal belongs to this user
            if ($this->currentUser->id != $userGoal->user_id)
            {
                App::abort('404', 'Personal goal not found!');
            }

            // Delete the goal
            $userGoal->delete();

            $success = Lang::get('forms/user.delete_personal_goal.success');

            return Redirect::route('goals', ['tab' => 'personal'])->with('success', $success);
        }
        catch (Exception $e)
        {
            $error = Lang::get('forms/user.delete_personal_goal.error');
        }

        return Redirect::route('goals', ['tab' => 'individual'])->withInput()->with('error', $error);
    }

    public function getCompletePersonalGoal($userGoal)
    {
        try
        {
            // Make sure the goal belongs to this user
            if ($this->currentUser->id != $userGoal->user_id)
            {
                App::abort('404', 'Personal goal not found!');
            }

            // Check if it has already been completed
            if ($userGoal->isComplete())
            {
                throw new Dynasty\UserGoals\Exception\AlreadyCompletedException;
            }

            // Complete the goal
            $userGoal->completed_at = Carbon::now();
            $userGoal->save();

            $success = Lang::get('forms/user.complete_personal_goal.success');

            return Redirect::route('goals', ['tab' => 'personal'])->with('success', $success);
        }
        catch (Dynasty\UserGoals\Exception\AlreadyCompletedException $e)
        {
            $error = Lang::get('forms/user.complete_personal_goal.already_completed');
        }

        catch (Exception $e)
        {
            $error = Lang::get('forms/user.complete_personal_goal.error');
        }

        return Redirect::route('goals', ['tab' => 'individual'])->withInput()->with('error', $error);
    }

    public function postUpdatePersonalGoal($userGoal)
    {
        // Make sure the goal belongs to this user
        if ($this->currentUser->id != $userGoal->user_id)
        {
            App::abort('404', 'Personal goal not found!');
        }

        // Declare the rules for the form validation
        $rules = array(
            'personal_goal_body' => 'required|max:1024',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('goals', ['tab' => 'personal'])->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            // Update the goal
            $userGoal->body = Input::get('personal_goal_body');
            $userGoal->save();

            $success = Lang::get('forms/user.update_personal_goal.success');

            return Redirect::route('goals', ['tab' => 'personal'])->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.update_personal_goal.error');
        }

        return Redirect::route('goals', ['tab' => 'personal'])->withInput()->with('error', $error);
    }

}
