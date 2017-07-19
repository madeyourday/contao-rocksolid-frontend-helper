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
	 * @param Request $request
	 *
	 * @return Response
	 *
	 * @Route("/elements", name="rocksolid_frontend_helper_elements")
	 */
	public function elementsAction(Request $request)
	{
		if (!is_string($request->get('table'))) {
			throw new NotFoundHttpException();
		}

		// Only tl_content is supported so far
		if ($request->get('table') !== 'tl_content') {
			throw new NotFoundHttpException();
		}

		$this->get('contao.framework')->initialize();

		return $this->json(
			array_filter(
				$this->get('rocksolid_frontend_helper.element_builder')->getElements($request->get('table')),
				function($element) {
					return !empty($element['insert']);
				}
			)
		);
	}

	/**
	 * @param Request $request
	 *
	 * @return Response
	 *
	 * @Route("/insert", name="rocksolid_frontend_helper_insert")
	 * @Method({"POST"})
	 */
	public function insertAction(Request $request)
	{
		$this->get('contao.framework')->initialize();

		$table = $request->get('table');
		$act = $request->get('act');
		$parent = explode(':', $request->get('parent'));

		if (!is_string($table) || !is_string($act) || !is_array($parent)) {
			throw new \InvalidArgumentException();
		}

		// Only tl_content is supported so far
		if ($table !== 'tl_content') {
			throw new NotFoundHttpException();
		}

		if ($act !== 'create' && $act !== 'cut') {
			throw new \InvalidArgumentException('Unknown act "'.$act.'"');
		}

		$previousId = null;

		if ($request->get('position') === 'before') {
			$content = $this->get('contao.framework')
				->getAdapter('ContentModel')
				->findByPk($request->get('pid'))
			;
			$previousId = $this->get('doctrine.dbal.default_connection')
				->fetchColumn('
					SELECT id
					FROM tl_content
					WHERE pid = :pid
						AND ptable = :ptable
						AND sorting < :sorting
					ORDER BY sorting DESC
					LIMIT 1
				', [
					'pid' => $content->pid,
					'ptable' => $content->ptable,
					'sorting' => $content->sorting,
				])
			;
		}

		$this->mockInsertGetParameters($request, $previousId);
		$result = ['success' => true];

		// Create a new element at the specified position
		if ($act === 'create') {
			$id = $this->callDcaMethod($act, $table, $parent[0]);
			if (!$id) {
				throw new \RuntimeException('Unable to create element.');
			}
			$this->updateDefaultValues($id, $table, $request->get('type'));
			$result['table'] = $table;
			$result['id'] = $id;
		}
		// Move all passed elements to the new position
		else {
			foreach (array_reverse(explode(',', $request->get('ids'))) as $id) {
				$this->callDcaMethod($act, $table, $parent[0], $id);
			}
		}

		return $this->json($result);
	}

	/**
	 * @param Request $request
	 *
	 * @return Response
	 *
	 * @Route("/delete", name="rocksolid_frontend_helper_delete")
	 * @Method({"POST"})
	 */
	public function deleteAction(Request $request)
	{
		$this->get('contao.framework')->initialize();

		$table = $request->get('table');
		$parent = explode(':', $request->get('parent'));
		$id = (int) $request->get('id');

		if (!is_string($table) || !$id || !is_array($parent)) {
			throw new \InvalidArgumentException();
		}

		// Only tl_content is supported so far
		if ($table !== 'tl_content') {
			throw new NotFoundHttpException();
		}

		$input = $this->get('contao.framework')->getAdapter('Input');
		$params = [
			'act' => 'delete',
			'rt' => $request->get('REQUEST_TOKEN'),
		];

		if (substr($request->get('parent'), 0, 11) === 'tl_article:') {
			$params['do'] = 'article';
		}

		foreach ($params as $key => $value) {
			$input->setGet($key, $value);
		}

		$this->callDcaMethod('delete', $table, $parent[0], $id);

		return $this->json(['success' => true]);
	}

	/**
	 * Mock get parameters for the data container
	 *
	 * @param Request  $request
	 * @param int|null $previousId
	 */
	private function mockInsertGetParameters(Request $request, $previousId)
	{
		$params = [
			'act' => $request->get('act'),
			'rt' => $request->get('REQUEST_TOKEN'),
			'pid' => $request->get('pid'),
			'mode' => '1',
		];

		if (substr($request->get('parent'), 0, 11) === 'tl_article:') {
			$params['do'] = 'article';
		}

		if ($request->get('position') === 'before') {
			if ($previousId) {
				$params['pid'] = (string) $previousId;
			}
			else {
				$params['pid'] = (string) explode(':', $request->get('parent'))[1];
				$params['mode'] = '2';
			}
		}

		$input = $this->get('contao.framework')->getAdapter('Input');

		foreach ($params as $key => $value) {
			$input->setGet($key, $value);
		}
	}

	/**
	 * Call a DCA method
	 *
	 * @param string $act
	 * @param string $table
	 * @param string $ptable
	 * @param int    $id
	 *
	 * @return int|null ID of the created element
	 */
	private function callDcaMethod($act, $table, $ptable = null, $id = null)
	{
		$framework = $this->get('contao.framework');
		$input = $framework->getAdapter('Input');
		$controller = $framework->getAdapter('Controller');

		if ($id) {
			$input->setGet('id', $id);
		}

		$input->setGet('table', $table);
		$controller->loadDataContainer($table);
		$GLOBALS['TL_DCA']['tl_content']['config']['ptable'] = $ptable;

		$driver = 'DC_' . $GLOBALS['TL_DCA'][$table]['config']['dataContainer'];
		$dca = new $driver($table);
		$newElementId = null;

		try {
			$dca->$act();
		}
		catch (RedirectResponseException $exception) {
			parse_str(parse_url($exception->getResponse()->headers->get('Location'), PHP_URL_QUERY), $params);
			if (isset($params['id'])) {
				$newElementId = (int) $params['id'];
			}
		}

		return $newElementId;
	}

	/**
	 * Update the database record with the default values from the element providers
	 *
	 * @param int    $id
	 * @param string $table
	 * @param string $type
	 */
	private function updateDefaultValues($id, $table, $type)
	{
		$values = $this->get('rocksolid_frontend_helper.element_builder')
			->getDefaultValues($table, $type);

		$values = array_merge([
			'type' => $type,
			'tstamp' => time(),
		], $values);

		$this->get('doctrine.dbal.default_connection')
			->update($table, $values, ['id' => $id])
		;
	}
}
