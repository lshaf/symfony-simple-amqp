<?php

namespace lshaf\amqp\Provider;

use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AMQPAbstract
{
    private $container, $manager, $connection;
    private $command, $data, $params;
    protected $doctrine;
    
    public function __construct(ContainerInterface $container, array $config)
    {
        $this->command = $config['command'] ?? null;
        $this->data = $config['data'] ?? null;
        $this->params = $config['params'] ?? null;
        $this->container = $container;
        $this->doctrine = $container->get("doctrine");
        $this->manager = $this->doctrine->getManager();
        $this->connection = $this->doctrine->getConnection();
        
        if (is_null($this->command) or $this->command == "") {
            throw new \InvalidArgumentException("Command can't be null");
        }
        
        /** Ping connection */
        if ($this->connection->ping() === false) {
            $this->connection->close();
            $this->connection->connect();
        }
    }
    
    public function getDoctrine()
    {
        return $this->doctrine;
    }
    
    public function getConnection()
    {
        return $this->connection;
    }
    
    public function getManager()
    {
        return $this->manager;
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
    
    public function execute()
    {
        $this->doctrine = $this->getContainer()->get('doctrine');
        $command = $this->getCommand();
        if (method_exists($this, $command)) {
            $this->$command();
        }
    }
}
