<?php namespace Controllers\Admin;

use AdminController;
use Illuminate\Support\Collection;
use View;
use DB;
use Carbon;
use Config;
use Input;
use URL;
use Validator;
use Lang;
use Redirect;
use Floats;

use Characteristic;
use CharacteristicLabel;
use CharacteristicDependency;
use CharacteristicCategory;
use CharacteristicTest;
use CharacteristicSeverity;
use CharacteristicSeveritySymptom;
use CharacteristicDependencyGroup;
use CharacteristicDependencyGroupRange;
use CharacteristicDependencyGroupIndependentCharacteristicGenotype;
use CharacteristicDependencyGroupIndependentCharacteristicRange;
use CharacteristicDependencyIndependentCharacteristic;
use Genotype;
use Locus;
use HelpPage;
use Symptom;
use BreedCharacteristic;
use BreedCharacteristicSeverity;
use BreedDraft;
use BreedDraftCharacteristic;

use Exception;
use Dynasty\Characteristics\Exceptions as DynastyCharacteristicsExceptions;
use Dynasty\CharacteristicDependencies\Exceptions as DynastyCharacteristicDependenciesExceptions;
use Dynasty\CharacteristicDependencyIndependentCharacteristics\Exceptions as DynastyCharacteristicDependencyIndependentCharacteristicExceptions;

class CharacteristicsController extends AdminController {

    public function __construct()
    {
        parent::__construct();

        $this->sidebarGroups = array(
            array(
                'heading' => 'Characteristics', 
                'items' => array(
                    array(
                        'title' => 'New', 
                        'url' => URL::route('admin/characteristics/characteristic/create'), 
                    ), 
                    array(
                        'title' => 'Existing', 
                        'url' => URL::route('admin/characteristics'), 
                    ), 
                ), 
            ),
            array(
                'heading' => 'Categories', 
                'items' => array(
                    array(
                        'title' => 'New', 
                        'url' => URL::route('admin/characteristics/category/create'), 
                    ), 
                    array(
                        'title' => 'Existing', 
                        'url' => URL::route('admin/characteristics/categories'), 
                    ), 
                ), 
            ),
            array(
                'heading' => 'Dependencies', 
                'items' => array(
                    array(
                        'title' => 'New', 
                        'url' => URL::route('admin/characteristics/dependency/create'), 
                    ), 
                    array(
                        'title' => 'Existing', 
                        'url' => URL::route('admin/characteristics/dependencies'), 
                    ), 
                ), 
            ),
            array(
                'heading' => 'Tests', 
                'items' => array(
                    array(
                        'title' => 'New', 
                        'url' => URL::route('admin/characteristics/test/create'), 
                    ), 
                    array(
                        'title' => 'Existing', 
                        'url' => URL::route('admin/characteristics/tests'), 
                    ), 
                ), 
            ),
        );
    }

    public function getIndex()
    {
        $results = new Characteristic;

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

        $characteristics = $results->orderBy('name', 'asc')->paginate();

        // Show the page
        return View::make('admin/characteristics/index', compact('characteristics'));
    }

    public function getCharacteristicCategories()
    {
        $results = new CharacteristicCategory;

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

        $characteristicCategories = $results->orderBy('name', 'asc')->paginate();

        // Show the page
        return View::make('admin/characteristics/characteristic_categories', compact('characteristicCategories'));
    }

    public function getCharacteristicTests()
    {
        $results = new CharacteristicTest;

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

        $characteristicTests = $results->orderBy('name', 'asc')->paginate();

        // Show the page
        return View::make('admin/characteristics/characteristic_tests', compact('characteristicTests'));
    }

    public function getCharacteristicDependencies()
    {
        $characteristicDependencies = CharacteristicDependency::select('characteristic_dependencies.*')
            ->join('characteristics', 'characteristics.id', '=', 'characteristic_dependencies.dependent_id')
            ->orderBy('characteristics.name', 'asc')
            ->paginate();

        // Show the page
        return View::make('admin/characteristics/characteristic_dependencies', compact('characteristicDependencies'));
    }

    public function getCreateCharacteristic()
    {
        $parentCharacteristicCategories = CharacteristicCategory::whereNull('parent_category_id')
            ->with(array(
                    'children' => function($query)
                        {
                            $query->orderBy('name', 'asc');
                        }
                ))
            ->orderBy('name', 'asc')
            ->get();

        $helpPages = HelpPage::orderBy('title', 'asc')->get();

        // Show the page
        return View::make('admin/characteristics/create_characteristic', compact('parentCharacteristicCategories', 'helpPages'));
    }

    public function getCreateCharacteristicCategory()
    {
        $parentCharacteristicCategories = CharacteristicCategory::whereNull('parent_category_id')->orderBy('name', 'asc')->get();

        // Show the page
        return View::make('admin/characteristics/create_characteristic_category', compact('parentCharacteristicCategories'));
    }

    public function getCreateCharacteristicTest()
    {
        $usedCharacteristicsIds = CharacteristicTest::lists('characteristic_id');

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

        // Get uncategorized characteristics
        $uncategorizedCharacteristics = Characteristic::whereNotIn('id', $usedCharacteristicsIds)->whereNull('category_id')->orderBy('name', 'asc')->get();

        // Show the page
        return View::make('admin/characteristics/create_characteristic_test', compact('characteristicCategories', 'uncategorizedCharacteristics'));
    }

    public function getCreateCharacteristicDependency()
    {
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

        // Get uncategorized characteristics
        $uncategorizedCharacteristics = Characteristic::whereNull('category_id')->orderBy('name', 'asc')->get();

        // Show the page
        return View::make('admin/characteristics/create_characteristic_dependency', compact('characteristicCategories', 'uncategorizedCharacteristics'));
    }

    public function getDeleteCharacteristic($characteristic)
    {
        try
        {
            $characteristic->delete();

            $success = Lang::get('forms/admin.delete_characteristic.success');

            return Redirect::route('admin/characteristics')->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.delete_characteristic.error');
        }

        return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->with('error', $error);
    }

    public function getDeleteCharacteristicCategory($characteristicCategory)
    {
        try
        {
            $characteristicCategory->delete();

            $success = Lang::get('forms/admin.delete_characteristic_category.success');

            return Redirect::route('admin/characteristics/categories')->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.delete_characteristic_category.error');
        }

        return Redirect::route('admin/characteristics/category/edit', $characteristicCategory->id)->with('error', $error);
    }

    public function getDeleteCharacteristicTest($characteristicTest)
    {
        try
        {
            $characteristicTest->delete();

            $success = Lang::get('forms/admin.delete_characteristic_test.success');

            return Redirect::route('admin/characteristics/tests')->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.delete_characteristic_test.error');
        }

        return Redirect::route('admin/characteristics/test/edit', $characteristicTest->id)->with('error', $error);
    }

    public function getDeleteCharacteristicDependency($characteristicDependency)
    {
        try
        {
            $characteristicDependency->delete();

            $success = Lang::get('forms/admin.delete_characteristic_dependency.success');

            return Redirect::route('admin/characteristics/dependencies')->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.delete_characteristic_dependency.error');
        }

        return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('error', $error);
    }

