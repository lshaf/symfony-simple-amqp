<?php

namespace lshaf\amqp\Provider;

abstract class AMQPAbstract
{
    private $command;
    private $data;
    private $params;
    
    final public function __construct(array $config)
    {
        $this->command = $config['command'] ?? null;
        $this->data = $config['data'] ?? null;
        $this->params = $config['params'] ?? null;
        
        if (is_null($this->command) or $this->command == "") {
            throw new \InvalidArgumentException("Command can't be null");
        }
        
        $this->init();
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