<?php
namespace RabbitCli;

use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class ConnectionFactory
{
    /**
     * Create AMQP connection instance.
     *
     * @return \PhpAmqpLib\Connection\AbstractConnection
     */
    public static function create(): AbstractConnection
    {
        return new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    }
}
