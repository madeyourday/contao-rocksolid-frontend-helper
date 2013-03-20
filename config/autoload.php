<?php
/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * RockSolid Frontend Guide autload configuration
 *
 * @author Martin Auswöger <martin@madeyourday.net>
 */

ClassLoader::addNamespaces(array(
	'MadeYourDay\\Contao\\Core',
));

ClassLoader::addClasses(array(
	'MadeYourDay\\Contao\\FrontendGuide' => 'system/modules/rocksolid-frontend-guide/src/MadeYourDay/Contao/FrontendGuide.php',
	'MadeYourDay\\Contao\\Core\\Module' => 'system/modules/rocksolid-frontend-guide/src/MadeYourDay/Contao/Core/Module.php',
));
