<?php
namespace RabbitCli;

class Cli
{
    /**
     * CLI usage description for Docopt.
     *
     * Describes available commands, arguments and options.
     */
    protected const DOC = "
    RabbitMQ CLI helper for publishing and listening queues
    Usage:
      rabbit-cli publish <queue> (--message=<message>) [--config=<config>]   # publish single message
      rabbit-cli listen  <queue> [--config=<config>]                        # listen and print messages
      rabbit-cli -h | --help                                                # show help
      rabbit-cli -v | --version                                             # show version
    Options:
      -m --message=<message> Message body to publish
      -c --config=<config>   Path to JSON/YAML config for exchange/queue
      -f --file=<file-path>  Read message body from file
      -h --help              Show this screen
      -v --version           Show version
    ";

    /**
     * Entry point for CLI.
     *
     * Parses arguments and dispatches to client.
     *
     * @return void
     */
    public function run()
    {
        $args = \Docopt::handle(self::DOC, [
            'help'    => true,
            'version' => 'rabbit-cli v0.1.0-rc'
        ]);

        $client = new Client();

        if ($args['publish']) {
            $client->publish($args['--message'], $args['<queue>']);
        }

        if ($args['listen']) {
            $client->listen($args['<queue>']);
        }

        $client->closeConnection();
    }
}
