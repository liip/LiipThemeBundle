<?php

namespace Liip\ThemeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * This class contains the configuration information for the bundle
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class Configuration
{
    /**
     * Generates the configuration tree.
     *
     * @return \Symfony\Component\DependencyInjection\Configuration\NodeInterface
     */
    public function getConfigTree()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('liip_view', 'array');

        $rootNode
            ->children()
                ->arrayNode('class')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('view')->defaultValue('Liip\ViewBundle\View\DefaultView')->end()
                        ->scalarNode('serializer')->defaultValue('Symfony\Component\Serializer\Serializer')->end()
                        ->scalarNode('json')->defaultValue('Symfony\Component\Serializer\Encoder\JsonEncoder')->end()
                        ->scalarNode('xml')->defaultValue('Symfony\Component\Serializer\Encoder\XmlEncoder')->end()
                        ->scalarNode('html')->defaultValue('Liip\ViewBundle\Serializer\Encoder\HtmlEncoder')->end()
                    ->end()
                ->end()
            ->end()
            ->fixXmlConfig('format', 'formats')
            ->children()
                ->arrayNode('formats')
                    ->useAttributeAsKey('format')
                    ->prototype('scalar')
                ->end()
            ->end()
            ->booleanNode('frameworkextra')->defaultFalse()->end()
        ->end();

        return $treeBuilder->buildTree();
    }

}
