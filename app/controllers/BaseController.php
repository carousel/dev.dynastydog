<?php

class BaseController extends Controller {

    protected $layout;

    protected $currentUser;
    protected $growlNotifications;
    protected $advancedTurn = false;
    protected $advancedTurnReport = array();
    
    protected $title = '';

    /**
     * Message bag.
     *
     * @var Illuminate\Support\MessageBag
     */
    protected $messageBag = null;

    /**
     * Permissions required
     *
     * @var array
     */
    protected $permissions = array();

    /**
     * Whitelisted auth routes.
     *
     * @var array
     */
    protected $csrf_whitelist = array();

    /**
     * Initializer.
     *
     * @return void
     */
    public function __construct()
    {
        if (Sentry::check())
        {
            $currentUser = Sentry::getUser();

            if ($currentUser->isBanned())
            {
                // Log the user out
                Sentry::logout();

                $params = array(
                    'expiry_date' => $currentUser->banned_until->format('F jS, Y g:i A'), 
                    'reason'      => $currentUser->ban_reason, 
                );

                $banMessage = Lang::get('auth/message.logout.banned', array_map('htmlentities', array_dot($params)));
                
                // Redirect to the home page
                return Redirect::route('home')->with('error', $banMessage);
            }

            if ($currentUser->isIpBanned())
            {
                // Log the user out
                Sentry::logout();

                $banMessage = Lang::get('auth/message.logout.ip_banned');
                
                // Redirect to the home page
                return Redirect::route('home')->with('error', $banMessage);
            }
        }

        // Apply the permissions auth filter
        if ( ! empty($this->permissions))
        {
            if ( ! Sentry::check())
            {
                // Store the current uri in the session
                Session::put('loginRedirect', Request::url());

                // Redirect to the login page
                return Redirect::route('auth/login');
            }

            $currentUser = Sentry::getUser();
            
            // Check if the user has access to this admin page
            if ( ! $currentUser->hasAnyAccess($this->permissions))
            {
                // Show the insufficient permissions page
                return App::abort(403, Lang::get('permissions.unauthorized_access'));
            }
        }

        // CSRF Protection
        $this->beforeFilter('csrf', array('on' => 'post', 'except' => $this->csrf_whitelist));

        //
        $this->messageBag = new Illuminate\Support\MessageBag;

        if (is_null($this->layout))
        {
            $this->layout = (Sentry::check())
                ? 'frontend/layouts/authorized'    // Signed in
                : 'frontend/layouts/unauthorized'; // Guest
        }

        $this->growlNotifications = new Illuminate\Support\Collection;

        if (Sentry::check())
        {
            $this->currentUser = Sentry::getUser();

            if ( ! Request::ajax())
            {
                // Log which page they're on
                $query = Request::getQueryString();

                $uri = $query ? Request::path().'?'.$query : Request::path();

                $this->currentUser->last_uri = $uri;
                $this->currentUser->last_action_at = Carbon::now();

                // Tell the world they did something by going to a page
                $this->currentUser->touch(); // Saves the model

                // Check if a notification should be read
                if ( ! is_null($notificationId = Input::get(UserNotification::$readParameter, null)))
                {
                    // Grab it
                    $notification = $this->currentUser->notifications()->whereUnread()->where('id', $notificationId)->first();

                    if ( ! is_null($notification))
                    {
                        // Mark it as read
                        $notification->unread = false;
                        $notification->save();
                    }
                }

                // Get all unseen notifications
                $this->growlNotifications = $this->currentUser->getNotifications();
            }
        }
    }

    /**
     * Setup the layout used by the controller.
     *
     * @return void
     */
    protected function setupLayout()
    {
        // We are not using controller layouts because we are dynamically 
        // setting the layouts via @extends($layout) in our blade views
        View::share(array(
            'layout'       => $this->layout, 
            'currentUser'  => $this->currentUser, 
            'growlNotifications' => $this->growlNotifications, 
        ));
    }

    protected function view($path, $data = [])
    {
        $this->layout->title = $this->title;

        $this->layout->content = View::make($path, $data);
    }

    protected function redirectTo($url, $statusCode = 302)
    {
        return Redirect::to($url, $statusCode);
    }

    protected function redirectAction($action, $data = [])
    {
        return Redirect::action($action, $data);
    }

    protected function redirectRoute($route, $data = [])
    {
        return Redirect::route($route, $data);
    }

    protected function redirectBack($data = [])
    {
        return Redirect::back()->withInput()->with($data);
    }

    protected function redirectIntended($default = null)
    {
        $intended = Session::get('auth.intended_redirect_url');

        if ($intended)
        {
            return $this->redirectTo($intended);
        }

        return Redirect::to($default);
    }

}
