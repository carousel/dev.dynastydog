<?php namespace Controllers\Admin;

use AdminController;
use App;
use View;
use DB;
use Carbon;
use Str;
use Config;
use Input;
use URL;
use Validator;
use Lang;
use Redirect;
use File;
use Floats;
use Breed;
use BreedCharacteristic;
use BreedDraft;
use Characteristic;
use CharacteristicCategory;
use Locus;
use UserNotification;
use Genotype;

use Exception;
use FileException;
use Dynasty\BreedDrafts\Exceptions as DynastyBreedDraftsExceptions;
use Dynasty\Dogs\Exceptions as DynastyDogsExceptions;
use Dynasty\Users\Exceptions as DynastyUsersExceptions;
use Dynasty\DynastyBreedDraftCharacteristicsExceptions\Exceptions as DynastyBreedDraftCharacteristicsExceptions;

class BreedsController extends AdminController {

    public function __construct()
    {
        parent::__construct();

        $this->sidebarGroups = array(
            array(
                'heading' => 'Breeds', 
                'items' => array(
                    array(
                        'title' => 'New', 
                        'url' => URL::route('admin/breeds/breed/create'), 
                    ), 
                    array(
                        'title' => 'Existing', 
                        'url' => URL::route('admin/breeds'), 
                    ), 
                    array(
                        'title' => 'Drafts', 
                        'url' => URL::route('admin/breeds/breed/drafts'), 
                    ), 
                    array(
                        'title' => 'Manage', 
                        'url' => URL::route('admin/breeds/manage'), 
                    ), 
                ), 
            ),
        );
    }

    public function getIndex()
    {
        $results = new Breed;

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

        $breeds = $results->orderBy('name', 'asc')->paginate();

        // Show the page
        return View::make('admin/breeds/index', compact('breeds'));
    }

    public function getManageBreeds()
    {
        // Get all breeds
        $breeds = Breed::orderBy('name', 'asc')->get();

        // Get the loci
        $loci = Locus::with(array(
                    'genotypes' => function($query)
                        {
                            $query->orderByAlleles();
                        }
                ))
            ->has('genotypes')
            ->orderBy('name', 'asc')
            ->get();

        // Get the characteristics categories
        $characteristicCategories = CharacteristicCategory::with(array(
                'parent', 
                'characteristics' => function($query)
                    {
                        $query->orderBy('name', 'asc');
                    }
            ))
            ->has('characteristics')
            ->select('characteristic_categories.*')
            ->join('characteristic_categories as parent', 'parent.id', '=', 'characteristic_categories.parent_category_id')
            ->orderBy('parent.name', 'asc')
            ->orderBy('characteristic_categories.name', 'asc')
            ->get();

        // Show the page
        return View::make('admin/breeds/manage_breeds', compact(
            'breeds', 'loci', 'characteristicCategories'
        ));
    }

    public function getBreedDrafts()
    {
        $results = new BreedDraft;

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

        $breedDrafts = $results->wherePending()->orderBy('name', 'asc')->paginate();

        // Show the page
        return View::make('admin/breeds/breed_drafts', compact('breedDrafts'));
    }

    public function getCreateBreed()
    {
        // Show the page
        return View::make('admin/breeds/create_breed');
    }

    public function getEditBreed($breed)
    {
        // Grab all locus IDs from the breed's genotypes
        $locusIds = $breed->genotypes()->lists('locus_id');

        // Always add -1
        $locusIds[] = -1;

        // Grab the loci
        $loci = Locus::whereIn('id', $locusIds)->orderBy('name', 'asc')->get();

        // Grab all breed characteristics
        $breedCharacteristics = $breed->characteristics()->orderByCharacteristic()->get();

        // Get the breed characteristic characteristic IDs
        $usedCharacteristicsIds = $breedCharacteristics->lists('characteristic_id');

        // Always add -1
        $usedCharacteristicsIds[] = -1;

        // Get the characteristics categories
        $characteristicCategories = CharacteristicCategory::with(array(
                'parent', 
                'characteristics' => function($query) use ($usedCharacteristicsIds)
                    {
                        $query
                            ->whereNotIn('id', $usedCharacteristicsIds)
                            ->orderBy('name', 'asc');
                    }
            ))
            ->whereHas('characteristics', function($query) use ($usedCharacteristicsIds)
                {
                    $query->whereNotIn('id', $usedCharacteristicsIds);
                })
            ->select('characteristic_categories.*')
            ->join('characteristic_categories as parent', 'parent.id', '=', 'characteristic_categories.parent_category_id')
            ->orderBy('parent.name', 'asc')
            ->orderBy('characteristic_categories.name', 'asc')
            ->get();

        // Show the page
        return View::make('admin/breeds/edit_breed', compact(
            'loci', 'characteristicCategories', 'breedCharacteristics', 
            'breed'
        ));
    }

    public function getEditBreedDraft($breedDraft)
    {
        // Must be in pending state
        if ( ! $breedDraft->isPending())
        {
            App::abort(404, 'Breed draft not found!');
        }

        $breedDraftCharacteristics = $breedDraft->characteristics()->orderByCharacteristic()->get();

        // Show the page
        return View::make('admin/breeds/edit_breed_draft', compact('breedDraft', 'breedDraftCharacteristics'));
    }

    public function getDeleteBreed($breed)
    {
        try
        {
            $breed->delete();

            $success = Lang::get('forms/admin.delete_breed.success');

            return Redirect::route('admin/breeds')->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.delete_breed.error');
        }

        return Redirect::route('admin/breeds/breed/edit', $breed->id)->withInput()->with('error', $error);
    }

