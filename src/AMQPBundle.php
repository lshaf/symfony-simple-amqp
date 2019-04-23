<?php

namespace lshaf\amqp;

use lshaf\amqp\DependencyInjection\AMQPExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AMQPBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new AMQPExtension();
    }
}