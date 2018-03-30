<?php
/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\RockSolidFrontendHelper;

/**
 * RockSolid Frontend Helper
 *
 * @author Martin Auswöger <martin@madeyourday.net>
 */
class BackendHooks
{
	/**
	 * initializeSystem hook
	 */
	public function initializeSystemHook()
	{
		if (TL_MODE !== 'BE' || !\Input::get('rsfhr')) {
			return;
		}

		\Environment::set('queryString', preg_replace('(([&?])rsfhr=1(&|$))', '$1', \Environment::get('queryString')));

		// Fix missing CURRENT_ID if rsfhr is set
		if (\Input::get('act') === 'create' && \Input::get('id')) {
			\System::getContainer()->get('session')->set('CURRENT_ID', \Input::get('id'));
		}
	}

	/**
	 * loadDataContainer hook
	 *
	 * - Saves the referrer in the session if it is a frontend URL
	 * - Preselects the original template in the template editor
	 *
	 * @param string $table The data container table name
	 */
	public function loadDataContainerHook($table)
	{
		if (TL_MODE !== 'BE') {
			return;
		}

		if (defined('TL_REFERER_ID') && \Input::get('ref')) {
			$this->removeRsfhrParam(\Input::get('ref'));
		}

		// Only handle requests from the frontend helper
		if (!\Input::get('rsfhr')) {
			return;
		}

		if ($table === 'tl_templates') {
			$this->handleTemplateSelection();
		}

		$this->storeFrontendReferrer();
	}

	/**
	 * Remove the `rsfhr=1` parameter from the session referer
	 *
	 * @param string $ref
	 */
	private function removeRsfhrParam($ref)
	{
		$session = \System::getContainer()->get('session');
		if (!$session->isStarted()) {
			return;
		}

		$referrerSession = $session->get('referer');
		if (!empty($referrerSession[$ref]['current'])) {
			$referrerSession[$ref]['current'] = preg_replace('(([&?])rsfhr=1(&|$))', '$1', $referrerSession[$ref]['current']);
			$session->set('referer', $referrerSession);
		}
	}

	/**
	 * Preselects the original template in the template editor
	 */
	private function handleTemplateSelection()
	{
		if (\Input::get('key') !== 'new_tpl') {
			return;
		}

		if (\Input::get('original') && !\Input::post('original')) {
			// Preselect the original template
			\Input::setPost('original', \Input::get('original'));
		}

		if (\Input::get('target') && !\Input::post('target')) {
			// Preselect the target template folder
			\Input::setPost('target', \Input::get('target'));
		}
	}

	/**
	 * Saves the referrer in the session if it is a frontend URL
	 */
	private function storeFrontendReferrer()
	{
		$base = \Environment::get('path');
		$base .= \System::getContainer()->get('router')->generate('contao_backend');

		$referrer = parse_url(\Environment::get('httpReferer'));
		$referrer = $referrer['path'] . ($referrer['query'] ? '?' . $referrer['query'] : '');

		// Stop if the referrer is a backend URL
		if (
			substr($referrer, 0, strlen($base)) === $base
			&& in_array(substr($referrer, strlen($base), 1), array(false, '/', '?'), true)
		) {
			return;
		}

		// Fix empty referrers
		if (empty($referrer)) {
			$referrer = '/';
		}

		// Make homepage possible as referrer
		if ($referrer === \Environment::get('path') . '/') {
			$referrer .= '?';
		}

		$referrer = \Environment::get('path') . '/bundles/rocksolidfrontendhelper/html/referrer.html?referrer=' . rawurlencode($referrer);

		// set the frontend URL as referrer

		$referrerSession = \System::getContainer()->get('session')->get('referer');

		if (defined('TL_REFERER_ID') && !\Input::get('ref')) {

			$referrer = substr($referrer, strlen(TL_PATH) + 1);
			$tlRefererId = substr(md5(TL_START - 1), 0, 8);
			$referrerSession[$tlRefererId]['current'] = $referrer;
			\Input::setGet('ref', $tlRefererId);
			$requestUri = \Environment::get('requestUri');
			$requestUri .= (strpos($requestUri, '?') === false ? '?' : '&') . 'ref=' . $tlRefererId;
			\Environment::set('requestUri', $requestUri);
			\System::getContainer()->get('request_stack')->getCurrentRequest()->query->set('ref', $tlRefererId);

		}

		\System::getContainer()->get('session')->set('referer', $referrerSession);
	}
}
