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
                ->arrayNode("options")
                    ->children()
                        ->scalarNode("queueName")->cannotBeEmpty()->end()
                        ->scalarNode("namespace")->end()
                        ->booleanNode("debug")->end()
                        ->booleanNode("autoDelete")->end()
                        ->booleanNode("passive")->end()
                        ->booleanNode("durable")->end()
                        ->booleanNode("exclusive")->end()
                        ->booleanNode("nowait")->end()
                        ->arrayNode("meta")
                            ->prototype("scalar")->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
        
        return $tree;
    }
}