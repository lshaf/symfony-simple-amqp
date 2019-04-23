<?php

namespace lshaf\amqp\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListenCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName("rabbitmq:listen")
            ->setDescription("Listen to rabbit mq server");
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Hello World");
    }
}