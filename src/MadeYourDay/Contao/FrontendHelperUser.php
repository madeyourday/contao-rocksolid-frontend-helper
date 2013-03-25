<?php
/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\Contao;

/**
 * RockSolid Frontend Helper User
 *
 * This User object is used to check if a backend user is logged in while
 * accessing the frontend
 *
 * @author Martin Auswöger <martin@madeyourday.net>
 */
class FrontendHelperUser extends \Contao\User
{
	/**
	 * @var FrontendHelperUser Singelton instance
	 */
	protected static $objInstance;

	/**
	 * @var string table name
	 */
	protected $strTable = 'tl_user';

	/**
	 * @var string auth cookie name
	 */
	protected $strCookie = 'BE_USER_AUTH';

	/**
	 * constructor
	 *
	 * @return static
	 */
	protected function __construct()
	{
		parent::__construct();
		$this->strIp = \Environment::get('ip');
		$this->strHash = \Input::cookie($this->strCookie);
	}

	/**
	 * Set all user properties from a database record
	 *
	 * @return void
	 */
	protected function setUserFromDb()
	{
		$this->intId = $this->id;

		foreach ($this->arrData as $key => $value) {
			if (! is_numeric($value)) {
				$this->$key = deserialize($value);
			}
		}
	}

	/**
	 * magic get method used for isAdmin member variable
	 *
	 * @param  string $key member variable name
	 * @return mixed       returns the member variable if possible
	 */
	public function __get($key)
	{
		if ($key === 'isAdmin') {
			return $this->arrData['admin'] ? true : false;
		}

		return parent::__get($key);
	}
}
