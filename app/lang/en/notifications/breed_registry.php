<?php

return array(

    'official_breed_draft_approved' => array(
        'to_user' => 'Your submission of the breed <a href=":manageBreedDraftsUrl?<<read_querystring>>">:breed</a> has been approved and will be available in the game soon. Stay tuned!',
    ),

    'breed_draft_approved' => array(
        'to_user' => 'Congratulations! Your breed submission for <a href=":manageBreedDraftsUrl?<<read_querystring>>">:breed</a> has been approved, and <a href=":originatorUrl">:originator</a> has been automatically registered as a :breed. You can now register other dogs as :breed if they meet breed requirements. Remember to have at least :active_dogs registered in the next :grace_period in order to avoid breed extinction!',
    ),

    'breed_draft_rejected' => array(
        'to_user' => 'Unfortunately, your breed submission for :breedDraft has been rejected. <a href=":breedDraftUrl?<<read_querystring>>">Click here to view the reasons</a>.',
    ),

    'breed_extinct' => array(
        'to_creator' => 'Warning! The breed you created, :breed, has gone extinct due to the number of active breed members dropping below :active_dogs. The breed no longer shows up in the Breed Registry, and no more dogs are able to be registered under that breed. The breed entry has been relocated to the <a href=":manageBreedDraftsUrl?<<read_querystring>>">Extinct section of your Manage Breeds page</a> for you to resubmit if you wish.',
    ),

    'breed_endangered' => array(
        'to_creator' => 'Warning! The breed you created, :breed, currently has :active_dogs and is in danger of extinction next Sunday if the number of active breed members dips below :required_active_dogs! If the breed goes extinct it will no longer show up in the <a href=":breedUrl?<<read_querystring>>">Breed Registry</a>, and no more dogs will be able to be registered under that breed. The breed entry will be relocated to the <a href=":manageBreedDraftsUrl?<<read_querystring>>">Extinct section of your Manage Breeds page</a> for you to resubmit if you wish.',
        'to_owner' => 'Warning! :breed currently has :active_dogs and is in danger of extinction next Sunday if the number of active breed members dips below :required_active_dogs! If the breed goes extinct it will no longer show up in the <a href=":breedUrl?<<read_querystring>>">Breed Registry</a>, and no more dogs will be able to be registered under that breed.',
    ),

);
