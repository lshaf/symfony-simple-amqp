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
                        ->scalarNode("host")->end()
                        ->scalarNode("user")->end()
                        ->scalarNode("password")->end()
                        ->integerNode("port")->end()
                        ->scalarNode("queueName")->end()
                    ->end()
                ->end()
                ->arrayNode("listener")
                    ->children()
                        ->scalarNode("namespace")
                    ->end()
                ->end()
            ->end();
        
        return $tree;
    }
}