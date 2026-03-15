<?php
namespace RabbitCli;

use PhpAmqpLib\Message\AMQPMessage;

class Client
{
    protected const DEFAULT_OPTIONS = [
        // Exchange
        'exchange'        => 'router',
        'exchangeType'    => 'direct',
        'exchangeDurable' => false,
        'internal'        => false,
        'arguments'       => [],
        'ticket'          => null,

        // Queue
        'passive'         => false,
        'durable'         => true,
        'exclusive'       => false,
        'autoDelete'      => false,
        'noWait'          => false,

        // Consumer / runtime
        'prefetchCount'   => 1,
        'autoAck'         => true,
        'usePcntl'        => false,
        'declare'         => false,
        'bindingKeys'     => [],
        'prefix'          => '',
    ];

    /**
     * AMQP connection instance.
     *
     * @var \PhpAmqpLib\Connection\AbstractConnection
     */
    protected $connection;

    /**
     * AMQP channel instance.
     *
     * @var \PhpAmqpLib\Channel\AbstractChannel|null
     */
    protected $channel;

    /**
     * Effective options for exchange / queue / consumer.
     *
     * @var array<string,mixed>
     */
    protected $options;

    /**
     * Client constructor.
     *
     * @param array<string,mixed> $options User‑provided options overriding defaults
     */
    public function __construct(array $options = [])
    {
        $this->connection = ConnectionFactory::create();
        $this->options    = array_merge(self::DEFAULT_OPTIONS, $options);
    }

    /**
     * Publish single message to queue.
     *
     * @param string $messageBody Message payload
     * @param string $queue       Target queue name
     *
     * @return void
     */
    public function publish(string $messageBody, string $queue)
    {
        $channel = $this->getChannel();

        $this->declareExchange();
        $this->declareQueue($queue);

        $message = new AMQPMessage($messageBody, [
            'content_type'  => 'text/plain',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ]);

        $channel->basic_publish($message, $this->options['exchange'], $queue);

        $this->closeChannel();
    }

    /**
     * Start consuming messages from queue.
     *
     * @param string $queue Queue name to listen
     *
     * @return void
     */
    public function listen(string $queue)
    {
        $channel = $this->getChannel();

        $this->declareExchange();
        $this->declareQueue($queue);

        $channel->basic_consume(
            $queue,
            '',
            false,
            false,
            false,
            false,
            [$this, 'processMessage']
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $this->closeChannel();
    }

    /**
     * Close underlying AMQP connection.
     *
     * @return void
     */
    public function closeConnection()
    {
        $this->connection->close();
    }

    /**
     * Get current channel or create a new one.
     *
     * @return \PhpAmqpLib\Channel\AbstractChannel
     */
    protected function getChannel()
    {
        if (!$this->channel) {
            return $this->connection->channel();
        }

        return $this->channel;
    }

    /**
     * Close channel if it exists.
     *
     * @return void
     */
    protected function closeChannel()
    {
        if ($this->channel) {
            $this->channel->close();
            $this->channel = null;
        }
    }

    /**
     * Declare queue and bind it to exchange.
     *
     * @param string $queue Queue name
     *
     * @return void
     */
    protected function declareQueue(string $queue)
    {
        $channel = $this->getChannel();
        $channel->queue_declare(
            $queue,
            $this->options['passive'],
            $this->options['durable'],
            $this->options['exclusive'],
            $this->options['autoDelete']
        );
        $channel->queue_bind($queue, $this->options['exchange'], $queue);
    }

    /**
     * Declare exchange according to options.
     *
     * @return void
     */
    protected function declareExchange()
    {
        $this->getChannel()->exchange_declare(
            $this->options['exchange'],
            $this->options['exchangeType'],
            $this->options['passive'],
            $this->options['exchangeDurable'],
            $this->options['autoDelete'],
            $this->options['internal'],
            $this->options['noWait'],
            $this->options['arguments'],
            $this->options['ticket']
        );
    }

    /**
     * Process single incoming message from consumer.
     *
     * @param \PhpAmqpLib\Message\AMQPMessage $message Received message
     *
     * @return void
     */
    public function processMessage(AMQPMessage $message)
    {
        echo "\n--------\n";
        echo $message->body;
        echo "\n--------\n";

        $message->ack();

        if ($message->body === 'quit') {
            $message->getChannel()->basic_cancel($message->getConsumerTag());
        }
    }
}
