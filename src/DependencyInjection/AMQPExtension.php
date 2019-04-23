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
                'namespace' => "AppBundle\\Jobs\\"
            ],
            'options' => [
                'queueName' => null,
                'debug' => false,
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
            'config' => $this->mergeArray($defaultConfig, $config),
            'logger' => '@logger'
        ]);
        $container->setDefinition('lshaf.amqp', $definition);
    }
    
    public function mergeArray($a, $b)
    {
        $args = func_get_args();
        $res = array_shift($args);
        while (!empty($args)) {
            foreach (array_shift($args) as $k => $v) {
                if ($v instanceof UnsetArrayValue) {
                    unset($res[$k]);
                } elseif ($v instanceof ReplaceArrayValue) {
                    $res[$k] = $v->value;
                } elseif (is_int($k)) {
                    if (array_key_exists($k, $res)) {
                        $res[] = $v;
                    } else {
                        $res[$k] = $v;
                    }
                } elseif (is_array($v) && isset($res[$k]) && is_array($res[$k])) {
                    $res[$k] = $this->mergeArray($res[$k], $v);
                } else {
                    $res[$k] = $v;
                }
            }
        }
        return $res;
    }
    
    public function getAlias()
    {
        return "lshaf_amqp";
    }
}