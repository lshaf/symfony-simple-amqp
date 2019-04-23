<?php

namespace lshaf\amqp\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessCommand extends ContainerAwareCommand
{
    /** @var \lshaf\amqp\Services\AMQPService */
    private $amqp;
    private $logger;
    protected function configure()
    {
        $this->setName("amqp:process")
            ->setDescription("Process input from amqp based on process class");
    }
    
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $container    = $this->getContainer();
        $this->amqp   = $container->get("lshaf.amqp");
        $this->logger = $container->get('logger');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->amqp->process($this->logger);
    }
}
