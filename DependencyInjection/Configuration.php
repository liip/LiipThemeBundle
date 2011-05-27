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
 * @author Tobias Ebnöther <ebi@liip.ch>
 * @author Roland Schilter <roland.schilter@liip.ch>
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
        $rootNode = $treeBuilder->root('liip_theme', 'array');
        $rootNode
            ->children()
                ->arrayNode('themes')
                    ->useAttributeAsKey('theme')
                    ->prototype('scalar')
                ->end()
            ->end()
            ->scalarNode('active_theme')->defaultValue(null)->end()
        ->end();
        return $treeBuilder->buildTree();
    }

}
