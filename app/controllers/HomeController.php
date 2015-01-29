<?php

class HomeController extends BaseController {

    /*
    |--------------------------------------------------------------------------
    | Default Home Controller
    |--------------------------------------------------------------------------
    |
    | You may wish to use controllers instead of, or in addition to, Closure
    | based routes. That's great! Here is an example controller method to
    | get you started. To route to this controller, just add the route:
    |
    |   Route::get('/', 'HomeController@getIndex');
    |
    */

    public function getIndex()
    {
        $breeds = Breed::whereImportable()->whereActive()->orderBy('name', 'asc')->get();
        $sexes  = Sex::orderBy('name', 'asc')->get();

        // Show the page
        return View::make('frontend.home.index', compact('breeds', 'sexes'));
    }

    public function getPrivacyPolicy()
    {
        // Show the page
        return View::make('frontend.home.privacy_policy');
    }

    public function getTermsOfService()
    {
        // Show the page
        return View::make('frontend.home.terms_of_service');
    }

    public function getStaff()
    {
        // Show the page
        return View::make('frontend.home.staff');
    }

    public function postStaff()
    {
        // Declare the rules for the form validation
        $rules = array(
            'from'  => 'required',
            'email' => 'required|email',
            'body'  => 'required',
            'recaptcha_response_field' => 'required|recaptcha',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('staff')->withInput()->withErrors($validator);
        }

        // Send the email
        Mail::queue('emails.blank', ['body' => nl2br(Input::get('body'))], function($m)
        {
            $sendTo = Config::get('mail.contact');

            $m->subject('Inquiry from '.Config::get('game.name'));
            $m->to($sendTo['address'], $sendTo['name']);
            $m->replyTo(Input::get('email'), Input::get('from'));
        });

        // Go back to the staff page
        return Redirect::route('staff')->with('success', Lang::get('forms/staff.contact.success'));
    }

    public function getCommunityGuidelines()
    {
        // Show the page
        return View::make('frontend.home.community_guidelines');
    }

}
