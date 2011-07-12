<?php

/*
 * This file is part of the Liip/ThemeBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Liip\ThemeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

class LiipThemeExtension extends Extension
{
    /**
     * Loads the services based on your application configuration.
     *
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();

        $config = $processor->process($configuration->getConfigTree(), $configs);

        // Set parameters first, services depend on them.
        $container->setParameter($this->getAlias().'.themes', $config['themes']);
        $container->setParameter($this->getAlias().'.active_theme', $config['active_theme']);


        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        if (! empty($config['theme_cookie'])) {
            $container->setParameter($this->getAlias().'.theme_cookie', $config['theme_cookie']);
            $loader->load('theme_request_listener.xml');
        }
        $loader->load('templating.xml');
    }
}
