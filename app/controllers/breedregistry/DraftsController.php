<?php namespace Controllers\BreedRegistry;

use AuthorizedController;
use App;
use Redirect;
use View;
use Input;
use Validator;
use Lang;
use DB;
use URL;
use Carbon;
use BreedDraft;
use Breed;
use Characteristic;
use BreedDraftCharacteristic;

class DraftsController extends AuthorizedController {

    public function getManage()
    {
        $breedDrafts = $this->currentUser->breedDrafts()->whereDraft()->orderBy('name', 'asc')->get();
        $pendingDrafts = $this->currentUser->breedDrafts()->wherePending()->orderBy('name', 'asc')->get();
        $rejectedDrafts = $this->currentUser->breedDrafts()->whereRejected()->orderBy('name', 'asc')->get();
        $extinctDrafts = $this->currentUser->breedDrafts()->whereExtinct()->orderBy('name', 'asc')->get();
        $breeds = $this->currentUser->breeds()->orderBy('name', 'asc')->get();

        // Show the page
        return View::make('frontend/breed_registry/drafts/manage', compact(
            'breedDrafts', 'pendingDrafts', 'rejectedDrafts', 'extinctDrafts', 'breeds'
        ));
    }

    public function getOfficial()
    {
        // Get all breed draft IDs that are also active breeds
        $activeBreedDraftIds = Breed::whereNotNull('draft_id')->whereActive()->lists('draft_id');

        // Always add -1
        $activeBreedDraftIds[] = -1;

        // Get all official breed drafts except for the ones that have an active BREED
        $results = BreedDraft::whereOfficial()->whereNotIn('id', $activeBreedDraftIds)->where('name', '<>', 'New Real Draft');

        if (Input::get('search'))
        {
            $name  = Input::get('name');

            if (strlen($name) > 0)
            {
                $results = $results->where('name', 'LIKE', '%'.$name.'%');
            }
        }

        $results = $results->orderBy('name', 'asc')->paginate();

        // Show the page
        return View::make('frontend/breed_registry/drafts/official', compact('results'));
    }

    public function getCharacteristic($breedCharacteristic)
    {
        // Grab the characteristic
        $characteristic = $breedCharacteristic->characteristic;

        // Make sure the characteristic can be viewed
        if ( ! $breedCharacteristic->isActive() or $breedCharacteristic->isHidden() or $breedCharacteristic->isHealth() or ! $characteristic->isActive() or $characteristic->isHidden())
        {
            App::abort('404', 'Characteristic not found!');
        }

        // Show the page
        return View::make('frontend/breed_registry/breed/characteristic', compact('breedCharacteristic'));
    }

    public function getNewDraft()
    {
        // Show the page
        return View::make('frontend/breed_registry/drafts/new_draft');
    }

    public function postNewDraft()
    {
        try
        {
            // Create the draft
            $breedDraft = BreedDraft::create(array(
                'user_id'   => $this->currentUser->id, 
                'official'  => false, 
                'name'      => 'New Draft', 
                'status_id' => BreedDraft::STATUS_DRAFT, 
                'edited_at' => Carbon::now(), 
            ));

            $success = Lang::get('forms/user.create_breed_draft.success');

            return Redirect::route('breed_registry/draft/form', $breedDraft->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.create_breed_draft.error');
        }

        return Redirect::route('breed_registry/drafts/new')->with('error', $error);
    }

    public function getNewOfficial()
    {
        // Show the page
        return View::make('frontend/breed_registry/drafts/new_official');
    }

    public function postNewOfficial()
    {
        try
        {
            $breedDraft = null;

            DB::transaction(function() use (&$breedDraft)
            {
                // Create the draft
                $breedDraft = BreedDraft::create(array(
                    'user_id'   => $this->currentUser->id, 
                    'official'  => true, 
                    'name'      => 'New Real Draft', 
                    'status_id' => BreedDraft::STATUS_DRAFT, 
                    'edited_at' => Carbon::now(), 
                ));

                // Add all of the active characteristics onto the draft
                $characteristics = Characteristic::whereActive()
                    ->whereVisible()
                    ->whereNotHealth()
                    ->where('type_id', '<>', Characteristic::TYPE_FERTILITY)
                    ->where('type_id', '<>', Characteristic::TYPE_FERTILITY_SPAN)
                    ->where('type_id', '<>', Characteristic::TYPE_FERTILITY_DROP_OFF)
                    ->get();

                // Add them to the draft
                foreach($characteristics as $characteristic)
                {
                    $breedDraftCharacteristic = BreedDraftCharacteristic::create(array(
                        'breed_draft_id'    => $breedDraft->id, 
                        'characteristic_id' => $characteristic->id, 
                        'min_female_ranged_value' => $characteristic->min_ranged_value, 
                        'max_female_ranged_value' => $characteristic->max_ranged_value, 
                        'min_male_ranged_value'   => $characteristic->min_ranged_value, 
                        'max_male_ranged_value'   => $characteristic->max_ranged_value, 
                    ));
                }
            });
                
            $success = Lang::get('forms/user.create_breed_draft.success');

            return Redirect::route('breed_registry/draft/form', $breedDraft->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.create_breed_draft.error');
        }

        return Redirect::route('breed_registry/drafts/new')->with('error', $error);
    }

}
