<?php

namespace lshaf\amqp\Command;

use lshaf\amqp\Services\AMQPService;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListenCommand extends ContainerAwareCommand
{
    /** @var \lshaf\amqp\Services\AMQPService */
    private $amqp;
    
    protected function configure()
    {
        $this->setName("amqp:listen")
            ->setDescription("Listen AMQP for debugging purpose");
    }
    
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->amqp = $this->getContainer()->get('lshaf.amqp');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(" [#] Listening your service");
        $this->amqp->listen(function (AMQPMessage $msg) {
            $info = $msg->delivery_info;
            unset($info['channel']);
            dump([
                'body' => $msg->getBody(),
                'delivery_info' => $info,
            ]);
        });
    }
}
