<?php

namespace MadeYourDay\RockSolidFrontendHelper;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class ContaoManagerPlugin implements BundlePluginInterface, RoutingPluginInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getBundles(ParserInterface $parser)
	{
		return [
			BundleConfig::create(RockSolidFrontendHelperBundle::class)
				->setLoadAfter([ContaoCoreBundle::class])
				->setReplace(['rocksolid-frontend-helper']),
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel)
	{
		return $resolver
			->resolve(__DIR__.'/Resources/config/routing.yml')
			->load(__DIR__.'/Resources/config/routing.yml')
		;
	}
}
