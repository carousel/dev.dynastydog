<?php

return array(

    'name'  => 'Dynasty', 

    'require_alpha_code' => false, // false for no, true for yes
    'maintenance_mode_message' => "Moving the recode over to the main site. We'll be back soon!",

    'start_payment_weeks'  => '2014-08-25 00:00:00', 

    'formulas' => array(
	    'immune_system_get'  => '0.15 - (:is * 0.15)', // :is = immune system range characteristic value of dog
        'immune_system_heal' => '(:is + 0.1) * 0.2', // :is = immune system range characteristic value of dog
        'ranged_characteristic_wiggle_room_close_to_midpoint' => '(((((:dam_fdo + :sire_fdo) * 0.4) / 2.00) + 20.00) / 100.00) * :diff',
        'ranged_characteristic_wiggle_room_far_from_midpoint' => '(60.00 / 100.00) * :diff',
    ), 

    'dog' => array(
        'prefix_cost'            => 1, // Credits
        'change_breed_cost'      => 10, // Credits
        'min_puppy_age'          => 0, // <= Months
        'max_puppy_age'          => 6, // <= Months
        'litter_size_variation'  => 2, // Puppies
        'pedigree_display_limit' => 4, // Generations
        'months_to_age'          => 2, // Months
        'advanced_turn_worked_limit' => 100, // 100 Worked Dogs
    ), 

    'user' => array(
        'starting' => array(
            'kennel_name'       => 'My Kennel',
            'turns'             => 10, // Turns
            'imports'           => 2, // Imports
            'custom_imports'    => 0, // Custom imports
            'kennel_groups'     => 3, // # primary
            'gifts_given'       => 0, // # gifts
            'show_gifter_level' => true, // Show it
            'total_completed_challenges' => 0, // # individual challenges completed, 
            'total_referrals'   => 0,  // # users referred
        ), 
        'upgrade_cost'              => 50, // Credits
        'gift_upgrade_bonus'        => 10, // Credits
        'max_kennel_groups'         => 15, // #
        'max_individual_challenges' => 3, // #
        'online_threshold'          => 10, // Minutes
    ), 

    'characteristics' => array(
        'ranged_value_percent_of_time_close_to_midpoint' => 90, // %, related formula: range_characteristic_wiggle_room_close_to_midpoint
    ), 

    'challenge' => array(
        'max_rolled'       => 3, // Challenges
        'reroll_cost'      => 5, // Credits
        'acceptance_range' => 15, // +- units
    ), 

    'community_challenge' => array(
        'acceptance_range'        => 15, // +- units
        'breeders_prize_duration' => 30, // Days
        'credit_prize'            => 20, // Credits
    ), 

    'breed' => array(
        'generations_needed' => 5, // # dog generations
        'active_threshold'   => 35, // Dogs
        'active_extinction'  => 20, // Dogs
        'grace_period'       => 7, // Days
    ), 

    'contest' => array(
        'max_days_in_advance'       => 4, // Days
        'max_prerequisites'         => 3, // Characteristics
        'max_requirements'          => 3, // Characteristics
        'random_score_adjustment'   => 5, // %
        'symptoms_score_adjustment' => 10, // %
        'size_min_small'            => 1, // Dogs
        'size_min_medium'           => 6, // Dogs
        'size_min_large'            => 15, // Dogs
    ), 

    'referral' => array(
        'points_per_credit'     => 1, // X Points = 1 Credit
        'reset_dog_status_cost' => 1, // Points
        'max_per_ip'            => 2, // Users
    ), 

    'tutorial' => array(
        'completion_turns' => 5, // Turns
    ), 

    'import' => array(
        'price' => 20, // Credits
        'min_purchase_amount' => 1, // Imports
        'max_purchase_amount' => 10, // Imports
    ), 
    
    'custom_import' => array(
        'price' => 60, // Credits
        'min_purchase_amount' => 1, // Imports
        'max_purchase_amount' => 10, // Imports
    ), 

);