    public function getDeleteBreedCharacteristic($breedCharacteristic)
    {
        // Grab the breed
        $breed = $breedCharacteristic->breed;

        try
        {
            $breedCharacteristic->delete();

            $success = Lang::get('forms/admin.delete_breed_characteristic.success');

            return Redirect::route('admin/breeds/breed/edit', $breed->id)->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.delete_breed_characteristic.error');
        }

        return Redirect::route('admin/breeds/breed/edit', $breed->id)->withInput()->with('error', $error);
    }

    public function postCreateBreed()
    {
        // Declare the rules for the form validation
        $rules = array(
            'name'           => 'required|max:32|unique:breeds,name',
            'description'    => 'max:1000',
            'image_filename' => 'required|max:255',
            'user_id'        => 'exists:users,id',
            'dog_id'         => 'exists:dogs,id',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/breeds/breed/create')->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $breed = null;

            DB::transaction(function() use (&$breed)
            {
                // Create the breed
                $breed = Breed::create(array( 
                    'name'          => Input::get('name'), 
                    'description'   => Input::get('description'), 
                    'image_url'     => Input::get('image_filename'), 
                    'creator_id'    => Input::get('user_id'), 
                    'originator_id' => Input::get('dog_id'), 
                    'active'        => (Input::get('active') === 'yes'), 
                    'importable'    => (Input::get('importable') === 'yes'), 
                    'extinctable'   => (Input::get('extinctable') === 'yes'), 
                ));

                // Grab the genotype IDs
                $genotypeIds = DB::table('genotypes')->lists('id');

                // Give this breed all genotypes
                $breed->genotypes()->sync($genotypeIds);
            });

            $success = Lang::get('forms/admin.create_breed.success');

            return Redirect::route('admin/breeds/breed/edit', $breed->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.create_breed.error');
        }

        return Redirect::route('admin/breeds/breed/create')->withInput()->with('error', $error);
    }

    public function postEditBreed($breed)
    {
        // Declare the rules for the form validation
        $rules = array(
            'name'           => 'required|max:32|unique:breeds,name,'.$breed->id,
            'description'    => 'max:1000',
            'image_filename' => 'required|max:255',
            'user_id'        => 'exists:users,id',
            'dog_id'         => 'exists:dogs,id',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/breeds/breed/edit', $breed->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $breed->name          = Input::get('name');
            $breed->description   = Input::get('description');
            $breed->image_url     = Input::get('image_filename');
            $breed->creator_id    = Input::get('user_id');
            $breed->originator_id = Input::get('dog_id');
            $breed->active        = (Input::get('active') === 'yes');
            $breed->importable    = (Input::get('importable') === 'yes');
            $breed->extinctable   = (Input::get('extinctable') === 'yes');
            $breed->save();

            $success = Lang::get('forms/admin.update_breed.success');

            return Redirect::route('admin/breeds/breed/edit', $breed->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_breed.error');
        }

        return Redirect::route('admin/breeds/breed/edit', $breed->id)->withInput()->with('error', $error);
    }

    public function getApproveBreedDraft($breedDraft)
    {
        // Must be in pending state to approve
        if ( ! $breedDraft->isPending())
        {
            App::abort(404, 'Breed draft not found!');
        }

        try
        {
            // Declare the rules for the form validation
            $rules = array(
                'name' => 'required|max:32|unique:breeds,name', 
                'dog'  => 'required|exists:dogs,id',
                'description' => 'max:1000', 
                'health_disorders' => 'max:255', 
            );

            if ($breedDraft->isOfficial())
            {
                unset($rules['dog']);
            }
            else
            {
                unset($rules['health_disorders']);
            }

            $values = array(
                'name' => $breedDraft->name, 
                'dog'  => $breedDraft->dog_id, 
                'description' => $breedDraft->description, 
                'health_disorders' => $breedDraft->health_disorders, 
            );

            // Create a new validator instance from our validation rules
            $validator = Validator::make($values, $rules);

            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::route('admin/breeds/breed/draft/edit', $breedDraft->id)->with('error', $validator->errors()->first());
            }

            $breed = null;

            DB::transaction(function() use ($breedDraft, &$breed)
            {
                // Check on the dog
                $breedDraft->checkOriginator();

                // Check on the characteristics
                $breedDraft->checkCharacteristics();

                // Check on the dog and ancestor characteristics
                $breedDraft->checkOriginatorCharacteristics();

                // Set status to accepted
                $breedDraft->status_id = BreedDraft::STATUS_ACCEPTED;

                // Log when it was accepted
                $breedDraft->accepted_at = Carbon::now();

                // Clear any old rejection reasons
                $breedDraft->rejection_reasons = '';

                $breedDraft->save();

                // Create the actual breed
                $breed = Breed::create(array(
                    'name'          => $breedDraft->name, 
                    'description'   => $breedDraft->description, 
                    'image_url'     => Str::slug($breedDraft->name), 
                    'creator_id'    => $breedDraft->user_id, 
                    'originator_id' => $breedDraft->dog_id, 
                    'draft_id'      => $breedDraft->id, 
                    'active'        => false, 
                    'importable'    => false, 
                    'extinctable'   => ( ! $breedDraft->isOfficial()), 
                ));

                // Pool the genotypes together
                $pooledGenotypeIdsByLocusId = [];

                // Copy over the characteristics
                $breedDraftCharacteristics = $breedDraft->characteristics()->with('characteristic')->get();

                foreach($breedDraftCharacteristics as $breedDraftCharacteristic)
                {
                    $characteristic = $breedDraftCharacteristic->characteristic;

                    $breedCharacteristic = BreedCharacteristic::create(array(
                        'breed_id'          => $breed->id, 
                        'characteristic_id' => $characteristic->id, 
                        'active'            => true, 
                        'hide'              => false, 
                        'min_female_ranged_value' => $breedDraftCharacteristic->min_female_ranged_value, 
                        'max_female_ranged_value' => $breedDraftCharacteristic->max_female_ranged_value, 
                        'min_male_ranged_value'   => $breedDraftCharacteristic->min_male_ranged_value, 
                        'max_male_ranged_value'   => $breedDraftCharacteristic->max_male_ranged_value, 
                        'min_age_to_reveal_genotypes'  => $characteristic->min_age_to_reveal_genotypes, 
                        'max_age_to_reveal_genotypes'  => $characteristic->max_age_to_reveal_genotypes, 
                        'min_age_to_reveal_phenotypes' => $characteristic->min_age_to_reveal_phenotypes, 
                        'max_age_to_reveal_phenotypes' => $characteristic->max_age_to_reveal_phenotypes, 
                        'min_age_to_reveal_ranged_value' => $characteristic->min_age_to_reveal_ranged_value, 
                        'max_age_to_reveal_ranged_value' => $characteristic->max_age_to_reveal_ranged_value, 
                        'min_age_to_stop_growing' => $characteristic->min_age_to_stop_growing, 
                        'max_age_to_stop_growing' => $characteristic->max_age_to_stop_growing, 
                    ));

                    // Get all of the genotypes available in the draft's characteristic
                    $possibleGenotypeIdsByLocusId = $breedDraftCharacteristic->getPossibleGenotypeIdsByLocusId();

                    foreach($possibleGenotypeIdsByLocusId as $locusId => $genotypeIds)
                    {
                        $pooledGenotypeIdsByLocusId[$locusId] = array_key_exists($locusId, $pooledGenotypeIdsByLocusId)
                            ? array_merge($pooledGenotypeIdsByLocusId[$locusId], $genotypeIds)
                            : $genotypeIds;
                    }
                }

                // Get the genotype IDs from the characteristics
                $possibleGenotypeIds = array_flatten($pooledGenotypeIdsByLocusId);

                // Collect attachable genotypes
                $attachableGenotypes = [];

                // Get all possible genotypes
                $allGenotypes = Genotype::all();

                foreach($allGenotypes as $genotype)
                {
                    $attachableGenotypes[$genotype->id] = array(
                        'frequency' => in_array($genotype->id, $possibleGenotypeIds), 
                    );
                }

                // Attach them to the breed
                $breed->genotypes()->attach($attachableGenotypes);

                if ( ! $breedDraft->isOfficial() and ! is_null($breedDraft->dog))
                {
                    // Get the dog originator
                    $originator = $breedDraft->dog;

                    // Set the dog to this breed
                    $originator->breed_id = $breed->id;

                    // Set the dog as an active member
                    $originator->active_breed_member = true;

                    // Save the dog
                    $originator->save();
                }

                // Notify the creator
                $creator = $breedDraft->user;

                if ( ! is_null($creator))
                {
                    if ($breedDraft->isOfficial())
                    {
                        $params = array(
                            'manageBreedDraftsUrl' => URL::route('breed_registry/manage'), 
                            'breed' => $breed->name, 
                        );

                        $body = Lang::get('notifications/breed_registry.official_breed_draft_approved.to_user', array_map('htmlentities', array_dot($params)));
                    }
                    else
                    {
                        // Get the dog originator
                        $originator = $breedDraft->dog;

                        $activeExtinction = Config::get('game.breed.active_extinction');
                        $gracePeriod      = Config::get('game.breed.grace_period');

                        $params = array(
                            'manageBreedDraftsUrl' => URL::route('breed_registry/manage'), 
                            'breed'         => $breed->name, 
                            'originator'    => $originator->nameplate(), 
                            'originatorUrl' => URL::route('dog/profile', $originator->id), 
                            'active_dogs'   => number_format($activeExtinction).' '.Str::plural('dog', $activeExtinction), 
                            'grace_period'  => number_format($gracePeriod).' '.Str::plural('day', $gracePeriod), 
                        );

                        $body = Lang::get('notifications/breed_registry.breed_draft_approved.to_user', array_map('htmlentities', array_dot($params)));
                    }
                    
                    // Send notification to the creator        
                    $creator->notify($body, UserNotification::TYPE_SUCCESS);
                }

                if ($breedDraft->isOfficial())
                {
                    // Reject other pending official drafts by the same name
                    $duplicateBreedDrafts = BreedDraft::where('name', $breed->name)->andWherePending()->get();

                    foreach($duplicateBreedDrafts as $duplicateBreedDraft)
                    {
                        // Reject the draft
                        $duplicateBreedDraft->status_id = BreedDraft::STATUS_REJECTED;
                        $duplicateBreedDraft->rejection_reasons = Lang::get('forms/admin.approve_breed_draft.duplicate_rejected');

                        $duplicateBreedDraft->save();

                        // Get the owner of the duplicate
                        $duplicateUser = $duplicateBreedDraft->user;

                        // Notify the owner of the rejected draft
                        if ( ! is_null($duplicateUser))
                        {
                            $params = array(
                                'breedDraftUrl' => URL::route('breed_registry/draft/submitted', $duplicateBreedDraft->id), 
                                'breedDraft'    => $duplicateBreedDraft->name, 
                            );

                            $body = Lang::get('notifications/breed_registry.breed_draft_rejected.to_user', array_map('htmlentities', array_dot($params)));
                            
                            $duplicateUser->notify($body, UserNotification::TYPE_DANGER);
                        }
                    }
                }

                // After everything, move the image if it exists
                if ($breedDraft->hasImage())
                {
                    // Get the image
                    $oldPath = $breedDraft->getImagePath();

                    // Get the new image path
                    $newPath = $breed->getImagePath();

                    File::move($oldPath, $newPath);
                }
            });

            $success = Lang::get('forms/admin.approve_breed_draft.success');

            return Redirect::route('admin/breeds/breed/edit', $breed->id)->with('success', $success);
        }
        catch(DynastyBreedDraftsExceptions\MissingDogException $e)
        {
            $error = Lang::get('forms/admin.approve_breed_draft.missing_dog');
        }
        catch(DynastyUsersExceptions\DoesNotOwnDogException $e)
        {
            $error = Lang::get('forms/admin.approve_breed_draft.wrong_owner');
        }
        catch(DynastyDogsExceptions\DeceasedException $e)
        {
            $error = Lang::get('forms/admin.approve_breed_draft.deceased_dog');
        }
        catch(DynastyDogsExceptions\IncompleteException $e)
        {
            $error = Lang::get('forms/admin.approve_breed_draft.incomplete_dog');
        }
        catch(DynastyDogsExceptions\NotEnoughGenerationsException $e)
        {
            $error = Lang::get('forms/admin.approve_breed_draft.not_enough_generations');
        }
        catch(DynastyDogsExceptions\BreedOriginatorException $e)
        {
            $error = Lang::get('forms/admin.approve_breed_draft.dog_is_breed_originator');
        }
        catch(DynastyBreedDraftsExceptions\MissingCharacteristicException $e)
        {
            $error = Lang::get('forms/admin.approve_breed_draft.no_characteristics');
        }
        catch(DynastyBreedDraftCharacteristicsExceptions\FemaleRangedValueOutOfBoundsException $e)
        {
            $params = array(
                'characteristic' => $e->getMessage(), 
            );

            $error = Lang::get('forms/admin.approve_breed_draft.female_ranged_value_out_of_bounds', array_map('htmlentities', array_dot($params)));
        }
        catch(DynastyBreedDraftCharacteristicsExceptions\MaleRangedValueOutOfBoundsException $e)
        {
            $params = array(
                'characteristic' => $e->getMessage(), 
            );

            $error = Lang::get('forms/admin.approve_breed_draft.male_ranged_value_out_of_bounds', array_map('htmlentities', array_dot($params)));
        }
        catch(DynastyBreedDraftCharacteristicsExceptions\IncompleteException $e)
        {
            $params = array(
                'characteristic' => $e->getMessage(), 
            );

            $error = Lang::get('forms/admin.approve_breed_draft.incomplete_characteristic', array_map('htmlentities', array_dot($params)));
        }
        catch(DynastyBreedDraftCharacteristicsExceptions\GenotypesNotFoundInCharacteristicException $e)
        {
            $params = json_decode($e->getMessage(), true);
            $error  = Lang::get('forms/admin.approve_breed_draft.genotypes_not_found_in_characteristic', array_map('htmlentities', array_dot($params)));
        }
        catch(DynastyBreedDraftCharacteristicsExceptions\InternalConflictException $e)
        {
            $params = array(
                'characteristic' => $e->getMessage(), 
            );

            $error = Lang::get('forms/admin.approve_breed_draft.internally_conflicted_characteristic', array_map('htmlentities', array_dot($params)));
        }
        catch(DynastyBreedDraftCharacteristicsExceptions\PhenotypeNotFoundInCharacteristicException $e)
        {
            $params = $e->getMessage();
            $error  = Lang::get('forms/admin.approve_breed_draft.phenotype_not_found_in_characteristic', array_map('htmlentities', array_dot($params)));
        }
        catch(DynastyBreedDraftCharacteristicsExceptions\ExternalConflictException $e)
        {
            $params = array(
                'characteristic' => $e->getMessage(), 
            );

            $error = Lang::get('forms/admin.approve_breed_draft.externally_conflicted_characteristic', array_map('htmlentities', array_dot($params)));
        }
        catch(DynastyBreedDraftsExceptions\DogDoesNotMeetRequirementsException $e)
        {
            $params = array(
                'failedCharacteristics' => $e->getMessage(), 
            );

            $error = Lang::get('forms/admin.approve_breed_draft.requirements_unmet_by_dog', array_map('htmlentities', array_dot($params)));
        }
        catch(DynastyBreedDraftsExceptions\AncestorDoesNotMeetRequirementsException $e)
        {
            $params = array(
                'failedCharacteristics' => $e->getMessage(), 
            );

            $error = Lang::get('forms/admin.approve_breed_draft.requirements_unmet_by_ancestor', array_map('htmlentities', array_dot($params)));
        }
        catch(FileException $e)
        {
            $error = Lang::get('forms/admin.approve_breed_draft.could_not_save_image');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.approve_breed_draft.error');
        }

        return Redirect::route('admin/breeds/breed/draft/edit', $breedDraft->id)->with('error', $error);
    }

    public function postRejectBreedDraft($breedDraft)
    {
        // Must be in pending state to reject
        if ( ! $breedDraft->isPending())
        {
            App::abort(404, 'Breed draft not found!');
        }

        // Declare the rules for the form validation
        $rules = array(
            'rejection_reasons' => 'required|max:255',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/breeds/breed/draft/edit', $breedDraft->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            DB::transaction(function() use ($breedDraft)
            {
                // Reject the draft
                $breedDraft->status_id = BreedDraft::STATUS_REJECTED;
                $breedDraft->rejection_reasons = Input::get('rejection_reasons');

                $breedDraft->save();

                // Get the owner of the duplicate
                $user = $breedDraft->user;

                // Notify the owner of the rejected draft
                if ( ! is_null($user))
                {
                    $params = array(
                        'breedDraftUrl' => URL::route('breed_registry/draft/submitted', $breedDraft->id), 
                        'breedDraft'    => $breedDraft->name, 
                    );

                    $body = Lang::get('notifications/breed_registry.breed_draft_rejected.to_user', array_map('htmlentities', array_dot($params)));
                    
                    $user->notify($body, UserNotification::TYPE_DANGER);
                }
            });

            $success = Lang::get('forms/admin.reject_breed_draft.success');

            return Redirect::route('admin/breeds/breed/drafts')->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.reject_breed_draft.error');
        }

        return Redirect::route('admin/breeds/breed/draft/edit', $breedDraft->id)->with('error', $error);
    }

