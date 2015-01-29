<?php 

class SearchController extends AuthorizedController {

    public function getIndex()
    {
        // Show the page
        return View::make('frontend/search/index');
    }

    public function getForums()
    {
        // Get all the forums
        $forums = Forum::orderBy('title', 'asc')->get();

        $results = null;

        if (Input::get('search'))
        {
            $forumIds = Input::get('forums', array());
            $authorId = Input::get('author');
            $terms    = Input::get('terms');

            if (strlen($terms) > 0)
            {
                $terms = explode(',', $terms);
                $terms = array_map('trim', $terms);
                $terms = array_filter($terms);
            }
            else
            {
                $terms = array();
            }

            if (Input::get('type') === 'topics')
            {
                $results = ForumTopic::with(array('forum', 'author'));

                foreach($terms as $term)
                {
                    $results = $results->where('title', 'LIKE', '%'.$term.'%');
                }

                if ( ! empty($forumIds))
                {
                    $results = $results->whereIn('forum_id', $forumIds);
                }

                if (strlen($authorId) > 0)
                {
                    $results = $results->where('author_id', $authorId);
                }

                $results = $results->orderBy('id', 'asc');
            }
            else
            {
                $results = ForumPost::with(array('topic.forum', 'author'))->has('topic')->select('forum_posts.*');

                foreach($terms as $term)
                {
                    $results = $results->where('body', 'LIKE', '%'.$term.'%');
                }

                if ( ! empty($forumIds))
                {
                    $results = $results->join('forum_topics', 'forum_topics.id', '=', 'forum_posts.topic_id')
                        ->whereIn('forum_topics.forum_id', $forumIds);
                }

                if (strlen($authorId) > 0)
                {
                    $results = $results->where('forum_posts.author_id', $authorId);
                }

                $results = $results->orderBy('forum_posts.id', 'asc');
            }

            $results = $results
                ->paginate(20);
        }

        // Show the page
        return View::make('frontend/search/forums', compact('forums', 'results'));
    }

    public function getUsers()
    {
        $results = null;

        if (Input::get('search'))
        {
            $id          = Input::get('id');
            $displayName = Input::get('display_name');

            $results = new User;

            if (strlen($id) > 0)
            {
                $results = $results->where('id', $id);
            }

            if (strlen($displayName) > 0)
            {
                $results = $results->where('display_name', 'LIKE', '%'.$displayName.'%');
            }

            $results = $results->orderBy('id', 'asc');

            $results = $results
                ->paginate(20);
        }

        // Show the page
        return View::make('frontend/search/users', compact('results'));
    }

