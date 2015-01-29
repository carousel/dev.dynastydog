<?php

return array(

    'reset_worked_status' => array(
        'wrong_owner' => "You do not own that dog.",
        'not_worked'  => "That dog has not been worked.",
        'deceased'    => "That dog is deceased.",
        'not_enough_referral_points' => "You do not have enough referral points to reset that dog's status.",
        'success'     => "You have reset the status for :dog.full_name (#:dog.id).",
        'error'       => "The dog's status was not reset, please try again.",
    ),

    'change_name' => array(
        'wrong_owner' => "You do not own that dog.",
        'deceased'    => "That dog is deceased.",
        'success'     => "The dog's name has been changed!",
        'error'       => "The dog's name was not changed, please try again.",
    ),

    'save_notes' => array(
        'wrong_owner' => "You do not own that dog.",
        'deceased'    => "That dog is deceased.",
        'success'     => "The dog's notes have been saved!",
        'error'       => "The dog's notes were not saved, please try again.",
    ),

    'change_breed' => array(
        'wrong_owner' => "You do not own that dog.",
        'incomplete'  => "That dog has not been completed.",
        'deceased'    => "That dog is deceased.",
        'breed_originator' => 'The dog is an originator of a breed.',
        'same_breed'  => 'The dog is already that breed',
        'not_enough_credits' => "You do not have enough credits to change this dog's breed.",
        'breed_requirements_unmet' => 'Sorry, this dog does not fall under the breed standard for this breed. They have failed the following characteristics: :failedCharacteristics.',
        'success' => "The dog's breed has been changed!",
        'error'   => "The dog's breed was not changed, please try again.",
    ),

    'change_image' => array(
        'wrong_owner' => "You do not own that dog.",
        'deceased'    => "That dog is deceased.",
        'success'     => "The dog's image settings have been changed!",
        'error'       => "The dog's image settings were not changed, please try again.",
    ),

    'add_prefix' => array(
        'wrong_owner' => "You do not own that dog.",
        'incomplete'  => "That dog has not been completed.",
        'deceased'    => "That dog is deceased.",
        'already_prefixed' => 'The dog already has a prefix.',
        'no_prefix' => 'You must have a kennel prefix. You can create one on your Settings page.',
        'not_enough_credits' => "You do not have enough credits to prefix this dog.",
        'success' => "You have added a prefix to this dog!",
        'error'   => "The dog's did not have a prefix added, please try again.",
    ),

    'pet_home' => array(
        'incomplete_tutorial' => "You have not completed an imported step in the tutorial!",
        'incomplete'  => "That dog has not been completed.",
        'deceased'    => "That dog is deceased.",
        'success' => "You have successfully pet homed the dog!",
        'error'   => "The dog could not be pet homed, please try again.",
    ),

    'manage_studding' => array(
        'wrong_owner' => "You do not own that dog.",
        'deceased'    => "That dog is deceased.",
        'incomplete'  => "That dog has not been completed.",
        'not_male'    => "That dog cannot is not male.",
        'not_breedable' => "That dog cannot be bred.",
        'success'     => "The dog's stud status has been changed!",
        'error'       => "The dog's stud status was not changed, please try again.",
    ),

    'summarize_characteristics' => array(
        'not_upgraded'  => "You must be upgraded to perform that action.",
        'none_selected' => "You must select at least one characteristic to add.",
        'wrong_owner'   => "You do not own that dog.",
        'deceased'      => "That dog is deceased.",
        'incomplete'    => "That dog has not been completed.",
        'invalid_characteristic' => "Invalid characteristic chosen.",
        'success' => "You have added characteristics to this dog's summary.",
        'error'   => "The dog's summary could not be updated, please try again.",
    ),

    'remove_summarized_characteristic' => array(
        'not_upgraded'   => "You must be upgraded to perform that action.",
        'wrong_owner'    => "You do not own that dog.",
        'deceased'       => "That dog is deceased.",
        'incomplete'     => "That dog has not been completed.",
        'not_in_summary' => "That characteristic chosen is not in this dog's summary.",
        'success' => "You have removed a characteristic from this dog's summary.",
        'error'   => "The dog's summary could not be updated, please try again.",
    ),

    'complete' => array(
        'wrong_owner'       => "You do not own that dog.",
        'deceased'          => "That dog is deceased.",
        'already_completed' => "That dog has already been completed.",
        'success' => "You have completed this dog.",
        'error'   => "The dog could not be completed at this time, please try again.",
    ),

);
