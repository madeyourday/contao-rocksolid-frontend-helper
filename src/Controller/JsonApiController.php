<?php
/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\RockSolidFrontendHelper\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * RockSolid Frontend Helper JSON API
 *
 * @author Martin Auswöger <martin@madeyourday.net>
 *
 * @Route("/contao/rocksolid-frontend-helper", defaults={"_scope" = "backend", "_token_check" = true})
 */
class JsonApiController extends Controller
{
	/**
	 * @return Response
	 *
	 * @Route("/content-elements", name="rocksolid_frontend_helper_content_elements")
	 */
	public function contentElementsAction()
	{
		$this->get('contao.framework')->initialize();

		return $this->json($this->getContentElements());
	}

	private function getContentElements()
	{
		return array_map(function($group) {
			$elements = [];
			foreach ($group as $key => $class) {
				$elements[$key] = $this->getLabel($key);
			}
			return $elements;
		}, $GLOBALS['TL_CTE']);
	}

	private function getLabel($key)
	{
		$this->get('contao.framework')->getAdapter('System')->loadLanguageFile('default', 'en');
		if (isset($GLOBALS['TL_LANG']['CTE'][$key])) {
			return $GLOBALS['TL_LANG']['CTE'][$key];
		}

		return $key;
	}
}
