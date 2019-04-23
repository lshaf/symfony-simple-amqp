<?php

namespace lshaf\amqp\Services;

use lshaf\amqp\Provider\AMQPAbstract;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

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
            $config['connection']['password']
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
    
    /**
     * @param string|array $data
     * @param string $exchange
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
        $channel = $this->getChannel();
        $channel->basic_consume(
            $opts['queueName'], '', false, true, 
            $opts['exclusive'], $opts['nowait'], $callback
        );
        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }
    
    public function process(LoggerInterface $logger = null)
    {
        $debug = $this->_config['options']['debug'];
        $namespace = $this->_config['listener']['namespace'];
        $this->listen(function (AMQPMessage $msg) use ($namespace, $logger, $debug) {
            try {
                if ($debug and $logger) {
                    $logger->info(" [#] Received: " . $msg->getBody());
                }

                $json = @json_decode($msg->getBody(), true);
                if (!$json) {
                    throw new \Exception('ERROR DECODE JSON');
                }
    
                $key = $msg->delivery_info['routing_key'];
                $namespace = rtrim($namespace, "\\");
                $className = $namespace . "\\" . ucfirst($key) . "Job";
        
                if (!class_exists($className)) {
                    throw new \Exception("Class {$className} is not exist");
                }
        
                $instance = new $className($json);
                if (!($instance instanceof AMQPAbstract)) {
                    throw new \Exception("Class {$className} must be instance of " . AMQPAbstract::class);
                }
        
                $instance->execute();
                if ($debug and $logger) {
                    $logger->info(" [>] RUN command {$command} in {$className}");
                }
            } catch (\Exception $e) {
                if ($debug and $logger)  {
                    $logger->error($e->getMessage());
                    $logger->error($e->getTraceAsString());
                }
            }
        });
    }
}
