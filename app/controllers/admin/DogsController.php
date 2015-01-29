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
use Dog;
use DogNotification;
use KennelGroup;
use Contest;
use DogContestType;
use Phenotype;
use BannedIp;

use Exception;
use Dynast\Dogs\Exceptions as DynastyDogsExceptions;

class DogsController extends AdminController {

    public function __construct()
    {
        parent::__construct();

        $this->sidebarGroups = array(
            array(
                'heading' => 'Dogs', 
                'items' => array(
                    array(
                        'title' => 'Existing', 
                        'url' => URL::route('admin/dogs'), 
                    ), 
                    array(
                        'title' => 'Manage', 
                        'url' => URL::route('admin/dogs/manage'), 
                    ), 
                ), 
            ),
        );
    }

    public function getIndex()
    {
        $results = new Dog;

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

        $dogs = $results->orderBy('id', 'asc')->paginate();

        // Show the page
        return View::make('admin/dogs/index', compact('dogs'));
    }

    public function getManageDogs()
    {
        // Show the page
        return View::make('admin/dogs/manage_dogs');
    }

    public function getEditDog($dog)
    {
        // Show the page
        return View::make('admin/dogs/edit_dog', compact('dog'));
    }

    public function getDeleteDog($dog)
    {
        try
        {
            $dog->delete();

            $success = Lang::get('forms/admin.delete_dog.success');

            return Redirect::route('admin/dogs')->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.delete_dog.error');
        }

        return Redirect::route('admin/dogs/dog/edit', $dog->id)->withInput()->with('error', $error);
    }

    public function postEditDog($dog)
    {
        // Declare the rules for the form validation
        $rules = array(
            'name'          => 'required|max:32', 
            'kennel_prefix' => 'max:5',
            'image_url'     => 'max:255|image_url:png,gif,jpeg|image_url_size:<=700,<=500', 
            'notes'         => 'max:10000',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/dogs/dog/edit', $dog->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $dog->name          = Input::get('name');
            $dog->kennel_prefix = Input::get('kennel_prefix');
            $dog->image_url     = Input::get('image_url');
            $dog->notes         = Input::get('notes');
            $dog->save();

            $success = Lang::get('forms/admin.update_dog.success');

            return Redirect::route('admin/dogs/dog/edit', $dog->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_dog.error');
        }

        return Redirect::route('admin/dogs/dog/edit', $dog->id)->withInput()->with('error', $error);
    }

    public function postFindDog()
    {
        // Declare the rules for the form validation
        $rules = array(
            'dog' => 'required|exists:dogs,id', 
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/dogs/manage')->withInput()->with('error', $validator->errors()->first());
        }

        return Redirect::route('admin/dogs/dog/edit', Input::get('dog'));
    }

    public function postAgeDogs()
    {
        // Declare the rules for the form validation
        $rules = array(
            'months' => 'required|integer|between:1,99', 
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/dogs/manage')->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $months = Input::get('months');

            $params = array(
                'months' => number_format($months).' '.Str::plural('month', $months), 
            );

            // Age all of the dogs
            if (Input::get('age_dogs') === 'increase')
            {
                DB::table('dogs')->increment('age', $months);

                $success = Lang::get('forms/admin.age_dogs_increase.success', $params);
            }
            else
            {
                DB::table('dogs')->where('age', '>', 0)->decrement('age', $months);

                $success = Lang::get('forms/admin.age_dogs_decrease.success', $params);
            }

            return Redirect::route('admin/dogs/manage')->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.age_dogs.error');
        }

        return Redirect::route('admin/dogs/manage')->withInput()->with('error', $error);
    }

    public function getRecompleteDog($dog)
    {
        try
        {
            DB::transaction(function() use ($dog)
            {
                $dog->complete();
            });

            $success = Lang::get('forms/admin.recomplete_dog.success');

            return Redirect::route('admin/dogs/dog/edit', $dog->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.recomplete_dog.error');
        }

        return Redirect::route('admin/dogs/dog/edit', $dog->id)->withInput()->with('error', $error);
    }

    public function getRefreshPhenotypesForDog($dog)
    {
        try
        {
            DB::transaction(function() use ($dog)
            {
                // Grab the dog's genotype IDs
                $genotypeIds = $dog->genotypes()->lists('id');

                // Store phenotypes for later
                $phenotypeIds = [];

                // Get all the phenotypes
                $phenotypes = Phenotype::all();

                // Cycle through the phenotypes
                foreach($phenotypes as $phenotype)
                {
                    // Grab genotype ids
                    $required = DB::table('phenotypes_genotypes')
                        ->select('genotypes.locus_id', 'genotypes.id')
                        ->join('genotypes', 'genotypes.id', '=', 'phenotypes_genotypes.genotype_id')
                        ->join('loci', 'loci.id', '=', 'genotypes.locus_id')
                        ->where('phenotypes_genotypes.phenotype_id', $phenotype->id)
                        ->where('loci.active', true)
                        ->lists('locus_id', 'id');

                    // Check if the phenotype matched all required genotypes with the dog's genotypes
                    $requiredIds   = array_keys($required);
                    $matchesNeeded = count(array_unique($required));
                    $matches       = count(array_intersect($requiredIds, $genotypeIds));

                    if ($matches == $matchesNeeded)
                    {
                        // Give the dog the phenotype
                        $phenotypeIds[] = $phenotype->id;
                    }
                }

                // Give the dog the phenotypes
                $dog->phenotypes()->sync($phenotypeIds);

                // We need to attach the phenotypes to the dog characteristics
                $dogCharacteristics = $dog->characteristics;

                // Go through the dog's characteristics
                foreach($dogCharacteristics as $dogCharacteristic)
                {
                    // Grab the characteristic
                    $characteristic = $dogCharacteristic->characteristic;

                    // Pheotype IDs to attach
                    $dogCharacteristicPhenotypeIds = [];

                    // Check if the characteristic has loci
                    $locusIds = $characteristic->loci()->lists('id', 'id');

                    if ( ! empty($locusIds))
                    {
                        // Go through each of the dog's phenotype ids
                        foreach ($phenotypeIds as $phenotypeId)
                        {
                            // Grab genotype ids
                            $required = DB::table('phenotypes_genotypes')
                                ->select('genotypes.locus_id', 'genotypes.id')
                                ->join('genotypes', 'genotypes.id', '=', 'phenotypes_genotypes.genotype_id')
                                ->join('loci', 'loci.id', '=', 'genotypes.locus_id')
                                ->where('phenotypes_genotypes.phenotype_id', $phenotypeId)
                                ->where('loci.active', true)
                                ->lists('locus_id', 'id');

                            $requiredIds   = array_values(array_unique($required));
                            $matchesNeeded = count($requiredIds);
                            $matches       = count(array_intersect($requiredIds, $locusIds));

                            if ($matches == $matchesNeeded)
                            {
                                // Give the dog the phenotype
                                $dogCharacteristicPhenotypeIds[] = $phenotypeId;
                            }
                        }
                    }

                    // Attach the phenotypes to the dog characteristic
                    $dogCharacteristic->phenotypes()->sync($dogCharacteristicPhenotypeIds);
                }
            });

            $success = Lang::get('forms/admin.refresh_phenotypes_for_dog.success');

            return Redirect::route('admin/dogs/dog/edit', $dog->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.refresh_phenotypes_for_dog.error');
        }

        return Redirect::route('admin/dogs/dog/edit', $dog->id)->withInput()->with('error', $error);
    }

}
