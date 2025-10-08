<?php

declare(strict_types=1);

/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\RockSolidFrontendHelper;

class ElementBuilder
{
    /**
     * @var array<ElementProviderInterface>
     */
    private $providers = [];

    public function addProvider(ElementProviderInterface $provider): void
    {
        $this->providers[] = $provider;
    }

    /**
     * Get elements by table name.
     *
     * @param string $table
     *
     * @return array Elements indexed by type name
     */
    public function getElements($table)
    {
        $elements = [];

        foreach ($this->providers as $provider) {
            $elements = array_merge($elements, $provider->getElements($table));
        }

        foreach (array_keys($elements) as $type) {
            $elements[$type] += [
                'label' => $type,
                'group' => '',
                'insert' => false,
                'showToolbar' => false,
            ];

            // Enable live reloading by default for dynamically insertable elements
            if (!isset($elements[$type]['liveReload'])) {
                $elements[$type]['liveReload'] = $elements[$type]['insert'];
            }
        }

        return $elements;
    }

    /**
     * Get default values by table name and type.
     *
     * @param string $table
     * @param string $type
     *
     * @return array
     */
    public function getDefaultValues($table, $type)
    {
        $values = [];

        foreach ($this->providers as $provider) {
            $values = array_merge($values, $provider->getDefaultValues($table, $type));
        }

        return $values;
    }
}
