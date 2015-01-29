<?php namespace Controllers\Admin;

use AdminController;
use View;
use DB;
use Carbon;
use Config;
use Input;
use URL;
use Validator;
use Lang;
use Redirect;
use CommunityChallenge;
use User;
use UserNotification;
use Exception;
use  Dynasty\CommunityChallenges\Exceptions as  DynastyCommunityChallengesExceptions;

class GoalsController extends AdminController {

    public function __construct()
    {
        parent::__construct();

        $this->sidebarGroups = array(
            array(
                'heading' => 'Community Challenges', 
                'items' => array(
                    array(
                        'title' => 'New', 
                        'url' => URL::route('admin/goals/community/challenge/create'), 
                    ), 
                    array(
                        'title' => 'Existing', 
                        'url' => URL::route('admin/goals'), 
                    ), 
                ), 
            ),
        );
    }

    public function getIndex()
    {
        $results = new CommunityChallenge;

        if (Input::get('search'))
        {
            $id = Input::get('id');

            if (strlen($id) > 0)
            {
                $results = $results->where('id', $id);
            }
        }

        $communityChallenges = $results->orderBy('id', 'desc')->paginate();

        // Show the page
        return View::make('admin/goals/index', compact('communityChallenges'));
    }

    public function getCreateCommunityChallenge()
    {
        // Show the page
        return View::make('admin/goals/create_community_challenge');
    }

    public function getEditCommunityChallenge($communityChallenge)
    {
        $communityChallengeCharacteristics = $communityChallenge->characteristics()->orderByCharacteristic()->get();

        // Show the page
        return View::make('admin/goals/edit_community_challenge', compact('communityChallenge', 'communityChallengeCharacteristics'));
    }

    public function postCreateCommunityChallenge()
    {
        // Declare the rules for the form validation
        $rules = array(
            'total_characteristics' => 'required|integer|between:1,99',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:'.Carbon::today()->toDateString('m/d/Y'),
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/goals/community/challenge/create')->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $startDate = Carbon::parse(Input::get('start_date'));
            $endDate   = Carbon::parse(Input::get('end_date'));

            if ($startDate->diffInDays($endDate) < 0)
            {
                throw new DynastyCommunityChallengesExceptions\InvalidDatesException;
            }

            $communityChallenge = null;

            DB::transaction(function() use (&$communityChallenge, $startDate, $endDate)
            {
                // Create the community challenge
                $communityChallenge = CommunityChallenge::create(array( 
                    'num_characteristics' => Input::get('total_characteristics'), 
                    'start_date' => $startDate, 
                    'end_date'   => $endDate, 
                    'healthy'    => (Input::get('healthy') === 'yes'), 
                ));

                $communityChallenge->rollCharacteristics();

                // Notify users
                $params = array(
                    'communityChallengeUrl' => URL::route('goals', ['tab' => 'community']), 
                    'startDate' => $communityChallenge->start_date->format('F j, Y'), 
                    'endDate'   => $communityChallenge->end_date->format('F j, Y').' 11:59 PM', 
                );

                $body = Lang::get('notifications/admin.created_community_challenge.to_user', array_map('htmlentities', $params));

                // Notify all users
                User::notifyAll($body, UserNotification::TYPE_INFO);
            });

            $success = Lang::get('forms/admin.create_community_challenge.success');

            return Redirect::route('admin/goals/community/challenge/edit', $communityChallenge->id)->with('success', $success);
        }
        catch(DynastyCommunityChallengesExceptions\InvalidDatesException $e)
        {
            $error = Lang::get('forms/admin.create_community_challenge.invalid_dates');
        }
        catch (Dynasty\Challenges\Exceptions\NotEnoughCharacteristicsToGenerateException $e)
        {
            $error = Lang::get('forms/admin.create_community_challenge.not_enough_characteristics_generated');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.create_community_challenge.error');
        }

        return Redirect::route('admin/goals/community/challenge/create')->withInput()->with('error', $error);
    }

    public function postEditCommunityChallenge($communityChallenge)
    {
        // Declare the rules for the form validation
        $rules = array(
            'end_date' => 'required|date|after:'.$communityChallenge->start_date->subDay()->toDateString('m/d/Y'),
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/goals/community/challenge/edit', $communityChallenge->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $endDate = Carbon::parse(Input::get('end_date'));

            if ($communityChallenge->start_date->diffInDays($endDate) < 0)
            {
                throw new DynastyCommunityChallengesExceptions\InvalidDatesException;
            }

            $communityChallenge->end_date = $endDate;
            $communityChallenge->save();

            $success = Lang::get('forms/admin.update_community_challenge.success');

            return Redirect::route('admin/goals/community/challenge/edit', $communityChallenge->id)->with('success', $success);
        }
        catch(DynastyCommunityChallengesExceptions\InvalidDatesException $e)
        {
            $error = Lang::get('forms/admin.update_community_challenge.invalid_dates');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_community_challenge.error');
        }


        return Redirect::route('admin/goals/community/challenge/edit', $communityChallenge->id)->withInput()->with('error', $error);
    }

}
