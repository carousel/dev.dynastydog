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
use Str;
use Locus;
use LocusAllele;
use Genotype;
use Phenotype;
use Breed;
use Exception;
use Dynasty\LocusAlleles\Exceptions as DynastyLocusAllelesExceptions;

class GeneticsController extends AdminController {

    public function __construct()
    {
        parent::__construct();

        $this->sidebarGroups = array(
            array(
                'heading' => 'Loci', 
                'items' => array(
                    array(
                        'title' => 'New', 
                        'url' => URL::route('admin/genetics/locus/create'), 
                    ), 
                    array(
                        'title' => 'Existing', 
                        'url' => URL::route('admin/genetics'), 
                    ), 
                ), 
            ),
            array(
                'heading' => 'Alleles', 
                'items' => array(
                    array(
                        'title' => 'New', 
                        'url' => URL::route('admin/genetics/locus/allele/create'), 
                    ), 
                    array(
                        'title' => 'Existing', 
                        'url' => URL::route('admin/genetics/locus/alleles'), 
                    ), 
                ), 
            ),
            array(
                'heading' => 'Genotypes', 
                'items' => array(
                    array(
                        'title' => 'New', 
                        'url' => URL::route('admin/genetics/genotype/create'), 
                    ), 
                    array(
                        'title' => 'Existing', 
                        'url' => URL::route('admin/genetics/genotypes'), 
                    ), 
                ), 
            ),
            array(
                'heading' => 'Phenotypes', 
                'items' => array(
                    array(
                        'title' => 'New', 
                        'url' => URL::route('admin/genetics/phenotype/create'), 
                    ), 
                    array(
                        'title' => 'Existing', 
                        'url' => URL::route('admin/genetics/phenotypes'), 
                    ), 
                ), 
            ),
        );
    }

    public function getIndex()
    {
        $results = new Locus;

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

        $loci = $results->orderBy('name', 'asc')->paginate();

        // Show the page
        return View::make('admin/genetics/index', compact('loci'));
    }

    public function getLocusAlleles()
    {
        $results = LocusAllele::select('locus_alleles.*');

        if (Input::get('search'))
        {
            $id     = Input::get('id');
            $symbol = Input::get('symbol');

            if (strlen($id) > 0)
            {
                $results = $results->where('locus_alleles.id', $id);
            }

            if (strlen($symbol) > 0)
            {
                $results = $results->where('locus_alleles.symbol', 'LIKE', '%'.$symbol.'%');
            }
        }

        $locusAlleles = $results
            ->join('loci', 'loci.id', '=', 'locus_alleles.locus_id')
            ->orderBy('loci.name', 'asc')
            ->orderBy('locus_alleles.symbol', 'asc')
            ->paginate();

        // Show the page
        return View::make('admin/genetics/locus_alleles', compact('locusAlleles'));
    }

    public function getGenotypes()
    {
        $results = Genotype::select('genotypes.*');

        if (Input::get('search'))
        {
            $id       = Input::get('id');
            $sequence = Input::get('sequence');

            if (strlen($id) > 0)
            {
                $results = $results->where('genotypes.id', $id);
            }

            if (strlen($sequence) > 0)
            {
                $results
                    ->where(DB::raw("CONCAT(allele_a.symbol, allele_b.symbol)"), 'LIKE', '%'.$sequence.'%');
            }
        }

        $genotypes = $results
            ->join('loci', 'loci.id', '=', 'genotypes.locus_id')
            ->orderBy('loci.name', 'asc')
            ->orderByAlleles()
            ->paginate();

        // Show the page
        return View::make('admin/genetics/genotypes', compact('genotypes'));
    }

    public function getPhenotypes()
    {
        $results = new Phenotype;

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

        $phenotypes = $results->orderBy('name', 'asc')->orderBy('id', 'asc')->paginate();

        // Show the page
        return View::make('admin/genetics/phenotypes', compact('phenotypes'));
    }

    public function getCreateLocus()
    {
        // Show the page
        return View::make('admin/genetics/create_locus');
    }

    public function getCreateLocusAllele()
    {
        // Grab all loci
        $loci = Locus::orderBy('name', 'asc')->get();

        // Show the page
        return View::make('admin/genetics/create_locus_allele', compact('loci'));
    }

    public function getCreateGenotype()
    {
        // Get the loci
        $loci = Locus::with(array(
                    'alleles' => function($query)
                        {
                            $query->orderBy('symbol', 'asc');
                        }
                ))
            ->has('alleles')
            ->orderBy('name', 'asc')
            ->get();

        // Show the page
        return View::make('admin/genetics/create_genotype', compact('loci'));
    }

