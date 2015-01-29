<?php

class AuthorizedController extends BaseController {

	/**
	 * Whitelisted auth routes.
	 *
	 * @var array
	 */
	protected $auth_whitelist = array();

	/**
	 * Initializer.
	 *
	 * @return void
	 */
	public function __construct()
	{
		// Call parent
		parent::__construct();

		// Apply the auth filter
		$this->beforeFilter('auth', array('except' => $this->auth_whitelist));
	}

}
