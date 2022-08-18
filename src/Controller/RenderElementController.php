<?php
/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\RockSolidFrontendHelper\Controller;

use Contao\Controller;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\Environment;
use Contao\InsertTags;
use Contao\PageModel;
use MadeYourDay\RockSolidFrontendHelper\FrontendHooks;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * RockSolid Frontend Helper render API
 *
 * @author Martin Auswöger <martin@madeyourday.net>
 *
 * @Route("/_rocksolid-frontend-helper", defaults={"_scope" = "frontend"})
 */
class RenderElementController extends AbstractController implements FrameworkAwareInterface
{
	use FrameworkAwareTrait;

	/**
	 * @param Request       $request
	 * @param FrontendHooks $frontendHooks
	 *
	 * @return Response
	 *
	 * @Route("/render", name="rocksolid_frontend_helper_render", methods={"POST"})
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

		// Setup environment URL
		try {
			Environment::reset();
			Environment::set('requestUri', preg_replace('(^https?://[^/]+)i', '', $request->headers->get('referer', '/')));
		}
		catch (\Throwable $e) {
			// Ignore set URL errors
		}

		// Setup global page, theme and layout state
		if ($request->get('pageId')) {
			$GLOBALS['objPage'] = PageModel::findPublishedById((int) $request->get('pageId'));
			if ($GLOBALS['objPage'] !== null) {
				$GLOBALS['objPage']->loadDetails();
				if ($GLOBALS['objPage']->type === 'regular') {
					try {
						$objHandler = new $GLOBALS['TL_PTY'][$GLOBALS['objPage']->type]();
						$objHandler->getResponse($GLOBALS['objPage']);
					}
					catch (\Throwable $e) {
						// Ignore page errors
					}
				}
			}
		}

		$html = $this->renderElement((int) $request->get('id'), $request->get('table'));
		$html = $this->container->get('contao.insert_tag.parser')->replaceInline($html);

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

		if (!defined('BE_USER_LOGGED_IN') && !defined('FE_USER_LOGGED_IN') && $this->container->has('contao.security.token_checker')) {
			$tokenChecker = $this->container->get('contao.security.token_checker');
			define('FE_USER_LOGGED_IN', $tokenChecker->hasFrontendUser());
			define('BE_USER_LOGGED_IN', $tokenChecker->isPreviewMode());
		}

		return $this->framework
			->getAdapter(Controller::class)
			->getContentElement($id)
		;
	}
}
