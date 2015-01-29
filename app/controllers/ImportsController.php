<?php

class ImportsController extends AuthorizedController {

    protected $importableAges = array(
        '24' => '2 Years', 
    );

    public function getIndex()
    {
        // @TUTORIAL: complete complete-first-individual-challenge
        if ($this->currentUser->isOnTutorialStage('complete-first-individual-challenge'))
        {
            try
            {
                DB::transaction(function()
                {
                    $this->currentUser->advanceTutorial(); 

                    // Give the user an import
                    $this->currentUser->imports += 1;
                    $this->currentUser->save();
                });
            }
            catch(Exception $e)
            {
                // Silently fail
            }
        }

        // Get the breeds
        $breeds = Breed::whereImportable()->whereActive()->orderBy('name', 'asc')->get();
        $sexes  = Sex::orderBy('name', 'asc')->get();
        $ages   = $this->importableAges;

        // Custom imports
        $counter = 0;

        // Show the page
        return View::make('frontend.imports.index', compact(
            'breeds', 'sexes', 'ages', 
            'counter'
        ));
    }

    public function postImport()
    {
        // Declare the rules for the form validation
        $rules = array(
            'import_name'  => 'required|max:32',
            // 'import_age'   => 'required',
            'import_breed' => 'required|exists:breeds,id', 
            'import_sex'   => 'required|exists:sexes,id', 
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('imports')->withInput()->withErrors($validator);
        }

        $name    = Input::get('import_name');
        // $age     = Input::get('import_age');
        reset($this->importableAges);
       $age     = key($this->importableAges);
        $breedId = Input::get('import_breed');
        $sexId   = Input::get('import_sex');

        try
        {
            // Make sure the user has imports left
            if ( ! $this->currentUser->canAffordImports(1))
            {
                throw new Dynasty\Users\Exceptions\NotEnoughImportsException;
            }

            // Get the breed model to use
            $breed = Breed::with('genotypes', 'characteristics.characteristic', 'characteristics.severities.symptoms')->find($breedId);

            // Make sure the breed is active
            if ( ! $breed->isActive())
            {
                throw new Dynasty\Breeds\Exceptions\NotActiveException;
            }

            // Make sure the breed can be imported
            if ( ! $breed->isImportable())
            {
                throw new Dynasty\Breeds\Exceptions\NotImportableException;
            }

            // // Make sure the age is either 3 months or 2 years
            // if ( ! in_array($age, array_keys($this->importableAges)))
            // {
            //     throw new Dynasty\Dogs\Exceptions\InvalidAgeException;
            // }

            // Grab the sex
            $sex = Sex::find($sexId);

            // Need to do tutorial checks
            if ($this->currentUser->isOnTutorialStage('visit-first-import-dogs'))
            {
                // // Must be an adult
                // if ($age != 24)
                // {
                //     throw new Dynasty\UserTutorials\Exceptions\CannotContinueException;
                // }

                // Get the current stage
                $currentStage = $this->currentUser->tutorialStages()->current()->first();

                // Get the tutorial dog
                $tutorialDogId = $currentStage->data['dog_id'];

                // Find the dog
                $tutorialDog = Dog::find($tutorialDogId);

                if ( ! is_null($tutorialDog))
                {
                    // Must be the opposite sex
                    if ($tutorialDog->sex_id == $sex->id)
                    {
                        throw new Dynasty\UserTutorials\Exceptions\CannotContinueException;
                    }

                    /*// Must be a different breed
                    if ($tutorialDog->breed_id == $breed->id)
                    {
                        throw new Dynasty\UserTutorials\Exceptions\CannotContinueException;
                    }*/
                }
            }

            $dog = null;

            // Start transaction
            DB::transaction(function() use (&$dog, $name, $breed, $sex, $age)
            {
                // Take away the imports
                $this->currentUser->imports--;
                $this->currentUser->save();

                // Import a dog normally
                $dog = $this->currentUser->importDog($name, $breed, $sex, $age);

                // @TUTORIAL: complete visit-first-import-dogs
                $this->currentUser->completeTutorialStage('visit-first-import-dogs');
            });

            $success = Lang::get('forms/user.import_dog.success');

            // Redirect to the dog's page
            return Redirect::route('dog/profile', $dog->id)->with('success', $success);

        }
        catch (Dynasty\Users\Exceptions\NotEnoughImportsException $e)
        {
            return Redirect::route('imports')->withInput()->with('error', Lang::get('forms/user.import_dog.not_enough_imports'));
        }
        catch (Dynasty\Breeds\Exceptions\NotActiveException $e)
        {
            $this->messageBag->add('import_breed', Lang::get('forms/user.import_dog.invalid_breed'));
        }
        catch (Dynasty\Breeds\Exceptions\NotImportableException $e)
        {
            $this->messageBag->add('import_breed', Lang::get('forms/user.import_dog.invalid_breed'));
        }
        catch (Dynasty\Dogs\Exceptions\InvalidAgeException $e)
        {
            $this->messageBag->add('import_age', Lang::get('forms/user.import_dog.invalid_age'));
        }
        catch (Dynasty\UserTutorials\Exceptions\CannotContinueException $e)
        {
            $oppositeSex = Sex::where('id', '<>', $sex->id)->first();
            
            $params = array(
                'breed' => $breed->name, 
                'sex'   => $oppositeSex->name, 
            );

            $error = Lang::get('forms/user.import_dog.tutorial_error', $params);

            return Redirect::route('imports')->withInput()->with('error', $error);
        }
        // We want to catch all exceptions thrown in the transaction block and 
        // give a generic error to the user
        catch(Exception $e)
        {
            // Prepare the error message
            $error = Lang::get('forms/user.import_dog.error');

            return Redirect::route('imports')->withInput()->with('error', $error);
        }

        // Redirect to the imports page
        return Redirect::route('imports')->withInput()->withErrors($this->messageBag);
    }

