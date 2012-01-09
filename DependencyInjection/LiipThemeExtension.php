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
use Symfony\Component\DependencyInjection\Reference;
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

        $config = $processor->processConfiguration($configuration, $configs);

        $container->setParameter($this->getAlias().'.themes', $config['themes']);
        $container->setParameter($this->getAlias().'.active_theme', $config['active_theme']);
        $container->setParameter($this->getAlias().'.cache_warming', $config['cache_warming']);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        if (!empty($config['cookie'])) {
            $options = array();
            foreach (array('name', 'lifetime', 'path', 'domain', 'secure', 'httponly') as $key) {
                if (isset($config['cookie'][$key])) {
                    $options[$key] = $config['cookie'][$key];
                }
            }
            $container->setParameter($this->getAlias().'.cookie', $options);

            $loader->load('theme_request_listener.xml');

            if (!empty($config['autodetect_theme'])) {
                $id = is_string($config['autodetect_theme']) ? $config['autodetect_theme'] : 'liip_theme.theme_auto_detect';
                $container->getDefinition($this->getAlias().'.theme_request_listener')->addArgument(new Reference($id));
            }
        }

        $loader->load('controller.xml');
        $loader->load('templating.xml');
    }
}
