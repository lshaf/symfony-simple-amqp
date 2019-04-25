<?php

namespace lshaf\amqp\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessCommand extends ContainerAwareCommand
{
    /** @var \lshaf\amqp\Services\AMQPService */
    private $amqp;
    private $container;
    protected function configure()
    {
        $this->setName("amqp:process")
            ->setDescription("Process input from amqp based on process class");
    }
    
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->container = $container = $this->getContainer();
        $this->amqp = $container->get("lshaf.amqp");
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(" [#] Listening your service");
        $this->amqp->process($this->container);
    }
}
