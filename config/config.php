<?php
/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * RockSolid Frontend Guide configuration
 *
 * @author Martin Auswöger <martin@madeyourday.net>
 */

$GLOBALS['TL_HOOKS']['parseFrontendTemplate'][] = array('MadeYourDay\\Contao\\FrontendGuide', 'parseFrontendTemplate');
$GLOBALS['TL_HOOKS']['getContentElement'][] = array('MadeYourDay\\Contao\\FrontendGuide', 'getContentElementHook');