    public function postCustomImport()
    {
        // Declare the rules for the form validation
        $rules = array(
            'custom_import_name'  => 'required|max:32',
            // 'custom_import_age'   => 'required',
            'custom_import_breed' => 'required|exists:breeds,id', 
            'custom_import_sex'   => 'required|exists:sexes,id', 
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            return Response::json(array('errors' => $validator->errors()->all()));
        }

        $name    = Input::get('custom_import_name');
        // $age     = Input::get('custom_import_age');
        reset($this->importableAges);
        $age     = key($this->importableAges);
        $breedId = Input::get('custom_import_breed');
        $sexId   = Input::get('custom_import_sex');

        try
        {
            // Make sure the user has custom imports left
            if ( ! $this->currentUser->canAffordCustomImports(1))
            {
                throw new Dynasty\Users\Exceptions\NotEnoughCustomImportsException;
            }

            // Get the breed model to use
            $breed = Breed::with('genotypes', 'characteristics.characteristic', 'characteristics.severities.symptoms')->find($breedId);

            // Make sure the breed is active
            if ( ! $breed->isActive())
            {
                throw new Dynasty\Breeds\Exceptions\NotActiveException;
            }

            // Make sure the breed can be imported
            if ( ! $breed->isImportable())
            {
                throw new Dynasty\Breeds\Exceptions\NotImportableException;
            }

            // // Make sure the age is either 3 months or 2 years
            // if ( ! in_array($age, array_keys($this->importableAges)))
            // {
            //     throw new Dynasty\Dogs\Exceptions\InvalidAgeException;
            // }

            $dog = null;

            // Get the characteristics
            $customizedCharacteristics = (array) Input::get('ch', []);

            if (count($customizedCharacteristics) > 3)
            {
                throw new Dynasty\Exceptions\TooManySelectedExpetion;
            }

            // Start transaction
            DB::transaction(function() use (&$dog, $name, $breed, $sexId, $age, $customizedCharacteristics)
            {
                // Take away the imports
                $this->currentUser->custom_imports--;
                $this->currentUser->save();

                // Import the dog
                $dog = $this->currentUser->importDog($name, $breed, Sex::find($sexId), $age, true, $customizedCharacteristics);
            });

            $success = Lang::get('forms/user.custom_import_dog.success');

            Session::flash('success', $success);

            return Response::json(array('redirect' => URL::route('dog/profile', $dog->id)));
        }
        catch (Dynasty\Users\Exceptions\NotEnoughCustomImportsException $e)
        {
            $this->messageBag->add('custom_imports', Lang::get('forms/user.custom_import_dog.not_enough_custom_imports'));
        }
        catch (Dynasty\Breeds\Exceptions\NotActiveException $e)
        {
            $this->messageBag->add('custom_import_breed', Lang::get('forms/user.custom_import_dog.invalid_breed'));
        }
        catch (Dynasty\Breeds\Exceptions\NotImportableException $e)
        {
            $this->messageBag->add('custom_import_breed', Lang::get('forms/user.custom_import_dog.invalid_breed'));
        }
        catch (Dynasty\Dogs\Exceptions\InvalidAgeException $e)
        {
            $this->messageBag->add('custom_import_age', Lang::get('forms/user.custom_import_dog.invalid_age'));
        }
        catch (Dynasty\Exceptions\NoneSelectedException $e)
        {
            $this->messageBag->add('characteristics', Lang::get('forms/user.custom_import_dog.no_characteristics'));
        }
        catch (Dynasty\Exceptions\TooManySelectedExpetion $e)
        {
            $this->messageBag->add('characteristics', Lang::get('forms/user.custom_import_dog.too_many_characteristics'));
        }
        catch (Dynasty\Characteristics\Exceptions\InvalidException $e)
        {
            $this->messageBag->add('characteristics', Lang::get('forms/user.custom_import_dog.invalid_characteristic'));
        }
        catch (Dynasty\Characteristics\Exceptions\NotFoundException $e)
        {
            $this->messageBag->add('characteristics', Lang::get('forms/user.custom_import_dog.blank_characteristic'));
        }
        catch (Dynasty\Characteristics\Exceptions\UniqueException $e)
        {
            $params = json_decode($e->getMessage(), true);
            $this->messageBag->add('characteristics', Lang::get('forms/user.custom_import_dog.duplicate_characteristics', $params));
        }
        catch (Dynasty\Characteristics\Exceptions\IncompleteException $e)
        {
            $params = json_decode($e->getMessage(), true);
            $this->messageBag->add('characteristics', Lang::get('forms/user.custom_import_dog.incomplete_characteristic', $params));
        }
        catch (Dynasty\DogCharacteristics\Exceptions\RangedValueOutOfBoundsException $e)
        {
            $params = json_decode($e->getMessage(), true);
            $this->messageBag->add('characteristics', Lang::get('forms/user.custom_import_dog.ranged_value_out_of_bounds', $params));
        }
        catch (Dynasty\Breed\Exceptions\GenotypeNotFoundException $e)
        {
            $params = json_decode($e->getMessage(), true);
            $this->messageBag->add('characteristics', Lang::get('forms/user.custom_import_dog.genotypes_not_in_breed', $params));
        }
        catch (Dynasty\Characteristics\Exceptions\GenotypeNotFoundException $e)
        {
            $params = json_decode($e->getMessage(), true);
            $this->messageBag->add('characteristics', Lang::get('forms/user.custom_import_dog.genotypes_not_in_characteristic', $params));
        }
        catch (Dynasty\DogCharacteristics\Exceptions\TooManyPhenotypesException $e)
        {
            $params = json_decode($e->getMessage(), true);
            $this->messageBag->add('characteristics', Lang::get('forms/user.custom_import_dog.too_many_phenotypes', $params));
        }
        catch (Dynasty\Breeds\Exceptions\PhenotypeNotFoundException $e)
        {
            $params = json_decode($e->getMessage(), true);
            $this->messageBag->add('characteristics', Lang::get('forms/user.custom_import_dog.phenotype_not_in_breed', $params));
        }
        catch (Dynasty\Characteristics\Exceptions\PhenotypeNotFoundException $e)
        {
            $params = json_decode($e->getMessage(), true);
            $this->messageBag->add('characteristics', Lang::get('forms/user.custom_import_dog.phenotype_not_in_characteristic', $params));
        }
        catch (Dynasty\DogCharacteristics\Exceptions\InternalConflictException $e)
        {
            $params = json_decode($e->getMessage(), true);
            $this->messageBag->add('characteristics', Lang::get('forms/user.custom_import_dog.internally_conflicted_characteristic', $params));
        }
        catch (Dynasty\DogCharacteristics\Exceptions\ExternalConflictException $e)
        {
            $params = json_decode($e->getMessage(), true);
            $this->messageBag->add('characteristics', Lang::get('forms/user.custom_import_dog.externally_conflicted_characteristic', $params));
        }
        catch (Dynasty\DogCharacteristics\Exceptions\MultipleBasePhenotypesException $e)
        {
            $params = json_decode($e->getMessage(), true);
            $this->messageBag->add('characteristics', Lang::get('forms/user.custom_import_dog.too_many_base_phenotypes', $params));
        }
        catch (Dynasty\DogCharacteristics\Exceptions\MissingBasePhenotypeException $e)
        {
            $params = json_decode($e->getMessage(), true);
            $this->messageBag->add('characteristics', Lang::get('forms/user.custom_import_dog.base_phenotype_not_found', $params));
        }
        catch (Dynasty\DogCharacteristics\Exceptions\IncompatibleException $e)
        {
            $params = json_decode($e->getMessage(), true);
            $this->messageBag->add('characteristics', Lang::get('forms/user.custom_import_dog.incompatible_characteristic', $params));
        }
        catch (Dynasty\DogCharacteristics\Exceptions\UnresolvedException $e)
        {
            $params = json_decode($e->getMessage(), true);
            $this->messageBag->add('characteristics', Lang::get('forms/user.custom_import_dog.unresolved_characteristic', $params));
        }
        // We want to catch all exceptions thrown in the transaction block and 
        // give a generic error to the user
        catch(Exception $e)
        {
            $this->messageBag->add('error', Lang::get('forms/user.custom_import_dog.error'));
        }

        return Response::json(array('errors' => $this->messageBag->all()));
    }

}
