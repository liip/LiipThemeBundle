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
        $config = $this->processConfiguration(new Configuration(), $configs);

        foreach (array('themes', 'active_theme', 'path_patterns', 'cache_warming') as $key) {
            $container->setParameter($this->getAlias().'.'.$key, $config[$key]);
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $options = null;
        if (!empty($config['cookie']['name'])) {
            $options = array();
            foreach (array('name', 'lifetime', 'path', 'domain', 'secure', 'http_only') as $key) {
                $options[$key] = $config['cookie'][$key];
            }
        }
        $container->setParameter($this->getAlias().'.cookie', $options);

        if (!empty($config['cookie']['name']) || (true == $config['autodetect_theme'])) {
            $loader->load('theme_request_listener.xml');
        }

        if  (true == $config['load_controllers']) {
            $loader->load('controller.xml');
        }

        if (!empty($config['cookie']['name'])) {
            $container->getDefinition($this->getAlias().'.theme_request_listener')
                ->addTag('kernel.event_listener', array('event'=>'kernel.response', 'method'=>'onKernelResponse'));
        }

        if (!empty($config['autodetect_theme'])) {
            $id = is_string($config['autodetect_theme']) ? $config['autodetect_theme'] : 'liip_theme.theme_auto_detect';
            $container->getDefinition($this->getAlias().'.theme_request_listener')
                ->addArgument(new Reference($id));
        }

        if (true === $config['assetic_integration']) {
            $container->setParameter('liip_theme.assetic_integration', true);
        }

        $loader->load('templating.xml');
    }
}
