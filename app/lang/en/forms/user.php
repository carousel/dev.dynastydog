<?php

return array(

    'trade_referral_points' => array(
        'not_enough_referral_points' => "You do not have enough referral points to trade in for that amount of credits.",
        'success' => "You have traded :referral_points for :credits.",
        'error'   => "Your referral points have not been traded for credits, please try again.",
    ),

    'update_basic' => array(
        'success' => "Your basic settings have been updated!",
    ),

    'change_password' => array(
        'wrong_password' => "You have input the wrong password for your current password.",
        'success'        => "Your password has been changed!",
    ),

    'block' => array(
        'cannot_block_self' => "You cannot block yourself.",
        'already_blocked'   => "You have already blocked :user.display_name (#:user.id).",
        'cannot_block'      => "You cannot block :user.display_name (#:user.id).",
        'success'           => "You have blocked :user.display_name (#:user.id).",
        'error'             => "The user could not be blocked at this time, please try again.",
    ),

    'unblock' => array(
        'not_blocked' => "You have not blocked :user.display_name (#:user.id).",
        'success'     => "You have unblocked :user.display_name (#:user.id).",
        'error'       => "The user could not be unblocked at this time, please try again.",
    ),

    'change_email' => array(
        'wrong_password' => "You have input the wrong password for your current password.",
        'success'        => "Your email has been changed!",
    ),

    'update_kennel_description' => array(
        'success' => "Your kennel description has been updated!",
    ),

    'give_chat_turns' => array(
        'not_enough_credits' => "You do not have enough credits to give away that many turns.",
        'success' => 'You have given away :turns in chat.',
        'error'   => "An error occurred while trying to give away turns in chat, please try again.",
    ),
    
    'claim_chat_turns' => array(
        'cannot_claim_own' => 'You cannot pick up your own turns.',
        'all_claimed'      => 'Oops, too late! All the turns :user.display_name (#:user.id) provided have already been picked up.',
        'success'          => 'Congratulations! You picked up a turn courtesy of :user.display_name (#:user.id)!',
        'error'            => "Could not pick up those chat turns at this time, please try again.",
    ),

    'purchase_upgrade' => array(
        'not_enough_credits' => "You do not have enough credits to upgrade your account.",
        'success'            => "You have added a 1 Month Upgrade to your account. Your current upgrade will end in :expires.",
        'error'              => "An error occurred while trying to upgrade your account, please try again.",
    ),

    'purchase_turns' => array(
        'not_enough_credits' => "You do not have enough credits to purchase that many turns.",
        'success'            => "You have added :turns to your account for :credits.",
        'error'              => "An error occurred while trying to purchase turns, please try again.",
    ),

    'purchase_imports' => array(
        'not_enough_credits' => "You do not have enough credits to purchase that many imports.",
        'success'            => "You have added :imports to your account for :credits.",
        'error'              => "An error occurred while trying to purchase imports, please try again.",
    ),

    'purchase_custom_imports' => array(
        'not_enough_credits' => "You do not have enough credits to purchase that many custom imports.",
        'success'            => "You have added :custom_imports to your account for :credits.",
        'error'              => "An error occurred while trying to purchase custom imports, please try again.",
    ),

    'gift_credits' => array(
        'cannot_gift_self'   => "You cannot gift credits to yourself.",
        'not_enough_credits' => "You do not have that many credits to gift.",
        'blocked_receiver'   => "You cannot send credits to that user.",
        'is_blocked'         => "You cannot send credits to that user.",
        'success'            => "You have gifted :credits to :receiver.display_name (#:receiver.id).",
        'error'              => "An error occurred while trying to gift your credits, please try again.",
    ),

    'gift_turns' => array(
        'cannot_gift_self'   => "You cannot gift turns to yourself.",
        'not_enough_credits' => "You do not have enough credits to gift that many turns.",
        'blocked_receiver'   => "You cannot send turns to that user.",
        'is_blocked'         => "You cannot send turns to that user.",
        'success'            => "You have gifted :turns to :receiver.display_name (#:receiver.id) for :credits.",
        'error'              => "An error occurred while trying to gift turns, please try again.",
    ),

    'gift_upgrade' => array(
        'cannot_gift_self'   => "You cannot gift an upgrade to yourself.",
        'not_enough_credits' => "You do not have enough credits to gift an upgrade.",
        'blocked_receiver'   => "You cannot upgrade that user.",
        'is_blocked'         => "You cannot upgrade that user.",
        'success'            => "You have gifted a 1 Month Upgrade to :receiver.display_name (#:receiver.id) for :credits.",
        'error'              => "An error occurred while trying to gift an upgrade, please try again.",
    ),

    'mark_notification_as_read' => array(
        'success' => "You have marked that notification as read.",
    ),

    'mark_all_notifications_as_read' => array(
        'success' => "You have marked all notifications as read.",
    ),

    'compose' => array(
        'blocked_receiver' => "You cannot send a message to that user.",
        'is_blocked'       => "You cannot send a message to that user.",
        'success'          => "You have sent a message.",
        'error'            => "An error occurred while trying to send a message, please try again.",
    ),

    'reply_to_conversation' => array(
        'not_in_inbox'     => "The conversation you are trying to reply to does not exist.",
        'blocked_receiver' => "You cannot send a message to that user.",
        'is_blocked'       => "You cannot send a message to that user.",
        'success'          => "You have replied to this conversation.",
        'error'            => "An error occurred while trying to reply to a conversation, please try again.",
    ),

    'delete_conversations' => array(
        'success' => "You have deleted those conversations.",
        'error'   => "An error occurred while trying to delete conversations, please try again.",
    ),

    'comment_on_news_post' => array(
        'success' => "You have commented on this news post.",
        'error'   => "An error occurred while trying to comment on a news post, please try again.",
    ),

    'vote_on_news_poll' => array(
        'already_voted_on' => "You have already cast your vote on that poll.",
        'success'          => "You have cast your vote.",
        'error'            => "An error occurred while trying to vote on a news poll, please try again.",
    ),

    'paypal' => array(
        'success' => ':credits has been successfully purchased and will be added to your account shortly.|:credits have been successfully purchased and will be added to your account shortly.',
    ),

    'import_dog' => array(
        'not_enough_imports' => "You do not have enough imports to import a dog.",
        'invalid_breed'      => "The breed you chose does not exist.",
        'invalid_age'        => "You cannot choose that age.",
        'tutorial_error'     => "Normally you'd be able to import this dog. However, now we need to find a mate for your original dog, and this mate must be of the opposite sex. Please import a 2-year-old :sex.",
        'success'            => "You have imported this dog!",
        'error'              => "An error occurred while trying to import a dog, please try again.",
    ),

    'create_contest' => array(
        'incomplete_prerequisites' => "The prerequisites are not complete.",
        'not_enough_requirements'  => "Not enough judging requirements.",
        'success'            => "You have created a contest!",
        'error'              => "An error occurred while trying to create a contest, please try again.",
    ),

    'create_contest_type' => array(
        'success' => "You have created a contest type!",
        'error'   => "An error occurred while trying to create a contest type, please try again.",
    ),

    'update_contest_type' => array(
        'success' => "You have updated this contest type!",
        'error'   => "An error occurred while trying to update this contest type, please try again.",
    ),

    'delete_contest_type' => array(
        'success' => "You have deleted a contest type!",
        'error'   => "An error occurred while trying to delete this contest type, please try again.",
    ),

    'add_prerequisites_to_contest_type' => array(
        'already_attached' => "The type already has a selected characteristic as a prerequisite.",
        'invalid_characteristic' => "Invalid characteristic chosen.",
        'success' => "You have added prerequisites to this contest type!",
        'error'   => "An error occurred while trying to add prerequisites this contest type, please try again.",
    ),

    'add_requirements_to_contest_type' => array(
        'already_attached' => "The type already has a selected characteristic as a judging requirement.",
        'invalid_characteristic' => "Invalid characteristic chosen.",
        'success' => "You have added judging requirements to this contest type!",
        'error'   => "An error occurred while trying to add judging requirements this contest type, please try again.",
    ),

    'delete_contest_type_prerequisite' => array(
        'success' => "You have removed a prerequisite from this contest type.",
        'error'   => "An error occurred while trying to remove a prerequisite this contest type, please try again.",
    ),

    'delete_contest_type_requirement' => array(
        'success' => "You have removed a judging requirement from this contest type.",
        'error'   => "An error occurred while trying to remove a judging requirement this contest type, please try again.",
    ),

    'update_contest_type_requirement' => array(
        'success' => "You have updated a judging requirement on this contest type!",
        'error'   => "An error occurred while trying to update a judging requirement on this contest type, please try again.",
    ),

    'update_contest_type_prerequisite' => array(
        'invalid_range' => "Invalid range chosen.",
        'invalid_genotype' => "Invalid genotype chosen.",
        'invalid_phenotype' => "Invalid phenotype chosen.",
        'success' => "You have updated a prerequisite on this contest type!",
        'error'   => "An error occurred while trying to update a prerequisite on this contest type, please try again.",
    ),

    'enter_dog_in_contest' => array(
        'invalid_dog'    => "The dog you have selected is invalid.",
        'already_worked' => "The dog cannot enter a contest because it has already been worked.",
        'has_ran'        => "The contest is over.",
        'unmet_prerequisites' => "The dog does not meet all of the prerequisites.",
        'locked_requirements' => "The dog does not have all of the requirements unlocked.",
        'already_entered'     => "The dog has already been entered.",
        'success' => "Your dog has been successfully entered into the contest!",
        'error'   => "An error occurred while trying to enter your dog into a contest, please try again.",
    ),

    'roll_challenge' => array(
        'too_many_incomplete' => "You cannot create any more challenges.",
        'cannot_continue_tutorial' => "You must own a dog to continue with the tutorial.",
        'no_testable_characteristics_found' => "An error occurred while trying to roll a challenge, please try again.",
        'no_testable_dog_characteristics_found' => "An error occurred while trying to roll a challenge, please try again.",
        'not_enough_characteristics_generated' => "An error occurred while trying to roll a challenge, please try again.",
        'success'     => "You have successfully rolled a new challenge!",
        'error'       => "An error occurred while trying to roll a challenge, please try again.",
    ),

    'reroll_challenge' => array(
        'not_enough_credits' => "You do not have enough credits to reroll a challenge.",
        'cannot_be_rerolled' => "You cannot reroll that challenge.",
        'cannot_continue_tutorial' => "You must own a dog to continue with the tutorial.",
        'no_testable_characteristics_found' => "An error occurred while trying to roll a challenge, please try again.",
        'no_testable_dog_characteristics_found' => "An error occurred while trying to roll a challenge, please try again.",
        'not_enough_characteristics_generated' => "An error occurred while trying to roll a challenge, please try again.",
        'success'     => "You have rolled a new challenge for :credits.",
        'error'       => "An error occurred while trying to roll a challenge, please try again.",
    ),

    'enter_dog_in_community_challenge' => array(
        'dog_already_entered' => "The dog you have selected has already been entered.",
        'invalid_dog'         => "The dog you have selected is invalid.",
        'requirements_unmet'  => "Sorry, that dog does not fulfill or has not been tested for the community challenge requirements.",
        'success'             => "You have successfully entered your dog in a community challenge!",
        'error'               => "An error occurred while trying to enter a community challenge, please try again.",
    ),

    'claimed_community_challenge_credit_prize' => array(
        'success' => "You have claimed :credits!",
        'error'   => "An error occurred while trying to claim your credit prize from a community challenge, please try again.",
    ),

    'claimed_community_challenge_breeders_prize' => array(
        'success' => "You have claimed the Breeders's Prize!",
        'error'   => "An error occurred while trying to claim your Breeder's Prize from a community challenge, please try again.",
    ),

    'complete_challenge' => array(
        'invalid_dog' => "The dog you have selected is invalid.",
        'characteristics_unmet' => "Sorry, that dog does not fulfill or has not been tested for the challenge requirements.",
        'success'     => "You have completed a challenge!",
        'error'       => "An error occurred while trying to complete a challenge, please try again.",
    ),

    'create_personal_goal' => array(
        'success' => "You have added a new personal goal!",
        'error'   => "An error occurred while trying to add a personal goal, please try again.",
    ),

    'delete_personal_goal' => array(
        'success' => "You have deleted a personal goal.",
        'error'   => "An error occurred while trying to delete a personal goal, please try again.",
    ),

    'complete_personal_goal' => array(
        'success' => "You have completed a personal goal!",
        'error'   => "An error occurred while trying to complete a personal goal, please try again.",
    ),

    'update_personal_goal' => array(
        'success' => "You have updated a personal goal!",
        'error'   => "An error occurred while trying to update a personal goal, please try again.",
    ),

    'mass_test_dogs' => array(
        'not_upgraded'    => "You must be upgraded to do that.",
        'test_not_found'  => "Invalid test selected.",
        'test_not_active' => "Invalid test selected.",
        'success' => "You have mass tested the selected dogs.",
        'error'   => "An error occurred while trying to mass test your dogs, please try again.",
    ),

    'compare_dog_characteristics' => array(
        'characteristic_not_found' => "Invalid characteristic selected.",
        'error'   => "An error occurred while trying to compare your dogs, please try again.",
    ),

    'move_dogs_to_kennel_group' => array(
        'kennel_group_not_found' => "Invalid tab selected.",
        'success' => "You have moved the selected dogs.",
        'error'   => "An error occurred while trying to move your dogs, please try again.",
    ),

    'manage_dogs_studding' => array(
        'invalid_studding' => "Invalid stud option selected.",
        'success' => "You have managed the studding for your selected dogs.",
        'error'   => "An error occurred while trying to manage your dogs's studding, please try again.",
    ),

    'request_beginners_luck' => array(
        'invalid_beginner' => "Invalid beginner selected.",
        'success' => "You have requested some Beginner's Luck!",
        'error'   => "An error occurred while trying to request Beginner's Luck, please try again.",
    ),

    'revoke_beginners_luck' => array(
        'success' => "You have canceled a Beginner's Luck Request.",
        'error'   => "An error occurred while trying to revoke a Beginner's Luck Request, please try again.",
    ),

    'reject_beginners_luck' => array(
        'success' => "You have rejected a Beginner's Luck Request.",
        'error'   => "An error occurred while trying to reject a Beginner's Luck Request, please try again.",
    ),

    'accept_beginners_luck' => array(
        'dog_not_breedable'   => "The dog is not breedable.",
        'bitch_not_breedable' => "The bitch is not breedable.",
        'success' => "Great! You have used your Beginner's Luck and helped breed :dog and :bitch for :owner. As a thank you, you received a free turn!",
        'error'   => "An error occurred while trying to accept a Beginner's Luck Request, please try again.",
    ),

    'breed_dogs' => array(
        'dog_not_found'       => "The dog selected is invalid.",
        'dog_not_breedable'   => "The dog is not breedable.",
        'bitch_not_found'     => "The bitch selected is invalid.",
        'bitch_not_breedable' => "The bitch is not breedable.",
        'success' => "You have bred :bitch to :dog!",
        'error'   => "An error occurred while trying to breed the dogs, please try again.",
    ),

    'request_breeding' => array(
        'not_up_for_stud'   => "The dog is not up for stud.",
        'already_requested' => "There is already a pending stud request with that bitch and dog.",
        'success' => "You have sent a stud request to the owner of :stud to breed with :bitch!",
        'error'   => "An error occurred while trying to request a stud, please try again.",
    ),

    'accept_stud_request' => array(
        'not_found' => "Invalid stud request selected.",
        'dog_not_breedable'   => "The stud is not breedable.",
        'bitch_not_breedable' => "The bitch is not breedable.", 
        'success' => "You have accepted a stud request!",
        'error'   => "An error occurred while trying to accept a stud request, please try again.",
    ),

    'reject_stud_request' => array(
        'not_found' => "Invalid stud request selected.",
        'success' => "You have rejected a stud request.",
        'error'   => "An error occurred while trying to reject a stud request, please try again.",
    ),

    'remove_stud_request' => array(
        'not_found' => "Invalid stud request selected.",
        'success' => "You have removed a stud request.",
        'error'   => "An error occurred while trying to remove a stud request, please try again.",
    ),

    'breed_stud_request' => array(
        'not_found' => "Invalid stud request selected.",
        'dog_not_breedable'   => "The stud cannot be bred to right now.",
        'bitch_not_breedable' => "The bitch cannot be bred to right now.",
        'success' => "You have bred :bitch to :stud.",
        'error'   => "An error occurred while trying to breed your dogs, please try again.",
    ),

    'send_lend_request' => array(
        'incomplete_tutorial' => "You have not completed an imported step in the tutorial!",
        'wrong_owner' => "You do not own that dog.",
        'deceased'    => "That dog is deceased.",
        'same_user'   => "You cannot send a dog to yourself.",
        'ownership_pending' => "The dog is already pending a new owner.",
        'success' => "You have sent a request to send this dog to :receiver.",
        'error'   => "The request to send your dog to another user was not created, please try again.",
    ),

    'revoke_lend_request' => array(
        'success' => "You have revoked the request to send this dog.",
        'error'   => "An error occurred while trying to revoke this dog's request to send, please try again.",
    ),

    'reject_lend_request' => array(
        'success' => "You have rejected this dog.",
        'error'   => "An error occurred while trying to reject a dog, please try again.",
    ),

    'accept_lend_request' => array(
        'success' => "You have accepted this dog.",
        'error'   => "An error occurred while trying to accept a dog, please try again.",
    ),

    'return_lend_request' => array(
        'success' => "You have sent back this dog early.",
        'error'   => "An error occurred while trying to send back a dog, please try again.",
    ),

    'advance_turn' => array(
        'requested_beginners_luck' => 'Sorry, you cannot advance the turn while you have an unfulfilled request for Beginner\'s Luck. Please wait for it to be answered or visit the Notification Centre to cancel it.',
        'not_enough_turns' => "You do not have enough turns to advance.",
        'success' => "You have advanced a turn!",
        'error'   => "An error occurred while trying to advance your turn, please try again.",
    ),

    'create_breed_draft' => array(
        'success' => "You have started this breed registry entry draft.",
        'error'   => "An error occurred while trying to start a breed registry entry draft, please try again.",
    ),

    'save_breed_draft' => array(
        'invalid_image'  => "The image is invalid.",
        'could_not_save_image' => "The image could not be saved.",
        'wrong_owner'    => "You do not own that dog.",
        'deceased_dog'   => "The dog is deceased.",
        'incomplete_dog' => "The dog has not been completed.",
        'not_enough_generations'  => "The dog does not have enough generations in their pedigree.",
        'dog_is_breed_originator' => "The dog is an originator of a breed.",
        'success' => "You have updated this breed registry entry draft!",
        'error'   => "An error occurred while trying to save, please try again.",
    ),

    'delete_breed_draft' => array(
        'success' => "You have deleted breed registry entry draft :breedDraft.name (#:breedDraft.id).",
        'error'   => "An error occurred while trying to delete this breed registry entry draft, please try again.",
    ),

    'add_characteristics_to_breed_draft' => array(
        'official' => "You cannot add a characteristic to a real breed registry entry draft.",
        'none_selected' => "You must select at least one characteristic to add.",
        'invalid_characteristic' => "Invalid characteristic chosen.",
        'success' => "You have added the following characteristics: :characteristics.",
        'error'   => "An error occurred while trying to add characteristics, please try again.",
    ),

    'save_breed_draft_characteristic' => array(
        'success' => "You have updated this characteristic!",
        'error'   => "An error occurred while trying to update a characteristic, please try again.",
    ),

    'remove_characteristic_from_breed_draft' => array(
        'success' => "You have removed a characteristic from this breed registry entry draft.",
        'error'   => "An error occurred while trying to remove the characteristic, please try again.",
    ),

    'submit_breed_draft' => array(
        'missing_dog'    => "You must choose a dog to be the originator of the breed.",
        'wrong_owner'    => "You do not own that dog.",
        'deceased_dog'   => "The dog is deceased.",
        'incomplete_dog' => "The dog has not been completed.",
        'not_enough_generations'  => "The dog does not have enough generations in their pedigree.",
        'dog_is_breed_originator' => "The dog is an originator of a breed.",
        'no_characteristics' => "You must add at least one characteristic.",
        'female_ranged_value_out_of_bounds' => "There range values chosen for bitches for :characteristic are out of bounds.",
        'male_ranged_value_out_of_bounds'   => "There range values chosen for dogs for :characteristic are out of bounds.",
        'incomplete_characteristic' => "The :characteristic characteristic is incomplete.",
        'genotypes_not_found_in_characteristic' => "The following genotypes do not exist for the :characteristic characteristic: :genotypes.",
        'internally_conflicted_characteristic'  => "There is a conflict between the phenotypes and genotypes selected in the :characteristic characteristic.",
        'phenotype_not_found_in_characteristic' => ":phenotype does not exist for the :characteristic characteristic.",
        'externally_conflicted_characteristic'  => "There is a conflict between :characteristic and at least one other characteristic.",
        'requirements_unmet_by_dog'      => "The dog you chose fails on the following characteristics: :failedCharacteristics.",
        'requirements_unmet_by_ancestor' => "A dog in your chosen dog's pedigree fails on the following characteristics: :failedCharacteristics.",
        'success' => "You have submitted this breed registry entry draft.",
        'error'   => "An error occurred while trying to submit this breed registry entry draft, please try again.",
    ),

    'revert_breed_draft' => array(
        'success' => "You have reverted this breed registry entry back to a draft.",
        'error'   => "An error occurred while trying to revert a breed registry entry, please try again.",
    ),

    'resubmit_breed_draft' => array(
        'missing_dog'    => "You must choose a dog to be the originator of the breed.",
        'wrong_owner'    => "You do not own that dog.",
        'deceased_dog'   => "The dog is deceased.",
        'incomplete_dog' => "The dog has not been completed.",
        'not_enough_generations'  => "The dog does not have enough generations in their pedigree.",
        'dog_is_breed_originator' => "The dog is an originator of a breed.",
        'requirements_unmet_by_dog'      => "The dog you chose fails on the following characteristics: :failedCharacteristics.",
        'requirements_unmet_by_ancestor' => "A dog in your chosen dog's pedigree fails on the following characteristics: :failedCharacteristics.",
        'success' => "You have resubmitted this breed.",
        'error'   => "An error occurred while trying to resubmit this breed , please try again.",
    ),

    'custom_import_dog' => array(
        'not_enough_custom_imports'        => "You do not have enough custom imports to custom import a dog.",
        'invalid_breed'                   => "The breed you chose does not exist.",
        'invalid_age'                     => "The age you chose is invalid.",
        'same_characteristic'             => "You cannot select the same characteristic more than once.",
        'no_characteristics'              => "No characteristics specified.",
        'too_many_characteristics'        => "You may only choose 3 to customize.",
        'invalid_characteristic'          => "Invalid characteristic.",
        'blank_characteristic'            => "You have a blank characteristic selected. Please complete it or remove it from the form.",
        'duplicate_characteristics'       => ":characteristic is a duplicate.",
        'incomplete_characteristic'       => ":characteristic is incomplete.",
        'ranged_value_out_of_bounds'      => ":ranged_value is out of bounds for :characteristic.",
        'genotypes_not_in_breed'          => "The genotype, :genotype, does not exist in :breed.",
        'genotypes_not_in_characteristic' => "The genotype, :genotype, does not exist for :characteristic.",
        'too_many_phenotypes'             => "Too many phenotypes selected for :characteristic.",
        'phenotype_not_in_breed'          => "The phenotype, :phenotype, does not exist in :breed.",
        'phenotype_not_in_characteristic' => "The phenotype, :phenotype, does not exist in :characteristic.",
        'internally_conflicted_characteristic' => "Sorry, unfortunately the phenotype(s) and genotypes(s) you have selected for :characteristic are incompatible with each other.",
        'externally_conflicted_characteristic' => "Unfortunately the settings you selected for :characteristic and other characteristics (:conflicted_characteristics) are incompatible with each other. Please change one or both to proceed.",
        'too_many_base_phenotypes'        => "You can only choose one base phenotype for :characteristic.",
        'base_phenotype_not_found'        => "You must choose at least one base phenotype for :characteristic.",
        'incompatible_characteristics'    => "The settings you chose for :characteristic are incompatible with one or more characteristics selected.",
        'unresolved_characteristic'       => "The settings you chose for :characteristic could not be resolved.",
        'success'                         => "You have custom imported this dog!",
        'error'                           => "An error occurred while trying to custom import a dog, please try again.",
    ),

    'pet_home_dogs' => array(
        'incomplete_tutorial' => "You have not completed an imported step in the tutorial!",
        'success' => "You have successfully pet homed the selected dogs!",
        'error'   => "The dogs could not be pet homed, please try again.",
    ),

    'create_kennel_group' => array(
        'not_upgraded' => "You must be upgraded to do that.",
        'at_capacity'  => "You cannot create a new kennel tab.",
        'success'      => "You have added a new tab.",
        'error'        => "An error occurred while trying to add a new tab, please try again.",
    ),

    'delete_kennel_group' => array(
        'not_empty' => "The kennel tab must be vacant.",
        'last_kennel_group'  => "You cannot delete your last kennel tab.",
        'success'   => "You have deleted a tab.",
        'error'     => "An error occurred while trying to delete a tab, please try again.",
    ),

    'update_kennel_group' => array(
        'success' => "You have updated a tab.",
        'error'   => "An error occurred while trying to update a tab, please try again.",
    ),

);
