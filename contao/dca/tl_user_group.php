<?php
/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

/*
 * RockSolid Frontend Helper user DCA
 *
 * @author Martin Auswöger <martin@madeyourday.net>
 */

PaletteManipulator::create()
    ->addLegend('rocksolid_frontend_helper_legend', 'forms_legend')
    ->addField('rocksolidFrontendHelperOperations', 'rocksolid_frontend_helper_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('rocksolidFrontendHelperContentElements', 'rocksolid_frontend_helper_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_user_group')
;

$GLOBALS['TL_DCA']['tl_user_group']['fields']['rocksolidFrontendHelperOperations'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_user']['rocksolidFrontendHelperOperations'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => ['feModules', 'beModules', 'pages', 'articles', 'contents', 'infos'],
    'reference' => &$GLOBALS['TL_LANG']['tl_user']['rocksolidFrontendHelperOperationsValues'],
    'eval' => ['multiple' => true],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['rocksolidFrontendHelperContentElements'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_user']['rocksolidFrontendHelperContentElements'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'options_callback' => static function () {
        $groups = [];

        foreach ($GLOBALS['TL_CTE'] as $k => $v) {
            foreach (array_keys($v) as $kk) {
                $groups[$k][] = $kk;
            }
        }

        return $groups;
    },
    'reference' => &$GLOBALS['TL_LANG']['CTE'],
    'eval' => ['multiple' => true],
    'sql' => 'blob NULL',
];
