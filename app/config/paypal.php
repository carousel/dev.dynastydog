<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| PayPal IPN configurations
	|--------------------------------------------------------------------------
	|
	|
	*/

	'receiver_email' => 'libertyhorsegame@gmail.com',

	'verifier'       => 'curl', // curl, fsockopen

	'environment' 	 => 'production',

	'url'  		  	 => 'https://www.paypal.com/cgi-bin/webscr',

	'button' 		=> 'HKM5V9979ZJYL', 

);
