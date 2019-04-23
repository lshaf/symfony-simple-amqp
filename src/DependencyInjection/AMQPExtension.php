<?php

namespace lshaf\amqp\DependencyInjection;

use lshaf\amqp\Services\AMQPService;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class AMQPExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        
        // replace with default config
        $defaultConfig = [
            'listener' => [
                'namespace' => "AppBundle\\jobs\\"
            ],
            'options' => [
                'queueName' => null,
                'autoDelete' => false,
                'passive' => false,
                'durable' => true,
                'exclusive' => false,
                'nowait' => false,
                'meta' => []
            ],
            'connection' => [
                'host' => '127.0.0.1',
                'user' => 'guest',
                'password' => 'guest',
                'vhost' => '/',
                'port' => 5672
            ],
        ];
    
    
        $definition = new Definition(AMQPService::class, [
            'config' => array_merge_recursive($defaultConfig, $config)
        ]);
        $container->setDefinition('lshaf.amqp', $definition);
    }
    
    public function getAlias()
    {
        return "lshaf_amqp";
    }
}