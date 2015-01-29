<?php

return array(

    'gift_credits' => array(
        'to_receiver_message' => 'You have received :credits from :sender.display_name (#:sender.id) with the following message: :message. <a href=":route?<<read_querystring>>">Click here to go to the Cash Shop.</a>',
        'to_receiver_anonymous' => 'You have received :credits from Anonymous. <a href=":route?<<read_querystring>>">Click here to go to the Cash Shop.</a>',
        'to_receiver_anonymous_message' => 'You have received :credits from Anonymous with the following message: :message. <a href=":route?<<read_querystring>>">Click here to go to the Cash Shop.</a>',
        'to_receiver' => 'You have received :credits from :sender.display_name (#:sender.id). <a href=":route?<<read_querystring>>">Click here to go to the Cash Shop.</a>',
        'to_sender' => 'You gifted :credits to :receiver.display_name (#:receiver.id).',
    ),

    'gift_turns' => array(
        'to_receiver_message' => 'You have received :turns from :sender.display_name (#:sender.id) with the following message: :message. <a href=":route?<<read_querystring>>">Click here to go to the Cash Shop.</a>',
        'to_receiver_anonymous' => 'You have received :turns from Anonymous. <a href=":route?<<read_querystring>>">Click here to go to the Cash Shop.</a>',
        'to_receiver_anonymous_message' => 'You have received :turns from Anonymous with the following message: :message. <a href=":route?<<read_querystring>>">Click here to go to the Cash Shop.</a>',
        'to_receiver' => 'You have received :turns from :sender.display_name (#:sender.id). <a href=":route?<<read_querystring>>">Click here to go to the Cash Shop.</a>',
        'to_sender' => 'You gifted :turns to :receiver.display_name (#:receiver.id) for :credits.',
    ),

    'gift_upgrade' => array(
        'to_receiver_message' => 'You have received a 1 Month Upgrade from :sender.display_name (#:sender.id) with the following message: :message. <a href=":route?<<read_querystring>>">Click here to go to the Cash Shop.</a>',
        'to_receiver_anonymous' => 'You have received a 1 Month Upgrade from Anonymous. <a href=":route?<<read_querystring>>">Click here to go to the Cash Shop.</a>',
        'to_receiver_anonymous_message' => 'You have received a 1 Month Upgrade from Anonymous with the following message: :message. <a href=":route?<<read_querystring>>">Click here to go to the Cash Shop.</a>',
        'to_receiver' => 'You have received a 1 Month Upgrade from :sender.display_name (#:sender.id). <a href=":route?<<read_querystring>>">Click here to go to the Cash Shop.</a>',
        'to_sender' => 'You gifted a 1 Month Upgrade to :receiver.display_name (#:receiver.id) for :credits.',
    ),

    'reply_to_forum_topic' => array(
        'to_forum_topic_author' => ':author just replied to your forum topic <a href=":topicRoute?<<read_querystring>>">:topic</a>.',
    ),

    'compose' => array(
        'to_receiver' => 'You have received a message from :sender.display_name (#:sender.id). <a href=":route?<<read_querystring>>">Click here to read it.</a>',
    ),

    'reply_to_conversation' => array(
        'to_receiver' => 'You have received a message from :sender.display_name (#:sender.id). <a href=":route&<<read_querystring>>">Click here to read it.</a>',
    ),

    'won_community_challenge' => array(
        'single' => 'Congratulations! You participated in the breeding of the Community Challenge’s winning dog, :winning_dog! The number of breeders in this dog\'s 10-generation pedigree is :highest_number_of_unique_breeders. <a href=":prize_route?<<read_querystring>>">Please click here to select your prize!</a>',
        'tie' => 'Congratulations! You participated in the breeding of the Community Challenge\'s winning dogs: :winning_dogs! There was a tie for number of breeders in these dogs\' 10-generation pedigree - #:highest_number_of_unique_breeders. <a href=":prize_route?<<read_querystring>>">Please click here to select your prize!</a> * Note: You only get one prize per challenge.',
    ),

    'lost_community_challenge' => array(
        'single' => 'The current Community Challenge has ended, and :winning_dog has won with :highest_number_of_unique_breeders breeders in its 10-generation pedigree. The winners have been awarded their prizes. Better luck next time!',
        'tie' => 'The current Community Challenge has ended, and the following dogs have tied for the winning spot with :highest_number_of_unique_breeders breeders in their 10-generation pedigrees: :winning_dogs. The breeders involved in breeding all winning dogs have been awarded their prizes. Better luck next time!',
    ),

    'request_beginners_luck' => array(
        'to_beginner' => 'Since your account is less than 7 days old, you have Beginner\'s Luck. :owner asks you to use your Beginner\'s Luck to help them breed <a href=":dogUrl?<<read_querystring>>">:dog</a> and <a href=":bitchUrl?<<read_querystring>>">:bitch</a>. Would you like to <a href=":acceptUrl?<<read_querystring>>">use Beginner\'s Luck</a> or <a href=":rejectUrl?<<read_querystring>>">reject the request</a>?',
        'to_owner' => 'You have sent a Beginner\'s Luck request to :beginner. You must wait for them to accept/reject the request before advancing to your next turn. Alternatively, you can revoke your request from the profile of <a href=":bitchUrl?<<read_querystring>>">:bitch</a>.',
    ),

    'revoke_beginners_luck' => array(
        'to_beginner' => ':owner has revoked their Beginner\'s Luck request to you for the breeding between <a href=":dogUrl?<<read_querystring>>">:dog</a> and <a href=":bitchUrl?<<read_querystring>>">:bitch</a>.',
    ),

    'reject_beginners_luck' => array(
        'to_owner' => 'Unfortunately :beginner rejected your request for Beginner\'s Luck. The breeding between <a href=":dogUrl?<<read_querystring>>">:dog</a> and <a href=":bitchUrl?<<read_querystring>>">:bitch</a> did not take place. You may try again with another newbie or without any Beginner\'s Luck at all.',
    ),

    'accept_beginners_luck' => array(
        'to_unresponsive' => 'Sorry, another Newbie responded to a Beginner\'s Luck request for <a href=":bitchUrl?<<read_querystring>>">:bitch</a> already. Better luck next time!',
        'to_owner' => 'Congratulations! :beginner used their Beginner\'s Luck to help you breed <a href=":dogUrl?<<read_querystring>>">:dog</a> and <a href=":bitchUrl?<<read_querystring>>">:bitch</a>! They have received a turn for their help. On your next turn, the litter will be born.',
    ),

    'request_breeding' => array(
        'to_owner' => '<a href=":userUrl?<<read_querystring>>">:user</a> requested to breed their bitch <a href=":bitchUrl?<<read_querystring>>">:bitch</a> with your stud <a href=":studUrl?<<read_querystring>>">:stud</a>. Visit the Stud Requests pane in the sidebar on your kennel page to Accept the request or to reject it.',
    ),

    'accept_stud_request' => array(
        'to_owner' => ':user has accepted your stud request to <a href=":studUrl?<<read_querystring>>">:stud</a>. You will now be able to breed the dogs together from the Your Stud Requests pane in the sidebar on your kennel page when your bitch <a href=":bitchUrl?<<read_querystring>>">:bitch</a> is in heat and not worked.',
    ),

    'reject_stud_request' => array(
        'to_owner' => ':user has rejected your stud request to <a href=":studUrl?<<read_querystring>>">:stud</a> with your bitch <a href=":bitchUrl?<<read_querystring>>">:bitch</a>.',
    ),

    'remove_stud_request' => array(
        'to_owner' => ':user has removed their stud request to <a href=":studUrl?<<read_querystring>>">:stud</a> with their bitch <a href=":bitchUrl?<<read_querystring>>">:bitch</a>.',
    ),

    'send_lend_request' => array(
        'to_receiver' => ':sender is lending you <a href=":dogUrl?<<read_querystring>>">:dog</a>:returnPeriod. Would you like to <a href=":acceptUrl?<<read_querystring>>">Accept</a> the dog or <a href=":rejectUrl?<<read_querystring>>">Reject</a> it?',
        'to_sender' => 'You have sent a request to lend :dogdog to :receiver:returnPeriod. <a href=":revokeUrl?<<read_querystring>>">Revoke</a> the dog?',
    ),

    'reject_lend_request' => array(
        'to_sender' => ':user has rejected <a href=":dogUrl?<<read_querystring>>">:dog</a>.',
    ),

    'accept_lend_request' => array(
        'to_sender' => ':user has accepted <a href=":dogUrl?<<read_querystring>>">:dog</a>.',
    ),

    'return_lend_request' => array(
        'to_sender' => 'Your dog, <a href=":dogUrl?<<read_querystring>>">:dog</a>, was returned early from :user.',
    ),

    'expired_lend_request' => array(
        'to_receiver' => '<a href=":dogUrl?<<read_querystring>>">:dog</a> was sent back to :sender since their specified lending period has ended. Say bye!',
        'to_sender' => 'Your dog, <a href=":dogUrl?<<read_querystring>>">:dog</a>, was automatically returned from :receiver at the end of your specified lending period.',
    ),

);
