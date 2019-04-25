<?php

namespace lshaf\amqp\Provider;

use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AMQPAbstract
{
    private $container;
    private $command;
    private $data;
    private $params;
    
    final public function __construct(ContainerInterface $container, array $config)
    {
        $this->command = $config['command'] ?? null;
        $this->data = $config['data'] ?? null;
        $this->params = $config['params'] ?? null;
        $this->container = $container;
        
        if (is_null($this->command) or $this->command == "") {
            throw new \InvalidArgumentException("Command can't be null");
        }
        
        $this->init();
    }
    
    final protected function getContainer()
    {
        return $this->container;
    }
    
    final protected function getCommand()
    {
        return $this->command;
    }
    
    final protected function getData()
    {
        return $this->data;
    }
    
    final protected function getParams()
    {
        return  $this->params;
    }
    
    abstract public function execute();
}