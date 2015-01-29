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
use Dynasty;
use Str;
use User;
use UserNotification;
use KennelGroup;
use Contest;
use UserContestType;
use BannedIp;

use Exception;
use Dynast\Users\Exceptions as DynastyUsersExceptions;

class UsersController extends AdminController {

    public function __construct()
    {
        parent::__construct();

        $this->sidebarGroups = array(
            array(
                'heading' => 'Users', 
                'items' => array(
                    array(
                        'title' => 'Existing', 
                        'url' => URL::route('admin/users'), 
                    ), 
                    array(
                        'title' => 'Manage', 
                        'url' => URL::route('admin/users/manage'), 
                    ), 
                ), 
            ),
            array(
                'heading' => 'Contests', 
                'items' => array(
                    array(
                        'title' => 'Existing', 
                        'url' => URL::route('admin/users/contests'), 
                    ), 
                ), 
            ),
            array(
                'heading' => 'Contest Types', 
                'items' => array(
                    array(
                        'title' => 'Existing', 
                        'url' => URL::route('admin/users/contest/types'), 
                    ), 
                ), 
            ),
        );
    }

    public function getIndex()
    {
        $results = new User;

        if (Input::get('search'))
        {
            $id = Input::get('id');
            $displayName = Input::get('display_name');
            $status = Input::get('status', 'all');

            if (strlen($id) > 0)
            {
                $results = $results->where('id', $id);
            }

            if (strlen($displayName) > 0)
            {
                $results = $results->where('display_name', 'LIKE', '%'.$displayName.'%');
            }

            if ($status === 'trashed')
            {
                $results = $results->onlyTrashed();
            }
            else if ($status === 'all')
            {
                $results = $results->withTrashed();
            }
        }
        else
        {
            $results = $results->withTrashed();
        }

        $users = $results->orderBy('id', 'asc')->paginate();

        // Show the page
        return View::make('admin/users/index', compact('users'));
    }

    public function getManageUsers()
    {
        $bannedIps = BannedIp::orderBy('created_at', 'desc')->get();

        // Show the page
        return View::make('admin/users/manage_users', compact('bannedIps'));
    }

    public function getContests()
    {
        $results = new Contest;

        if (Input::get('search'))
        {
            $id = Input::get('id');
            $name = Input::get('name');

            if (strlen($id) > 0)
            {
                $results = $results->where('id', $id);
            }

            if (strlen($name) > 0)
            {
                $results = $results->where('name', 'LIKE', '%'.$name.'%');
            }
        }

        $contests = $results->orderBy('name', 'asc')->paginate();

        // Show the page
        return View::make('admin/users/contests', compact('contests'));
    }

    public function getContestTypes()
    {
        $results = new UserContestType;

        if (Input::get('search'))
        {
            $id = Input::get('id');
            $name = Input::get('name');

            if (strlen($id) > 0)
            {
                $results = $results->where('id', $id);
            }

            if (strlen($name) > 0)
            {
                $results = $results->where('name', 'LIKE', '%'.$name.'%');
            }
        }

        $contestTypes = $results->orderBy('name', 'asc')->paginate();

        // Show the page
        return View::make('admin/users/contest_types', compact('contestTypes'));
    }

    public function getEditUser($user)
    {
        $kennelGroups = $user->kennelGroups()->orderBy('id', 'asc')->get();

        // Show the page
        return View::make('admin/users/edit_user', compact('user', 'kennelGroups'));
    }

    public function getEditContest($contest)
    {
        // Show the page
        return View::make('admin/users/edit_contest', compact('contest'));
    }

    public function getEditContestType($contestType)
    {
        // Show the page
        return View::make('admin/users/edit_contest_type', compact('contestType'));
    }

    public function getDeleteUser($user)
    {
        try
        {
            if ($user->id == $this->currentUser->id)
            {
                throw new DynastyUsersExceptions\CannotDeleteSelfException;
            }

            $user->delete();

            $success = Lang::get('forms/admin.delete_user.success');

            return Redirect::route('admin/users')->withInput()->with('success', $success);
        }
        catch(DynastyUsersExceptions\CannotDeleteSelfException $e)
        {
            $error = Lang::get('forms/admin.delete_user.cannot_delete_self');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.delete_user.error');
        }

        return Redirect::route('admin/users/user/edit', $user->id)->withInput()->with('error', $error);
    }