    public function getCreatePhenotype()
    {
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

        // Show the page
        return View::make('admin/genetics/create_phenotype', compact('loci'));
    }

    public function getEditLocus($locus)
    {
        // Show the page
        return View::make('admin/genetics/edit_locus', compact('locus'));
    }

    public function getEditLocusAllele($locusAllele)
    {
        // Grab all loci
        $loci = Locus::orderBy('name', 'asc')->get();

        // Show the page
        return View::make('admin/genetics/edit_locus_allele', compact('locusAllele', 'loci'));
    }

    public function getEditGenotype($genotype)
    {
        // Get the loci
        $loci = Locus::with(array(
                    'alleles' => function($query)
                        {
                            $query->orderBy('symbol', 'asc');
                        }
                ))
            ->has('alleles')
            ->orderBy('name', 'asc')
            ->get();

        // Show the page
        return View::make('admin/genetics/edit_genotype', compact('genotype', 'loci'));
    }

    public function getEditPhenotype($phenotype)
    {
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

        // Show the page
        return View::make('admin/genetics/edit_phenotype', compact('phenotype', 'loci'));
    }

    public function getDeleteLocus($locus)
    {
        try
        {
            $locus->delete();

            $success = Lang::get('forms/admin.delete_locus.success');

            return Redirect::route('admin/genetics')->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.delete_locus.error');
        }

        return Redirect::route('admin/genetics/locus/edit', $locus->id)->withInput()->with('error', $error);
    }

    public function getDeleteLocusAllele($locusAllele)
    {
        try
        {
            $locusAllele->delete();

            $success = Lang::get('forms/admin.delete_locus_allele.success');

            return Redirect::route('admin/genetics/locus/alleles')->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.delete_locus_allele.error');
        }

        return Redirect::route('admin/genetics/locus/allele/edit', $locusAllele->id)->withInput()->with('error', $error);
    }

    public function getDeleteGenotype($genotype)
    {
        try
        {
            $genotype->delete();

            $success = Lang::get('forms/admin.delete_genotype.success');

            return Redirect::route('admin/genetics/genotypes')->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.delete_genotype.error');
        }

        return Redirect::route('admin/genetics/genotype/edit', $genotype->id)->withInput()->with('error', $error);
    }

    public function getDeletePhenotype($phenotype)
    {
        try
        {
            $phenotype->delete();

            $success = Lang::get('forms/admin.delete_phenotype.success');

            return Redirect::route('admin/genetics/phenotypes')->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.delete_phenotype.error');
        }

        return Redirect::route('admin/genetics/phenotype/edit', $phenotype->id)->withInput()->with('error', $error);
    }

