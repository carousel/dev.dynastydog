<?php namespace Controllers\BreedRegistry;

use AuthorizedController;
use Redirect;
use View;
use Input;
use DB;
use CharacteristicCategory;
use Breed;
use Characteristic;
use BreedCharacteristic;
use Phenotype;

class SearchController extends AuthorizedController {

    public function getIndex()
    {
        // Get all characteristic categories
        $categories = CharacteristicCategory::with(array(
                'parent', 
                'characteristics' => function($query)
                    {
                        $query->whereActive()->whereVisible()->orderBy('name', 'asc');
                    }, 
            ))
            ->whereHas('characteristics', function($query)
                {
                    $query->whereActive()->whereVisible();
                })
            ->select('characteristic_categories.*')
            ->join('characteristic_categories as parent', 'parent.id', '=', 'characteristic_categories.parent_category_id')
            ->whereNotHealth()
            ->orderBy('parent.name', 'asc')
            ->orderBy('characteristic_categories.name', 'asc')
            ->get();

        $characteristicCategories = [];

        foreach($categories as $category)
        {
            $characteristicCategories[] = array(
                'name'            => $category->name, 
                'parent_name'     => $category->parent->name, 
                'characteristics' => $category->characteristics, 
            );
        }

        $searchedCharacteristics = [];

        $results = Breed::whereActive();

        if (Input::get('search'))
        {
            $name  = Input::get('name');
            $searchedCharacteristics = (array) Input::get('ch', []);

            if (strlen($name) > 0)
            {
                $results = $results->where('name', 'LIKE', '%'.$name.'%');
            }

            if ( ! empty($searchedCharacteristics))
            {
                // Get all possible searchable characteristics
                $searchableCharacteristicIds = [];

                foreach($characteristicCategories as $category)
                {
                    $searchableCharacteristicIds = array_merge($searchableCharacteristicIds, $category['characteristics']->lists('id'));
                }

                // Always add -1 in
                $knownBreedIds = [ -1 ];

                // Store the characteristics that were looked at to remove duplicates
                $lookedAtIds = [];

                // Get all breeds that satisfy ALL of the characteristics
                $characteristicsBreedIds = [];

                foreach($searchedCharacteristics as $index => $searchedCharacteristic)
                {
                    try
                    {
                        // Make sure an id was provided
                        if ( ! array_key_exists('id', $searchedCharacteristic))
                        {
                            throw new Exception;
                        }

                        // Grab the id
                        $characteristicId = $searchedCharacteristic['id'];

                        // Make sure the characteristic id could be searched
                        if ( ! in_array($characteristicId, $searchableCharacteristicIds))
                        {
                            throw new Exception;
                        }

                        // Search only unique ones
                        if (in_array($characteristicId, $lookedAtIds))
                        {
                            throw new Exception;
                        }

                        // Save it
                        $lookedAtIds[] = $characteristicId;

                        // Grab the characteristic
                        $characteristic = Characteristic::find($characteristicId);

                        // Make sure the characteristic was found
                        if (is_null($characteristic))
                        {
                            throw new Exception;
                        }

                        // Save the characteristic back onto the search for the view
                        $searchedCharacteristics[$index]['characteristic'] = $characteristic;

                        if ( ! array_key_exists('r', $searchedCharacteristic) and  ! array_key_exists('g', $searchedCharacteristic) and  ! array_key_exists('ph', $searchedCharacteristic))
                        {
                            throw new Exception;
                        }

                        // Test on range
                        if ($characteristic->isRanged())
                        {
                            if ( ! array_key_exists('r', $searchedCharacteristic))
                            {
                                throw new Exception;
                            }

                            $rangedValues = explode(',', $searchedCharacteristic['r']);

                            if (count($rangedValues) === 0)
                            {
                                $minRangedValue = $characteristic->min_ranged_value;
                                $maxRangedValue = $characteristic->max_ranged_value;

                            }
                            else if (count($rangedValues) == 1)
                            {
                                $minRangedValue = $maxRangedValue = $rangedValues[0];
                            }
                            else
                            {
                                $minRangedValue = $rangedValues[0];
                                $maxRangedValue = $rangedValues[1];
                            }

                            // Save it back just in case it changed
                            $searchedCharacteristics[$index]['r'] = "$minRangedValue,$maxRangedValue";

                            // Compare against the labels if they exist
                            $minRangedLabel = $characteristic->getRangedValueLabel($minRangedValue);
                            $maxRangedLabel = $characteristic->getRangedValueLabel($maxRangedValue);

                            if ( ! is_null($minRangedLabel))
                            {
                                $minRangedValue = $minRangedLabel->min_ranged_value;
                            }

                            if ( ! is_null($maxRangedLabel))
                            {
                                $maxRangedValue = $maxRangedLabel->max_ranged_value;
                            }

                            // Make sure the ranged value is revealed
                            $foundBreedIds = BreedCharacteristic::whereVisible()
                                ->where('characteristic_id', $characteristic->id)
                                ->where(function($query) use ($minRangedValue, $maxRangedValue)
                                    {
                                        $query
                                            ->where(function($q) use ($minRangedValue, $maxRangedValue)
                                                {
                                                    $q->where('min_female_ranged_value', '>=', $minRangedValue)->where('max_female_ranged_value', '<=', $maxRangedValue);
                                                })
                                            ->orWhere(function($q) use ($minRangedValue, $maxRangedValue)
                                                {
                                                    $q->where('min_male_ranged_value', '>=', $minRangedValue)->where('max_male_ranged_value', '<=', $maxRangedValue);
                                                });
                                    })
                                ->lists('breed_id');

                            // Add the found breeds to the list
                            $characteristicsBreedIds = empty($characteristicsBreedIds)
                                ? $foundBreedIds
                                : array_intersect($characteristicsBreedIds, $foundBreedIds);
                        }

                        // Test on genetics
                        if ($characteristic->isGenetic())
                        {
                            if ( ! array_key_exists('g', $searchedCharacteristic) and ! array_key_exists('ph', $searchedCharacteristic))
                            {
                                throw new Exception;
                            }

                            $genotypeIds  = [];
                            $phenotypeIds = [];

                            // Test on genotypes
                            if ( ! $characteristic->hideGenotypes() and array_key_exists('g', $searchedCharacteristic))
                            {
                                $genotypes = (array) $searchedCharacteristic['g'];

                                // Get the IDs
                                $genotypeIds = array_flatten($genotypes);

                                if ( ! empty($genotypeIds))
                                {
                                    // Count the loci
                                    $totalLoci = count($genotypes);

                                    $foundBreedIds = [];

                                        // Genotypes must be known and the breed must have a match at all loci
                                        $foundBreedIds = DB::table('breed_genotypes')
                                            ->select('breed_genotypes.breed_id', DB::raw('COUNT(breed_genotypes.genotype_id) as counted'))
                                            ->where('breed_genotypes.frequency', '>', 0)
                                            ->whereIn('breed_genotypes.genotype_id', $genotypeIds)
                                            ->having('counted', '=', count($genotypeIds))
                                            ->groupBy('breed_genotypes.breed_id')
                                            ->lists('breed_genotypes.breed_id');

                                    // Add the found breeds to the list
                                    $characteristicsBreedIds = empty($characteristicsBreedIds)
                                        ? $foundBreedIds
                                        : array_intersect($characteristicsBreedIds, $foundBreedIds);
                                }
                            }

                            // Test on phenotypes
                            if (array_key_exists('ph', $searchedCharacteristic))
                            {
                                // Grab the phenotype IDs
                                $phenotypeIds = (array) $searchedCharacteristic['ph'];

                                if ( ! empty($phenotypeIds))
                                {
                                    // Get all of the phenotypes
                                    $phenotypes = Phenotype::with(array(
                                            'genotypes' => function($query)
                                            {
                                                $query->whereActive();
                                            }
                                        ))
                                        ->whereIn('id', $phenotypeIds)->get();

                                    foreach($phenotypes as $phenotype)
                                    {
                                        $phenotypeGenotypeIdsByLocusId = [];

                                        $phenotypeGenotypes = $phenotype->genotypes;

                                        // Get the phenotype's genotypes
                                        foreach($phenotypeGenotypes as $genotype)
                                        {
                                            $phenotypeGenotypeIdsByLocusId[$genotype->locus_id][] = $genotype->id;
                                        }

                                        if ( ! empty($phenotypeGenotypeIdsByLocusId))
                                        {
                                            $phenotypeGenotypeIds = array_flatten($phenotypeGenotypeIdsByLocusId);

                                            $foundBreedIds = DB::table('breed_genotypes')
                                                ->select('breed_genotypes.breed_id', DB::raw('COUNT(DISTINCT genotypes.locus_id) as counted'))
                                                ->join('genotypes', 'genotypes.id', '=', 'breed_genotypes.genotype_id')
                                                ->where('breed_genotypes.frequency', '>', 0)
                                                ->whereIn('breed_genotypes.genotype_id', $phenotypeGenotypeIds)
                                                ->having('counted', '=', count($phenotypeGenotypeIdsByLocusId))
                                                ->groupBy('breed_genotypes.breed_id')
                                                ->lists('breed_genotypes.breed_id');

                                            // Add the found breeds to the list
                                            $characteristicsBreedIds = empty($characteristicsBreedIds)
                                                ? $foundBreedIds
                                                : array_intersect($characteristicsBreedIds, $foundBreedIds);
                                        }
                                    }
                                }
                            }

                            if (empty($genotypeIds) and empty($phenotypeIds))
                            {
                                throw new Exception;
                            }
                        }
                    }
                    catch (Exception $e)
                    {
                        // Unsearchable characteristic
                        unset($searchedCharacteristics[$index]);
                    }
                }

                $searchedCharacteristicIds = array_fetch($searchedCharacteristics, 'id');

                if ( ! empty($searchedCharacteristicIds))
                {
                    // Save the breeds found
                    $knownBreedIds = array_merge($knownBreedIds, $characteristicsBreedIds);

                


                    $results = $results->with(array(
                            'characteristics' => function($query) use ($searchedCharacteristicIds)
                            {
                                $query->with(array(
                                        'characteristic', 
                                        'characteristic.loci' => function($q)
                                            {
                                                $q->orderBy('name', 'asc');
                                            }, 
                                    ))
                                ->whereIn('characteristic_id', $searchedCharacteristicIds)
                                ->orderByCharacteristic();
                            }
                        ))
                        ->whereIn('id', $knownBreedIds);
                }
            }
        }

        $results = $results->orderBy('name', 'asc')->paginate(20);

        $counter = 0;
        $showCharacteristics = (count($searchedCharacteristics) > 0);

        // Show the page
        return View::make('frontend/breed_registry/search/index', compact(
            'searchedCharacteristics', 'showCharacteristics', 'counter', 'results', 'characteristicCategories'
        ));
    }

}
