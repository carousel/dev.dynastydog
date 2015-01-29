<?php namespace Controllers\Dog;

use AuthorizedController;
use Redirect;
use View;
use Input;
use Validator;
use Lang;
use Config;
use DB;
use Str;
use Dog;
use Response;
use CharacteristicTest;
use Characteristic;

use Dynasty\Dogs\Exceptions as DynastyDogsExceptions;
use Dynasty\Users\Exceptions as DynastyUsersExceptions;
use Dynasty\DogCharacteristics\Exceptions as DynastyDogCharacteristicsExceptions;
use Dynasty\CharacteristicTests\Exceptions as DynastyCharacteristicTestsExceptions;

class TestController extends AuthorizedController {

    public function postPerform()
    {
        $dogId  = Input::get('dog');
        $testId = Input::get('test');

        // Make sure the dog exists
        $dog = Dog::find($dogId);

        // Make sure the current user owns the dog
        if ( ! $this->currentUser->ownsDog($dog))
        {
            throw new DynastyUsersExceptions\DoesNotOwnDogException;
        }

        // Make sure the dog is alive
        if ( ! $dog->isAlive())
        {
            throw new DynastyDogsExceptions\DeceasedException;
        }

        // Make sure the dog has been compelted
        if ( ! $dog->isComplete())
        {
            throw new DynastyDogsExceptions\IncompleteException;
        }
        // Make sure the dog has not been worked
        if ($dog->isWorked())
        {
            throw new DynastyDogsExceptions\AlreadyWorkedException;
        }

        // Make sure the test exists and is active
        $test = CharacteristicTest::find($testId);

        if (is_null($test))
        {
            throw new DynastyCharacteristicTestsExceptions\NotFoundException;
        }

        if ( ! $test->isActive())
        {
            throw new DynastyCharacteristicTestsExceptions\NotActiveException;
        }

        // Make sure the dog has the characteristic to test and that it's not hidden
        $dogCharacteristic = $dog->characteristics()->whereCharacteristic($test->characteristic_id)->first();

        if (is_null($dogCharacteristic))
        {
            throw new DynastyDogCharacteristicsExceptions\NotFoundException;
        }

        if ($dogCharacteristic->isHidden())
        {
            throw new DynastyDogCharacteristicsExceptions\HiddenException;
        }

        // Make sure the dog has not already had this test performed
        if ($dogCharacteristic->hasHadTest($test))
        {
            throw new DynastyCharacteristicTestsExceptions\AlreadyTestedDogCharacteristicException;
        }

        // Make sure this dog is the right age for this test
        if ( ! $test->validAge($dog->age))
        {
            throw new DynastyCharacteristicTestsExceptions\InvalidAgeException;
        }

        $message = '';

        // Start transaction
        DB::transaction(function() use ($dog, $test, $dogCharacteristic, &$message)
        {
            // Work the dog
            $dog->worked = true;
            $dog->save();

            // Perform the test
            $message = $test->performOnDogCharacteristic($dogCharacteristic);
        });

        $showTests = true;

        $view = View::make('frontend/dog/_characteristic', compact('dogCharacteristic', 'showTests', 'message'));

        // @TUTORIAL: complete visit-first-dog-page
        if ($this->currentUser->isOnTutorialStage('visit-first-dog-page'))
        {
            $this->currentUser->advanceTutorial(array(
                'dog_characteristic_id' => $dogCharacteristic->id, 
            )); 
            
            $showTutorial = true;
        }
        else
        {
            $showTutorial = false;
        } 

        return Response::json(array('rendered' => $view->render(), 'show_tutorial' => $showTutorial));
    }

}
