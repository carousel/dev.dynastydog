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
use AlphaCode;
use Exception;

class AlphaController extends AdminController {

    public function __construct()
    {
        parent::__construct();

        $this->sidebarGroups = array(
            array(
                'heading' => 'Codes', 
                'items' => array(
                    array(
                        'title' => 'New', 
                        'url' => URL::route('admin/alpha/code/create'), 
                    ), 
                    array(
                        'title' => 'Existing', 
                        'url' => URL::route('admin/alpha'), 
                    ), 
                ), 
            ),
        );
    }

    public function getIndex()
    {
        $results = new AlphaCode;

        if (Input::get('search'))
        {
            $code = Input::get('code');

            if (strlen($code) > 0)
            {
                $results = $results->where('code', 'LIKE', '%'.$code.'%');
            }
        }

        $alphaCodes = $results->orderBy('created_at', 'desc')->paginate();

        // Show the page
        return View::make('admin/alpha/index', compact('alphaCodes'));
    }

    public function getCreateAlphaCode()
    {
        // Show the page
        return View::make('admin/alpha/create_alpha_code');
    }

    public function getDeleteAlphaCode($alphaCode)
    {
        try
        {
            $alphaCode->delete();

            $success = Lang::get('forms/admin.delete_alpha_code.success');

            return Redirect::route('admin/alpha')->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.delete_alpha_code.error');
        }

        return Redirect::route('admin/alpha/code/edit', $alphaCode->code)->withInput()->with('error', $error);
    }

    public function getEditAlphaCode($alphaCode)
    {
        // Grab the users
        $users = $alphaCode->users()->orderBy('id', 'asc')->get();

        // Show the page
        return View::make('admin/alpha/edit_alpha_code', compact('alphaCode', 'users'));
    }

    public function postCreateAlphaCode()
    {
        // Declare the rules for the form validation
        $rules = array(
            'capacity' => 'required|integer|between:0,255',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/alpha/code/create')->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            // Generate a code
            $code = AlphaCode::generateCode();

            // Create the alpha code
            $alphaCode = AlphaCode::create(array(
                'code'       => $code, 
                'capacity'   => Input::get('capacity'), 
                'population' => 0, 
            ));

            $success = Lang::get('forms/admin.create_alpha_code.success');

            return Redirect::route('admin/alpha/code/edit', $code)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.create_alpha_code.error');
        }

        return Redirect::route('admin/alpha/code/create')->withInput()->with('error', $error);
    }

    public function postEditAlphaCode($alphaCode)
    {
        // Declare the rules for the form validation
        $rules = array(
            'capacity' => 'required|integer|between:0,255',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/alpha/code/edit', $alphaCode->code)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $alphaCode->capacity = Input::get('capacity');
            $alphaCode->save();
            $success = Lang::get('forms/admin.update_alpha_code.success');

            return Redirect::route('admin/alpha/code/edit', $alphaCode->code)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_alpha_code.error');
        }

        return Redirect::route('admin/alpha/code/edit', $alphaCode->code)->withInput()->with('error', $error);
    }

}
