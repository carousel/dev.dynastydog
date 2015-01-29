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
use Symptom;
use Characteristic;
use Exception;

class HealthController extends AdminController {

    public function __construct()
    {
        parent::__construct();

        $this->sidebarGroups = array(
            array(
                'heading' => 'Symptoms', 
                'items' => array(
                    array(
                        'title' => 'New', 
                        'url' => URL::route('admin/health/symptom/create'), 
                    ), 
                    array(
                        'title' => 'Existing', 
                        'url' => URL::route('admin/health'), 
                    ), 
                ), 
            ),
        );
    }

    public function getIndex()
    {
        $results = new Symptom;

        if (Input::get('search'))
        {
            $id   = Input::get('id');
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

        $symptoms = $results->orderBy('name', 'asc')->paginate();

        // Show the page
        return View::make('admin/health/index', compact('symptoms'));
    }

    public function getCreateSymptom()
    {
        // Show the page
        return View::make('admin/health/create_symptom');
    }

    public function getDeleteSymptom($symptom)
    {
        try
        {
            $symptom->delete();

            $success = Lang::get('forms/admin.delete_symptom.success');

            return Redirect::route('admin/health')->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.delete_symptom.error');
        }

        return Redirect::route('admin/health/symptom/edit', $symptom->id)->withInput()->with('error', $error);
    }

    public function getEditSymptom($symptom)
    {
        $characteristics = $symptom->characteristics()->orderBy('name', 'asc')->get();

        // Show the page
        return View::make('admin/health/edit_symptom', compact('symptom', 'characteristics'));
    }

    public function postCreateSymptom()
    {
        // Declare the rules for the form validation
        $rules = array(
            'name' => 'required|max:255',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/health/symptom/create')->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            // Create the symptom
            $symptom = Symptom::create(array( 
                'name'   => Input::get('name'), 
            ));

            $success = Lang::get('forms/admin.create_symptom.success');

            return Redirect::route('admin/health/symptom/edit', $symptom->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.create_symptom.error');
        }

        return Redirect::route('admin/health/symptom/create')->withInput()->with('error', $error);
    }

    public function postEditSymptom($symptom)
    {
        // Declare the rules for the form validation
        $rules = array(
            'name' => 'required|max:255',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/health/symptom/edit', $symptom->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $symptom->name = Input::get('name');
            $symptom->save();

            $success = Lang::get('forms/admin.update_symptom.success');

            return Redirect::route('admin/health/symptom/edit', $symptom->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_symptom.error');
        }

        return Redirect::route('admin/health/symptom/edit', $symptom->id)->withInput()->with('error', $error);
    }

    public function getRemoveSymptomFromCharacteristic($symptom, $characteristic)
    {
        try
        {
            // Get all the severities in this characteristic
            $characteristicSeverityIds = $characteristic->severities()->lists('id');

            // Always add -1
            $characteristicSeverityIds[] = -1;

            // Remove the symptom from the severities
            DB::table('characteristic_severity_symptoms')
                ->where('symptom_id', $symptom->id)
                ->whereIn('severity_id', $characteristicSeverityIds)
                ->delete();

            $success = Lang::get('forms/admin.remove_symptom_from_characteristic_severities.success');

            return Redirect::route('admin/health/symptom/edit', $symptom->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.remove_symptom_from_characteristic_severities.error');
        }

        return Redirect::route('admin/health/symptom/edit', $symptom->id)->withInput()->with('error', $error);
    }

}
