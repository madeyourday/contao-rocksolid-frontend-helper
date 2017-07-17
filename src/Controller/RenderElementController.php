<?php
/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\RockSolidFrontendHelper\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * RockSolid Frontend Helper render API
 *
 * @author Martin Auswöger <martin@madeyourday.net>
 *
 * @Route("/_rocksolid-frontend-helper", defaults={"_scope" = "frontend"})
 */
class RenderElementController extends Controller
{
	/**
	 * @param Request $request
	 *
	 * @return Response
	 *
	 * @Route("/render", name="rocksolid_frontend_helper_render")
	 * @Method({"POST"})
	 */
	public function renderAction(Request $request)
	{
		$this->get('contao.framework')->initialize();
		$permissions = $this->get('rocksolid_frontend_helper.frontend_hooks')->checkLogin();

		if (!$permissions || !in_array('contents', $permissions, true)) {
			throw new AccessDeniedHttpException();
		}

		if (!is_string($request->get('table')) || !is_string($request->get('id'))) {
			throw new NotFoundHttpException();
		}

		// Only tl_content is supported so far
		if ($request->get('table') !== 'tl_content') {
			throw new NotFoundHttpException();
		}

		return new Response(
			$this->renderElement((int) $request->get('id'), $request->get('table'))
		);
	}

	/**
	 * Render an element and return the resulting HTML
	 *
	 * @param int    $id
	 * @param string $table
	 *
	 * @return string HTML code
	 */
	private function renderElement($id, $table)
	{
		// Only tl_content is supported so far
		if ($table !== 'tl_content') {
			throw new \InvalidArgumentException('Table "'.$act.'" is not supported');
		}

		return $this->get('contao.framework')
			->getAdapter('Controller')
			->getContentElement($id)
		;
	}
}
