<?php

return array(

    'approve_breed_draft' => array(
        'not_pending'    => "Invalid breed draft.",
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
        'could_not_save_image' => "The image could not be saved.",
        'success' => "You have approved this breed registry entry draft.",
        'error'   => "An error occurred while trying to approve this breed registry entry draft, please try again.",
        'duplicate_rejected' => "This breed has just been added to the Breed Registry based on another user's submission.",
    ),

    'reject_breed_draft' => array(
        'success' => "You have rejected a breed registry entry draft.",
        'error'   => "An error occurred while trying to reject this breed registry entry draft, please try again.",
    ),

    'create_alpha_code' => array(
        'success' => "Alpha code has been successfully created.",
        'error'   => "An error occurred while trying create an alpha code, please try again.",
    ),

    'update_alpha_code' => array(
        'success' => "Alpha code has been successfully updated.",
        'error'   => "An error occurred while trying update the alpha code, please try again.",
    ),

    'delete_alpha_code' => array(
        'success' => "Alpha code has been successfully deleted.",
        'error'   => "An error occurred while trying to delete an alpha code, please try again.",
    ),

    'create_news_post' => array(
        'success' => "News post has been successfully created.",
        'error'   => "An error occurred while trying create a news post, please try again.",
    ),

    'update_news_post' => array(
        'success' => "News post has been successfully updated.",
        'error'   => "An error occurred while trying update the news post, please try again.",
    ),

    'delete_news_post' => array(
        'success' => "News post has been successfully deleted.",
        'error'   => "An error occurred while trying to delete a news post, please try again.",
    ),

    'add_news_poll_to_news_post' => array(
        'news_poll_already_attached' => "That poll is already attached to this post.",
        'success' => "You added a poll to this post.",
        'error'   => "An error occurred while trying add a news poll to this news post, please try again.",
    ),

    'remove_news_poll_from_news_post' => array(
        'success' => "You removed a poll from this post.",
        'error'   => "An error occurred while trying remove a news poll from this news post, please try again.",
    ),

    'create_news_poll' => array(
        'success' => "News poll has been successfully created.",
        'error'   => "An error occurred while trying create a news poll, please try again.",
    ),

    'update_news_poll' => array(
        'success' => "News poll has been successfully updated.",
        'error'   => "An error occurred while trying update the news poll, please try again.",
    ),

    'delete_news_poll' => array(
        'success' => "News poll has been successfully deleted.",
        'error'   => "An error occurred while trying to delete a news poll, please try again.",
    ),

    'add_answer_to_news_poll' => array(
        'success' => "You added an answer to this poll.",
        'error'   => "An error occurred while trying add an answer to this news poll, please try again.",
    ),

    'update_news_poll_answer' => array(
        'success' => "News poll answer has been successfully updated.",
        'error'   => "An error occurred while trying update a news poll answer, please try again.",
    ),

    'delete_news_poll_answer' => array(
        'success' => "News poll answer has been successfully deleted.",
        'error'   => "An error occurred while trying to delete a news poll answer, please try again.",
    ),

    'create_news_post_comment' => array(
        'success' => "News post comment has been successfully created.",
        'error'   => "An error occurred while trying create a news post comment, please try again.",
    ),

    'update_news_post_comment' => array(
        'success' => "News post comment has been successfully updated.",
        'error'   => "An error occurred while trying update the news post comment, please try again.",
    ),

    'delete_news_post_comment' => array(
        'success' => "News post comment has been successfully deleted.",
        'error'   => "An error occurred while trying to delete a news post comment, please try again.",
    ),

    'create_symptom' => array(
        'success' => "Symptom has been successfully created.",
        'error'   => "An error occurred while trying create a symptom, please try again.",
    ),

    'update_symptom' => array(
        'success' => "Symptom has been successfully updated.",
        'error'   => "An error occurred while trying update the symptom, please try again.",
    ),

    'delete_symptom' => array(
        'success' => "Symptom has been successfully deleted.",
        'error'   => "An error occurred while trying to delete a symptom, please try again.",
    ),

    'remove_symptom_from_characteristic_severities' => array(
        'success' => "The symptom has been successfully removed from the characteristic's severities.",
        'error'   => "An error occurred while trying remove the symptom from the characteristic's severities, please try again.",
    ),

    'create_characteristic' => array(
        'success' => "Characteristic has been successfully created.",
        'error'   => "An error occurred while trying create a characteristic, please try again.",
    ),

    'update_characteristic' => array(
        'success' => "Characteristic has been successfully updated.",
        'error'   => "An error occurred while trying update the characteristic, please try again.",
    ),

    'delete_characteristic' => array(
        'success' => "Characteristic has been successfully deleted.",
        'error'   => "An error occurred while trying to delete a characteristic, please try again.",
    ),

    'update_characteristic_range' => array(
        'success' => "The range settings has been successfully updated.",
        'error'   => "An error occurred while trying update the range settings, please try again.",
    ),

    'remove_characteristic_range' => array(
        'success' => "The range settings has been removed.",
        'error'   => "An error occurred while trying remove the range settings, please try again.",
    ),

    'update_characteristic_genetics' => array(
        'success' => "The genetic settings has been successfully updated.",
        'error'   => "An error occurred while trying update the genetic settings, please try again.",
    ),

    'remove_characteristic_genetics' => array(
        'success' => "The genetic settings has been removed.",
        'error'   => "An error occurred while trying remove the genetic settings, please try again.",
    ),

    'update_characteristic_health' => array(
        'success' => "The health settings has been successfully updated.",
        'error'   => "An error occurred while trying update the health settings, please try again.",
    ),

    'remove_characteristic_health' => array(
        'success' => "The health settings has been removed.",
        'error'   => "An error occurred while trying remove the health settings, please try again.",
    ),

    'add_label_to_characteristic' => array(
        'success' => "A label has been successfully added to this characteristic.",
        'error'   => "An error occurred while trying add a label to the characteristic, please try again.",
    ),

    'add_label_to_characteristic' => array(
        'not_ranged' => "You can only add a label to a ranged characteristic.",
        'success'    => "A label has been successfully added to this characteristic.",
        'error'      => "An error occurred while trying add a label to the characteristic, please try again.",
    ),

    'remove_label_from_characteristic' => array(
        'success' => "A label has been successfully removed from this characteristic.",
        'error'   => "An error occurred while trying remove a label from the characteristic, please try again.",
    ),

    'add_severity_to_characteristic' => array(
        'success' => "A severity has been successfully added to this characteristic.",
        'error'   => "An error occurred while trying add a severity to the characteristic, please try again.",
    ),

    'update_characteristic_severity' => array(
        'success' => "The characteristic severity has been successfully updated.",
        'error'   => "An error occurred while trying update the characteristic severity, please try again.",
    ),

    'remove_severity_from_characteristic' => array(
        'success' => "A severity has been successfully removed from this characteristic.",
        'error'   => "An error occurred while trying remove a severity from the characteristic, please try again.",
    ),

    'add_symptom_to_characteristic_severity' => array(
        'success' => "A symptom has been successfully added to this characteristic severity.",
        'error'   => "An error occurred while trying add a symptom to the characteristic severity, please try again.",
    ),

    'update_characteristic_severity_symptom' => array(
        'success' => "The characteristic severity symptom has been successfully updated.",
        'error'   => "An error occurred while trying update the characteristic severity symptom, please try again.",
    ),

    'remove_symptom_from_characteristic_severity' => array(
        'success' => "A symptom has been successfully removed from this characteristic severity.",
        'error'   => "An error occurred while trying remove a symptom from the characteristic severity, please try again.",
    ),

    'create_characteristic_category' => array(
        'success' => "Characteristic category has been successfully created.",
        'error'   => "An error occurred while trying create a characteristic category, please try again.",
    ),

    'update_characteristic_category' => array(
        'success' => "Characteristic category has been successfully updated.",
        'error'   => "An error occurred while trying update the characteristic category, please try again.",
    ),

    'delete_characteristic_category' => array(
        'success' => "Characteristic category has been successfully deleted.",
        'error'   => "An error occurred while trying to delete a characteristic category, please try again.",
    ),

    'remove_child_characteristic_category_from_parent_characteristic_category' => array(
        'success' => "You removed a child characteristic category from this characteristic category.",
        'error'   => "An error occurred while trying remove a child characteristic category from this characteristic category, please try again.",
    ),

    'remove_characteristic_from_characteristic_category' => array(
        'success' => "You removed a characteristic from this characteristic category.",
        'error'   => "An error occurred while trying remove a characteristic from this characteristic category, please try again.",
    ),

    'create_characteristic_test' => array(
        'success' => "Characteristic test has been successfully created.",
        'error'   => "An error occurred while trying create a characteristic test, please try again.",
    ),

    'update_characteristic_test' => array(
        'success' => "Characteristic test has been successfully updated.",
        'error'   => "An error occurred while trying update the characteristic test, please try again.",
    ),

    'delete_characteristic_test' => array(
        'success' => "Characteristic test has been successfully deleted.",
        'error'   => "An error occurred while trying to delete a characteristic test, please try again.",
    ),

    'create_characteristic_dependency' => array(
        'success' => "Characteristic dependency has been successfully created.",
        'error'   => "An error occurred while trying create a characteristic dependency, please try again.",
    ),

    'update_characteristic_dependency' => array(
        'success' => "Characteristic dependency has been successfully updated.",
        'error'   => "An error occurred while trying update the characteristic dependency, please try again.",
    ),

    'delete_characteristic_dependency' => array(
        'success' => "Characteristic dependency has been successfully deleted.",
        'error'   => "An error occurred while trying to delete a characteristic dependency, please try again.",
    ),

    'add_independent_characteristic_to_characteristic_dependency' => array(
        'success' => "You have successfully added an independent characteristic to this dependency.",
        'error'   => "An error occurred while trying add an independent characteristic to this dependency, please try again.",
    ),

    'remove_characteristic_dependency_independent_characteristic' => array(
        'success' => "You have successfully removed an independent characteristic from this dependency.",
        'error'   => "An error occurred while trying remove an independent characteristic from this dependency, please try again.",
    ),

    'update_characteristic_dependency_independent_characteristic_percents' => array(
        'invalid_percents' => "This type of dependency doesn't need that.",
        'success' => "You have successfully updated the percents of an independent characteristic.",
        'error'   => "An error occurred while trying update the percents an independent characteristic, please try again.",
    ),

    'create_g2r_characteristic_dependency_group' => array(
        'invalid_type' => "This type of dependency doesn't need that.",
        'success' => "You have successfully created a group in this dependency.",
        'error'   => "An error occurred while trying create a group in this dependency, please try again.",
    ),

    'add_range_to_characteristic_dependency_group' => array(
        'invalid_type' => "This type of dependency doesn't need that.",
        'success' => "You have successfully added a range to a group.",
        'error'   => "An error occurred while trying add a range to a group in this dependency, please try again.",
    ),

    'remove_range_from_characteristic_dependency_group' => array(
        'success' => "You have successfully removed a range from a group.",
        'error'   => "An error occurred while trying remove a range from  a group in this dependency, please try again.",
    ),

    'delete_characteristic_dependency_group' => array(
        'success' => "A group in this dependency has been successfully deleted.",
        'error'   => "An error occurred while trying to delete a group, please try again.",
    ),

    'update_g2x_characteristic_dependency_group' => array(
        'invalid_type' => "This type of dependency doesn't need that.",
        'success' => "You have successfully updated the group.",
        'error'   => "An error occurred while trying update a group, please try again.",
    ),

    'update_x2g_characteristic_dependency_group' => array(
        'invalid_type' => "This type of dependency doesn't need that.",
        'success' => "You have successfully updated the group.",
        'error'   => "An error occurred while trying update a group, please try again.",
    ),

    'create_x2g_characteristic_dependency_group' => array(
        'invalid_type' => "This type of dependency doesn't need that.",
        'success' => "You have successfully created a group in this dependency.",
        'error'   => "An error occurred while trying create a group in this dependency, please try again.",
    ),

    'add_genotypes_to_characteristic_dependency_group' => array(
        'invalid_type' => "This type of dependency doesn't need that.",
        'success' => "You have successfully updated the genotype results in a group.",
        'error'   => "An error ocurred while trying to update the genotype results of a group, please try again.",
    ),

    'add_independent_range_to_characteristic_dependency_group' => array(
        'invalid_independent_characteristic' => "Invalid independent characteristc.",
        'invalid_type' => "This type of dependency doesn't need that.",
        'success' => "You have successfully added an independent range to a group.",
        'error'   => "An error ocurred while trying to add an independent range to a group, please try again.",
    ),

    'remove_independent_range_from_characteristic_dependency_group' => array(
        'success' => "You have successfully removed an independent range from a group.",
        'error'   => "An error occurred while trying remove an independent range from  a group in this dependency, please try again.",
    ),

    'create_forum' => array(
        'success' => "Forum has been successfully created.",
        'error'   => "An error occurred while trying create a forum, please try again.",
    ),

    'update_forum' => array(
        'success' => "Forum has been successfully updated.",
        'error'   => "An error occurred while trying update the forum, please try again.",
    ),

    'delete_forum' => array(
        'success' => "Forum has been successfully deleted.",
        'error'   => "An error occurred while trying to delete a forum, please try again.",
    ),

    'update_forum_post' => array(
        'success' => "Forum post has been successfully updated.",
        'error'   => "An error occurred while trying update the forum post, please try again.",
    ),

    'delete_forum_post' => array(
        'success' => "Forum post has been successfully deleted.",
        'error'   => "An error occurred while trying to delete a forum post, please try again.",
    ),

    'permanently_delete_forum_post' => array(
        'success' => "Forum post has been permanently deleted.",
        'error'   => "An error occurred while trying to permanently delete a forum post, please try again.",
    ),

    'restore_forum_post' => array(
        'success' => "Forum post has been restored.",
        'error'   => "An error occurred while trying to restore a forum post, please try again.",
    ),

    'update_forum_topic' => array(
        'success' => "Forum topic has been successfully updated.",
        'error'   => "An error occurred while trying update the forum topic, please try again.",
    ),

    'delete_forum_topic' => array(
        'success' => "Forum topic has been successfully deleted.",
        'error'   => "An error occurred while trying to delete a forum topic, please try again.",
    ),

    'permanently_delete_forum_topic' => array(
        'success' => "Forum topic has been permanently deleted.",
        'error'   => "An error occurred while trying to permanently delete a forum topic, please try again.",
    ),

    'restore_forum_topic' => array(
        'success' => "Forum topic has been restored.",
        'error'   => "An error occurred while trying to restore a forum topic, please try again.",
    ),

    'lock_forum_topic' => array(
        'success' => "You have locked this topic.",
        'error'   => "An error occurred while trying lock a topic, please try again.",
    ),

    'unlock_forum_topic' => array(
        'success' => "You have unlocked this topic.",
        'error'   => "An error occurred while trying unlock a topic, please try again.",
    ),

    'sticky_forum_topic' => array(
        'success' => "You have stickied this topic.",
        'error'   => "An error occurred while trying sticky a topic, please try again.",
    ),

    'unsticky_forum_topic' => array(
        'success' => "You have unstickied this topic.",
        'error'   => "An error occurred while trying unsticky a topic, please try again.",
    ),

    'move_forum_topic' => array(
        'success' => "You have moved this topic.",
        'error'   => "An error occurred while trying move a topic, please try again.",
    ),


    'create_help_category' => array(
        'success' => "Help category has been successfully created.",
        'error'   => "An error occurred while trying create a help category, please try again.",
    ),

    'update_help_category' => array(
        'success' => "Help category has been successfully updated.",
        'error'   => "An error occurred while trying update the help category, please try again.",
    ),

    'delete_help_category' => array(
        'success' => "Help category has been successfully deleted.",
        'error'   => "An error occurred while trying to delete a help category, please try again.",
    ),

    'create_help_page' => array(
        'success' => "Help page has been successfully created.",
        'error'   => "An error occurred while trying create a help page, please try again.",
    ),

    'update_help_page' => array(
        'success' => "Help page has been successfully updated.",
        'error'   => "An error occurred while trying update the help page, please try again.",
    ),

    'delete_help_page' => array(
        'success' => "Help page has been successfully deleted.",
        'error'   => "An error occurred while trying to delete a help page, please try again.",
    ),

    'update_user' => array(
        'success' => "User has been successfully updated.",
        'error'   => "An error occurred while trying update the user, please try again.",
    ),

    'delete_user' => array(
        'cannot_delete_self' => "You cannot delete yourself.",
        'success' => "User has been successfully deleted.",
        'error'   => "An error occurred while trying to delete a user, please try again.",
    ),

    'update_contest' => array(
        'success' => "Contest has been successfully updated.",
        'error'   => "An error occurred while trying update the contest, please try again.",
    ),

    'delete_contest' => array(
        'success' => "Contest has been successfully deleted.",
        'error'   => "An error occurred while trying to delete a contest, please try again.",
    ),

    'update_contest_type' => array(
        'success' => "Contest type has been successfully updated.",
        'error'   => "An error occurred while trying update the contest type, please try again.",
    ),

    'delete_contest_type' => array(
        'success' => "Contest type has been successfully deleted.",
        'error'   => "An error occurred while trying to delete a contest type, please try again.",
    ),

    'permanently_delete_user' => array(
        'cannot_delete_self' => "You cannot delete yourself.",
        'success' => "User has been permanently deleted.",
        'error'   => "An error occurred while trying to permanently delete a user, please try again.",
    ),

    'restore_user' => array(
        'success' => "User has been restored.",
        'error'   => "An error occurred while trying to restore a user, please try again.",
    ),

    'ban_user' => array(
        'cannot_ban_self' => "You cannot ban yourself.",
        'success' => "User has been successfully banned.",
        'error'   => "An error occurred while trying to ban a user, please try again.",
    ),

    'unban_user' => array(
        'cannot_unban_self' => "You cannot unban yourself.",
        'success' => "User has been successfully unbanned.",
        'error'   => "An error occurred while trying to unban a user, please try again.",
    ),

    'unban_chat_user' => array(
        'cannot_unban_self' => "You cannot unban yourself from chat.",
        'success' => "User has been successfully unbanned from chat.",
        'error'   => "An error occurred while trying to unban a user from chat, please try again.",
    ),

    'update_kennel_group' => array(
        'success' => "Kennel group has been successfully updated.",
        'error'   => "An error occurred while trying update the kennel group, please try again.",
    ),

    'give_currency' => array(
        'success' => "You have given :users: :currencies.",
        'error'   => "An error occurred while trying to give currency to users, please try again.",
    ),

    'ban_ip' => array(
        'cannot_ban_self' => "You cannot ban your own IP.",
        'success' => "IP has been successfully banned.",
        'error'   => "An error occurred while trying to ban an IP, please try again.",
    ),

    'unban_ip' => array(
        'success' => "IP has been successfully unbanned.",
        'error'   => "An error occurred while trying to unban an IP, please try again.",
    ),

    'create_breed' => array(
        'success' => "Breed has been successfully created.",
        'error'   => "An error occurred while trying create a breed, please try again.",
    ),

    'update_breed' => array(
        'success' => "Breed has been successfully updated.",
        'error'   => "An error occurred while trying update the breed, please try again.",
    ),

    'delete_breed' => array(
        'success' => "Breed has been successfully deleted.",
        'error'   => "An error occurred while trying to delete a breed, please try again.",
    ),

    'create_breed_characteristic' => array(
        'success' => "Breed characteristic has been successfully created.",
        'error'   => "An error occurred while trying create a breed characteristic, please try again.",
    ),

    'update_breed_characteristic' => array(
        'success' => "Breed characteristic has been successfully updated.",
        'error'   => "An error occurred while trying update the breed characteristic, please try again.",
    ),

    'delete_breed_characteristic' => array(
        'success' => "Breed characteristic has been successfully deleted.",
        'error'   => "An error occurred while trying to delete a breed characteristic, please try again.",
    ),

    'update_breed_genotypes' => array(
        'success' => "This breed's genotypes have been successfully updated.",
        'error'   => "An error occurred while trying update the breed's genotypes, please try again.",
    ),

    'clone_breed' => array(
        'success' => "Breed has been successfully cloned.",
        'error'   => "An error occurred while trying to clone a breed, please try again.",
    ),

    'add_characteristics_to_breeds' => array(
        'success' => "You have added characteristics to the selected breeds.",
        'error'   => "An error occurred while trying to add characteristics to breeds, please try again.",
    ),

    'add_genotypes_to_breeds' => array(
        'success' => "You have updated the genotypes of the selected breeds.",
        'error'   => "An error occurred while trying to update the genotypes of breeds, please try again.",
    ),

    'create_locus' => array(
        'success' => "Locus has been successfully created.",
        'error'   => "An error occurred while trying create a locus, please try again.",
    ),

    'update_locus' => array(
        'success' => "Locus has been successfully updated.",
        'error'   => "An error occurred while trying update the locus, please try again.",
    ),

    'delete_locus' => array(
        'success' => "Locus has been successfully deleted.",
        'error'   => "An error occurred while trying to delete a locus, please try again.",
    ),

    'create_locus_allele' => array(
        'success' => "Locus allele has been successfully created.",
        'error'   => "An error occurred while trying create a locus allele, please try again.",
    ),

    'update_locus_allele' => array(
        'success' => "Locus allele has been successfully updated.",
        'error'   => "An error occurred while trying update the locus allele, please try again.",
    ),

    'delete_locus_allele' => array(
        'success' => "Locus allele has been successfully deleted.",
        'error'   => "An error occurred while trying to delete a locus allele, please try again.",
    ),

    'create_genotype' => array(
        'success' => "Genotype has been successfully created.",
        'error'   => "An error occurred while trying create a genotype, please try again.",
    ),

    'update_genotype' => array(
        'success' => "Genotype has been successfully updated.",
        'error'   => "An error occurred while trying update the genotype, please try again.",
    ),

    'delete_genotype' => array(
        'success' => "Genotype has been successfully deleted.",
        'error'   => "An error occurred while trying to delete a genotype, please try again.",
    ),

    'create_phenotype' => array(
        'success' => "Phenotype has been successfully created.",
        'error'   => "An error occurred while trying create a phenotype, please try again.",
    ),

    'update_phenotype' => array(
        'success' => "Phenotype has been successfully updated.",
        'error'   => "An error occurred while trying update the phenotype, please try again.",
    ),

    'delete_phenotype' => array(
        'success' => "Phenotype has been successfully deleted.",
        'error'   => "An error occurred while trying to delete a phenotype, please try again.",
    ),

    'clone_phenotype' => array(
        'success' => "Phenotype has been successfully cloned.",
        'error'   => "An error occurred while trying to clone a phenotype, please try again.",
    ),

    'create_dog' => array(
        'success' => "Dog has been successfully created.",
        'error'   => "An error occurred while trying create a dog, please try again.",
    ),

    'update_dog' => array(
        'success' => "Dog has been successfully updated.",
        'error'   => "An error occurred while trying update the dog, please try again.",
    ),

    'delete_dog' => array(
        'success' => "Dog has been successfully deleted.",
        'error'   => "An error occurred while trying to delete a dog, please try again.",
    ),

    'age_dogs' => array(
        'error'   => "An error occurred while trying to age dogs, please try again.",
    ),

    'age_dogs_increase' => array(
        'success' => "Dogs have been successfully aged by :months.",
    ),

    'age_dogs_decrease' => array(
        'success' => "Dogs have been successfully de-aged by :months.",
    ),

    'recomplete_dog' => array(
        'success' => "Dog has been successfully regenerated.",
        'error'   => "An error occurred while trying to regenerate a dog, please try again.",
    ),

    'refresh_phenotypes_for_dog' => array(
        'success' => "Dog has successfully had its phenotypes updated.",
        'error'   => "An error occurred while trying to update a dog's phenotyped, please try again.",
    ),

    'create_community_challenge' => array(
        'invalid_dates' => "The start and/or end date is invalid.",
        'not_enough_characteristics_generated' => "An error occurred while trying to generate characteristics, please try again.",
        'success' => "Community challenge has been successfully created.",
        'error'   => "An error occurred while trying create a community challenge, please try again.",
    ),

    'update_community_challenge' => array(
        'invalid_dates' => "The start and/or end date is invalid.",
        'success' => "Community challenge has been successfully updated.",
        'error'   => "An error occurred while trying update the community challenge, please try again.",
    ),

);
