<?php

namespace lshaf\amqp\Command;

use lshaf\amqp\Provider\AMQPAbstract;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ReceiverCommand
 *
 * @author  L Shaf <shafry2008@gmail.com>
 * @package lshaf\amqp\Command
 */
class ReceiverCommand extends ContainerAwareCommand
{
    /** @var \Symfony\Bridge\Monolog\Logger */
    private $logger;
    
    /** @var \lshaf\amqp\Services\AMQPService */
    private $amqp;
    
    protected function configure()
    {
        $this->setName("amqp:receiver")
            ->setHidden(true)
            ->setDescription("RabbitMQ Push Receiver")
            ->addArgument("exchange_name")
            ->addArgument("routing_key")
            ->addArgument("message");
    }
    
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->logger = $this->getContainer()->get('logger');
        $this->amqp = $this->getContainer()->get('lshaf.amqp');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->amqp->getConfig();
        $debug = $config['options']['debug'];
        
        $debugString = ($debug ? "" : "IN") . "ACTIVE";
        $this->logger->info(" [#] DEBUG MODE IS {$debugString}");
        
        try {
            $exchange = $input->getArgument("exchange_name");
            $routingKey = $input->getArgument("routing_key");
            $rawMessage = $input->getArgument("message");
            $message = @file_get_contents($rawMessage);
            
            if ($debug) {
                $this->logger->info(" [#] Received: " . $rawMessage);
            }
            
            if (is_null($message)) {
                throw new \Exception(" [@] ERROR: message null");
            }
            
            $data = @json_decode($message, true);
            if (is_null($data)) {
                throw new \Exception(" [@] ERROR: JSON invalid");
            }
            
            $classFile = preg_replace_callback('/(^|_|\.|\s)+(.)/', function ($match) {
                return ('.' === $match[1] ? '_' : '').strtoupper($match[2]);
            }, $routingKey);
            $namespace = rtrim($config['options']['namespace'], "\\");
            $className = $namespace . "\\{$exchange}\\{$classFile}Job";
            
            if (!class_exists($className)) {
                throw new \Exception("Class {$className} is not exist");
            }
            
            $instance = new $className($this->getContainer(), $data);
            if (!($instance instanceof AMQPAbstract)) {
                throw new \Exception("Class {$className} must be instance of " . AMQPAbstract::class);
            }
            
            if ($debug) {
                $this->logger->info(" [>] RUN script {$className}");
            }
            
            $instance->execute();
            $this->logger->info(" [V] Done");
            $output->writeln("Done");
        } catch (\Exception $e) {
            if ($debug) $this->logger->error("{$e->getFile()}[{$e->getLine()}]:  " . $e->getMessage());
        }
    }
}
