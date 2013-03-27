<?php
/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * RockSolid Frontend Helper autload configuration
 *
 * @author Martin Auswöger <martin@madeyourday.net>
 */

ClassLoader::addNamespaces(array(
	'MadeYourDay\\Contao\\Core',
));

ClassLoader::addClasses(array(
	'MadeYourDay\\Contao\\FrontendHelper' => 'system/modules/rocksolid-frontend-helper/src/MadeYourDay/Contao/FrontendHelper.php',
	'MadeYourDay\\Contao\\FrontendHelperUser' => 'system/modules/rocksolid-frontend-helper/src/MadeYourDay/Contao/FrontendHelperUser.php',
));