    public function getDogs()
    {
        // Get all the sexes
        $sexes = Sex::orderBy('name', 'asc')->get();

        // Get all the breeds
        $breeds = Breed::whereActive()->orderBy('name', 'asc')->get();

        // Get possible studding options
        $studdingOptions = Dog::studdingOptions();

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

        $results = null;

        if (Input::get('search'))
        {
            $id    = Input::get('id');
            $name  = Input::get('name');
            $sexId = Input::get('sex');
            $minimumAge = Input::get('minimum_age');
            $maximumAge = Input::get('maximum_age');
            $breedId    = Input::get('breed');
            $ownerId    = Input::get('owner');
            $breederId  = Input::get('breeder');
            $kennelPrefix = Input::get('kennel_prefix');
            $studding = (array) Input::get('studding_options', []);
            $status   = Input::get('status');
            $healthy  = Input::get('healthy');

            $searchedCharacteristics = (array) Input::get('ch', []);

            $results = new Dog;

            if (strlen($id) > 0)
            {
                $results = $results->where('id', $id);
            }

            if (strlen($name) > 0)
            {
                $results = $results->where('name', 'LIKE', '%'.$name.'%');
            }

            if (strlen($sexId) > 0)
            {
                $results = $results->where('sex_id', $sexId);
            }

            if (strlen($minimumAge) > 0)
            {
                $results = $results->where('age', '>=', $minimumAge);
            }

            if (strlen($maximumAge) > 0)
            {
                $results = $results->where('age', '<=', $maximumAge);
            }

            if (strlen($breedId) > 0)
            {
                $results = ($breedId == 'unregistered')
                    ? $results->whereNull('breed_id')
                    : $results->where('breed_id', $breedId);
            }

            if (strlen($ownerId) > 0)
            {
                $results = $results->where('owner_id', $ownerId);
            }

            if (strlen($breederId) > 0)
            {
                $results = $results->where('breeder_id', $breederId);
            }

            if (strlen($kennelPrefix) > 0)
            {
                $results = $results->where('kennel_prefix', $kennelPrefix);
            }

            if ( ! empty($studding))
            {
                $results = $results->whereIn('studding', $studding);
            }

            switch ($status)
            {
                case 'active':
                    $results = $results->where('active_breed_member', true);
                    break;
                
                case 'alive':
                    $results = $results->whereAlive();
                    break;
                
                default:
                    break;
            }

            if (strlen($healthy) > 0)
            {
                // Get all dogs that have active symptoms
                $sickDogIds = DB::table('dog_characteristic_symptoms')
                    ->join('dog_characteristics', 'dog_characteristics.id', '=', 'dog_characteristic_symptoms.dog_characteristic_id')
                    ->where('dog_characteristic_symptoms.expressed', true)
                    ->lists('dog_characteristics.dog_id');

                if ( ! empty($sickDogIds))
                {
                    $results = $results->whereNotIn('id', $sickDogIds);
                }
            }

            if ( ! empty($searchedCharacteristics))
            {
                // Get all possible searchable characteristics
                $searchableCharacteristicIds = [];

                foreach($characteristicCategories as $category)
                {
                    $searchableCharacteristicIds = array_merge($searchableCharacteristicIds, $category['characteristics']->lists('id'));
                }

                // Store the characteristics that were looked at to remove duplicates
                $lookedAtIds = [];

                // Get all dogs that satisfy ALL of the characteristics
                $characteristicsDogIds = [];

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

                            // Save it back just in case it changed
                            $searchedCharacteristics[$index]['r'] = "$minRangedValue,$maxRangedValue";

                            // Make sure the ranged value is revealed
                            $foundDogIds = DogCharacteristic::whereVisible()
                                ->whereRangedValueIsRevealed()
                                ->where('characteristic_id', $characteristic->id)
                                ->where('current_ranged_value', '>=', $minRangedValue)
                                ->where('current_ranged_value', '<=', $maxRangedValue)
                                ->lists('dog_id');

                            // Add the found dogs to the list
                            $characteristicsDogIds = empty($characteristicsDogIds)
                                ? $foundDogIds
                                : array_intersect($characteristicsDogIds, $foundDogIds);
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

                                    $foundDogIds = [];

                                        // Genotypes must be known and the dog must have a match at all loci
                                        $foundDogIds = DB::table('dog_characteristics')
                                            ->select('dog_characteristics.dog_id', DB::raw("COUNT(genotypes.locus_id) as matched_loci"))
                                            ->join('dog_characteristic_genotypes', 'dog_characteristic_genotypes.dog_characteristic_id', '=', 'dog_characteristics.id')
                                            ->join('genotypes', 'genotypes.id', '=', 'dog_characteristic_genotypes.genotype_id')
                                            ->where('dog_characteristics.hide', false)
                                            ->where('dog_characteristics.genotypes_revealed', true)
                                            ->whereIn('dog_characteristic_genotypes.genotype_id', $genotypeIds)
                                            ->having('matched_loci', '>=', $totalLoci)
                                            ->groupBy('dog_characteristics.dog_id')
                                            ->groupBy('genotypes.locus_id')
                                            ->lists('dog_characteristics.dog_id');

                                    // Add the found dogs to the list
                                    $characteristicsDogIds = empty($characteristicsDogIds)
                                        ? $foundDogIds
                                        : array_intersect($characteristicsDogIds, $foundDogIds);
                                }
                            }

                            // Test on phenotypes
                            if (array_key_exists('ph', $searchedCharacteristic))
                            {
                                // Grab the phenotype IDs
                                $phenotypeIds = (array) $searchedCharacteristic['ph'];

                                if ( ! empty($phenotypeIds))
                                {
                                    // Search the dogs by phenotypes
                                    $foundDogIds = DB::table('dog_characteristics')
                                        ->select('dog_characteristics.dog_id')
                                        ->join('dog_characteristic_phenotypes', 'dog_characteristic_phenotypes.dog_characteristic_id', '=', 'dog_characteristics.id')
                                        ->where('dog_characteristics.characteristic_id', $characteristic->id)
                                        ->where('dog_characteristics.hide', false)
                                        ->where('dog_characteristics.phenotypes_revealed', true)
                                        ->whereIn('dog_characteristic_phenotypes.phenotype_id', $phenotypeIds)
                                        ->lists('dog_characteristics.dog_id');

                                    // Add the found dogs to the list
                                    $characteristicsDogIds = empty($characteristicsDogIds)
                                        ? $foundDogIds
                                        : array_intersect($characteristicsDogIds, $foundDogIds);
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
                    // Always add -1 in
                    $characteristicsDogIds[] = -1;
                
                    $results = $results->with(array(
                            'characteristics' => function($query) use ($searchedCharacteristicIds)
                            {
                                $query->whereIn('characteristic_id', $searchedCharacteristicIds)->orderByCharacteristic();
                            }
                        ))
                        ->whereIn('id', $characteristicsDogIds);
                }
            }

            $results = $results->orderBy('id', 'asc');

            $results = $results
                ->paginate(20);
        }

        $counter = 0;
        $showCharacteristics = (count($searchedCharacteristics) > 0);

        // Show the page
        return View::make('frontend/search/dogs', compact(
            'sexes', 'breeds', 'studdingOptions', 'searchedCharacteristics', 'showCharacteristics', 
            'counter', 'results', 'characteristicCategories'
        ));
    }

}
