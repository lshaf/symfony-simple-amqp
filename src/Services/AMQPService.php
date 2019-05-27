<?php

namespace lshaf\amqp\Services;

use lshaf\amqp\Provider\AMQPAbstract;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AMQPService
{
    private $_config;
    private $_connection;
    
    public function __construct(array $config)
    {
        $this->_config = $config;
        $this->_connection = new AMQPStreamConnection(
            $config['connection']['host'],
            $config['connection']['port'],
            $config['connection']['user'],
            $config['connection']['password'],
            $config['connection']['vhost']
        );
    }
    
    private $_lastChannel;
    private $_declaredChannel = [];
    public function getChannel($channel_id = null)
    {
        $opts = $this->_config['options'];
        $channel_id = $channel_id ?? $this->_lastChannel;
        $channel = $this->_connection->channel($channel_id);
        $this->_lastChannel = $channel_id  = $channel->getChannelId();
        
        if (!in_array($channel_id, $this->_declaredChannel)) {
            $options = new AMQPTable($opts['meta']);
            $channel->queue_declare(
                $opts['queueName'],
                $opts['passive'],
                $opts['durable'],
                $opts['exclusive'],
                $opts['autoDelete'],
                $opts['nowait'],
                $options
            );
        }
        $this->_declaredChannel[] = $channel_id;
        return $channel;
    }
    
    private $_declaredExchange = [];
    public function bindExchange($exchange, $type, array $key = [])
    {
        $keyExchange = "{$exchange}.{$type}";
        $channel = $this->getChannel();
        $opts = $this->_config['options'];
        
        if (!in_array($keyExchange, $this->_declaredExchange)) {
            $channel->exchange_declare(
                $exchange,
                $type,
                $opts['passive'],
                $opts['durable'],
                $opts['autoDelete'],
                false,
                $opts['nowait']
            );
        }
        
        if (count($key) == 0) {
            $channel->queue_bind(
                $opts['queueName'],
                $exchange
            );
        } else {
            foreach ($key as $route) {
                $channel->queue_bind(
                    $opts['queueName'],
                    $exchange,
                    $route
                );
            }
        }
    }
    
    /**
     * @param string|array $data
     * @param string       $exchange
     * @param string       $key
     */
    public function send($data, $exchange = '', $key = '')
    {
        if (is_array($data)) {
            $data = json_encode($data);
        } else if (!is_string($data)) {
            throw new InvalidArgumentException("Param data only receive array or string");
        }
        
        $channel = $this->getChannel();
        $msg = new AMQPMessage($data);
        $channel->basic_publish($msg, $exchange, $key);
    }
    
    public function listen($callback)
    {
        $opts = $this->_config['options'];
        $exchanges = $opts['exchanges'];
        $channel = $this->getChannel();
        $channel->basic_consume(
            $opts['queueName'], '', false, true,
            $opts['exclusive'], $opts['nowait'], $callback
        );
    
        foreach ($exchanges as $exchange => $keys) {
            $this->bindExchange($exchange, 'direct', $keys);
        }

        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }
    
    public function process(ContainerInterface $container = null)
    {
        $debug = $this->_config['options']['debug'];
        $namespace = $this->_config['options']['namespace'];
        $exchanges = $this->_config['options']['exchanges'];
        
        foreach ($exchanges as $exchange => $keys) {
            $this->bindExchange($exchange, 'direct', $keys);
        }
        
        $this->listen(function (AMQPMessage $msg) use ($namespace, $container, $debug) {
            try {
                $logger = false;
                if ($container) {
                    $logger = $container->get('logger');
                }
                if ($debug and $logger) {
                    $logger->info(" [#] Received: " . $msg->getBody());
                }
                
                $json = @json_decode($msg->getBody(), true);
                if (!$json) {
                    throw new \Exception('ERROR DECODE JSON');
                }
                
                $exchange = $msg->delivery_info['exchange'];
                $routingKey = $msg->delivery_info['routing_key'];
                $classFile = preg_replace_callback('/(^|_|\.)+(.)/', function ($match) {
                    return ('.' === $match[1] ? '_' : '').strtoupper($match[2]);
                }, $routingKey);
                $namespace = rtrim($namespace, "\\");
                $className = $namespace . "\\{$exchange}\\{$classFile}Job";
                
                if (!class_exists($className)) {
                    throw new \Exception("Class {$className} is not exist");
                }
                
                $instance = new $className($container, $json);
                if (!($instance instanceof AMQPAbstract)) {
                    throw new \Exception("Class {$className} must be instance of " . AMQPAbstract::class);
                }
                
                $instance->execute();
                if ($debug and $logger) {
                    $logger->info(" [>] RUN script {$className}");
                }
            } catch (\Exception $e) {
                if ($debug and $logger)  {
                    $logger->error($e->getMessage());
                }
            }
        });
    }
}
