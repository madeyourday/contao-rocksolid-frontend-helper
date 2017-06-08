<?php
/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\RockSolidFrontendHelper\Controller;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
	 * @Route("/elements", name="rocksolid_frontend_helper_elements")
	 */
	public function elementsAction(Request $request)
	{
		if (!is_string($request->get('table'))) {
			throw new NotFoundHttpException();
		}

		$this->get('contao.framework')->initialize();

		return $this->json(
			$this->get('rocksolid_frontend_helper.element_builder')->getElements($request->get('table'))
		);
	}
}
