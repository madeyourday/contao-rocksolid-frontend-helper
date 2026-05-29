<?php

namespace MadeYourDay\RockSolidFrontendHelper;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouteCollection;

class ContaoManagerPlugin implements BundlePluginInterface, RoutingPluginInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getBundles(ParserInterface $parser): array
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
	public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel): ?RouteCollection
	{
		return $resolver
			->resolve(__DIR__.'/../config/routing.yml')
			->load(__DIR__.'/../config/routing.yml')
		;
	}
}