    public function postCreateLocus()
    {
        // Declare the rules for the form validation
        $rules = array(
            'name' => 'required|max:32|unique:loci,name',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/genetics/locus/create')->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            // Create the locus
            $locus = Locus::create(array( 
                'name'   => Input::get('name'), 
                'active' => (Input::get('active') === 'yes'), 
            ));

            $success = Lang::get('forms/admin.create_locus.success');

            return Redirect::route('admin/genetics/locus/edit', $locus->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.create_locus.error');
        }

        return Redirect::route('admin/genetics/locus/create')->withInput()->with('error', $error);
    }

    public function postCreateLocusAllele()
    {
        // Declare the rules for the form validation
        $rules = array(
            'locus'  => 'required|exists:loci,id',
            'symbol' => 'required|max:4',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/genetics/locus/allele/create')->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            // Create the locus allele
            $locusAllele = LocusAllele::create(array( 
                'locus_id' => Input::get('locus'), 
                'symbol'   => Input::get('symbol'), 
            ));

            $success = Lang::get('forms/admin.create_locus_allele.success');

            return Redirect::route('admin/genetics/locus/allele/edit', $locusAllele->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.create_locus_allele.error');
        }

        return Redirect::route('admin/genetics/locus/allele/create')->withInput()->with('error', $error);
    }

    public function postCreateGenotype()
    {
        // Declare the rules for the form validation
        $rules = array(
            'locus_allele_a' => 'required|exists:locus_alleles,id',
            'locus_allele_b' => 'required|exists:locus_alleles,id',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/genetics/genotype/create')->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $genotype = null;

            DB::transaction(function() use (&$genotype)
            {
                // Grab the alleles
                $locusAlleleA = LocusAllele::find(Input::get('locus_allele_a'));
                $locusAlleleB = LocusAllele::find(Input::get('locus_allele_b'));

                // Make sure a genotype does not already exist with these locus alleles
                if ( ! $locusAlleleA->isUniquePair($locusAlleleB))
                {
                    throw new DynastyLocusAllelesExceptions\GenotypeAlreadyExistsException;
                }

                // Make sure the loci are the same
                if ($locusAlleleA->locus_id != $locusAlleleB->locus_id)
                {
                    throw new DynastyLocusAllelesExceptions\MismatchedLocusException;
                }

                // Create the genotype
                $genotype = Genotype::create(array( 
                    'locus_id'            => $locusAlleleA->locus_id, 
                    'locus_allele_id_a'   => $locusAlleleA->id, 
                    'locus_allele_id_b'   => $locusAlleleB->id, 
                    'available_to_female' => (Input::get('available_to_female') === 'yes'), 
                    'available_to_male'   => (Input::get('available_to_male') === 'yes'), 
                ));

                // Give the genotype to all of the breeds
                $breeds = Breed::all();

                foreach($breeds as $breed)
                {
                    $breed->genotypes()->attach($genotype->id, array('frequency' => 0));
                }
            });

            $success = Lang::get('forms/admin.create_genotype.success');

            return Redirect::route('admin/genetics/genotype/edit', $genotype->id)->with('success', $success);
        }
        catch(DynastyLocusAllelesExceptions\GenotypeAlreadyExistsException $e)
        {
            $error = Lang::get('forms/admin.create_genotype.already_exists');
        }
        catch(DynastyLocusAllelesExceptions\MismatchedLocusException $e)
        {
            $error = Lang::get('forms/admin.create_genotype.mismatched_locus');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.create_genotype.error');
        }

        return Redirect::route('admin/genetics/genotype/create')->withInput()->with('error', $error);
    }

    public function postCreatePhenotype()
    {
        // Declare the rules for the form validation
        $rules = array(
            'name'     => 'required|max:64',
            'priority' => 'integer|between:0,255',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/genetics/phenotype/create')->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $phenotype = null;

            DB::transaction(function() use (&$phenotype)
            {
                // Create the phenotype
                $phenotype = Phenotype::create(array( 
                    'name'     => Input::get('name'), 
                    'priority' => Input::get('priority', 0), 
                ));

                // Grab the genotypes
                $potentialGenotypeIds = (array) Input::get('genotypes');

                // Always add -1
                $potentialGenotypeIds[] = -1;

                // Find the actual genotype to add
                $genotypeIds = Genotype::whereIn('id', $potentialGenotypeIds)->lists('id');

                // Sync them to the phenotype
                $phenotype->genotypes()->sync($genotypeIds);
            });

            $success = Lang::get('forms/admin.create_phenotype.success');

            return Redirect::route('admin/genetics/phenotype/edit', $phenotype->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.create_phenotype.error');
        }

        return Redirect::route('admin/genetics/phenotype/create')->withInput()->with('error', $error);
    }

    public function postEditLocus($locus)
    {
        // Declare the rules for the form validation
        $rules = array(
            'name' => 'required|max:32|unique:loci,name,'.$locus->id,
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/genetics/locus/edit', $locus->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $locus->name   = Input::get('name');
            $locus->active = (Input::get('active') === 'yes');
            $locus->save();

            $success = Lang::get('forms/admin.update_locus.success');

            return Redirect::route('admin/genetics/locus/edit', $locus->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_locus.error');
        }

        return Redirect::route('admin/genetics/locus/edit', $locus->id)->withInput()->with('error', $error);
    }

    public function postEditLocusAllele($locusAllele)
    {
        // Declare the rules for the form validation
        $rules = array(
            'locus'  => 'required|exists:loci,id',
            'symbol' => 'required|max:4',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/genetics/locus/allele/edit', $locusAllele->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $newLocusId = Input::get('locus');

            if ($locusAllele->locus_id != $newLocusId and $locusAllele->hasGenotypes())
            {
                throw new DynastyLocusAllelesExceptions\AffectsGenotypesException;
            }

            $locusAllele->locus_id = $newLocusId;
            $locusAllele->symbol   = Input::get('symbol');
            $locusAllele->save();

            $success = Lang::get('forms/admin.update_locus_allele.success');

            return Redirect::route('admin/genetics/locus/allele/edit', $locusAllele->id)->with('success', $success);
        }
        catch(DynastyLocusAllelesExceptions\AffectsGenotypesException $e)
        {
            $error = Lang::get('forms/admin.update_locus_allele.affects_genotypes');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_locus_allele.error');
        }

        return Redirect::route('admin/genetics/locus/edit', $locus->id)->withInput()->with('error', $error);
    }

    public function postEditPhenotype($phenotype)
    {
        // Declare the rules for the form validation
        $rules = array(
            'name'     => 'required|max:64',
            'priority' => 'integer|between:0,255',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/genetics/phenotype/edit', $phenotype->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            DB::transaction(function() use ($phenotype)
            {
                $phenotype->name     = Input::get('name');
                $phenotype->priority = Input::get('priority', 0);
                $phenotype->save();

                // Grab the genotypes
                $potentialGenotypeIds = (array) Input::get('genotypes');

                // Always add -1
                $potentialGenotypeIds[] = -1;

                // Find the actual genotype to add
                $genotypeIds = Genotype::whereIn('id', $potentialGenotypeIds)->lists('id');

                // Sync them to the phenotype
                $phenotype->genotypes()->sync($genotypeIds);
            });

            $success = Lang::get('forms/admin.update_phenotype.success');

            return Redirect::route('admin/genetics/phenotype/edit', $phenotype->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_phenotype.error');
        }

        return Redirect::route('admin/genetics/phenotype/edit', $phenotype->id)->withInput()->with('error', $error);
    }

    public function postEditGenotype($genotype)
    {
        // Declare the rules for the form validation
        $rules = array(
            'locus_allele_a' => 'required|exists:locus_alleles,id',
            'locus_allele_b' => 'required|exists:locus_alleles,id',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/genetics/genotype/edit', $genotype->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            DB::transaction(function() use ($genotype)
            {
                // Grab the alleles
                $locusAlleleA = LocusAllele::find(Input::get('locus_allele_a'));
                $locusAlleleB = LocusAllele::find(Input::get('locus_allele_b'));

                // Make sure a genotype does not already exist with these locus alleles
                if ( ! $locusAlleleA->isUniquePair($locusAlleleB, [ $genotype->id ]))
                {
                    throw new DynastyLocusAllelesExceptions\GenotypeAlreadyExistsException;
                }

                // Make sure the loci are the same
                if ($locusAlleleA->locus_id != $locusAlleleB->locus_id)
                {
                    throw new DynastyLocusAllelesExceptions\MismatchedLocusException;
                }

                $genotype->locus_id            = $locusAlleleA->locus_id;
                $genotype->locus_allele_id_a   = $locusAlleleA->id;
                $genotype->locus_allele_id_b   = $locusAlleleB->id;
                $genotype->available_to_female = (Input::get('available_to_female') === 'yes');
                $genotype->available_to_male   = (Input::get('available_to_male') === 'yes');
                $genotype->save();
            });

            $success = Lang::get('forms/admin.update_genotype.success');

            return Redirect::route('admin/genetics/genotype/edit', $genotype->id)->with('success', $success);
        }
        catch(DynastyLocusAllelesExceptions\GenotypeAlreadyExistsException $e)
        {
            $error = Lang::get('forms/admin.update_genotype.already_exists');
        }
        catch(DynastyLocusAllelesExceptions\MismatchedLocusException $e)
        {
            $error = Lang::get('forms/admin.update_genotype.mismatched_locus');
        }
        // catch(Exception $e)
        // {
        //     $error = Lang::get('forms/admin.update_genotype.error');
        // }

        return Redirect::route('admin/genetics/genotype/edit', $genotype->id)->withInput()->with('error', $error);
    }

    public function getClonePhenotype($phenotype)
    {
        try
        {
            $clonedPhenotype = null;

            DB::transaction(function() use ($phenotype, &$clonedPhenotype)
            {
                $clonedPhenotype = $phenotype->replicate();

                $name = Str::quickRandom(64);

                do
                {
                    $unique = (Phenotype::where('name', $name)->count() <= 0);
                }
                while ( ! $unique);

                $clonedPhenotype->name = $name;
                $clonedPhenotype->save();

                // Grab the phenotype's genotypes
                $genotypeIds = $phenotype->genotypes()->lists('id');

                if ( ! empty($genotypeIds))
                {
                    $clonedPhenotype->genotypes()->sync($genotypeIds);
                }
            });

            $success = Lang::get('forms/admin.clone_phenotype.success');

            return Redirect::route('admin/genetics/phenotype/edit', $clonedPhenotype->id)->with('success', $success);
        }
        catch(Exceptionsdfsdf $e)
        {
            $error = Lang::get('forms/admin.clone_phenotype.error');
        }

        return Redirect::route('admin/genetics/phenotype/edit', $phenotype->id)->with('error', $error);
    }

}