    public function postUpdateBreedGenotypes($breed)
    {
        try
        {
            DB::transaction(function() use ($breed)
            {
                // Get the genotype frequencies
                $frequencies = (array) Input::get('frequency');

                // Get all of the breed genotypes
                $breedGenotypes = $breed->genotypes;

                foreach($breedGenotypes as $genotype)
                {
                    $frequency = array_key_exists($genotype->id, $frequencies)
                        ? (int) $frequencies[$genotype->id]
                        : 0;

                    $breed->genotypes()->updateExistingPivot($genotype->id, ['frequency' => $frequency]);
                }
            });

            $success = Lang::get('forms/admin.update_breed_genotypes.success');

            return Redirect::route('admin/breeds/breed/edit', $breed->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_breed_genotypes.error');
        }

        return Redirect::route('admin/breeds/breed/edit', $breed->id)->withInput()->with('error', $error);
    }

    public function postCreateBreedCharacteristic($breed)
    {
        try
        {
            DB::transaction(function() use ($breed)
            {
                // Get the characteristic IDs
                $characteristicIds = (array) Input::get('characteristics');

                // Always add -1
                $characteristicIds[] = -1;

                // Get the characteristics
                $characteristics = Characteristic::whereIn('id', $characteristicIds)->get();

                $active = (Input::get('active_characteristic') === 'yes');
                $hide   = (Input::get('hide_characteristic') === 'yes');

                // false = do not remove characteristics that have already been attached and being readded
                $breed->addCharacteristics($characteristics, $active, $hide, false);
            });

            $success = Lang::get('forms/admin.create_breed_characteristic.success');

            return Redirect::route('admin/breeds/breed/edit', $breed->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.create_breed_characteristic.error');
        }

        return Redirect::route('admin/breeds/breed/edit', $breed->id)->withInput()->with('error', $error);
    }

    protected function postUpdateBreedCharacteristic($breedCharacteristic)
    {
        // Grab the breed
        $breed = $breedCharacteristic->breed;

        // Grab the characteristic
        $characteristic = $breedCharacteristic->characteristic;

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), []);
        
        $validator->sometimes([
            'minimum_age_to_reveal_phenotypes', 'maximum_age_to_reveal_phenotypes', 
        ], 'required|integer|min:'.$characteristic->min_age_to_reveal_phenotypes.'|max:'.$characteristic->max_age_to_reveal_phenotypes.'', function($input) use ($characteristic)
        {
            return ($characteristic->isGenetic() and $characteristic->phenotypesCanBeRevealed());
        });
        
        $validator->sometimes([
            'minimum_age_to_reveal_genotypes', 'maximum_age_to_reveal_genotypes', 
        ], 'required|integer|min:'.$characteristic->min_age_to_reveal_genotypes.'|max:'.$characteristic->max_age_to_reveal_genotypes.'', function($input) use ($characteristic)
        {
            return ($characteristic->isGenetic() and $characteristic->genotypesCanBeRevealed());
        });
        
        $validator->sometimes([
            'minimum_female_ranged_value', 'maximum_female_ranged_value', 
            'minimum_male_ranged_value', 'maximum_male_ranged_value', 
        ], 'required|numeric|min:'.$characteristic->min_ranged_value.'|max:'.$characteristic->max_ranged_value.'', function($input) use ($characteristic)
        {
            return $characteristic->isRanged();
        });
        
        $validator->sometimes([
            'minimum_age_to_reveal_ranged_value', 'maximum_age_to_reveal_ranged_value', 
        ], 'required|integer|min:'.$characteristic->min_age_to_reveal_ranged_value.'|max:'.$characteristic->max_age_to_reveal_ranged_value.'', function($input) use ($characteristic)
        {
            return ($characteristic->isRanged() and $characteristic->rangedValueCanBeRevealed());
        });
        
        $validator->sometimes([
            'minimum_age_to_stop_growing', 'maximum_age_to_stop_growing', 
        ], 'required|integer|min:'.$characteristic->min_age_to_stop_growing.'|max:'.$characteristic->max_age_to_stop_growing.'', function($input) use ($characteristic)
        {
            return ($characteristic->isRanged() and $characteristic->hasRangedGrowth());
        });

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/breeds/breed/edit', $breed->id)->withInput()->with('error', $validator->errors()->first());
        }

