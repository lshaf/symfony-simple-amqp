<?php

namespace lshaf\amqp\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $root = $tree->root("lshaf_amqp");
        
        $root
            ->children()
                ->arrayNode("connection")
                    ->children()
                        ->scalarNode("host")->defaultValue('127.0.0.1')->end()
                        ->scalarNode("user")->defaultValue('guest')->end()
                        ->scalarNode("password")->defaultValue('guest')->end()
                        ->integerNode("port")->defaultValue(5672)->end()
                        ->scalarNode("vhost")->defaultValue('/')->end()
                    ->end()
                ->end()
                ->arrayNode("options")
                    ->children()
                        ->scalarNode("queueName")->isRequired()->end()
                        ->booleanNode("debug")->defaultFalse()->end()
                        ->booleanNode("autoDelete")->defaultFalse()->end()
                        ->booleanNode("passive")->defaultFalse()->end()
                        ->booleanNode("durable")->defaultTrue()->end()
                        ->booleanNode("exclusive")->defaultFalse()->end()
                        ->booleanNode("nowait")->defaultFalse()->end()
                        ->arrayNode("meta")
                            ->normalizeKeys(false)
                            ->prototype("scalar")->end()
                        ->end()
                        # used by process
                        ->scalarNode("namespace")->defaultValue("AppBundle\\Jobs\\")->end()
                        ->arrayNode("exchanges")
                            ->prototype('array')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
        
        return $tree;
    }
}