    public function getEditCharacteristic($characteristic)
    {
        $parentCharacteristicCategories = CharacteristicCategory::whereNull('parent_category_id')
            ->with(array(
                    'children' => function($query)
                        {
                            $query->orderBy('name', 'asc');
                        }
                ))
            ->orderBy('name', 'asc')
            ->get();

        $helpPages = HelpPage::orderBy('title', 'asc')->get();

        $labels = $characteristic->labels()
            ->orderBy('min_ranged_value', 'asc')
            ->orderBy('max_ranged_value', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $loci = Locus::with(array(
                    'genotypes' => function($query)
                        {
                            $query->orderByAlleles();
                        }
                ))
            ->has('genotypes')
            ->orderBy('name', 'asc')
            ->get();

        $characteristicSeverities = $characteristic->severities()
            ->with(array(
                    'symptoms' => function($query)
                        {
                            $query
                                ->orderBy('min_offset_age_to_express', 'asc')
                                ->orderBy('max_offset_age_to_express', 'asc');
                        }, 
                    'symptoms.symptom', 
                ))
            ->orderBy('min_value', 'asc')
            ->orderBy('max_value', 'asc')
            ->get();

        // Show the page
        return View::make('admin/characteristics/edit_characteristic', compact(
            'parentCharacteristicCategories', 'helpPages', 'labels', 'loci', 'characteristicSeverities', 
            'characteristic'
        ));
    }

    public function getEditCharacteristicSeverity($characteristicSeverity)
    {
        $symptoms = Symptom::orderBy('name', 'asc')->get();

        $characteristicSeveritySymptoms = $characteristicSeverity->symptoms()
            ->orderBy('min_offset_age_to_express', 'asc')
            ->orderBy('max_offset_age_to_express', 'asc')
            ->get();

        // Show the page
        return View::make('admin/characteristics/edit_characteristic_severity', compact(
            'characteristicSeverity', 'symptoms', 'characteristicSeveritySymptoms'
        ));
    }

    public function getEditCharacteristicCategory($characteristicCategory)
    {
        $parentCharacteristicCategories = CharacteristicCategory::where('id', '<>', $characteristicCategory->id)
            ->whereNull('parent_category_id')
            ->orderBy('name', 'asc')
            ->get();

        $childCharacteristicCategories = $characteristicCategory->children()->orderBy('name', 'asc')->get();
        $characteristics = $characteristicCategory->characteristics()->orderBy('name', 'asc')->get();

        // Show the page
        return View::make('admin/characteristics/edit_characteristic_category', compact(
            'characteristicCategory', 'parentCharacteristicCategories', 
            'childCharacteristicCategories', 'characteristics'
        ));
    }

    public function getEditCharacteristicTest($characteristicTest)
    {
        $usedCharacteristicsIds = CharacteristicTest::where('characteristic_id', '<>', $characteristicTest->characteristic_id)->lists('characteristic_id');

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

        // Get uncategorized characteristics
        $uncategorizedCharacteristics = Characteristic::whereNotIn('id', $usedCharacteristicsIds)->whereNull('category_id')->orderBy('name', 'asc')->get();

        // Show the page
        return View::make('admin/characteristics/edit_characteristic_test', compact(
            'characteristicTest', 'uncategorizedCharacteristics', 'characteristicCategories'
        ));
    }

    public function getEditCharacteristicDependency($characteristicDependency)
    {
        $independentCharacteristics = $characteristicDependency->independentCharacteristics;

        $usedCharacteristicsIds = $independentCharacteristics->lists('independent_characteristic_id');

        // Always add the dependent characteristic
        $usedCharacteristicsIds[] = $characteristicDependency->dependent_id;

        // Grab only appropriate characteristics
        $allCharacteristics = Characteristic::whereNotIn('id', $usedCharacteristicsIds)->get();

        $validCharacteristicIds = $allCharacteristics->filter(function($characteristic) use ($characteristicDependency)
            {
                return $characteristicDependency->takesInRanged()
                    ? $characteristic->isRanged()
                    : $characteristic->isGenetic();
            })
            ->lists('id');

        // Always add in -1
        $validCharacteristicIds[] = -1;

        // Get the characteristics categories
        $independentCharacteristicCategories = CharacteristicCategory::with(array(
                'parent', 
                'characteristics' => function($query) use ($validCharacteristicIds)
                    {
                        $query->whereIn('id', $validCharacteristicIds)->orderBy('name', 'asc');
                    }
            ))
            ->whereHas('characteristics', function($query) use ($validCharacteristicIds)
                {
                    $query->whereIn('id', $validCharacteristicIds);
                })
            ->select('characteristic_categories.*')
            ->join('characteristic_categories as parent', 'parent.id', '=', 'characteristic_categories.parent_category_id')
            ->orderBy('parent.name', 'asc')
            ->orderBy('characteristic_categories.name', 'asc')
            ->get();

        // Get uncategorized characteristics
        $uncategorizedIndependentCharacteristics = Characteristic::whereNotIn('id', $usedCharacteristicsIds)->whereNull('category_id')->orderBy('name', 'asc')->get();

        // Get attached independent characteristics
        $characteristicDependencyIndependentCharacteristics = $characteristicDependency->independentCharacteristics()->get();

        // Get all groups
        $characteristicDependencyGroups = $characteristicDependency->groups()->orderBy('identifier', 'asc')->get();

        $dependentCharacteristicLoci = $characteristicDependency->characteristic->loci()
            ->with(array(
                    'genotypes' => function($query)
                    {
                        $query->orderByAlleles();
                    }
                ))
            ->has('genotypes')
            ->orderBy('name', 'asc')
            ->get();

        // Grab the independent characteristic characteristic IDs
        $independentCharacteristicIds = $independentCharacteristics->lists('independent_characteristic_id');

        // Always add -1
        $independentCharacteristicIds[] = -1;

        // Get possible independent genotypes
        $possibleGenotypeIds = DB::table('genotypes')
            ->join('characteristics_loci', 'characteristics_loci.locus_id', '=', 'genotypes.locus_id')
            ->whereIn('characteristics_loci.characteristic_id', $independentCharacteristicIds)
            ->lists('genotypes.id');

        // Always add -1
        $possibleGenotypeIds[] = -1;

        $independentCharacteristicLoci = Locus::with(array(
                    'genotypes' => function($query) use ($possibleGenotypeIds)
                        {
                            $query->whereIn('genotypes.id', $possibleGenotypeIds)->orderByAlleles();
                        }
                ))
            ->whereHas('genotypes', function($query) use ($possibleGenotypeIds)
                {
                    $query->whereIn('genotypes.id', $possibleGenotypeIds);
                })
            ->orderBy('name', 'asc')
            ->get();

        // Show the page
        return View::make('admin/characteristics/edit_characteristic_dependency', compact(
            'uncategorizedIndependentCharacteristics', 
            'independentCharacteristicCategories', 
            'characteristicDependencyIndependentCharacteristics', 
            'characteristicDependencyGroups', 
            'dependentCharacteristicLoci', 
            'independentCharacteristicLoci', 
            'characteristicDependency'
        ));
    }

    public function postCreateCharacteristic()
    {
        // Declare the rules for the form validation
        $rules = array(
            'name' => 'required|max:32',
            'description' => 'max:255',
            'category' => 'exists:characteristic_categories,id',
            'help_page' => 'exists:help_pages,id',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/characteristics/characteristic/create')->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $characteristic = null;

            DB::transaction(function() use (&$characteristic)
            {
                // Create the characteristic
                $characteristic = Characteristic::create(array( 
                    'name'           => Input::get('name'), 
                    'description'    => Input::get('description'), 
                    'category_id'    => Input::get('category'), 
                    'help_page_id'   => Input::get('help_page'), 
                    'hide'           => (Input::get('hide') === 'yes'), 
                    'active'         => (Input::get('active') === 'yes'), 
                    'ignorable'      => (Input::get('ignorable') === 'yes'), 
                    'hide_genotypes' => (Input::get('hide_genotypes') === 'yes'), 
                ));

                if ($characteristic->isActive() and ! $characteristic->isHidden() and ! $characteristic->isHealth())
                {
                    // Add it to all official breed drafts that are drafts
                    $officialBreedDrafts = BreedDraft::whereDraft()->whereOfficial()->get();

                    foreach($officialBreedDrafts as $breedDraft)
                    {
                        BreedDraftCharacteristic::create(array(
                            'breed_draft_id'    => $breedDraft->id, 
                            'characteristic_id' => $characteristic->id, 
                        ));
                    }
                }
            });

            $success = Lang::get('forms/admin.create_characteristic.success');

            return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.create_characteristic.error');
        }

        return Redirect::route('admin/characteristics/characteristic/create')->withInput()->with('error', $error);
    }

    public function postCreateCharacteristicCategory()
    {
        // Declare the rules for the form validation
        $rules = array(
            'name'   => 'required|max:255',
            'parent' => 'exists:characteristic_categories,id',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/characteristics/category/create')->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            // Create the characteristic category
            $characteristicCategory = CharacteristicCategory::create(array( 
                'name' => Input::get('name'), 
                'parent_category_id' => (Input::get('parent') ?: null), 
            ));

            $success = Lang::get('forms/admin.create_characteristic_category.success');

            return Redirect::route('admin/characteristics/category/edit', $characteristicCategory->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.create_characteristic_category.error');
        }

        return Redirect::route('admin/characteristics/category/create')->withInput()->with('error', $error);
    }

    public function postCreateCharacteristicTest()
    {
        $usedCharacteristicsIds = CharacteristicTest::lists('characteristic_id');

        // Always add -1
        $usedCharacteristicsIds[] = -1;

        $possibleCharacteristicIds = Characteristic::whereNotIn('id', $usedCharacteristicsIds)->lists('id');

        // Always add -1
        $possibleCharacteristicIds[] = -1;

        // Declare the rules for the form validation
        $rules = array(
            'name'   => 'required|max:255',
            'characteristic' => 'required|in:'.implode(',', $possibleCharacteristicIds),
            'type'    => 'required|in:'.implode(',', array_keys(CharacteristicTest::types())),
            'min_age' => 'integer|min:0|max:65535',
            'max_age' => 'integer|min:0|max:65535',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/characteristics/test/create')->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $minAge = Input::get('min_age');
            $maxAge = Input::get('max_age');

            if (strlen($minAge) and strlen($maxAge) and $minAge > $maxAge)
            {
                $temp   = $minAge;
                $minAge = $maxAge;
                $maxAge = $temp;
            }

            // Create the characteristic test
            $characteristicTest = CharacteristicTest::create(array( 
                'name'    => Input::get('name'), 
                'characteristic_id' => Input::get('characteristic'), 
                'type_id' => Input::get('type'), 
                'min_age' => $minAge, 
                'max_age' => $maxAge, 
                'active'  => (Input::get('active') == 'yes'), 
                'reveal_genotypes'      => (Input::get('reveal_genotypes') == 'yes'), 
                'reveal_phenotypes'     => (Input::get('reveal_phenotypes') == 'yes'), 
                'reveal_ranged_value'   => (Input::get('reveal_ranged_value') == 'yes'), 
                'reveal_severity_value' => (Input::get('reveal_severity_value') == 'yes'), 
            ));

            $success = Lang::get('forms/admin.create_characteristic_test.success');

            return Redirect::route('admin/characteristics/test/edit', $characteristicTest->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.create_characteristic_test.error');
        }

        return Redirect::route('admin/characteristics/test/create')->withInput()->with('error', $error);
    }

    public function postCreateCharacteristicDependency()
    {
        // Declare the rules for the form validation
        $rules = array(
            'characteristic' => 'required|exists:characteristics,id',
            'type' => 'required|in:'.implode(',', array_keys(CharacteristicDependency::types())),
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/characteristics/dependency/create')->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $typeId = Input::get('type');

            // Grab the characteristic
            $characteristic = Characteristic::find(Input::get('characteristic'));

            if ( ! CharacteristicDependency::validTypeForDependentCharacteristic($characteristic, $typeId))
            {
                throw new DynastyCharacteristicDependenciesExceptions\InvalidTypeException;
            }

            // Create the characteristic dependency
            $characteristicDependency = CharacteristicDependency::create(array( 
                'dependent_id' => $characteristic->id, 
                'type_id'      => $typeId, 
                'active'       => (Input::get('active') == 'yes'), 
            ));

            $success = Lang::get('forms/admin.create_characteristic_dependency.success');

            return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('success', $success);
        }
        catch(DynastyCharacteristicDependenciesExceptions\InvalidTypeException $e)
        {
            $error = Lang::get('forms/admin.create_characteristic_dependency.invalid_type');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.create_characteristic_dependency.error');
        }

        return Redirect::route('admin/characteristics/dependency/create')->withInput()->with('error', $error);
    }

    public function postEditCharacteristic($characteristic)
    {
        // Declare the rules for the form validation
        $rules = array(
            'name'        => 'required|max:32',
            'description' => 'max:255',
            'category'    => 'exists:characteristic_categories,id',
            'help_page'   => 'exists:help_pages,id',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            DB::transaction(function() use ($characteristic)
            {
                $characteristic->name           = Input::get('name');
                $characteristic->description    = Input::get('description');
                $characteristic->category_id    = Input::get('category');
                $characteristic->help_page_id   = Input::get('help_page');
                $characteristic->hide           = (Input::get('hide') === 'yes');
                $characteristic->active         = (Input::get('active') === 'yes');
                $characteristic->ignorable      = (Input::get('ignorable') === 'yes');
                $characteristic->hide_genotypes = (Input::get('hide_genotypes') === 'yes');
                $characteristic->save();

                if ( ! $characteristic->isActive() or $characteristic->isHidden() or $characteristic->isHealth())
                {
                    // Need to remove it from the drafts
                    DB::table('breed_draft_characteristics')->where('characteristic_id', $characteristic->id)->delete();
                }
                else if ($characteristic->isActive() and ! $characteristic->isHidden() and ! $characteristic->isHealth())
                {
                    // Get all the breed drafts that already have this characteristic
                    $invalidBreedDraftIds = BreedDraftCharacteristic::where('characteristic_id', $characteristic->id)->lists('breed_draft_id');

                    // Always add -1
                    $invalidBreedDraftIds[] = -1;

                    // Add it to all official breed drafts that are drafts and don't have it
                    $officialBreedDrafts = BreedDraft::whereDraft()->whereOfficial()->whereNotIn('id', $invalidBreedDraftIds)->get();

                    foreach($officialBreedDrafts as $breedDraft)
                    {
                        BreedDraftCharacteristic::create(array(
                            'breed_draft_id'    => $breedDraft->id, 
                            'characteristic_id' => $characteristic->id, 
                        ));
                    }
                }
            });

            $success = Lang::get('forms/admin.update_characteristic.success');

            return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_characteristic.error');
        }

        return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->withInput()->with('error', $error);
    }

    public function postEditCharacteristicCategory($characteristicCategory)
    {
        // Declare the rules for the form validation
        $rules = array(
            'name'   => 'required|max:255',
            'parent' => 'exists:characteristic_categories,id|not_in:'.$characteristicCategory->id,
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/characteristics/category/edit', $characteristicCategory->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $characteristicCategory->name = Input::get('name');
            $characteristicCategory->parent_category_id = (Input::get('parent') ?: null);
            $characteristicCategory->save();

            $success = Lang::get('forms/admin.update_characteristic_category.success');

            return Redirect::route('admin/characteristics/category/edit', $characteristicCategory->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_characteristic_category.error');
        }

        return Redirect::route('admin/characteristics/category/edit', $characteristicCategory->id)->withInput()->with('error', $error);
    }
    
    public function postEditCharacteristicTest($characteristicTest)
    {
        $usedCharacteristicsIds = CharacteristicTest::where('characteristic_id', '<>', $characteristicTest->characteristic_id)->lists('characteristic_id');

        // Always add -1
        $usedCharacteristicsIds[] = -1;

        $possibleCharacteristicIds = Characteristic::whereNotIn('id', $usedCharacteristicsIds)->lists('id');

        // Always add -1
        $possibleCharacteristicIds[] = -1;

        // Declare the rules for the form validation
        $rules = array(
            'name'   => 'required|max:255',
            'characteristic' => 'required|in:'.implode(',', $possibleCharacteristicIds),
            'type'    => 'required|in:'.implode(',', array_keys(CharacteristicTest::types())),
            'min_age' => 'integer|min:0|max:65535',
            'max_age' => 'integer|min:0|max:65535',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/characteristics/test/edit', $characteristicTest->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $minAge = Input::get('min_age');
            $maxAge = Input::get('max_age');

            if (strlen($minAge) and strlen($maxAge) and $minAge > $maxAge)
            {
                $temp   = $minAge;
                $minAge = $maxAge;
                $maxAge = $temp;
            }

            $characteristicTest->name    = Input::get('name');
            $characteristicTest->characteristic_id = Input::get('characteristic');
            $characteristicTest->type_id = Input::get('type');
            $characteristicTest->min_age = $minAge;
            $characteristicTest->max_age = $maxAge;
            $characteristicTest->active  = (Input::get('active') == 'yes');
            $characteristicTest->reveal_genotypes      = (Input::get('reveal_genotypes') == 'yes');
            $characteristicTest->reveal_phenotypes     = (Input::get('reveal_phenotypes') == 'yes');
            $characteristicTest->reveal_ranged_value   = (Input::get('reveal_ranged_value') == 'yes');
            $characteristicTest->reveal_severity_value = (Input::get('reveal_severity_value') == 'yes');
            $characteristicTest->save();

            $success = Lang::get('forms/admin.update_characteristic_test.success');

            return Redirect::route('admin/characteristics/test/edit', $characteristicTest->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_characteristic_test.error');
        }

        return Redirect::route('admin/characteristics/test/edit', $characteristicTest->id)->withInput()->with('error', $error);
    }
    
    public function postEditCharacteristicDependency($characteristicDependency)
    {
        try
        {
            $characteristicDependency->active  = (Input::get('active') == 'yes');
            $characteristicDependency->save();

            $success = Lang::get('forms/admin.update_characteristic_dependency.success');

            return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_characteristic_dependency.error');
        }

        return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->withInput()->with('error', $error);
    }

    public function postUpdateCharacteristicRange($characteristic)
    {
        // Declare the rules for the form validation
        $rules = array(
            'minimum_ranged_value'   => 'required|numeric|min:0.00|max:99999999.99',
            'maximum_ranged_value'   => 'required|numeric|min:0.00|max:99999999.99',
            'ranged_value_precision' => 'required|numeric|min:0|max:2',
            'ranged_lower_boundary_label' => 'max:32',
            'ranged_upper_boundary_label' => 'max:32',
            'ranged_prefix_units' => 'max:16',
            'ranged_suffix_units' => 'max:16',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);
        
        $validator->sometimes(['minimum_age_to_reveal_ranged_value', 'maximum_age_to_reveal_ranged_value'], 'required|numeric|min:0|max:65535', function($input)
        {
            return ($input->ranged_value_can_be_revealed === 'yes');
        });
        
        $validator->sometimes(['minimum_age_to_stop_growing', 'maximum_age_to_stop_growing'], 'required|numeric|min:0|max:65535', function($input)
        {
            return ($input->growth === 'yes');
        });

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            DB::transaction(function() use ($characteristic)
            {
                // Save the original values
                $originalValues = $characteristic->toArray();

                // Get the values
                $minRangedValue = Input::get('minimum_ranged_value');
                $maxRangedValue = Input::get('maximum_ranged_value');
                $minAgeToRevealRangedValue = Input::get('minimum_age_to_reveal_ranged_value');
                $maxAgeToRevealRangedValue = Input::get('maximum_age_to_reveal_ranged_value');
                $minAgeToStopGrowing = Input::get('minimum_age_to_stop_growing');
                $maxAgeToStopGrowing = Input::get('maximum_age_to_stop_growing');

                if (Floats::compare($minRangedValue, $maxRangedValue, '>'))
                {
                    $temp = $minRangedValue;
                    $minRangedValue = $maxRangedValue;
                    $maxRangedValue = $temp;
                }

                if (Floats::compare($minAgeToRevealRangedValue, $maxAgeToRevealRangedValue, '>'))
                {
                    $temp = $minAgeToRevealRangedValue;
                    $minAgeToRevealRangedValue = $maxAgeToRevealRangedValue;
                    $maxAgeToRevealRangedValue = $temp;
                }

                if (Floats::compare($minAgeToStopGrowing, $maxAgeToStopGrowing, '>'))
                {
                    $temp = $minAgeToStopGrowing;
                    $minAgeToStopGrowing = $maxAgeToStopGrowing;
                    $maxAgeToStopGrowing = $temp;
                }

                $characteristic->min_ranged_value       = $minRangedValue;
                $characteristic->max_ranged_value       = $maxRangedValue;
                $characteristic->ranged_value_precision = Input::get('ranged_value_precision');
                $characteristic->ranged_value_can_be_revealed   = (Input::get('ranged_value_can_be_revealed') === 'yes');
                $characteristic->min_age_to_reveal_ranged_value = $minAgeToRevealRangedValue;
                $characteristic->max_age_to_reveal_ranged_value = $maxAgeToRevealRangedValue;
                $characteristic->ranged_lower_boundary_label = Input::get('ranged_lower_boundary_label');
                $characteristic->ranged_upper_boundary_label = Input::get('ranged_upper_boundary_label');
                $characteristic->ranged_prefix_units = Input::get('ranged_prefix_units');
                $characteristic->ranged_suffix_units = Input::get('ranged_suffix_units');
                $characteristic->ranged_value_can_grow   = (Input::get('growth') === 'yes');
                $characteristic->min_age_to_stop_growing = $minAgeToStopGrowing;
                $characteristic->max_age_to_stop_growing = $maxAgeToStopGrowing;
                $characteristic->save();

                // Get all breed characteristics that belong to this characteristic
                $breedCharacteristics = BreedCharacteristic::where('characteristic_id', $characteristic->id)->get();

                foreach($breedCharacteristics as $breedCharacteristic)
                {
                    $breedCharacteristic->min_female_ranged_value = is_null($breedCharacteristic->min_female_ranged_value)
                        ? $characteristic->min_female_ranged_value
                        : Floats::normalizeValueInRange(
                                $breedCharacteristic->min_female_ranged_value, 
                                [ $originalValues['min_ranged_value'], $originalValues['max_ranged_value'] ], 
                                [ $characteristic->min_ranged_value, $characteristic->max_ranged_value ]
                            );
                        
                    $breedCharacteristic->max_female_ranged_value = is_null($breedCharacteristic->max_female_ranged_value)
                        ? $characteristic->max_female_ranged_value
                        : Floats::normalizeValueInRange(
                                $breedCharacteristic->max_female_ranged_value, 
                                [ $originalValues['min_ranged_value'], $originalValues['max_ranged_value'] ], 
                                [ $characteristic->min_ranged_value, $characteristic->max_ranged_value ]
                            );
                        
                    $breedCharacteristic->min_male_ranged_value = is_null($breedCharacteristic->min_male_ranged_value)
                        ? $characteristic->min_male_ranged_value
                        : Floats::normalizeValueInRange(
                                $breedCharacteristic->min_male_ranged_value, 
                                [ $originalValues['min_ranged_value'], $originalValues['max_ranged_value'] ], 
                                [ $characteristic->min_ranged_value, $characteristic->max_ranged_value ]
                            );
                        
                    $breedCharacteristic->max_male_ranged_value = is_null($breedCharacteristic->max_male_ranged_value)
                        ? $characteristic->max_male_ranged_value
                        : Floats::normalizeValueInRange(
                                $breedCharacteristic->max_male_ranged_value, 
                                [ $originalValues['min_ranged_value'], $originalValues['max_ranged_value'] ], 
                                [ $characteristic->min_ranged_value, $characteristic->max_ranged_value ]
                            );
                        
                    $breedCharacteristic->min_age_to_reveal_ranged_value = is_null($breedCharacteristic->min_age_to_reveal_ranged_value)
                        ? $characteristic->min_age_to_reveal_ranged_value
                        : Floats::normalizeValueInRange(
                                $breedCharacteristic->min_age_to_reveal_ranged_value, 
                                [ $originalValues['min_age_to_reveal_ranged_value'], $originalValues['max_age_to_reveal_ranged_value'] ], 
                                [ $characteristic->min_age_to_reveal_ranged_value, $characteristic->max_age_to_reveal_ranged_value ]
                            );
                        
                    $breedCharacteristic->max_age_to_reveal_ranged_value = is_null($breedCharacteristic->max_age_to_reveal_ranged_value)
                        ? $characteristic->max_age_to_reveal_ranged_value
                        : Floats::normalizeValueInRange(
                                $breedCharacteristic->max_age_to_reveal_ranged_value, 
                                [ $originalValues['min_age_to_reveal_ranged_value'], $originalValues['max_age_to_reveal_ranged_value'] ], 
                                [ $characteristic->min_age_to_reveal_ranged_value, $characteristic->max_age_to_reveal_ranged_value ]
                            );
                        
                    $breedCharacteristic->min_age_to_stop_growing = is_null($breedCharacteristic->min_age_to_stop_growing)
                        ? $characteristic->min_age_to_stop_growing
                        : Floats::normalizeValueInRange(
                                $breedCharacteristic->min_age_to_stop_growing, 
                                [ $originalValues['min_age_to_stop_growing'], $originalValues['max_age_to_stop_growing'] ], 
                                [ $characteristic->min_age_to_stop_growing, $characteristic->max_age_to_stop_growing ]
                            );
                        
                    $breedCharacteristic->max_age_to_stop_growing = is_null($breedCharacteristic->max_age_to_stop_growing)
                        ? $characteristic->max_age_to_stop_growing
                        : Floats::normalizeValueInRange(
                                $breedCharacteristic->max_age_to_stop_growing, 
                                [ $originalValues['min_age_to_stop_growing'], $originalValues['max_age_to_stop_growing'] ], 
                                [ $characteristic->min_age_to_stop_growing, $characteristic->max_age_to_stop_growing ]
                            );

                    $breedCharacteristic->save();
                }

                // Get all breed drafts that have this characteristic
                $breedDraftCharacteristics = BreedDraftCharacteristic::where('characteristic_id', $characteristic->id)->get();

                foreach($breedDraftCharacteristics as $breedDraftCharacteristic)
                {
                    $breedDraftCharacteristic->min_female_ranged_value = is_null($breedDraftCharacteristic->min_female_ranged_value)
                        ? $characteristic->min_female_ranged_value
                        : Floats::normalizeValueInRange(
                                $breedDraftCharacteristic->min_female_ranged_value, 
                                [ $originalValues['min_ranged_value'], $originalValues['max_ranged_value'] ], 
                                [ $characteristic->min_ranged_value, $characteristic->max_ranged_value ]
                            );

                    $breedDraftCharacteristic->max_female_ranged_value = is_null($breedDraftCharacteristic->max_female_ranged_value)
                        ? $characteristic->max_female_ranged_value
                        : Floats::normalizeValueInRange(
                                $breedDraftCharacteristic->max_female_ranged_value, 
                                [ $originalValues['min_ranged_value'], $originalValues['max_ranged_value'] ], 
                                [ $characteristic->min_ranged_value, $characteristic->max_ranged_value ]
                            );
                        
                    $breedDraftCharacteristic->min_male_ranged_value = is_null($breedDraftCharacteristic->min_male_ranged_value)
                        ? $characteristic->min_male_ranged_value
                        : Floats::normalizeValueInRange(
                                $breedDraftCharacteristic->min_male_ranged_value, 
                                [ $originalValues['min_ranged_value'], $originalValues['max_ranged_value'] ], 
                                [ $characteristic->min_ranged_value, $characteristic->max_ranged_value ]
                            );
                        
                    $breedDraftCharacteristic->max_male_ranged_value = is_null($breedDraftCharacteristic->max_male_ranged_value)
                        ? $characteristic->max_male_ranged_value
                        : Floats::normalizeValueInRange(
                                $breedDraftCharacteristic->max_male_ranged_value, 
                                [ $originalValues['min_ranged_value'], $originalValues['max_ranged_value'] ], 
                                [ $characteristic->min_ranged_value, $characteristic->max_ranged_value ]
                            );

                    $breedDraftCharacteristic->save();
                }
            });

            $success = Lang::get('forms/admin.update_characteristic_range.success');

            return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_characteristic_range.error');
        }

        return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->withInput()->with('error', $error);
    }

    public function getRemoveCharacteristicRange($characteristic)
    {
        try
        {
            DB::transaction(function() use ($characteristic)
            {
                $characteristic->min_ranged_value       = null;
                $characteristic->max_ranged_value       = null;
                $characteristic->ranged_value_precision = null;
                $characteristic->ranged_value_can_be_revealed   = null;
                $characteristic->min_age_to_reveal_ranged_value = null;
                $characteristic->max_age_to_reveal_ranged_value = null;
                $characteristic->ranged_lower_boundary_label = null;
                $characteristic->ranged_upper_boundary_label = null;
                $characteristic->ranged_prefix_units = null;
                $characteristic->ranged_suffix_units = null;
                $characteristic->ranged_value_can_grow   = null;
                $characteristic->min_age_to_stop_growing = null;
                $characteristic->max_age_to_stop_growing = null;
                $characteristic->save();

                // Remove all the labels
                $characteristic->labels()->delete();

                // Get all breed characteristics that belong to this characteristic
                DB::table('breed_characteristics')
                    ->where('characteristic_id', $characteristic->id)
                    ->update(array(
                        'min_female_ranged_value' => null, 
                        'max_female_ranged_value' => null, 
                        'min_male_ranged_value'   => null, 
                        'max_male_ranged_value'   => null, 
                        'min_age_to_reveal_ranged_value' => null, 
                        'max_age_to_reveal_ranged_value' => null, 
                        'min_age_to_stop_growing' => null, 
                        'max_age_to_stop_growing' => null, 
                    ));

                // Get all breed drafts that have this characteristic
                DB::table('breed_draft_characteristics')
                    ->where('characteristic_id', $characteristic->id)
                    ->update(array(
                        'min_female_ranged_value' => null, 
                        'max_female_ranged_value' => null, 
                        'min_male_ranged_value'   => null, 
                        'max_male_ranged_value'   => null, 
                    ));
            });

            $success = Lang::get('forms/admin.remove_characteristic_range.success');

            return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.remove_characteristic_range.error');
        }

        return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->with('error', $error);
    }

    public function postUpdateCharacteristicGenetics($characteristic)
    {
        // Declare the rules for the form validation
        $rules =[];

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        $validator->sometimes(['minimum_age_to_reveal_phenotypes', 'maximum_age_to_reveal_phenotypes'], 'required|numeric|min:0|max:65535', function($input)
        {
            return ($input->phenotypes_can_be_revealed === 'yes');
        });

        $validator->sometimes(['minimum_age_to_reveal_genotypes', 'maximum_age_to_reveal_genotypes'], 'required|numeric|min:0|max:65535', function($input)
        {
            return ($input->genotypes_can_be_revealed === 'yes');
        });

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            DB::transaction(function($query) use ($characteristic)
            {
                // Save the original values
                $originalValues = $characteristic->toArray();

                // Get the values
                $minAgeToRevealPhenotypes = Input::get('minimum_age_to_reveal_phenotypes');
                $maxAgeToRevealPhenotypes = Input::get('maximum_age_to_reveal_phenotypes');
                $minAgeToRevealGenotypes  = Input::get('minimum_age_to_reveal_genotypes');
                $maxAgeToRevealGenotypes  = Input::get('maximum_age_to_reveal_genotypes');

                if (Floats::compare($minAgeToRevealPhenotypes, $maxAgeToRevealPhenotypes, '>'))
                {
                    $temp = $minAgeToRevealPhenotypes;
                    $minAgeToRevealPhenotypes = $maxAgeToRevealPhenotypes;
                    $maxAgeToRevealPhenotypes = $temp;
                }

                if (Floats::compare($minAgeToRevealGenotypes, $maxAgeToRevealGenotypes, '>'))
                {
                    $temp = $minAgeToRevealGenotypes;
                    $minAgeToRevealGenotypes = $maxAgeToRevealGenotypes;
                    $maxAgeToRevealGenotypes = $temp;
                }

                $characteristic->phenotypes_can_be_revealed   = (Input::get('phenotypes_can_be_revealed') === 'yes');
                $characteristic->min_age_to_reveal_phenotypes = $minAgeToRevealPhenotypes;
                $characteristic->max_age_to_reveal_phenotypes = $maxAgeToRevealPhenotypes;
                $characteristic->genotypes_can_be_revealed    = (Input::get('genotypes_can_be_revealed') === 'yes');
                $characteristic->min_age_to_reveal_genotypes  = $minAgeToRevealGenotypes;
                $characteristic->max_age_to_reveal_genotypes  = $maxAgeToRevealGenotypes;
                $characteristic->save();

                // Save the loci
                $potentialLocusIds = (array) Input::get('loci');

                // Always add -1
                $potentialLocusIds[] = -1;

                // Find the actual locus to add
                $locusIds = Locus::whereIn('id', $potentialLocusIds)->lists('id');

                // Sync them to the characteristic
                $characteristic->loci()->sync($locusIds);

                $characteristic->save();

                // Get all breed characteristics that belong to this characteristic
                $breedCharacteristics = BreedCharacteristic::where('characteristic_id', $characteristic->id)->get();

                foreach($breedCharacteristics as $breedCharacteristic)
                {
                    $breedCharacteristic->min_age_to_reveal_genotypes = is_null($breedCharacteristic->min_age_to_reveal_genotypes)
                        ? $characteristic->min_age_to_reveal_genotypes
                        : Floats::normalizeValueInRange(
                                $breedCharacteristic->min_age_to_reveal_genotypes, 
                                [ $originalValues['min_age_to_reveal_genotypes'], $originalValues['max_age_to_reveal_genotypes'] ], 
                                [ $characteristic->min_age_to_reveal_genotypes, $characteristic->max_age_to_reveal_genotypes ]
                            );
                        
                    $breedCharacteristic->max_age_to_reveal_genotypes = is_null($breedCharacteristic->max_age_to_reveal_genotypes)
                        ? $characteristic->max_age_to_reveal_genotypes
                        : Floats::normalizeValueInRange(
                                $breedCharacteristic->max_age_to_reveal_genotypes, 
                                [ $originalValues['min_age_to_reveal_genotypes'], $originalValues['max_age_to_reveal_genotypes'] ], 
                                [ $characteristic->min_age_to_reveal_genotypes, $characteristic->max_age_to_reveal_genotypes ]
                            );
                        
                    $breedCharacteristic->min_age_to_reveal_phenotypes = is_null($breedCharacteristic->min_age_to_reveal_phenotypes)
                        ? $characteristic->min_age_to_reveal_phenotypes
                        : Floats::normalizeValueInRange(
                                $breedCharacteristic->min_age_to_reveal_phenotypes, 
                                [ $originalValues['min_age_to_reveal_phenotypes'], $originalValues['max_age_to_reveal_phenotypes'] ], 
                                [ $characteristic->min_age_to_reveal_phenotypes, $characteristic->max_age_to_reveal_phenotypes ]
                            );
                        
                    $breedCharacteristic->max_age_to_reveal_phenotypes = is_null($breedCharacteristic->max_age_to_reveal_phenotypes)
                        ? $characteristic->max_age_to_reveal_phenotypes
                        : Floats::normalizeValueInRange(
                                $breedCharacteristic->max_age_to_reveal_phenotypes, 
                                [ $originalValues['min_age_to_reveal_phenotypes'], $originalValues['max_age_to_reveal_phenotypes'] ], 
                                [ $characteristic->min_age_to_reveal_phenotypes, $characteristic->max_age_to_reveal_phenotypes ]
                            );

                    $breedCharacteristic->save();
                }

                // Get all breed drafts that have this characteristic
                $breedDraftCharacteristics = BreedDraftCharacteristic::where('characteristic_id', $characteristic->id)->get();

                foreach($breedDraftCharacteristics as $breedDraftCharacteristic)
                {
                    $invalidGenotypeIds  = [];
                    $invalidPhenotypeIds = [];

                    // Go through the genotypes
                    foreach($breedDraftCharacteristic->genotypes as $genotype)
                    {
                        if ( ! in_array($genotype->locus_id, $locusIds))
                        {
                            $invalidGenotypeIds[] = $genotype->id;
                        }
                    }

                    // Remove the invalid genotypes
                    $breedDraftCharacteristic->genotypes()->detach($invalidGenotypeIds);

                    // Go through the phenotypes
                    foreach($breedDraftCharacteristic->phenotypes as $phenotype)
                    {
                        // Get all of the loci involved
                        $phenotypeLocusIds = $phenotype->genotypes()->lists('locus_id');

                        // Return the loci that are in the phenotype, but not in the characteristic
                        $missingLocusIds = array_diff($phenotypeLocusIds, $locusIds);

                        if ( ! empty($missingLocusIds))
                        {
                            $invalidPhenotypeIds[] = $phenotype->id;
                        }
                    }

                    // Remove the invalid phenotypes
                    $breedDraftCharacteristic->phenotypes()->detach($invalidPhenotypeIds);
                }
            });

            $success = Lang::get('forms/admin.update_characteristic_genetics.success');

            return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_characteristic_genetics.error');
        }

        return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->withInput()->with('error', $error);
    }

    public function getRemoveCharacteristicGenetics($characteristic)
    {
        try
        {
            DB::transaction(function() use ($characteristic)
            {
                $characteristic->genotypes_can_be_revealed    = null;
                $characteristic->min_age_to_reveal_genotypes  = null;
                $characteristic->max_age_to_reveal_genotypes  = null;
                $characteristic->phenotypes_can_be_revealed   = null;
                $characteristic->min_age_to_reveal_phenotypes = null;
                $characteristic->max_age_to_reveal_phenotypes = null;
                $characteristic->save();

                // Remove all the loci
                $characteristic->loci()->detach();

                // Get all breed characteristics that belong to this characteristic
                DB::table('breed_characteristics')
                    ->where('characteristic_id', $characteristic->id)
                    ->update(array(
                        'min_age_to_reveal_genotypes'  => null, 
                        'max_age_to_reveal_genotypes'  => null, 
                        'min_age_to_reveal_phenotypes' => null, 
                        'max_age_to_reveal_phenotypes' => null, 
                    ));

                // Get all breed drafts that have this characteristic
                $breedDraftCharacteristics = BreedDraftCharacteristic::where('characteristic_id', $characteristic->id)->get();

                foreach($breedDraftCharacteristics as $breedDraftCharacteristic)
                {
                    $breedDraftCharacteristic->genotypes()->detach();
                    $breedDraftCharacteristic->phenotypes()->detach();
                }
            });

            $success = Lang::get('forms/admin.remove_characteristic_genetics.success');

            return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.remove_characteristic_genetics.error');
        }

        return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->with('error', $error);
    }

    public function postUpdateCharacteristicHealth($characteristic)
    {
        try
        {
            DB::transaction(function($query) use ($characteristic)
            {
                // Save the genotypes
                $potentialGenotypeIds = (array) Input::get('health_genotypes');

                // Always add -1
                $potentialGenotypeIds[] = -1;

                // Find the actual genotype to add
                $genotypeIds = Genotype::whereIn('id', $potentialGenotypeIds)->lists('id');

                // Sync them to the characteristic
                $characteristic->genotypes()->sync($genotypeIds);
            });

            $success = Lang::get('forms/admin.update_characteristic_health.success');

            return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_characteristic_health.error');
        }

        return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->withInput()->with('error', $error);
    }

    public function getRemoveCharacteristicHealth($characteristic)
    {
        try
        {
            DB::transaction(function() use ($characteristic)
            {
                // Remove all the genotypes
                $characteristic->genotypes()->detach();

                // Remove the severities
                $characteristic->severities()->delete();
            });

            $success = Lang::get('forms/admin.remove_characteristic_health.success');

            return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.remove_characteristic_health.error');
        }

        return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->with('error', $error);
    }

    public function postAddLabelToCharacteristic($characteristic)
    {
        try
        {
            if ( ! $characteristic->isRanged())
            {
                throw new DynastyCharacteristicsExceptions\NotRangedException;
            }

            // Declare the rules for the form validation
            $rules = array(
                'label_name' => 'required|max:32',
                'minimum_ranged_label_value' => 'required|numeric|min:0|max:99999999.99',
                'maximum_ranged_label_value' => 'required|numeric|min:0|max:99999999.99',
            );

            // Create a new validator instance from our validation rules
            $validator = Validator::make(Input::all(), $rules);

            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->withInput()->with('error', $validator->errors()->first());
            }

            // Get the values
            $minRangedValue = Input::get('minimum_ranged_label_value');
            $maxRangedValue = Input::get('maximum_ranged_label_value');

            if (Floats::compare($minRangedValue, $maxRangedValue, '>'))
            {
                $temp = $minRangedValue;
                $minRangedValue = $maxRangedValue;
                $maxRangedValue = $temp;
            }

            // Add the label
            CharacteristicLabel::create(array(
                'characteristic_id' => $characteristic->id, 
                'name' => Input::get('label_name'), 
                'min_ranged_value' => $minRangedValue, 
                'max_ranged_value' => $maxRangedValue, 
            ));

            $success = Lang::get('forms/admin.add_label_to_characteristic.success');

            return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->with('success', $success);
        }
        catch(DynastyCharacteristicsExceptions\NotRangedException $e)
        {
            $error = Lang::get('forms/admin.add_label_to_characteristic.not_ranged');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.add_label_to_characteristic.error');
        }

        return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->with('error', $error);
    }

    public function getRemoveLabelFromCharacteristic($characteristicLabel)
    {
        try
        {
            // Grab the characteristic
            $characteristic = $characteristicLabel->characteristic;

            $characteristicLabel->delete();

            $success = Lang::get('forms/admin.remove_label_from_characteristic.success');

            return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.remove_label_from_characteristic.error');
        }

        return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->with('error', $error);
    }

    public function postAddSeverityToCharacteristic($characteristic)
    {
        try
        {
            // Declare the rules for the form validation
            $rules = array(
                'minimum_severity_value' => 'required|integer|between:0,65535',
                'maximum_severity_value' => 'required|integer|between:0,65535',
                'severity_prefix_units'  => 'max:16',
                'severity_suffix_units'  => 'max:16',
            );

            // Create a new validator instance from our validation rules
            $validator = Validator::make(Input::all(), $rules);
        
            $validator->sometimes(['minimum_age_to_express', 'maximum_age_to_express'], 'required|integer|between:0,65535', function($input)
            {
                return ($input->severity_can_be_expressed === 'yes');
            });
        
            $validator->sometimes(['minimum_age_to_reveal_severity_value', 'maximum_age_to_reveal_severity_value'], 'required|integer|between:0,65535', function($input)
            {
                return ($input->severity_value_can_be_revealed === 'yes');
            });

            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->withInput()->with('error', $validator->errors()->first());
            }

            $characteristicSeverity = null;

            DB::transaction(function() use ($characteristic, &$characteristicSeverity)
            {
                // Get the values
                $minValue = Input::get('minimum_severity_value');
                $maxValue = Input::get('maximum_severity_value');
                $minAgeToExpress = Input::get('minimum_age_to_express');
                $maxAgeToExpress = Input::get('maximum_age_to_express');
                $minAgeToRevealValue = Input::get('minimum_age_to_reveal_severity_value');
                $maxAgeToRevealValue = Input::get('maximum_age_to_reveal_severity_value');

                if (Floats::compare($minValue, $maxValue, '>'))
                {
                    $temp = $minValue;
                    $minValue = $maxValue;
                    $maxValue = $temp;
                }

                if (Floats::compare($minAgeToExpress, $maxAgeToExpress, '>'))
                {
                    $temp = $minAgeToExpress;
                    $minAgeToExpress = $maxAgeToExpress;
                    $maxAgeToExpress = $temp;
                }

                if (Floats::compare($minAgeToRevealValue, $maxAgeToRevealValue, '>'))
                {
                    $temp = $minAgeToRevealValue;
                    $minAgeToRevealValue = $maxAgeToRevealValue;
                    $maxAgeToRevealValue = $temp;
                }

                // Add the severity
                $characteristicSeverity = CharacteristicSeverity::create(array(
                    'characteristic_id' => $characteristic->id, 
                    'min_value' => $minValue, 
                    'max_value' => $maxValue, 
                    'can_be_expressed'   => (Input::get('severity_can_be_expressed') === 'yes'), 
                    'min_age_to_express' => $minAgeToExpress, 
                    'max_age_to_express' => $maxAgeToExpress, 
                    'value_can_be_revealed'   => (Input::get('severity_value_can_be_revealed') === 'yes'), 
                    'min_age_to_reveal_value' => $minAgeToRevealValue, 
                    'max_age_to_reveal_value' => $maxAgeToRevealValue, 
                    'prefix_units' => Input::get('severity_prefix_units'), 
                    'suffix_units' => Input::get('severity_suffix_units'), 
                ));

                // Add it to all breeds characteristics that belong to this characteristic
                $breedCharacteristics = BreedCharacteristic::where('characteristic_id', $characteristic->id)->get();

                foreach($breedCharacteristics as $breedCharacteristic)
                {
                    BreedCharacteristicSeverity::create(array(
                        'breed_characteristic_id'    => $breedCharacteristic->id, 
                        'characteristic_severity_id' => $characteristicSeverity->id, 
                        'min_age_to_express' => $characteristicSeverity->min_age_to_express, 
                        'max_age_to_express' => $characteristicSeverity->max_age_to_express, 
                        'min_age_to_reveal_value' => $characteristicSeverity->min_age_to_reveal_value, 
                        'max_age_to_reveal_value' => $characteristicSeverity->max_age_to_reveal_value, 
                    ));
                }
            });

            $success = Lang::get('forms/admin.add_severity_to_characteristic.success');

            return Redirect::route('admin/characteristics/characteristic/severity/edit', $characteristicSeverity->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.add_severity_to_characteristic.error');
        }

        return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->with('error', $error);
    }

    public function postUpdateCharacteristicSeverity($characteristicSeverity)
    {
        try
        {
            // Declare the rules for the form validation
            $rules = array(
                'minimum_severity_value' => 'required|integer|between:0,65535',
                'maximum_severity_value' => 'required|integer|between:0,65535',
                'severity_prefix_units'  => 'max:16',
                'severity_suffix_units'  => 'max:16',
            );

            // Create a new validator instance from our validation rules
            $validator = Validator::make(Input::all(), $rules);
        
            $validator->sometimes(['minimum_age_to_express', 'maximum_age_to_express'], 'required|integer|between:0,65535', function($input)
            {
                return ($input->severity_can_be_expressed === 'yes');
            });
        
            $validator->sometimes(['minimum_age_to_reveal_severity_value', 'maximum_age_to_reveal_severity_value'], 'required|integer|between:0,65535', function($input)
            {
                return ($input->severity_value_can_be_revealed === 'yes');
            });

            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::route('admin/characteristics/characteristic/severity/edit', $characteristicSeverity->id)->withInput()->with('error', $validator->errors()->first());
            }

            DB::transaction(function() use ($characteristicSeverity)
            {
                // Save the original values
                $originalValues = $characteristicSeverity->toArray();

                // Get the values
                $minValue = Input::get('minimum_severity_value');
                $maxValue = Input::get('maximum_severity_value');
                $minAgeToExpress = Input::get('minimum_age_to_express');
                $maxAgeToExpress = Input::get('maximum_age_to_express');
                $minAgeToRevealValue = Input::get('minimum_age_to_reveal_severity_value');
                $maxAgeToRevealValue = Input::get('maximum_age_to_reveal_severity_value');

                if (Floats::compare($minValue, $maxValue, '>'))
                {
                    $temp = $minValue;
                    $minValue = $maxValue;
                    $maxValue = $temp;
                }

                if (Floats::compare($minAgeToExpress, $maxAgeToExpress, '>'))
                {
                    $temp = $minAgeToExpress;
                    $minAgeToExpress = $maxAgeToExpress;
                    $maxAgeToExpress = $temp;
                }

                if (Floats::compare($minAgeToRevealValue, $maxAgeToRevealValue, '>'))
                {
                    $temp = $minAgeToRevealValue;
                    $minAgeToRevealValue = $maxAgeToRevealValue;
                    $maxAgeToRevealValue = $temp;
                }

                // Update the severity
                $characteristicSeverity->min_value = $minValue;
                $characteristicSeverity->max_value = $maxValue;
                $characteristicSeverity->can_be_expressed   = (Input::get('severity_can_be_expressed') === 'yes');
                $characteristicSeverity->min_age_to_express = $minAgeToExpress;
                $characteristicSeverity->max_age_to_express = $maxAgeToExpress;
                $characteristicSeverity->value_can_be_revealed   = (Input::get('severity_value_can_be_revealed') === 'yes');
                $characteristicSeverity->min_age_to_reveal_value = $minAgeToRevealValue;
                $characteristicSeverity->max_age_to_reveal_value = $maxAgeToRevealValue;
                $characteristicSeverity->prefix_units = Input::get('severity_prefix_units');
                $characteristicSeverity->suffix_units = Input::get('severity_suffix_units');
                $characteristicSeverity->save();

                // Get all breed characteristic severities that belong to this characteristic severity
                $breedCharacteristicSeverities = BreedCharacteristicSeverity::where('characteristic_severity_id', $characteristicSeverity->id)->get();

                foreach($breedCharacteristicSeverities as $breedCharacteristicSeverity)
                {
                    $breedCharacteristicSeverity->min_age_to_express = Floats::normalizeValueInRange(
                        $breedCharacteristicSeverity->min_age_to_express, 
                        [ $originalValues['min_age_to_express'], $originalValues['max_age_to_express'] ], 
                        [ $characteristicSeverity->min_age_to_express, $characteristicSeverity->max_age_to_express ]
                    );
                        
                    $breedCharacteristicSeverity->max_age_to_express = Floats::normalizeValueInRange(
                        $breedCharacteristicSeverity->max_age_to_express, 
                        [ $originalValues['min_age_to_express'], $originalValues['max_age_to_express'] ], 
                        [ $characteristicSeverity->min_age_to_express, $characteristicSeverity->max_age_to_express ]
                    );
                        
                    $breedCharacteristicSeverity->min_age_to_reveal_value = Floats::normalizeValueInRange(
                        $breedCharacteristicSeverity->min_age_to_reveal_value, 
                        [ $originalValues['min_age_to_reveal_value'], $originalValues['max_age_to_reveal_value'] ], 
                        [ $characteristicSeverity->min_age_to_reveal_value, $characteristicSeverity->max_age_to_reveal_value ]
                    );
                        
                    $breedCharacteristicSeverity->max_age_to_reveal_value = Floats::normalizeValueInRange(
                        $breedCharacteristicSeverity->max_age_to_reveal_value, 
                        [ $originalValues['min_age_to_reveal_value'], $originalValues['max_age_to_reveal_value'] ], 
                        [ $characteristicSeverity->min_age_to_reveal_value, $characteristicSeverity->max_age_to_reveal_value ]
                    );

                    $breedCharacteristicSeverity->save();
                }
            });

            $success = Lang::get('forms/admin.update_characteristic_severity.success');

            return Redirect::route('admin/characteristics/characteristic/severity/edit', $characteristicSeverity->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_characteristic_severity.error');
        }

        return Redirect::route('admin/characteristics/characteristic/severity/edit', $characteristicSeverity->id)->with('error', $error);
    }

    public function getRemoveSeverityFromCharacteristic($characteristicSeverity)
    {
        try
        {
            // Grab the characteristic
            $characteristic = $characteristicSeverity->characteristic;

            $characteristicSeverity->delete();

            $success = Lang::get('forms/admin.remove_severity_from_characteristic.success');

            return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.remove_severity_from_characteristic.error');
        }

        return Redirect::route('admin/characteristics/characteristic/edit', $characteristic->id)->with('error', $error);
    }

    public function postAddSymptomToCharacteristicSeverity($characteristicSeverity)
    {
        try
        {
            // Declare the rules for the form validation
            $rules = array(
                'symptom' => 'required|exists:symptoms,id',
                'minimum_offset_age_to_express' => 'required|integer|between:0,65535',
                'maximum_offset_age_to_express' => 'required|integer|between:0,65535',
            );

            // Create a new validator instance from our validation rules
            $validator = Validator::make(Input::all(), $rules);
        
            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::route('admin/characteristics/characteristic/severity/edit', $characteristicSeverity->id)->withInput()->with('error', $validator->errors()->first());
            }

            // Get the values
            $minOffsetAgeToExpress = Input::get('minimum_offset_age_to_express');
            $maxOffsetAgeToExpress = Input::get('maximum_offset_age_to_express');

            if (Floats::compare($minOffsetAgeToExpress, $maxOffsetAgeToExpress, '>'))
            {
                $temp = $minOffsetAgeToExpress;
                $minOffsetAgeToExpress = $maxOffsetAgeToExpress;
                $maxOffsetAgeToExpress = $temp;
            }

            // Add the symptom
            CharacteristicSeveritySymptom::create(array(
                'severity_id' => $characteristicSeverity->id, 
                'symptom_id'  => Input::get('symptom'), 
                'min_offset_age_to_express' => $minOffsetAgeToExpress, 
                'max_offset_age_to_express' => $maxOffsetAgeToExpress, 
                'lethal' => (Input::get('lethal') === 'yes'), 
            ));

            $success = Lang::get('forms/admin.add_symptom_to_characteristic_severity.success');

            return Redirect::route('admin/characteristics/characteristic/severity/edit', $characteristicSeverity->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.add_symptom_to_characteristic_severity.error');
        }

        return Redirect::route('admin/characteristics/characteristic/severity/edit', $characteristicSeverity->id)->with('error', $error);
    }

    public function postUpdateCharacteristicSeveritySymptom($characteristicSeveritySymptom)
    {
        // Grab the characteristic severity
        $characteristicSeverity = $characteristicSeveritySymptom->characteristicSeverity;

        try
        {
            // Declare the rules for the form validation
            $rules = array(
                'existing_minimum_offset_age_to_express' => 'required|integer|between:0,65535',
                'existing_maximum_offset_age_to_express' => 'required|integer|between:0,65535',
            );

            // Create a new validator instance from our validation rules
            $validator = Validator::make(Input::all(), $rules);
        
            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::route('admin/characteristics/characteristic/severity/edit', $characteristicSeverity->id)->withInput()->with('error', $validator->errors()->first());
            }

            // Get the values
            $minOffsetAgeToExpress = Input::get('existing_minimum_offset_age_to_express');
            $maxOffsetAgeToExpress = Input::get('existing_maximum_offset_age_to_express');

            if (Floats::compare($minOffsetAgeToExpress, $maxOffsetAgeToExpress, '>'))
            {
                $temp = $minOffsetAgeToExpress;
                $minOffsetAgeToExpress = $maxOffsetAgeToExpress;
                $maxOffsetAgeToExpress = $temp;
            }

            // Update the symptom
            $characteristicSeveritySymptom->min_offset_age_to_express = $minOffsetAgeToExpress;
            $characteristicSeveritySymptom->max_offset_age_to_express = $maxOffsetAgeToExpress;
            $characteristicSeveritySymptom->lethal = (Input::get('lethal') === 'yes');
            $characteristicSeveritySymptom->save();

            $success = Lang::get('forms/admin.update_characteristic_severity_symptom.success');

            return Redirect::route('admin/characteristics/characteristic/severity/edit', $characteristicSeverity->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_characteristic_severity_symptom.error');
        }

        return Redirect::route('admin/characteristics/characteristic/severity/edit', $characteristicSeverity->id)->with('error', $error);
    }

    public function getRemoveSymptomFromCharacteristicSeverity($characteristicSeveritySymptom)
    {
        try
        {
            // Grab the characteristic severity
            $characteristicSeverity = $characteristicSeveritySymptom->characteristicSeverity;

            $characteristicSeveritySymptom->delete();

            $success = Lang::get('forms/admin.remove_symptom_from_characteristic_severity.success');

            return Redirect::route('admin/characteristics/characteristic/severity/edit', $characteristicSeverity->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.remove_symptom_from_characteristic_severity.error');
        }

        return Redirect::route('admin/characteristics/characteristic/severity/edit', $characteristicSeverity->id)->with('error', $error);
    }

    public function getRemoveChildCharacteristicCategoryFromParentCharacteristicCategory($parentCharacteristicCategory, $childCharacteristicCategory)
    {
        try
        {
            if ($childCharacteristicCategory->parent_category_id == $parentCharacteristicCategory->id)
            {
                $childCharacteristicCategory->parent_category_id = null;
                $childCharacteristicCategory->save();
            }

            $success = Lang::get('forms/admin.remove_child_characteristic_category_from_parent_characteristic_category.success');

            return Redirect::route('admin/characteristics/category/edit', $parentCharacteristicCategory->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.remove_child_characteristic_category_from_parent_characteristic_category.error');
        }

        return Redirect::route('admin/characteristics/category/edit', $parentCharacteristicCategory->id)->with('error', $error);
    }

    public function getRemoveCharacteristicFromCharacteristicCategory($characteristicCategory, $characteristic)
    {
        try
        {
            if ($characteristic->characteristic_category_id == $characteristicCategory->id)
            {
                $characteristic->characteristic_category_id = null;
                $characteristic->save();
            }

            $success = Lang::get('forms/admin.remove_characteristic_from_characteristic_category.success');

            return Redirect::route('admin/characteristics/category/edit', $characteristicCategory->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.remove_characteristic_from_characteristic_category.error');
        }

        return Redirect::route('admin/characteristics/category/edit', $characteristicCategory->id)->with('error', $error);
    }

    public function postAddIndependentCharacteristicToCharacteristicDependency($characteristicDependency)
    {
        try
        {
            // Grab potential characteristic IDs
            $potentialIndependentCharacteristicIds = (array) Input::get('independent_characteristics');

            // Find the valid ones
            $usedCharacteristicsIds = CharacteristicDependencyIndependentCharacteristic::where('characteristic_dependency_id', $characteristicDependency->id)->lists('independent_characteristic_id');

            // Always add the dependent characteristic
            $usedCharacteristicsIds[] = $characteristicDependency->dependent_id;

            // Grab only appropriate characteristics
            $allCharacteristics = Characteristic::whereNotIn('id', $usedCharacteristicsIds)->get();

            $validCharacteristicIds = $allCharacteristics->filter(function($characteristic) use ($characteristicDependency)
                {
                    return $characteristicDependency->takesInRanged()
                        ? $characteristic->isRanged()
                        : $characteristic->isGenetic();
                })
                ->lists('id');

            $intersect = array_intersect($potentialIndependentCharacteristicIds, $validCharacteristicIds);

            if ( ! empty($intersect))
            {
                if ($characteristicDependency->needsRangedPercents())
                {
                    $minPercent = 0.00;
                    $maxPercent = 100.00;
                }
                else
                {
                    $minPercent = null;
                    $maxPercent = null;
                }

                // Turn them into characteristics
                $potentialIndependentCharacteristics = Characteristic::whereIn('id', $intersect)
                    ->get()
                    ->filter(function($item) use ($characteristicDependency)
                        {
                            return $characteristicDependency->validIndependentCharacteristic($item);
                        });

                // Attach the valid ones
                foreach($potentialIndependentCharacteristics as $characteristic)
                {
                    $independentCharacteristic = CharacteristicDependencyIndependentCharacteristic::create(array(
                        'characteristic_dependency_id'  => $characteristicDependency->id, 
                        'independent_characteristic_id' => $characteristic->id, 
                        'min_percent' => $minPercent, 
                        'max_percent' => $maxPercent, 
                    ));
                }
            }

            $success = Lang::get('forms/admin.add_independent_characteristic_to_characteristic_dependency.success');

            return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.add_independent_characteristic_to_characteristic_dependency.error');
        }

        return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('error', $error);
    }

    public function getRemoveCharacteristicDependencyIndependentCharacteristic($characteristicDependencyIndependentCharacteristic)
    {
        try
        {
            // Grab the dependency
            $characteristicDependency = $characteristicDependencyIndependentCharacteristic->dependency;

            $characteristicDependencyIndependentCharacteristic->delete();

            $success = Lang::get('forms/admin.remove_characteristic_dependency_independent_characteristic.success');

            return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.remove_characteristic_dependency_independent_characteristic.error');
        }

        return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('error', $error);
    }

    public function postUpdateCharacteristicDependendencyIndependentCharacteristicPercents($characteristicDependencyIndependentCharacteristic)
    {
        // Grab the dependency
        $characteristicDependency = $characteristicDependencyIndependentCharacteristic->dependency;

        // Declare the rules for the form validation
        $rules = array(
            'minimum_percent' => 'required|numeric|min:0.00|max:100.00',
            'maximum_percent' => 'required|numeric|min:0.00|max:100.00',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            if ( ! $characteristicDependency->needsRangedPercents())
            {
                throw new DynastyCharacteristicDependenciesExceptions\InvalidRangedPercentsException;
            }

            $minPercent = Input::get('minimum_percent');
            $maxPercent = Input::get('maximum_percent');

            if (Floats::compare($minPercent, $maxPercent, '>'))
            {
                $temp       = $minPercent;
                $minPercent = $maxPercent;
                $maxPercent = $temp;
            }

            $characteristicDependencyIndependentCharacteristic->min_percent = $minPercent;
            $characteristicDependencyIndependentCharacteristic->max_percent = $maxPercent;
            $characteristicDependencyIndependentCharacteristic->save();

            $success = Lang::get('forms/admin.update_characteristic_dependency_independent_characteristic_percents.success');

            return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('success', $success);
        }
        catch(DynastyCharacteristicDependenciesExceptions\InvalidRangedPercentsException $e)
        {
            $error = Lang::get('forms/admin.update_characteristic_dependency_independent_characteristic_percents.invalid_percents');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_characteristic_dependency_independent_characteristic_percents.error');
        }

        return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('error', $error);
    }

    public function postCreateG2RCharacteristicDependencyGroup($characteristicDependency)
    {
        // Grab the characteristic
        $characteristic = $characteristicDependency->characteristic;

        // Declare the rules for the form validation
        $rules = array(
            'identifier'    => 'required|max:32',
            'minimum_value' => 'required|numeric|min:'.$characteristic->min_ranged_value.'|max:'.$characteristic->max_ranged_value,
            'maximum_value' => 'required|numeric|min:'.$characteristic->min_ranged_value.'|max:'.$characteristic->max_ranged_value,
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            if ( ! $characteristicDependency->isG2R())
            {
                throw new DynastyCharacteristicDependenciesExceptions\InvalidTypeException;
            }

            DB::transaction(function($query) use ($characteristicDependency)
            {
                // Create the group
                $characteristicDependencyGroup = CharacteristicDependencyGroup::create(array(
                    'characteristic_dependency_id'  => $characteristicDependency->id, 
                    'identifier' => Input::get('identifier'), 
                ));

                // Get the values
                $minValue = Input::get('minimum_value');
                $maxValue = Input::get('maximum_value');

                if (Floats::compare($minValue, $maxValue, '>'))
                {
                    $temp     = $minValue;
                    $minValue = $maxValue;
                    $maxValue = $temp;
                }

                // Add the range component
                $characteristicDependencyGroupRange = CharacteristicDependencyGroupRange::create(array(
                    'characteristic_dependency_group_id' => $characteristicDependencyGroup->id, 
                    'min_value' => $minValue, 
                    'max_value' => $maxValue, 
                ));
            });

            $success = Lang::get('forms/admin.create_g2r_characteristic_dependency_group.success');

            return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('success', $success);
        }
        catch(DynastyCharacteristicDependenciesExceptions\InvalidTypeException $e)
        {
            $error = Lang::get('forms/admin.create_g2r_characteristic_dependency_group.invalid_type');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.create_g2r_characteristic_dependency_group.error');
        }

        return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('error', $error);
    }

    public function postAddRangeToCharacteristicDependencyGroup($characteristicDependencyGroup)
    {
        // Grab the characteristic dependency
        $characteristicDependency = $characteristicDependencyGroup->dependency;

        // Grab the characteristic dependency
        $characteristic = $characteristicDependency->characteristic;

        // Declare the rules for the form validation
        $rules = array(
            'minimum_value' => 'required|numeric|min:'.$characteristic->min_ranged_value.'|max:'.$characteristic->max_ranged_value,
            'maximum_value' => 'required|numeric|min:'.$characteristic->min_ranged_value.'|max:'.$characteristic->max_ranged_value,
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            if ( ! $characteristicDependency->isG2R())
            {
                throw new DynastyCharacteristicDependenciesExceptions\InvalidTypeException;
            }

            DB::transaction(function($query) use ($characteristicDependencyGroup)
            {
                // Get the values
                $minValue = Input::get('minimum_value');
                $maxValue = Input::get('maximum_value');

                if (Floats::compare($minValue, $maxValue, '>'))
                {
                    $temp     = $minValue;
                    $minValue = $maxValue;
                    $maxValue = $temp;
                }

                // Add the range component
                $characteristicDependencyGroupRange = CharacteristicDependencyGroupRange::create(array(
                    'characteristic_dependency_group_id' => $characteristicDependencyGroup->id, 
                    'min_value' => $minValue, 
                    'max_value' => $maxValue, 
                ));
            });

            $success = Lang::get('forms/admin.add_range_to_characteristic_dependency_group.success');

            return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('success', $success);
        }
        catch(DynastyCharacteristicDependenciesExceptions\InvalidTypeException $e)
        {
            $error = Lang::get('forms/admin.add_range_to_characteristic_dependency_group.invalid_type');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.add_range_to_characteristic_dependency_group.error');
        }

        return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('error', $error);
    }

    public function getRemoveRangeFromCharacteristicDependencyGroup($characteristicDependencyGroupRange)
    {
        try
        {
            // Grab the dependency
            $characteristicDependency = $characteristicDependencyGroupRange->group->dependency;

            // Remove the range
            $characteristicDependencyGroupRange->delete();

            $success = Lang::get('forms/admin.remove_range_from_characteristic_dependency_group.success');

            return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.remove_range_from_characteristic_dependency_group.error');
        }

        return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('error', $error);
    }

    public function geteDeleteCharacteristicDependencyGroup($characteristicDependencyGroup)
    {
        try
        {
            // Grab the dependency
            $characteristicDependency = $characteristicDependencyGroup->dependency;

            // Delete the group
            $characteristicDependencyGroup->delete();

            $success = Lang::get('forms/admin.delete_characteristic_dependency_group.success');

            return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.delete_characteristic_dependency_group.error');
        }

        return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('error', $error);
    }

    public function postUpdateX2GCharacteristicDependencyGroup($characteristicDependencyGroup)
    {
        // Grab the characteristic
        $characteristicDependency = $characteristicDependencyGroup->dependency;

        // Declare the rules for the form validation
        $rules = array(
            'identifier' => 'required|max:32',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            if ( ! $characteristicDependency->outputsGenotypes())
            {
                throw new DynastyCharacteristicDependenciesExceptions\InvalidTypeException;
            }

            // Update the identifier
            $characteristicDependencyGroup->identifier = Input::get('identifier');
            $characteristicDependencyGroup->save();

            $success = Lang::get('forms/admin.update_x2g_characteristic_dependency_group.success');

            return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('success', $success);
        }
        catch(DynastyCharacteristicDependenciesExceptions\InvalidTypeException $e)
        {
            $error = Lang::get('forms/admin.update_x2g_characteristic_dependency_group.invalid_type');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_x2g_characteristic_dependency_group.error');
        }

        return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('error', $error);
    }

    public function postUpdateG2XCharacteristicDependencyGroup($characteristicDependencyGroup)
    {
        // Grab the characteristic
        $characteristicDependency = $characteristicDependencyGroup->dependency;

        // Declare the rules for the form validation
        $rules = array(
            'identifier' => 'required|max:32',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            if ( ! $characteristicDependency->takesInGenotypes())
            {
                throw new DynastyCharacteristicDependenciesExceptions\InvalidTypeException;
            }

            DB::transaction(function() use ($characteristicDependencyGroup, $characteristicDependency)
            {
                // Update the identifier
                $characteristicDependencyGroup->identifier = Input::get('identifier');
                $characteristicDependencyGroup->save();

                // Remove all attached independent characteristic genotypes
                $characteristicDependencyGroup->independentCharacteristicGenotypes()->delete();

                // Grab all independent characteristics
                $independentCharacteristics = $characteristicDependency->independentCharacteristics()->with('characteristic.loci')->get();

                // Get the selected genotype IDs
                $genotypeIds = (array) Input::get('genotypes');

                // Always add -1
                $genotypeIds[] = -1;

                // Get all of the genotypes
                $genotypes = Genotype::whereIn('id', $genotypeIds)->get();

                foreach($genotypes as $genotype)
                {
                    foreach($independentCharacteristics as $independentCharacteristic)
                    {
                        if ($independentCharacteristic->characteristic->loci->contains($genotype->locus_id))
                        {
                            CharacteristicDependencyGroupIndependentCharacteristicGenotype::create(array(
                                'characteristic_dependency_group_id' => $characteristicDependencyGroup->id,
                                'characteristic_dependency_ind_characteristic_id' => $independentCharacteristic->id,
                                'genotype_id' => $genotype->id,
                            ));
                        }
                    }
                }
            });

            $success = Lang::get('forms/admin.update_g2x_characteristic_dependency_group.success');

            return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('success', $success);
        }
        catch(DynastyCharacteristicDependenciesExceptions\InvalidTypeException $e)
        {
            $error = Lang::get('forms/admin.update_g2x_characteristic_dependency_group.invalid_type');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_g2x_characteristic_dependency_group.error');
        }

        return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('error', $error);
    }

    public function postCreateX2GCharacteristicDependencyGroup($characteristicDependency)
    {
        // Grab the characteristic
        $characteristic = $characteristicDependency->characteristic;

        // Declare the rules for the form validation
        $rules = array(
            'identifier' => 'required|max:32',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            if ( ! $characteristicDependency->outputsGenotypes())
            {
                throw new DynastyCharacteristicDependenciesExceptions\InvalidTypeException;
            }

            DB::transaction(function($query) use ($characteristicDependency)
            {
                // Create the group
                $characteristicDependencyGroup = CharacteristicDependencyGroup::create(array(
                    'characteristic_dependency_id' => $characteristicDependency->id, 
                    'identifier' => Input::get('identifier'), 
                ));

                // Get the dependent characteristic locus IDs
                $dependentCharacteristicLocusIds = $characteristicDependency->characteristic->loci()->lists('id');

                // Get the selected genotype IDs
                $genotypeIds = (array) Input::get('genotypes');

                // Always add -1
                $dependentCharacteristicLocusIds[] = -1;
                $genotypeIds[] = -1;

                // Get the genotype IDs
                $validGenotypeIds = Genotype::whereIn('id', $genotypeIds)->whereIn('locus_id', $dependentCharacteristicLocusIds)->lists('id');

                // Sync the genotypes
                $characteristicDependencyGroup->genotypes()->sync($validGenotypeIds);
            });

            $success = Lang::get('forms/admin.create_x2g_characteristic_dependency_group.success');

            return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('success', $success);
        }
        catch(DynastyCharacteristicDependenciesExceptions\InvalidTypeException $e)
        {
            $error = Lang::get('forms/admin.create_x2g_characteristic_dependency_group.invalid_type');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.create_x2g_characteristic_dependency_group.error');
        }

        return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('error', $error);
    }

    public function postAddGenotypesToCharacteristicDependencyGroup($characteristicDependencyGroup)
    {
        // Grab the characteristic dependency
        $characteristicDependency = $characteristicDependencyGroup->dependency;

        try
        {
            if ( ! $characteristicDependency->outputsGenotypes())
            {
                throw new DynastyCharacteristicDependenciesExceptions\InvalidTypeException;
            }

            DB::transaction(function($query) use ($characteristicDependencyGroup, $characteristicDependency)
            {
                // Get the dependent characteristic locus IDs
                $dependentCharacteristicLocusIds = $characteristicDependency->characteristic->loci()->lists('id');

                // Get the selected genotype IDs
                $genotypeIds = (array) Input::get('genotypes');

                // Always add -1
                $dependentCharacteristicLocusIds[] = -1;
                $genotypeIds[] = -1;

                // Get the genotype IDs
                $validGenotypeIds = Genotype::whereIn('id', $genotypeIds)->whereIn('locus_id', $dependentCharacteristicLocusIds)->lists('id');

                // Sync the genotypes
                $characteristicDependencyGroup->genotypes()->sync($validGenotypeIds);
            });

            $success = Lang::get('forms/admin.add_genotypes_to_characteristic_dependency_group.success');

            return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('success', $success);
        }
        catch(DynastyCharacteristicDependenciesExceptions\InvalidTypeException $e)
        {
            $error = Lang::get('forms/admin.add_genotypes_to_characteristic_dependency_group.invalid_type');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.add_genotypes_to_characteristic_dependency_group.error');
        }

        return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('error', $error);
    }

    public function postAddIndependentRangeToCharacteristicDependencyGroup($characteristicDependencyGroup)
    {
        try
        {
            // Grab the characteristic dependency
            $characteristicDependency = $characteristicDependencyGroup->dependency;

            // Grab the independeny characteristic
            $characteristicDependencyIndependentCharacteristic = CharacteristicDependencyIndependentCharacteristic::find(Input::get('independent_characteristic'));

            if (is_null($characteristicDependencyIndependentCharacteristic) or ! $characteristicDependency->independentCharacteristics->contains($characteristicDependencyIndependentCharacteristic->id))
            {
                throw DynastyCharacteristicDependencyIndependentCharacteristicExceptions\NotFoundException;
            }

            // Grab the characteristic from the characteristic dependency independent characteristic
            $independentCharacteristic = $characteristicDependencyIndependentCharacteristic->characteristic;

            // Declare the rules for the form validation
            $rules = array(
                'minimum_value' => 'required|numeric|min:'.$independentCharacteristic->min_ranged_value.'|max:'.$independentCharacteristic->max_ranged_value,
                'maximum_value' => 'required|numeric|min:'.$independentCharacteristic->min_ranged_value.'|max:'.$independentCharacteristic->max_ranged_value,
            );

            // Create a new validator instance from our validation rules
            $validator = Validator::make(Input::all(), $rules);

            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->withInput()->with('error', $validator->errors()->first());
            }

            if ( ! $characteristicDependency->isR2G())
            {
                throw new DynastyCharacteristicDependenciesExceptions\InvalidTypeException;
            }

            // Get the values
            $minValue = Input::get('minimum_value');
            $maxValue = Input::get('maximum_value');

            if (Floats::compare($minValue, $maxValue, '>'))
            {
                $temp     = $minValue;
                $minValue = $maxValue;
                $maxValue = $temp;
            }

            // Add the range component
            CharacteristicDependencyGroupIndependentCharacteristicRange::create(array(
                'characteristic_dependency_group_id' => $characteristicDependencyGroup->id, 
                'characteristic_dependency_ind_characteristic_id' => $characteristicDependencyIndependentCharacteristic->id, 
                'min_value' => $minValue, 
                'max_value' => $maxValue, 
            ));

            $success = Lang::get('forms/admin.add_independent_range_to_characteristic_dependency_group.success');

            return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('success', $success);
        }
        catch(DynastyCharacteristicDependencyIndependentCharacteristicExceptions\NotFoundException $e)
        {
            $error = Lang::get('forms/admin.add_independent_range_to_characteristic_dependency_group.invalid_independent_characteristic');
        }
        catch(DynastyCharacteristicDependenciesExceptions\InvalidTypeException $e)
        {
            $error = Lang::get('forms/admin.add_independent_range_to_characteristic_dependency_group.invalid_type');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.add_independent_range_to_characteristic_dependency_group.error');
        }

        return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('error', $error);
    }

    public function getRemoveIndependentRangeFromCharacteristicDependencyGroup($characteristicDependencyGroupIndependentCharacteristicRange)
    {
        try
        {
            // Grab the dependency
            $characteristicDependency = $characteristicDependencyGroupIndependentCharacteristicRange->group->dependency;

            // Remove the range
            $characteristicDependencyGroupIndependentCharacteristicRange->delete();

            $success = Lang::get('forms/admin.remove_independent_range_from_characteristic_dependency_group.success');

            return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.remove_independent_range_from_characteristic_dependency_group.error');
        }

        return Redirect::route('admin/characteristics/dependency/edit', $characteristicDependency->id)->with('error', $error);
    }

}
