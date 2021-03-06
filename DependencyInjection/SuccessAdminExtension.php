<?php

namespace Success\AdminBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class SuccessAdminExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        
        $container->setParameter('success_admin.user_admin', $config['user_admin']);
        $container->setParameter('success_admin.user_model', $config['user_model']);        
        $container->setParameter('success_admin.user_controller', $config['user_controller']);
        
        $container->setParameter('success_admin.group_admin', $config['group_admin']);
        $container->setParameter('success_admin.group_model', $config['group_model']);        
        $container->setParameter('success_admin.group_controller', $config['group_controller']);        
    }
}
