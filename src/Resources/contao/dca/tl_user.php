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

\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('rocksolid_frontend_helper_legend', 'forms_legend')
    ->addField('rocksolidFrontendHelperOperations', 'rocksolid_frontend_helper_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->addField('rocksolidFrontendHelperContentElements', 'rocksolid_frontend_helper_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('extend', 'tl_user')
    ->applyToPalette('custom', 'tl_user')
;

$GLOBALS['TL_DCA']['tl_user']['palettes']['admin'] = preg_replace('(([,;}]useCE)([,;{]))i', '$1,rocksolidFrontendHelper,rocksolidFrontendHelperLightbox$2', $GLOBALS['TL_DCA']['tl_user']['palettes']['admin']);
$GLOBALS['TL_DCA']['tl_user']['palettes']['default'] = preg_replace('(([,;}]useCE)([,;{]))i', '$1,rocksolidFrontendHelper,rocksolidFrontendHelperLightbox$2', $GLOBALS['TL_DCA']['tl_user']['palettes']['default']);
$GLOBALS['TL_DCA']['tl_user']['palettes']['group'] = preg_replace('(([,;}]useCE)([,;{]))i', '$1,rocksolidFrontendHelper,rocksolidFrontendHelperLightbox$2', $GLOBALS['TL_DCA']['tl_user']['palettes']['group']);
$GLOBALS['TL_DCA']['tl_user']['palettes']['extend'] = preg_replace('(([,;}]useCE)([,;{]))i', '$1,rocksolidFrontendHelper,rocksolidFrontendHelperLightbox$2', $GLOBALS['TL_DCA']['tl_user']['palettes']['extend']);
$GLOBALS['TL_DCA']['tl_user']['palettes']['custom'] = preg_replace('(([,;}]useCE)([,;{]))i', '$1,rocksolidFrontendHelper,rocksolidFrontendHelperLightbox$2', $GLOBALS['TL_DCA']['tl_user']['palettes']['custom']);
$GLOBALS['TL_DCA']['tl_user']['palettes']['login'] = preg_replace('(([,;}]useCE)([,;{]))i', '$1,rocksolidFrontendHelper,rocksolidFrontendHelperLightbox$2', $GLOBALS['TL_DCA']['tl_user']['palettes']['login']);

$GLOBALS['TL_DCA']['tl_user']['fields']['rocksolidFrontendHelperOperations'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_user']['rocksolidFrontendHelperOperations'],
	'exclude' => true,
	'inputType' => 'checkbox',
	'options' => array('feModules', 'beModules', 'pages', 'articles', 'contents', 'infos'),
	'reference' => &$GLOBALS['TL_LANG']['tl_user']['rocksolidFrontendHelperOperationsValues'],
	'eval' => array('multiple' => true),
	'sql' => "blob NULL",
);
$GLOBALS['TL_DCA']['tl_user']['fields']['rocksolidFrontendHelperContentElements'] = array(
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
$GLOBALS['TL_DCA']['tl_user']['fields']['rocksolidFrontendHelper'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_user']['rocksolidFrontendHelper'],
	'exclude' => true,
	'inputType' => 'checkbox',
	'eval' => array('tl_class' => 'clr w50'),
	'sql' => "char(1) NOT NULL default '1'",
);
$GLOBALS['TL_DCA']['tl_user']['fields']['rocksolidFrontendHelperLightbox'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_user']['rocksolidFrontendHelperLightbox'],
	'exclude' => true,
	'inputType' => 'checkbox',
	'eval' => array('tl_class' => 'w50'),
	'sql' => "char(1) NOT NULL default '1'",
);
