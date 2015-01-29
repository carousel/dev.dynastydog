<?php

return array(

    'won_contest' => array(
        'to_owner' => 'Congratulations! <a href=":winning_dog_route?<<read_querystring>>">:winning_dog</a> won :contest.name (#:contest.id), a :contest.type_name contest! Well done!',
    ),

    'lethal_symptom' => array(
        'to_owner' => 'Oh no! Unfortunately, due to :symptom, your dog, <a href=":dogUrl?<<read_querystring>>">:dog</a>, passed away recently. May :pronoun memory be cherished.',
    ),

    'old_age_death' => array(
        'to_owner' => 'Oh no! Unfortunately, due to old age, your dog, <a href=":dogUrl?<<read_querystring>>">:dog</a>, passed away recently. May :pronoun memory be cherished.',
    ),

    'pet_homed' => array(
        'to_owner' => '<a href=":dogUrl">:dog</a> was pet homed.',
    ),

    'pet_homed_deleted' => array(
        'to_owner' => ':dog was pet homed and has been removed from the game.',
    ),

);
