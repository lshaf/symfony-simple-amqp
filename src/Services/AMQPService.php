<?php

namespace lshaf\amqp\Services;

class AMQPService
{
    private $_config;

    public function __construct(array $config)
    {
        $this->_config = $config;
    }
    
    public function dump()
    {
        dump($this->_config);
        exit;
    }
}