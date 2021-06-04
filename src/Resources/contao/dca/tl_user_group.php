<?php
/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * RockSolid Frontend Helper user DCA
 *
 * @author Martin Auswöger <martin@madeyourday.net>
 */

$GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] = str_replace('formp;', 'formp;{rocksolid_frontend_helper_legend},rocksolidFrontendHelperOperations,rocksolidFrontendHelperContentElements;', $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default']);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['rocksolidFrontendHelperOperations'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_user']['rocksolidFrontendHelperOperations'],
	'exclude' => true,
	'inputType' => 'checkbox',
	'options' => array('feModules', 'beModules', 'pages', 'articles', 'contents', 'infos'),
	'reference' => &$GLOBALS['TL_LANG']['tl_user']['rocksolidFrontendHelperOperationsValues'],
	'eval' => array('multiple' => true),
	'sql' => "blob NULL",
);
$GLOBALS['TL_DCA']['tl_user_group']['fields']['rocksolidFrontendHelperContentElements'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_user']['rocksolidFrontendHelperContentElements'],
	'exclude' => true,
	'inputType' => 'checkbox',
	'options_callback' => function() {
		$groups = [];
		foreach ($GLOBALS['TL_CTE'] as $k => $v) {
			foreach (array_keys($v) as $kk) {
				$groups[$k][] = $kk;
			}
		}
		return $groups;
	},
	'reference' => &$GLOBALS['TL_LANG']['CTE'],
	'eval' => array('multiple' => true),
	'sql' => "blob NULL",
);