        // Grab the severity data
        $breedCharacteristicSeverityData = (array) Input::get('breed_characteristic_severity');

        // Grab the severity symptom data
        $breedCharacteristicSeveritySymptomData = (array) Input::get('breed_characteristic_severity_symptom');

        // Go through all of the severities
        foreach($breedCharacteristic->severities as $breedCharacteristicSeverity)
        {
            if (array_key_exists($breedCharacteristicSeverity->id, $breedCharacteristicSeverityData))
            {
                // Grab the characteristic severity
                $characteristicSeverity = $breedCharacteristicSeverity->characteristicSeverity;

                // Create a new validator instance from our validation rules
                $validator = Validator::make($breedCharacteristicSeverityData[$breedCharacteristicSeverity->id], []);
                
                $validator->sometimes([
                    'minimum_age_to_express', 'maximum_age_to_express', 
                ], 'required|integer|min:'.$characteristicSeverity->min_age_to_express.'|max:'.$characteristicSeverity->max_age_to_express.'', function($input) use ($characteristicSeverity)
                {
                    return $characteristicSeverity->canBeExpressed();
                });
                
                $validator->sometimes([
                    'minimum_age_to_reveal_value', 'minimum_age_to_reveal_value', 
                ], 'required|integer|min:'.$characteristicSeverity->min_age_to_reveal_value.'|max:'.$characteristicSeverity->max_age_to_reveal_value.'', function($input) use ($characteristicSeverity)
                {
                    return $characteristicSeverity->valueCanBeRevealed();
                });

                // If validation fails, we'll exit the operation now.
                if ($validator->fails())
                {
                    // Ooops.. something went wrong
                    return Redirect::route('admin/breeds/breed/edit', $breed->id)->withInput()->with('error', $validator->errors()->first());
                }

                // Go through the symptoms
                foreach($breedCharacteristicSeverity->symptoms as $breedCharacteristicSeveritySymptom)
                {
                    if (array_key_exists($breedCharacteristicSeveritySymptom->id, $breedCharacteristicSeveritySymptomData))
                    {
                        // Grab the characteristic severity symptom
                        $characteristicSeveritySymptom = $breedCharacteristicSeveritySymptom->characteristicSeveritySymptom;

                        // Declare the rules for the form validation
                        $rules = array(
                            'minimum_offset_age_to_express' => 'required|numeric|min:'.$characteristicSeveritySymptom->max_offset_age_to_express.'|max:'.$characteristicSeveritySymptom->max_offset_age_to_express,
                            'maximum_offset_age_to_express' => 'required|numeric|min:'.$characteristicSeveritySymptom->max_offset_age_to_express.'|max:'.$characteristicSeveritySymptom->max_offset_age_to_express,
                        );

                        // Create a new validator instance from our validation rules
                        $validator = Validator::make($breedCharacteristicSeveritySymptomData[$breedCharacteristicSeveritySymptom->id], $rules);
                        
                        // If validation fails, we'll exit the operation now.
                        if ($validator->fails())
                        {
                            // Ooops.. something went wrong
                            return Redirect::route('admin/breeds/breed/edit', $breed->id)->withInput()->with('error', $validator->errors()->first());
                        }
                    }
                }
            }
        }

