<?php
/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\RockSolidFrontendHelper\Controller;

use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\InsertTags;
use MadeYourDay\RockSolidFrontendHelper\FrontendHooks;
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
class RenderElementController extends Controller implements FrameworkAwareInterface
{
	use FrameworkAwareTrait;

	/**
	 * @param Request       $request
	 * @param FrontendHooks $frontendHooks
	 *
	 * @return Response
	 *
	 * @Route("/render", name="rocksolid_frontend_helper_render")
	 * @Method({"POST"})
	 */
	public function renderAction(Request $request, FrontendHooks $frontendHooks)
	{
		$this->framework->initialize();
		$permissions = $frontendHooks->checkLogin();

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

		$html = $this->renderElement((int) $request->get('id'), $request->get('table'));
		$html = $this->framework->createInstance(InsertTags::class)->replace($html);

		return new Response($html);
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

		if (!defined('BE_USER_LOGGED_IN') && !defined('FE_USER_LOGGED_IN') && $this->has('contao.security.token_checker')) {
			$tokenChecker = $this->get('contao.security.token_checker');
			define('FE_USER_LOGGED_IN', $tokenChecker->hasFrontendUser());
			define('BE_USER_LOGGED_IN', $tokenChecker->hasBackendUser() && $tokenChecker->isPreviewMode());
		}

		return $this->framework
			->getAdapter('Controller')
			->getContentElement($id)
		;
	}
}