    public function getPermanentlyDeleteUser($userId)
    {
        try
        {
            $user = User::where('id', $userId)->withTrashed()->first();

            if (is_null($user))
            {
                App::abort(404, 'User does not exist!');
            }

            if ($user->id == $this->currentUser->id)
            {
                throw new DynastyUsersExceptions\CannotDeleteSelfException;
            }

            $user->forceDelete();

            $success = Lang::get('forms/admin.permanently_delete_user.success');

            return Redirect::route('admin/users')->withInput()->with('success', $success);
        }
        catch(DynastyUsersExceptions\CannotDeleteSelfException $e)
        {
            $error = Lang::get('forms/admin.delete_user.cannot_delete_self');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.permanently_delete_user.error');
        }

        return Redirect::route('admin/users')->withInput()->with('error', $error);
    }

    public function getRestoreUser($userId)
    {
        try
        {
            $user = User::where('id', $userId)->withTrashed()->first();

            if (is_null($user))
            {
                App::abort(404, 'User does not exist!');
            }

            $user->restore();

            $success = Lang::get('forms/admin.restore_user.success');

            return Redirect::route('admin/users/user/edit', $user->id)->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.restore_user.error');
        }

        return Redirect::route('admin/users')->withInput()->with('error', $error);
    }

    public function postEditUser($user)
    {
        // Declare the rules for the form validation
        $rules = array(
            'display_name'       => 'required|max:50', 
            'avatar'             => 'max:255|image_url:png,gif,jpeg|image_url_size:<=150',
            'kennel_name'        => 'required|max:50', 
            'kennel_prefix'      => 'max:5|unique:users,kennel_prefix,'.$user->id,
            'kennel_description' => 'max:10000', 
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/users/user/edit', $user->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $user->display_name       = Input::get('display_name');
            $user->avatar             = Input::get('avatar');
            $user->kennel_name        = Input::get('kennel_name');
            $user->kennel_prefix      = Input::get('kennel_prefix');
            $user->kennel_description = Input::get('kennel_description');
            $user->save();

            $success = Lang::get('forms/admin.update_user.success');

            return Redirect::route('admin/users/user/edit', $user->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_user.error');
        }

        return Redirect::route('admin/users/user/edit', $user->id)->withInput()->with('error', $error);
    }

    public function postEditContest($contest)
    {
        // Declare the rules for the form validation
        $rules = array(
            'name'      => 'required|max:32',
            'type_name' => 'required|max:32',
            'type_description' => 'max:255',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/users/contest/edit', $contest->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $contest->name      = Input::get('name');
            $contest->type_name = Input::get('type_name');
            $contest->type_description = Input::get('type_description');
            $contest->save();

            $success = Lang::get('forms/admin.update_contest.success');

            return Redirect::route('admin/users/contest/edit', $contest->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_contest.error');
        }

        return Redirect::route('admin/users/contest/edit', $contest->id)->withInput()->with('error', $error);
    }

    public function postEditContestType($contestType)
    {
        // Declare the rules for the form validation
        $rules = array(
            'name'        => 'required|max:32',
            'description' => 'max:255',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/users/contest/type/edit', $contestType->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $contestType->name        = Input::get('name');
            $contestType->description = Input::get('description');
            $contestType->save();

            $success = Lang::get('forms/admin.update_contest_type.success');

            return Redirect::route('admin/users/contest/type/edit', $contestType->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_contest_type.error');
        }

        return Redirect::route('admin/users/contest/type/edit', $contestType->id)->withInput()->with('error', $error);
    }

    public function postBanUser($user)
    {
        try
        {
            if ($user->id == $this->currentUser->id)
            {
                throw new DynastyUsersExceptions\CannotBanSelfException;
            }

            // Declare the rules for the form validation
            $rules = array(
                'ban_until'  => 'required|after:'.Carbon::now()->toDateTimeString(),
                'ban_reason' => 'required|max:255',
            );

            // Create a new validator instance from our validation rules
            $validator = Validator::make(Input::all(), $rules);

            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::route('admin/users/user/edit', $user->id)->withInput()->with('error', $validator->errors()->first());
            }

            DB::transaction(function() use ($user)
            {
                if (Input::get('chat_ban') === 'yes')
                {
                    $user->chat_banned_until = Carbon::parse(Input::get('ban_until'))->toDateTimeString();
                    $user->chat_ban_reason   = Input::get('ban_reason');
                }
                else
                {
                    $user->banned_until = Carbon::parse(Input::get('ban_until'))->toDateTimeString();
                    $user->ban_reason   = Input::get('ban_reason');
                }

                $user->save();

                // Remove all social aspects
                if (Input::get('unsocialize') === 'yes')
                {
                    $user->removeSocialPresence();
                }
            });

            $success = Lang::get('forms/admin.ban_user.success');

            return Redirect::route('admin/users/user/edit', $user->id)->with('success', $success);
        }
        catch(DynastyUsersExceptions\CannotBanSelfException $e)
        {
            $error = Lang::get('forms/admin.ban_user.cannot_ban_self');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.ban_user.error');
        }

        return Redirect::route('admin/users/user/edit', $user->id)->withInput()->with('error', $error);
    }

    public function getUnbanUser($user)
    {
        try
        {
            if ($user->id == $this->currentUser->id)
            {
                throw new DynastyUsersExceptions\CannotUnbanSelfException;
            }

            $user->banned_until = null;
            $user->ban_reason = '';
            $user->save();

            $success = Lang::get('forms/admin.unban_user.success');

            return Redirect::route('admin/users/user/edit', $user->id)->with('success', $success);
        }
        catch(DynastyUsersExceptions\CannotUnbanSelfException $e)
        {
            $error = Lang::get('forms/admin.unban_user.cannot_unban_self');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.unban_user.error');
        }
    }

    public function getUnbanChatUser($user)
    {
        try
        {
            if ($user->id == $this->currentUser->id)
            {
                throw new DynastyUsersExceptions\CannotUnbanSelfException;
            }

            $user->chat_banned_until = null;
            $user->chat_ban_reason = '';
            $user->save();

            $success = Lang::get('forms/admin.unban_chat_user.success');

            return Redirect::route('admin/users/user/edit', $user->id)->with('success', $success);
        }
        catch(DynastyUsersExceptions\CannotUnbanSelfException $e)
        {
            $error = Lang::get('forms/admin.unban_chat_user.cannot_unban_self');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.unban_chat_user.error');
        }
    }

    public function postUpdateKennelGroup($kennelGroup)
    {
        // Grab the user
        $user = $kennelGroup->user;

        // Declare the rules for the form validation
        $rules = array(
            'description' => 'max:10000',
            'dog_order'   => 'required|in:'.implode(',', array_keys(KennelGroup::getDogOrders())),
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        $validator->sometimes('name', 'required|max:32', function($input) use ($kennelGroup)
        {
            return $kennelGroup->canBeEdited();
        });

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/users/user/edit', $user->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            // Update the kennel group
            if ($kennelGroup->canBeEdited())
            {
                $kennelGroup->name = Input::get('name');
                $kennelGroup->description = Input::get('description');
            }

            $kennelGroup->dog_order_id = Input::get('dog_order');
            $kennelGroup->save();

            $success = Lang::get('forms/admin.update_kennel_group.success');

            return Redirect::route('admin/users/user/edit', $user->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_kennel_group.error');
        }

        return Redirect::route('admin/users/user/edit', $user->id)->withInput()->with('error', $error);
    }

    public function postFindUser()
    {
        // Declare the rules for the form validation
        $rules = array(
            'user' => 'required|exists:users,id', 
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/users/manage')->withInput()->with('error', $validator->errors()->first());
        }

        return Redirect::route('admin/users/user/edit', Input::get('user'));
    }

    public function postGiveCurrency()
    {
        // Declare the rules for the form validation
        $rules = array(
            'credits' => 'numeric|min:0|required_without:turns',
            'turns'   => 'numeric|min:0|required_without:credits',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        $validator->sometimes('credits', 'numeric|min:1|required_without:turns', function($input)
        {
            return ( ! $input->turns);
        });

        $validator->sometimes('turns', 'numeric|min:1|required_without:credits', function($input)
        {
            return ( ! $input->credits);
        });

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/users/manage')->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $totalUsersNotified = 0;

            $active = (Input::get('give_currency') === 'active');

            $creditsGiven = (int) Input::get('credits');
            $turnsGiven   = (int) Input::get('turns');

            $giftedCurrencies = [];

            if ($creditsGiven)
            {
                $giftedCurrencies[] = Dynasty::credits($creditsGiven);
            }

            if ($turnsGiven)
            {
                $giftedCurrencies[] = Dynasty::turns($turnsGiven);
            }

            $allCurrencies = implode(' and ', $giftedCurrencies);


            DB::transaction(function() use (&$totalUsersNotified, $active, $creditsGiven, $turnsGiven, $allCurrencies)
            {
                $params = array(
                    'currencies' => $allCurrencies, 
                );

                $body = Lang::get('notifications/admin.give_currency.to_user', array_map('htmlentities', array_dot($params)));

                if ($active)
                {
                    $activeUserIds = User::whereActive()->lists('id');

                    // Always add -1
                    $activeUserIds[] = -1;

                    if ($creditsGiven)
                    {
                        DB::table('users')->whereIn('id', $activeUserIds)->increment('credits', $creditsGiven);
                    }

                    if ($turnsGiven)
                    {
                        DB::table('users')->whereIn('id', $activeUserIds)->increment('turns', $turnsGiven);
                    }

                    // Notify the active users
                    $totalUsersNotified = User::notifyOnly($activeUserIds, $body, UserNotification::TYPE_SUCCESS);
                }
                else
                {
                    if ($creditsGiven)
                    {
                        DB::table('users')->increment('credits', $creditsGiven);
                    }

                    if ($creditsGiven)
                    {
                        DB::table('users')->increment('turns', $turnsGiven);
                    }

                    // Notify all user
                    $totalUsersNotified = User::notifyAll($body, UserNotification::TYPE_SUCCESS);
                }
            });

            $params = array(
                'users'      => number_format($totalUsersNotified).($active ? ' active' : '').' '.Str::plural('user', $totalUsersNotified), 
                'currencies' => $allCurrencies, 
            );

            $success = Lang::get('forms/admin.give_currency.success', array_map('htmlentities', array_dot($params)));

            return Redirect::route('admin/users/manage')->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.give_currency.error');
        }

        return Redirect::route('admin/users/manage')->withInput()->with('error', $error);
    }

    public function postBanIp()
    {
        try
        {
            // Declare the rules for the form validation
            $rules = array(
                'ip'   => 'required_without:user',
                'user' => 'required_without:ip|exists:users,id',
            );

            // Create a new validator instance from our validation rules
            $validator = Validator::make(Input::all(), $rules);

            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::route('admin/users/manage')->withInput()->with('error', $validator->errors()->first());
            }

            $bannedIps = [];

            DB::transaction(function() use (&$bannedIps)
            {
                $ip     = Input::get('ip');
                $userId = Input::get('user');

                $existingIpBans = BannedIp::all();

                if ($ip)
                {
                    if ($ip == $this->currentUser->last_login_ip)
                    {
                        throw new DynastyUsersExceptions\CannotBanSelfException;
                    }

                    if ( ! $existingIpBans->contains($ip))
                    {
                        $bannedIps[] = $ip;
                    }
                }

                if ($userId)
                {
                    $user = User::find($userId);

                    $mostRecentIp = ( ! is_null($user->last_login_ip)) 
                        ? $user->last_login_ip 
                        : $user->created_ip;

                    if ($user->id == $this->currentUser->id or $mostRecentIp == $this->currentUser->last_login_ip)
                    {
                        throw new DynastyUsersExceptions\CannotBanSelfException;
                    }

                    if ( ! $existingIpBans->contains($mostRecentIp))
                    {
                        $bannedIps[] = $mostRecentIp;
                    }
                }

                if ( ! empty($bannedIps))
                {
                    $values = [];
                    $now = Carbon::now();

                    foreach($bannedIps as $ip)
                    {
                        $values[] = array(
                            'ip' => $ip, 
                            'created_at' => $now, 
                            'updated_at' => $now, 
                        );
                    }

                    DB::table('banned_ips')->insert($values);

                    // Get all users that need to be banned
                    $bannedUserIds = DB::table('users')
                        ->whereIn(DB::raw("IFNULL(users.last_login_ip, users.created_ip)"), $bannedIps)
                        ->lists('id');

                    // Always add -1
                    $bannedUserIds[] = -1;

                    // Ban all of them
                    DB::table('users')
                        ->whereIn('id', $bannedUserIds)
                        ->update(array(
                            'ip_banned' => true, 
                        ));

                    // Remove all social aspects
                    if (Input::get('unsocialize') === 'yes')
                    {
                        // Go through all of the banned users
                        $bannedUsers = User::whereIn('id', $bannedUserIds)->get();

                        foreach($bannedUsers as $bannedUser)
                        {
                            $bannedUser->removeSocialPresence();
                        }
                    }
                }
            });

            $params = array(
                'ips' => implode(', ', $bannedIps), 
            );

            $success = Lang::get('forms/admin.ban_ip.success', array_map('htmlentities', array_dot($params)));

            return Redirect::route('admin/users/manage')->with('success', $success);
        }
        catch(DynastyUsersExceptions\CannotBanSelfException $e)
        {
            $error = Lang::get('forms/admin.ban_ip.cannot_ban_self');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.ban_ip.error');
        }

        return Redirect::route('admin/users/manage')->withInput()->with('error', $error);
    }

    public function getUnbanIp($bannedIp)
    {
        try
        {
            DB::transaction(function() use ($bannedIp)
            {
                // Get all users that need to be unbanned
                DB::table('users')
                    ->where(DB::raw("IFNULL(last_login_ip, created_ip)"), $bannedIp->ip)
                    ->update(array(
                        'ip_banned' => false, 
                    ));

                // Remove the IP ban
                $bannedIp->delete();
            });

            $success = Lang::get('forms/admin.unban_ip.success');

            return Redirect::route('admin/users/manage')->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.unban_ip.error');
        }

        return Redirect::route('admin/users/manage')->withInput()->with('error', $error);
    }

}
