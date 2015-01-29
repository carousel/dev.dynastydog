<?php

class 57q_ImportsController extends BaseController {

    public function getIndex()
    {



        // We're just going to do ALL coding here. 
        // Move it to classes later.
        // Exception being models
        // Functions can stick around here at the top.

        // This is what the user gives us for a normal import

        $input = Input::all();

        // @TODO: remove
        $input = array(
            'name'  => 'New dog', 
            'breed' => 1, 
            'sex'   => mt_rand(1,2), // Bitch or Dog
            'age'   => 24, // 2 years
        );

        // Declare the rules for the form validation
        $rules = array(
            'name'  => 'required|max:32',
            'age'   => 'required',
            'breed' => 'required|exists:breeds,id', 
            'sex'   => 'required|exists:sexes,id', 
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make($input, $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('import-dogs')->withInput()->withErrors($validator);
        }

        try
        {
            // Get the breed model to use
            $breed = Breed::with(array(
                'genotypes', 
                'characteristics' => function($query) {
                    $query->active();
                }, 
                'characteristics.characteristic.genotypes', 
                'characteristics.characteristic.loci', 
                'characteristics.severities.symptoms'
            ))->find($input['breed']);

            // Make sure the breed is active
            if ( ! $breed->active())
            {
                throw new Dynasty\Breeds\Exceptions\NotActiveException(Lang::get('user/import-dog.invalid_breed'));
            }

            // Make sure the breed can be imported
            if ( ! $breed->importable())
            {
                throw new Dynasty\Breeds\Exceptions\NotImportableException(Lang::get('user/import-dog.invalid_breed'));
            }

            // Make sure the age is either 3 months or 2 years
            if ($input['age'] != 3 and $input['age'] != 24)
            {
                throw new Dynasty\Dogs\Exceptions\InvalidAgeException(Lang::get('user/import-dog.invalid_age'));
            }

            // Make sure the user has imports left
            if ( ! $this->currentUser->canAffordImports(1))
            {
                throw new Dynasty\Users\Exceptions\NotEnoughImportsException(Lang::get('user/import-dog.not_enough_imports'));
            }

            // Start the new dog
            $dog = new Dog;

            $dog->fill(array(
                'name'     => $input['name'], 
                'breed_id' => $input['breed'], 
                'sex_id'   => $input['sex'], 
                'age'      => $input['age'], 
            ));

            try
            {
                // Start transaction
                DB::transaction(function() use ($dog, $breed)
                {
                    // Create the dog
                    $dog = $this->currentUser->dogs()->save($dog);

                    if ( ! $dog)
                    {
                        throw new Dynasty\Dogs\Exceptions\NotSavedException;
                    }

                    // Store genotypes and phenotypes for later
                    $genotypeIds  = [];
                    $phenotypeIds = [];

                    $genotypes = $breed->genotypes()->active()->get()->toArray();

                    // Grab all genotypes from the breed
                    $collectedBreedGenotypes = array_collect($genotypes, 'locus_id');

                    $possibleGenotypes = [];

                    // Adjust for frequencies
                    foreach($collectedBreedGenotypes as $locusId => $genotypes)
                    {
                        foreach($genotypes as $genotype)
                        {
                            for ($i = 0; $i < $genotype['pivot']['frequency']; $i++)
                            {
                                $possibleGenotypes[$locusId][] = $genotype;
                            }
                        }
                    }

                    $dogGenotypes = [];

                    foreach($possibleGenotypes as $locusId => $genotypes)
                    {
                        // Choose one
                        $genotype = array_random($genotypes);

                        // Add them to save later
                        $genotypeIds[$genotype['locus_id']] = $genotype['id'];

                        $dogGenotypes[] = array(
                            'dog_id'      => $dog->id, 
                            'genotype_id' => $genotype['id'], 
                        );
                    }

                    // Give the dog the genotypes
                    DB::table('dog_genotypes')->insert($dogGenotypes);

                    // Get all the phenotypes
                    $phenoypes = Phenotype::with(array('genotypes' => function($query)
                    {
                        $query->active();
                    }))->get();

                    $dogPhenotypes = [];

                    foreach($phenoypes as $phenotype)
                    {
                        $required = [];

                        // List the  genotypes
                        foreach($phenotype->genotypes as $genotype)
                        {
                            $required[$genotype->id] = $genotype->locus_id;
                        }

                        // $required = $phenotype->genotypes()->lists('locus_id', 'genotype_id');

                        $requiredIds   = array_keys($required);
                        $matchesNeeded = count(array_unique($required));
                        $matches       = count(array_intersect($requiredIds, $genotypeIds));

                        if ($matches == $matchesNeeded)
                        {
                            // Give the dog the phenotype
                            $phenotypeIds[] = $phenotype->id;

                            $dogPhenotypes[] = array(
                                'dog_id'       => $dog->id, 
                                'phenotype_id' => $phenotype->id, 
                            );
                        }
                    }

                    // Give the dog the phenotypes
                    DB::table('dog_phenotypes')->insert($dogPhenotypes);

                    // $dog->phenotypes()->sync($phenotypeIds);

                    // Store the characteristics given to be added in one query 
                    $dogCharacteristics = [];
                    
                    // Store the symtpoms given to be added in one query
                    $dogCharacteristicSymptoms = [];

                    // Save them for later for the dependencies
                    $breedCharacteristics = [];

                    // Go through all characteristics from the breed
                    foreach($breed->characteristics as $breedCharacteristic)
                    {
                        $characteristic = $breedCharacteristic->characteristic;

                        // Store for later
                        $breedCharacteristics[$characteristic->id] = $breedCharacteristic;

                        $ageToRevealGenotypes    = $breedCharacteristic->getRandomAgeToRevealGenotypes();
                        $ageToRevealPhenotypes   = $breedCharacteristic->getRandomAgeToRevealPhenotypes();

                        $finalRangedValue        = $breedCharacteristic->getRandomRangedValue($dog->isFemale() ? 'female' : 'male');
                        $ageToStopGrowing        = $breedCharacteristic->getRandomAgeToStopGrowing();
                        $currentRangedValue      = DogCharacteristic::currentRangedValue($finalRangedValue, $ageToStopGrowing, $dog->age);
                        $ageToRevealRangedValue  = $breedCharacteristic->getRandomAgeToRevealRangedValue();

                        $filling = array(
                            'characteristic_id'          => $breedCharacteristic->characteristic_id, 

                            // Do genetics
                            'age_to_reveal_genotypes'    => $ageToRevealGenotypes, 
                            'genotypes_revealed'         => ($dog->age >= $ageToRevealGenotypes), 
                            'age_to_reveal_phenotypes'   => $ageToRevealPhenotypes, 
                            'phenotypes_revealed'        => ($dog->age >= $ageToRevealPhenotypes), 

                            // Do ranged
                            'final_ranged_value'         => $finalRangedValue, 
                            'age_to_stop_growing'        => $ageToStopGrowing, 
                            'current_ranged_value'       => $currentRangedValue, 
                            'age_to_reveal_ranged_value' => $ageToRevealRangedValue, 
                            'ranged_value_revealed'      => ($dog->age >= $ageToRevealRangedValue), 
                        );

                        if ($characteristic->eligibleForSeverity($genotypeIds))
                        {
                            // Grab a nonlethal health severity if it exists
                            if ( ! is_null($breedCharacteristicSeverity = $breedCharacteristic->getRandomSeverity(true)))
                            {
                                $filling['characteristic_severity_id']   = $breedCharacteristicSeverity->characteristic_severity_id;
                                $filling['age_to_express_severity']      = $breedCharacteristicSeverity->getRandomAgeToExpress();
                                $filling['severity_expressed']           = ($dog->age >= $filling['age_to_express_severity']);
                                $filling['severity_value']               = $breedCharacteristicSeverity->getRandomValue();
                                $filling['age_to_reveal_severity_value'] = $breedCharacteristicSeverity->getRandomAgeToRevealSeverityValue();
                                $filling['severity_value_revealed']      = ($dog->age >= $filling['age_to_reveal_severity_value']);

                                // Check if there are any symptoms that need to be attached
                                $orderedDogCharacteristicSymptoms[$filling['characteristic_severity_id']] = [];

                                foreach($breedCharacteristicSeverity->symptoms as $breedCharacteristicSeveritySymptom)
                                {
                                    $ageToExpress = $breedCharacteristicSeveritySymptom->getRandomAgeToExpress($filling['age_to_express_severity']);

                                    $dogCharacteristicSymptom = new DogCharacteristicSymptom;

                                    $dogCharacteristicSymptom->dog_characteristic_id = null;
                                    $dogCharacteristicSymptom->characteristic_severity_symptom_id = $breedCharacteristicSeveritySymptom->characteristic_severity_symptom_id;
                                    $dogCharacteristicSymptom->age_to_express = $ageToExpress;
                                    $dogCharacteristicSymptom->expressed      = ($dog->age >= $ageToExpress);

                                    // Store the symptom to be added later
                                    $orderedDogCharacteristicSymptoms[$filling['characteristic_severity_id']][] = $dogCharacteristicSymptom;
                                }
                            }
                        }

                        // Fill the dog's characteristic and store it to be added later
                        $dogCharacteristic = new DogCharacteristic;

                        $dogCharacteristics[] = $dogCharacteristic->fill($filling);
                    }

                    $insertable = [];

                    foreach($dogCharacteristics as $dogCharacteristic)
                    {
                        $dogCharacteristic->dog_id = $dog->id;

                        $insertable[] = $dogCharacteristic->toArray();
                    }

                    // Attach the dog's characteristics
                    DB::table('dog_characteristics')->insert($insertable);
                    // $dog->characteristics()->saveMany($dogCharacteristics);

                    // We need to attach the genotypes, phenotypes, and symptoms to the newly saved dog characteristics
                    $dogCharacteristics = $dog->load('characteristics.characteristic.loci')->characteristics;

                    // Store for saving later
                    $dogCharacteristicSymptoms   = [];
                    $dogCharacteristicGenotypes  = [];
                    $dogCharacteristicPhenotypes = [];

                    // Grab all the phenotypes given to the dog
                    $phenotypes = Phenotype::with(array('genotypes' => function($query)
                    {
                        $query->active();
                    }))->whereIn('id', $phenotypeIds)->get();

                    // Go through the dog's characteristics
                    foreach($dogCharacteristics as $dogCharacteristic)
                    {
                        $characteristic = $dogCharacteristic->characteristic;

                        $locusIds = [];

                        // Check if the characteristic has loci
                        foreach($characteristic->loci as $locus)
                        {
                            $locusIds[$locus->id] = $locus->id;
                        }

                        if ( ! empty($locusIds))
                        {
                            $attachedPhenotypeIds = [];
                            $attachedGenotypeIds = [];

                            // Attach the genotypes to the dog characteristic
                            $attachedGenotypeIds = array_intersect_key($genotypeIds, $locusIds);

                            foreach($attachedGenotypeIds as $attachedGenotypeId)
                            {
                                $dogCharacteristicGenotypes[] = array(
                                    'dog_characteristic_id' => $dogCharacteristic->id, 
                                    'genotype_id'           => $attachedGenotypeId, 
                                );
                            }

                            // Go through each of the dog's phenotype ids
                            foreach ($phenotypes as $phenotype)
                            {
                                $required = [];

                                // List the  genotypes
                                foreach($phenotype->genotypes as $genotype)
                                {
                                    $required[$genotype->id] = $genotype->locus_id;
                                }

                                $requiredIds   = array_values(array_unique($required));
                                $matchesNeeded = count($requiredIds);
                                $matches       = count(array_intersect($requiredIds, $locusIds));

                                if ($matches == $matchesNeeded)
                                {
                                    // Give the dog the phenotype
                                    $dogCharacteristicPhenotypes[] = array(
                                        'dog_characteristic_id' => $dogCharacteristic->id, 
                                        'phenotype_id'          => $phenotype->id, 
                                    );
                                }
                            }
                        }

                        // Check if we need to attach a symptom
                        if ( ! is_null($dogCharacteristic->characteristic_severity_id))
                        {
                            foreach($orderedDogCharacteristicSymptoms[$dogCharacteristic->characteristic_severity_id] as $dogCharacteristicSymptom)
                            {
                                // Assign it to the characteristic
                                $dogCharacteristicSymptom->dog_characteristic_id = $dogCharacteristic->id;

                                // Save it back in the array to be saved
                                $dogCharacteristicSymptoms[] = $dogCharacteristicSymptom->toArray();
                            }
                        }
                    }

                    // Insert the dog's symptoms
                    DB::table('dog_characteristic_symptoms')->insert($dogCharacteristicSymptoms);

                    // Attach the genotypes to the characteristics
                    DB::table('dog_characteristic_genotypes')->insert($dogCharacteristicGenotypes);

                    // Attach the phenotypes to the characteristics
                    DB::table('dog_characteristic_phenotypes')->insert($dogCharacteristicPhenotypes);

                    // Grab the dog's characteristics again
                    // FASTER: Selecting everything again is quicker than lazy loading the genotypes, phenotypes and dependencies for the dog's characteristics
                    $dogCharacteristics = $dog
                        ->load(array(
                            'characteristics' => function($query) {
                                $query->dependent();
                            }, 
                            'characteristics.characteristic.dependencies.independentCharacteristics', 
                            'characteristics.characteristic.dependencies.groups', 
                            'characteristics.genotypes', 
                            'characteristics.phenotypes'
                        ))->characteristics;

                    // We need to go back through the dog characteristics to do the dependency checks, but only on the dependent characteristics
                    foreach($dogCharacteristics as $dogCharacteristic)
                    {
                        // SLOWER: Lazy load the genotypes, phenotypes and dependencies
                        // $dogCharacteristic->load('genotypes', 'phenotypes', 'characteristic.dependencies');

                        $characteristic = $dogCharacteristic->characteristic;

                        foreach($characteristic->dependencies as $dependency)
                        {
                            if ($dependency->takesInRanged())
                            {
                                // Get the independent characteristics range values for this dog
                                $independentRangedValues = DB::table('characteristic_dependency_ind_characteristics')
                                    ->select('characteristic_dependency_ind_characteristics.independent_characteristic_id', 'dog_characteristics.final_ranged_value')
                                    ->join('dog_characteristics', 'dog_characteristics.characteristic_id', '=', 'characteristic_dependency_ind_characteristics.independent_characteristic_id')
                                    ->where('dog_characteristics.dog_id', $dog->id)
                                    ->where('characteristic_dependency_ind_characteristics.characteristic_dependency_id', $dependency->id)
                                    ->lists('final_ranged_value', 'independent_characteristic_id');
                            }
                            else if ($dependency->takesInGenotypes())
                            {
                                // Get the independent characteristics genotypes for this dog
                                $independentGenotypes = DB::table('characteristic_dependency_ind_characteristics')
                                    ->select('genotypes.locus_id', 'dog_characteristic_genotypes.genotype_id')
                                    ->join('dog_characteristics', 'dog_characteristics.characteristic_id', '=', 'characteristic_dependency_ind_characteristics.independent_characteristic_id')
                                    ->join('dog_characteristic_genotypes', 'dog_characteristic_genotypes.dog_characteristic_id', '=', 'dog_characteristics.id')
                                    ->join('genotypes', 'genotypes.id', '=', 'dog_characteristic_genotypes.genotype_id')

                                    ->where('dog_characteristics.dog_id', $dog->id)
                                    ->where('characteristic_dependency_ind_characteristics.characteristic_dependency_id', $dependency->id)
                                    ->lists('genotype_id', 'locus_id');
                            }

                            if ($dependency->outputsRanged())
                            {
                                // Get this characteristics dependent value for this dog
                                $finalRangedValue = $dogCharacteristic->final_ranged_value;

                                $newRangedValue = $finalRangedValue;

                                // Do the dependencies
                                if ($dependency->isR2R())
                                {
                                    $newRangedValue = $dependency->doR2R($finalRangedValue, $independentRangedValues);
                                }
                                else if ($dependency->isG2R())
                                {
                                    $newRangedValue = $dependency->doG2R($finalRangedValue, $independentGenotypes);
                                }

                                // Only need to update and bind if it changed
                                if (Floats::compare($finalRangedValue, $newRangedValue, '!='))
                                {
                                    // We need to bind to the breed of the characteristic itself
                                    // Get the breed's characteristic equivalent
                                    $breedCharacteristic = $breedCharacteristics[$characteristic->id];

                                    if ($dog->isFemale())
                                    {
                                        $ub = $breedCharacteristic->max_female_ranged_value;
                                        $lb = $breedCharacteristic->min_female_ranged_value;
                                    }
                                    else
                                    {
                                        $ub = $breedCharacteristic->max_male_ranged_value;
                                        $lb = $breedCharacteristic->min_male_ranged_value;
                                    }

                                    // Bind it between the bounds
                                    $finalRangedValue = max(min($newRangedValue, $ub), $lb);

                                    // Adjust for growth
                                    $currentRangedValue = DogCharacteristic::currentRangedValue($finalRangedValue, $dogCharacteristic->age_to_stop_growing, $dog->age);

                                    // Save it back on the characteristic
                                    $dogCharacteristic->final_ranged_value   = $finalRangedValue;
                                    $dogCharacteristic->current_ranged_value = $currentRangedValue;

                                    // Save it IMMEDIATELY
                                    $dogCharacteristic->save();
                                }
                            }
                            else if ($dependency->outputsGenotypes())
                            {
                                // We no longer support X2G dependencies, but this check is here for legacy value
                            }
                        }
                    }

                    // Give them an imported pedigree
                    $pedigree = Pedigree::imported();

                    $pedigree->dog_id = $dog->id;

                    if ( ! $pedigree->save())
                    {
                        throw new Dynasty\Pedigrees\Exceptions\NotSavedException;
                    }

                    // Calculate the COI of the pedigree
                    $coi = $pedigree->calculateCoi();

                    // Assign the dog to its owners first kennel group
                    $kennelGroup = $this->currentUser->kennelGroups()->notCemetery()->first();

                    // Souldn't ever be null, but we should check for it regardless
                    if ( ! is_null($kennelGroup))
                    {
                        $dog->kennel_group_id = $kennelGroup->id;
                    }

                    // We need to save the dog again
                    $dog->save();
                }); // End transaction

                // Prepare the success message
                $success = Lang::get('user/message.import-dog.success');

                // Redirect to the dog's page
                // @TODO: uncomment
                // return Redirect::route('dog/profile', $dog->id)->with('success', $success);

                // @TODO: remove
                return View::make('frontend.imports.index');
            }
            // We want to catch all exceptions thrown in the transaction block and 
            // give a generaic error to the user
            catch(Exception $e)
            {
                // Prepare the error message
                $error = Lang::get('user/message.import-dog.error');

                var_dump($e->getMessage());
                exit();

                return Redirect::route('import-dogs')->withInput()->with('error', $error);
            }

            /* NOTHING GOES HERE */
        }
        catch (Dynasty\Breeds\Exceptions\NotActiveException $e)
        {
            $this->messageBag->add('breed', $e->getMessage());
        }
        catch (Dynasty\Breeds\Exceptions\NotImportableException $e)
        {
            $this->messageBag->add('breed', $e->getMessage());
        }
        catch (Dynasty\Breeds\Exceptions\InvalidAgeException $e)
        {
            $this->messageBag->add('age', $e->getMessage());
        }
        catch (Dynasty\Users\Exceptions\NotEnoughImportsException $e)
        {

            var_dump($e->getMessage());
            exit();

            return Redirect::route('import-dogs')->withInput()->with('error', $e->getMessage());
        }

        // Redirect to the imports page

        var_dump($this->messageBag);
        exit();

        return Redirect::route('import-dogs')->withInput()->withErrors($this->messageBag);

    }

}
