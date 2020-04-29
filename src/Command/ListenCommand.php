<?php

namespace lshaf\amqp\Command;

use lshaf\amqp\Services\AMQPService;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListenCommand extends ContainerAwareCommand
{
    /** @var \lshaf\amqp\Services\AMQPService */
    private $amqp;
    private $container;
    
    protected function configure()
    {
        $this->setName("amqp:listen")
            ->setDescription("Listen AMQP for debugging purpose")
            ->addArgument('debug', InputArgument::OPTIONAL, 'Activate debug mode');
    }
    
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->container = $container = $this->getContainer();
        $this->amqp = $container->get('lshaf.amqp');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isDebug = $input->getArgument('debug');
        $output->writeln(" [#] Listening your service");
        
        if ($isDebug == "1") {
            $output->writeln(" [#] Debugging mode");
            $this->amqp->listen(function (AMQPMessage $msg) {
                $info = $msg->delivery_info;
                unset($info['channel']);
                dump([
                    'body' => $msg->getBody(),
                    'delivery_info' => $info,
                ]);
            });
        } else {
            $output->writeln(" [#] Waiting for data transmission");
            $this->amqp->process($this->container);
        }
    }
}