        try
        {
            DB::transaction(function() use ($breedCharacteristic, $characteristic, $breedCharacteristicSeverityData, $breedCharacteristicSeveritySymptomData)
            {
                // Go through again and save all new data to the models
                $breedCharacteristic->active = (Input::get('existing_active_characteristic') === 'yes');
                $breedCharacteristic->hide   = (Input::get('existing_hide_characteristic') === 'yes');

                if ($characteristic->isGenetic() and $characteristic->phenotypesCanBeRevealed())
                {
                    $minAgeToRevealPhenotypes = Input::get('minimum_age_to_reveal_phenotypes');
                    $maxAgeToRevealPhenotypes = Input::get('maximum_age_to_reveal_phenotypes');

                    if (Floats::compare($minAgeToRevealPhenotypes, $maxAgeToRevealPhenotypes, '>'))
                    {
                        $temp = $minAgeToRevealPhenotypes;
                        $minAgeToRevealPhenotypes = $maxAgeToRevealPhenotypes;
                        $maxAgeToRevealPhenotypes = $temp;
                    }

                    $breedCharacteristic->min_age_to_reveal_phenotypes = $minAgeToRevealPhenotypes;
                    $breedCharacteristic->max_age_to_reveal_phenotypes = $maxAgeToRevealPhenotypes;
                }

                if ($characteristic->isGenetic() and $characteristic->genotypesCanBeRevealed())
                {
                    $minAgeToRevealGenotypes = Input::get('minimum_age_to_reveal_genotypes');
                    $maxAgeToRevealGenotypes = Input::get('maximum_age_to_reveal_genotypes');

                    if (Floats::compare($minAgeToRevealGenotypes, $maxAgeToRevealGenotypes, '>'))
                    {
                        $temp = $minAgeToRevealGenotypes;
                        $minAgeToRevealGenotypes = $maxAgeToRevealGenotypes;
                        $maxAgeToRevealGenotypes = $temp;
                    }

                    $breedCharacteristic->min_age_to_reveal_genotypes = $minAgeToRevealGenotypes;
                    $breedCharacteristic->max_age_to_reveal_genotypes = $maxAgeToRevealGenotypes;
                }

                if ($characteristic->isRanged())
                {
                    $minFemaleRangedValue = Input::get('minimum_female_ranged_value');
                    $maxFemaleRangedValue = Input::get('maximum_female_ranged_value');
                    $minMaleRangedValue = Input::get('minimum_male_ranged_value');
                    $maxMaleRangedValue = Input::get('maximum_male_ranged_value');

                    if (Floats::compare($minFemaleRangedValue, $maxFemaleRangedValue, '>'))
                    {
                        $temp = $minFemaleRangedValue;
                        $minFemaleRangedValue = $maxFemaleRangedValue;
                        $maxFemaleRangedValue = $temp;
                    }

                    if (Floats::compare($minMaleRangedValue, $maxMaleRangedValue, '>'))
                    {
                        $temp = $minMaleRangedValue;
                        $minMaleRangedValue = $maxMaleRangedValue;
                        $maxMaleRangedValue = $temp;
                    }

                    $breedCharacteristic->min_female_ranged_value = $minFemaleRangedValue;
                    $breedCharacteristic->max_female_ranged_value = $maxFemaleRangedValue;
                    $breedCharacteristic->min_male_ranged_value = $minMaleRangedValue;
                    $breedCharacteristic->max_male_ranged_value = $maxMaleRangedValue;
                }

                if ($characteristic->isRanged() and $characteristic->rangedValueCanBeRevealed())
                {
                    $minAgeToRevealRangedValue = Input::get('minimum_age_to_reveal_ranged_value');
                    $maxAgeToRevealRangedValue = Input::get('maximum_age_to_reveal_ranged_value');

                    if (Floats::compare($minAgeToRevealRangedValue, $maxAgeToRevealRangedValue, '>'))
                    {
                        $temp = $minAgeToRevealRangedValue;
                        $minAgeToRevealRangedValue = $maxAgeToRevealRangedValue;
                        $maxAgeToRevealRangedValue = $temp;
                    }

                    $breedCharacteristic->min_age_to_reveal_ranged_value = $minAgeToRevealRangedValue;
                    $breedCharacteristic->max_age_to_reveal_ranged_value = $maxAgeToRevealRangedValue;
                }

                if ($characteristic->isRanged() and $characteristic->hasRangedGrowth())
                {
                    $minAgeToStopGrowing = Input::get('minimum_age_to_stop_growing');
                    $maxAgeToStopGrowing = Input::get('maximum_age_to_stop_growing');

                    if (Floats::compare($minAgeToStopGrowing, $maxAgeToStopGrowing, '>'))
                    {
                        $temp = $minAgeToStopGrowing;
                        $minAgeToStopGrowing = $maxAgeToStopGrowing;
                        $maxAgeToStopGrowing = $temp;
                    }

                    $breedCharacteristic->min_age_to_stop_growing = $minAgeToStopGrowing;
                    $breedCharacteristic->max_age_to_stop_growing = $maxAgeToStopGrowing;
                }

                $breedCharacteristic->save();

                // Go through the severities
                foreach($breedCharacteristic->severities as $breedCharacteristicSeverity)
                {
                    if (array_key_exists($breedCharacteristicSeverity->id, $breedCharacteristicSeverityData))
                    {
                        $input = $breedCharacteristicSeverityData[$breedCharacteristicSeverity->id];

                        // Grab the characteristic severity
                        $characteristicSeverity = $breedCharacteristicSeverity->characteristicSeverity;

                        if ($characteristicSeverity->canBeExpressed())
                        {
                            $minAgeToExpress = $input['minimum_age_to_express'];
                            $maxAgeToExpress = $input['maximum_age_to_express'];

                            if (Floats::compare($minAgeToExpress, $maxAgeToExpress, '>'))
                            {
                                $temp = $minAgeToExpress;
                                $minAgeToExpress = $maxAgeToExpress;
                                $maxAgeToExpress = $temp;
                            }

                            $breedCharacteristicSeverity->min_age_to_express = $minAgeToExpress;
                            $breedCharacteristicSeverity->max_age_to_express = $maxAgeToExpress;
                        }

                        if ($characteristicSeverity->valueCanBeRevealed())
                        {
                            $minAgeToRevealValue = $input['minimum_age_to_reveal_value'];
                            $maxAgeToRevealValue = $input['maximum_age_to_reveal_value'];

                            if (Floats::compare($minAgeToRevealValue, $maxAgeToRevealValue, '>'))
                            {
                                $temp = $minAgeToRevealValue;
                                $minAgeToRevealValue = $maxAgeToRevealValue;
                                $maxAgeToRevealValue = $temp;
                            }

                            $breedCharacteristicSeverity->min_age_to_reveal_value = $minAgeToRevealValue;
                            $breedCharacteristicSeverity->max_age_to_reveal_value = $maxAgeToRevealValue;
                        }

                        $breedCharacteristicSeverity->save();

                        // Go through the symptoms
                        foreach($breedCharacteristicSeverity->symptoms as $breedCharacteristicSeveritySymptom)
                        {
                            if (array_key_exists($breedCharacteristicSeveritySymptom->id, $breedCharacteristicSeveritySymptomData))
                            {
                                $input = $breedCharacteristicSeveritySymptomData[$breedCharacteristicSeveritySymptom->id];

                                $minOffsetAgeToExpress = $input['minimum_offset_age_to_express'];
                                $maxOffsetAgeToExpress = $input['maximum_offset_age_to_express'];

                                if (Floats::compare($minOffsetAgeToExpress, $maxOffsetAgeToExpress, '>'))
                                {
                                    $temp = $minOffsetAgeToExpress;
                                    $minOffsetAgeToExpress = $maxOffsetAgeToExpress;
                                    $maxOffsetAgeToExpress = $temp;
                                }

                                $breedCharacteristicSeveritySymptom->min_offset_age_to_express = $minOffsetAgeToExpress;
                                $breedCharacteristicSeveritySymptom->max_offset_age_to_express = $maxOffsetAgeToExpress;

                                $breedCharacteristicSeveritySymptom->save();
                            }
                        }
                    }
                }
            });

            $success = Lang::get('forms/admin.update_breed_characteristic.success');

            return Redirect::route('admin/breeds/breed/edit', $breed->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_breed_characteristic.error');
        }

        return Redirect::route('admin/breeds/breed/edit', $breed->id)->withInput()->with('error', $error);
    }

