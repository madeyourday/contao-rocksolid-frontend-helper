<?php

declare(strict_types=1);

/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\RockSolidFrontendHelper\Controller;

use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\DataContainer;
use Contao\Input;
use Contao\System;
use Doctrine\DBAL\Connection;
use MadeYourDay\RockSolidFrontendHelper\ElementBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * RockSolid Frontend Helper JSON API.
 *
 * @Route("/contao/rocksolid-frontend-helper", defaults={"_scope" = "backend", "_token_check" = true})
 */
class JsonApiController extends AbstractController implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * @Route("/elements", name="rocksolid_frontend_helper_elements")
     */
    public function elementsAction(Request $request, ElementBuilder $elementBuilder): Response
    {
        if (!\is_string($request->get('table'))) {
            throw new NotFoundHttpException();
        }

        // Only tl_content is supported so far
        if ('tl_content' !== $request->get('table')) {
            throw new NotFoundHttpException();
        }

        $this->framework->initialize();

        return $this->json(
            array_filter(
                $elementBuilder->getElements($request->get('table')),
                static fn ($element) => !empty($element['insert']),
            ),
        );
    }

    /**
     * @Route("/insert", name="rocksolid_frontend_helper_insert", methods={"POST"})
     */
    public function insertAction(Request $request, ElementBuilder $elementBuilder, Connection $connection): Response
    {
        $this->framework->initialize();

        $table = $request->get('table');
        $act = $request->get('act');
        $parent = explode(':', (string) $request->get('parent'));

        if (!\is_string($table) || !\is_string($act) || !\is_array($parent)) {
            throw new \InvalidArgumentException();
        }

        // Only tl_content is supported so far
        if ('tl_content' !== $table) {
            throw new NotFoundHttpException();
        }

        if ('create' !== $act && 'cut' !== $act) {
            throw new \InvalidArgumentException('Unknown act "'.$act.'"');
        }

        $previousId = null;

        if ('before' === $request->get('position')) {
            $content = $this->framework
                ->getAdapter(ContentModel::class)
                ->findById($request->get('pid'))
            ;

            $previousId = $connection
                ->fetchOne(
                    '
					SELECT id
					FROM tl_content
					WHERE pid = :pid
						AND ptable = :ptable
						AND sorting < :sorting
					ORDER BY sorting DESC
					LIMIT 1
				',
                    [
                        'pid' => $content->pid,
                        'ptable' => $content->ptable,
                        'sorting' => $content->sorting,
                    ],
                )
            ;
        }

        $this->mockInsertGetParameters($request, $previousId);
        $result = ['success' => true];

        // Create a new element at the specified position
        if ('create' === $act) {
            $id = $this->callDcaMethod($act, $table, $parent[0]);
            if (!$id) {
                throw new \RuntimeException('Unable to create element.');
            }
            $this->updateDefaultValues($elementBuilder, $connection, $id, $table, $request->get('type'));
            $result['table'] = $table;
            $result['id'] = $id;
        }
        // Move all passed elements to the new position
        else {
            foreach (array_reverse(explode(',', (string) $request->get('ids'))) as $id) {
                $this->callDcaMethod($act, $table, $parent[0], $id);
            }
        }

        return $this->json($result);
    }

    /**
     * @Route("/delete", name="rocksolid_frontend_helper_delete", methods={"POST"})
     */
    public function deleteAction(Request $request): Response
    {
        $this->framework->initialize();

        $table = $request->get('table');
        $parent = explode(':', (string) $request->get('parent'));
        $ids = array_values(array_map('intval', (array) $request->get('ids', [])));

        if (!\is_string($table) || !$ids || !\is_array($parent)) {
            throw new \InvalidArgumentException();
        }

        // Only tl_content is supported so far
        if ('tl_content' !== $table) {
            throw new NotFoundHttpException();
        }

        $input = $this->framework->getAdapter(Input::class);
        $params = [
            'act' => 'delete',
            'rt' => $request->get('REQUEST_TOKEN'),
        ];

        if (str_starts_with((string) $request->get('parent'), 'tl_article:')) {
            $params['do'] = 'article';
        }

        foreach ($params as $key => $value) {
            $input->setGet($key, $value);
        }

        foreach ($ids as $id) {
            $this->callDcaMethod('delete', $table, $parent[0], $id);
        }

        return $this->json(['success' => true]);
    }

    /**
     * Mock get parameters for the data container.
     *
     * @param int|null $previousId
     */
    private function mockInsertGetParameters(Request $request, $previousId): void
    {
        $params = [
            'act' => $request->get('act'),
            'rt' => $request->get('REQUEST_TOKEN'),
            'pid' => $request->get('pid'),
            'mode' => '1',
        ];

        if (str_starts_with((string) $request->get('parent'), 'tl_article:')) {
            $params['do'] = 'article';
        }

        if ('before' === $request->get('position')) {
            if ($previousId) {
                $params['pid'] = (string) $previousId;
            } else {
                $params['pid'] = explode(':', (string) $request->get('parent'))[1];
                $params['mode'] = '2';
            }
        }

        $input = $this->framework->getAdapter(Input::class);

        foreach ($params as $key => $value) {
            $input->setGet($key, $value);
        }

        if (!\defined('CURRENT_ID')) {
            \define('CURRENT_ID', explode(':', (string) $request->get('parent'))[1]);
        }
    }

    /**
     * Call a DCA method.
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
        $input = $this->framework->getAdapter(Input::class);
        $controller = $this->framework->getAdapter(Controller::class);
        $system = $this->framework->getAdapter(System::class);

        if ($id) {
            $input->setGet('id', (string) $id);
        }

        $input->setGet('table', $table);
        $system->loadLanguageFile('default');
        $system->loadLanguageFile($table);
        $controller->loadDataContainer($table);
        $GLOBALS['TL_DCA']['tl_content']['config']['ptable'] = $ptable;

        $driver = DataContainer::getDriverForTable($table);
        $dca = new $driver($table);
        $newElementId = null;

        try {
            $dca->$act();
        } catch (RedirectResponseException $exception) {
            parse_str(parse_url((string) $exception->getResponse()->headers->get('Location'), PHP_URL_QUERY), $params);
            if (isset($params['id'])) {
                $newElementId = (int) $params['id'];
            }
        }

        return $newElementId;
    }

    /**
     * Update the database record with the default values from the element providers.
     *
     * @param int    $id
     * @param string $table
     * @param string $type
     */
    private function updateDefaultValues(ElementBuilder $elementBuilder, Connection $connection, $id, $table, $type): void
    {
        $values = $elementBuilder->getDefaultValues($table, $type);

        $values = array_merge(
            [
                'type' => $type,
                'tstamp' => time(),
            ],
            $values,
        );

        $connection->update($table, $values, ['id' => $id]);
    }
}
