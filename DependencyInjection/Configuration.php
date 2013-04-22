<?php

namespace Success\AdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('success_admin');

        $rootNode
            //->addDefaultsIfNotSet()
            ->children()
              ->scalarNode('user_model')
                  ->isRequired()
                  ->cannotBeEmpty()
              ->end()
              ->scalarNode('user_admin')
                  ->isRequired()
                  ->cannotBeEmpty()
              ->end()
              ->scalarNode('user_controller')
                  ->isRequired()
                  ->cannotBeEmpty()
              ->end()
              ->scalarNode('group_model')
                  ->isRequired()
                  ->cannotBeEmpty()
              ->end()
              ->scalarNode('group_admin')
                  ->isRequired()
                  ->cannotBeEmpty()
              ->end()
              ->scalarNode('group_controller')
                  ->isRequired()
                  ->cannotBeEmpty()
              ->end()                
            ->end()
        ;

        return $treeBuilder;
    }
}