    public function getCloneBreed($breed)
    {
        try
        {
            $clonedBreed = null;

            DB::transaction(function() use ($breed, &$clonedBreed)
            {
                $clonedBreed = $breed->replicate();

                /*// Check to see if this clone exists
                $totalClones = Breed::where('name', 'LIKE', $clonedBreed->name.'%')->count();

                $clonedBreed->name = ($totalClones > 0)
                    ? Str::limit($clonedBreed->name.' ('.$totalClones.')', 32)
                    : $clonedBreed->name;*/

                $name   = Str::quickRandom(32);

                do
                {
                    $unique = (Breed::where('name', $name)->count() <= 0);
                }
                while ( ! $unique);

                $clonedBreed->name      = $name;
                $clonedBreed->active    = false;
                $clonedBreed->draft_id  = null;

                $clonedBreed->save();

                // Grab the breed's genotypes
                $breedGenotypes = $breed->genotypes;

                $genotypesToAdd = [];

                foreach($breedGenotypes as $genotype)
                {
                    $genotypesToAdd[] = array(
                        'breed_id'    => $clonedBreed->id, 
                        'genotype_id' => $genotype->id, 
                        'frequency'   => $genotype->pivot->frequency, 
                    );
                }

                // Add the genotypes to the clone
                if ( ! empty($genotypesToAdd))
                {
                    DB::table('breed_genotypes')->insert($genotypesToAdd);
                }

                // Go through the characteristics
                foreach($breed->characteristics as $breedCharacteristic)
                {
                    // Clone the characteristic
                    $clonedBreedCharacteristic = $breedCharacteristic->replicate();

                    $clonedBreedCharacteristic->breed_id = $clonedBreed->id;

                    $clonedBreedCharacteristic->save();

                    // Go through the severities
                    foreach($breedCharacteristic->severities as $breedCharacteristicSeverity)
                    {
                        // Clone the characteristic severity
                        $clonedBreedCharacteristicSeverity = $breedCharacteristicSeverity->replicate();

                        $clonedBreedCharacteristicSeverity->breed_characteristic_id = $clonedBreedCharacteristic->id;

                        $clonedBreedCharacteristicSeverity->save();

                        // Go through the symptoms
                        foreach($breedCharacteristicSeverity->symptoms as $breedCharacteristicSeveritySymptom)
                        {
                            // Clone the characteristic severity symptom
                            $clonedBreedCharacteristicSeveritySymptom = $breedCharacteristicSeveritySymptom->replicate();

                            $clonedBreedCharacteristicSeveritySymptom->breed_characteristic_severity_id = $clonedBreedCharacteristicSeverity->id;

                            $clonedBreedCharacteristicSeveritySymptom->save();
                        }
                    }
                }
            });

            $success = Lang::get('forms/admin.clone_breed.success');

            return Redirect::route('admin/breeds/breed/edit', $clonedBreed->id)->with('success', $success);
        }
        catch(Exceptionsdfsdf $e)
        {
            $error = Lang::get('forms/admin.clone_breed.error');
        }

        return Redirect::route('admin/breeds/breed/edit', $breed->id)->with('error', $error);
    }

