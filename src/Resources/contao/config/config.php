<?php
/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * RockSolid Frontend Helper configuration
 *
 * @author Martin Auswöger <martin@madeyourday.net>
 */

$GLOBALS['TL_HOOKS']['parseFrontendTemplate'][] = array('rocksolid_frontend_helper.frontend_hooks', 'parseFrontendTemplateHook');
$GLOBALS['TL_HOOKS']['outputFrontendTemplate'][] = array('rocksolid_frontend_helper.frontend_hooks', 'outputFrontendTemplateHook');
$GLOBALS['TL_HOOKS']['parseTemplate'][] = array('rocksolid_frontend_helper.frontend_hooks', 'parseTemplateHook');
$GLOBALS['TL_HOOKS']['parseWidget'][] = array('rocksolid_frontend_helper.frontend_hooks', 'parseWidgetHook');
$GLOBALS['TL_HOOKS']['parseArticles'][] = array('rocksolid_frontend_helper.frontend_hooks', 'parseArticlesHook');
$GLOBALS['TL_HOOKS']['getAllEvents'][] = array('rocksolid_frontend_helper.frontend_hooks', 'getAllEventsHook');
$GLOBALS['TL_HOOKS']['getContentElement'][] = array('rocksolid_frontend_helper.frontend_hooks', 'getContentElementHook');
$GLOBALS['TL_HOOKS']['getFrontendModule'][] = array('rocksolid_frontend_helper.frontend_hooks', 'getFrontendModuleHook');
$GLOBALS['TL_HOOKS']['getArticle'][] = array('rocksolid_frontend_helper.frontend_hooks', 'getArticleHook');

$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('rocksolid_frontend_helper.backend_hooks', 'loadDataContainerHook');
$GLOBALS['TL_HOOKS']['initializeSystem'][] = array('rocksolid_frontend_helper.backend_hooks', 'initializeSystemHook');

$GLOBALS['TL_PERMISSIONS'][] = 'rocksolidFrontendHelperOperations';
