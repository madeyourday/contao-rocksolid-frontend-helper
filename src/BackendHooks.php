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
		if (!\Input::get('rsfhr')) {
			return;
		}

		\Environment::set('queryString', preg_replace('(([&?])rsfhr=1(&|$))', '$1', \Environment::get('queryString')));
	}

	/**
	 * loadDataContainer hook
	 *
	 * - Saves the referrer in the session if it is a frontend URL
	 * - Preselects the original template in the template editor
	 *
	 * @param  string $table The data container table name
	 * @return void
	 */
	public function loadDataContainerHook($table)
	{
		if (TL_MODE !== 'BE') {
			return;
		}

		if (defined('TL_REFERER_ID') && \Input::get('ref')) {
			$referrerSession = \Session::getInstance()->get('referer');
			if (!empty($referrerSession[\Input::get('ref')]['current'])) {
				$referrerSession[\Input::get('ref')]['current'] = preg_replace('(([&?])rsfhr=1(&|$))', '$1', $referrerSession[\Input::get('ref')]['current']);
				\Session::getInstance()->set('referer', $referrerSession);
			}
		}

		// Only handle requests from the frontend helper
		if (!\Input::get('rsfhr')) {
			return;
		}

		if ($table === 'tl_templates' && \Input::get('key') === 'new_tpl') {
			if (\Input::get('original') && !\Input::post('original')) {
				// Preselect the original template
				\Input::setPost('original', \Input::get('original'));
			}
			if (\Input::get('target') && !\Input::post('target')) {
				// Preselect the target template folder
				\Input::setPost('target', \Input::get('target'));
			}
		}

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

		$referrerSession = \Session::getInstance()->get('referer');

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
		// Backwards compatibility for Contao 3.0
		else if (!defined('TL_REFERER_ID')) {
			$referrerSession['current'] = $referrer;
		}

		\Session::getInstance()->set('referer', $referrerSession);
	}
}
