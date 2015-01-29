<?php

return array(

	'account_already_exists' => 'An account with the provided email, already exists.',
	'account_not_found'      => 'This user account was not found.',
	'account_not_activated'  => 'This user account is not activated.',
	'account_suspended'      => 'This user account is suspended.',
	'account_banned'         => 'This user account is banned.',
	'reset_password'         => 'You need to reset your password. You can reset your password here: <a href="'.route('auth/forgot_password').'">'.route('auth/forgot_password').'</a>.',

	'login' => array(
		'banned'    => 'The account you are attempting to log into is banned until :expiry_date for the following reason: :reason.',
		'ip_banned' => 'The account you are attempting to log into has recently logged into the game from a banned IP address.',
		'error'     => 'There was a problem while trying to log you in, please try again.',
		'success'   => 'You have successfully logged in.',
	),

	'register' => array(
		'invalid_breed' => 'The dog breed you chose does not exist.',
		'error'   => 'There was a problem while trying to create your account, please try again.',
		'success' => 'Your account has been sucessfully created! Check your email for your activation code.',
	),

	'logout' => array(
        'banned'    => 'This account is banned until :expiry_date for the following reason: :reason. You have been logged out of your account.',
        'ip_banned' => 'You are logged into an account on a banned IP address. You have been logged out of your account.',
		'success'   => 'You have successfully logged out!',
	),

    'forgot_password' => array(
        'error'   => 'There was a problem while trying to get a reset password code, please try again.',
        'success' => 'Password recovery email successfully sent.',
    ),

    'forgot_password_confirm' => array(
        'error'   => 'There was a problem while trying to reset your password, please try again.',
        'success' => 'Your password has been successfully reset.',
    ),
    
	'activate' => array(
		'error'   => 'There was a problem while trying to activate your account, please try again.',
		'success' => 'Your account has been successfully activated.',
	),

);
