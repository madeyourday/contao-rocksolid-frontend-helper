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
class FrontendHelperUser extends \BackendUser
{
	/**
	 * @var FrontendHelperUser Singelton instance
	 */
	protected static $objInstance;

	/**
	 * Disable BackendUser authentication redirect
	 */
	public function authenticate()
	{
		return \User::authenticate();
	}
}
