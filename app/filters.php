<?php

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

App::before(function($request)
{
	//
});


App::after(function($request, $response)
{
	//
});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. The "basic" filter easily
| integrates HTTP Basic authentication for quick, simple checking.
|
*/

Route::filter('auth', function()
{
    if ( ! Sentry::check())
    {
        if (Request::ajax())
        {
            return Response::make('Unauthorized', 401);
        }
        else
        {
            return App::abort(401, 'Login to access this page!');
        }
    }
});


Route::filter('auth.basic', function()
{
    return Auth::basic();
});

/*
|--------------------------------------------------------------------------
| Guest Filter
|--------------------------------------------------------------------------
|
| The "guest" filter is the counterpart of the authentication filters as
| it simply checks that the current user is not logged in. A redirect
| response will be issued if they are, which you may freely change.
|
*/

Route::filter('guest', function()
{
    if (Sentry::check()) return Redirect::route('user/kennel');
});

/*
|--------------------------------------------------------------------------
| Admin authentication filter.
|--------------------------------------------------------------------------
|
| This filter does the same as the 'auth' filter but it checks if the user
| has 'admin' privileges.
|
*/

Route::filter('admin-auth', function()
{
    if ( ! Sentry::check())
    {
        if (Request::ajax())
        {
            return Response::make('Unauthorized', 401);
        }
        else
        {
            return App::abort(401, 'Login to access this page!');
        }
    }

    // Check if the user has access to the admin page
    if ( ! Sentry::getUser()->hasAccess('admin'))
    {
        // Show the insufficient permissions page
        return App::abort(403);
    }
});

/*
|--------------------------------------------------------------------------
| CSRF Protection Filter
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/

Route::filter('csrf', function()
{
    $token = Request::ajax() ? Request::header('X-CSRF-Token') : Input::get('_token');
    
    if (Session::token() != $token)
    {
        throw new Illuminate\Session\TokenMismatchException;
    }
});


/*
|--------------------------------------------------------------------------
| AJAX Filter
|--------------------------------------------------------------------------
|
|
*/

Route::filter('ajax', function()
{
    if ( ! Request::ajax())
    {
        return Response::make('Unauthorized', 404);
    }
});