    public function postAddCharacteristicsToBreeds()
    {
        try
        {
            DB::transaction(function()
            {
                // Get the breed IDs
                $breedIds = (array) Input::get('breeds');

                // Get the characteristic IDs
                $characteristicIds = (array) Input::get('characteristics');

                // Always add -1
                $breedIds[] = -1;
                $characteristicIds[] = -1;

                // Get the breeds
                $breeds = Breed::whereIn('id', $breedIds)->get();

                // Get the characteristics
                $characteristics = Characteristic::whereIn('id', $characteristicIds)->get();

                $active = (Input::get('active_characteristic') === 'yes');
                $hide   = (Input::get('hide_characteristic') === 'yes');

                foreach($breeds as $breed)
                {
                    // false = do not remove characteristics that have already been attached and being readded
                    $breed->addCharacteristics($characteristics, $active, $hide, false);
                }
            });

            $success = Lang::get('forms/admin.add_characteristics_to_breeds.success');

            return Redirect::route('admin/breeds/manage')->with('success', $success);
        }
        catch(Exceptionsdf $e)
        {
            $error = Lang::get('forms/admin.add_characteristics_to_breeds.error');
        }

        return Redirect::route('admin/breeds/manage')->withInput()->with('error', $error);
    }

    public function postAddGenotypesToBreeds()
    {
        try
        {
            DB::transaction(function()
            {
                // Get the breed IDs
                $breedIds = (array) Input::get('breeds');

                // Always add -1
                $breedIds[] = -1;

                // Get the genotype data
                $genotypeData = (array) Input::get('genotypes');

                $genotypeFrequenciesById = [];

                // Pull out frequencies
                foreach($genotypeData as $genotypeId => $data)
                {
                    if ( ! array_key_exists('ignore', $data) and array_key_exists('frequency', $data))
                    {
                        $frequency = (int) $data['frequency'];

                        DB::table('breed_genotypes')
                            ->where('genotype_id', $genotypeId)
                            ->whereIn('breed_id', $breedIds)
                            ->update(array(
                                'frequency' => $frequency, 
                            ));
                    }
                }
            });

            $success = Lang::get('forms/admin.add_genotypes_to_breeds.success');

            return Redirect::route('admin/breeds/manage')->with('success', $success);
        }
        catch(Exceptionsdf $e)
        {
            $error = Lang::get('forms/admin.add_genotypes_to_breeds.error');
        }

        return Redirect::route('admin/breeds/manage')->withInput()->with('error', $error);
    }

}
