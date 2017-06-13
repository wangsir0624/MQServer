<?php
namespace Wangjian\MQServer;

use Wangjian\MQServer\EventLoop\EventLoopFactory;
use Wangjian\MQServer\EventLoop\EventLoopInterface;
use Wangjian\MQServer\Connection\Connection;
use Wangjian\MQServer\Queue\QueueManager;

class Server {
    /**
     * the Server Object
     * @var Server
     */
    protected static $server = null;

    /**
     * the queue manager
     * @var QueueManager
     */
    public $queues;

    /**
     * the event loop
     * @var EventLoopInterface
     */
    public $loop;

    /**
     * the connection timeout
     * @var int
     */
    public $connectionTimeout;

    /**
     * the socket stream
     * @var resource
     */
    public $stream;

    /**
     * the connections
     * @var \SplObjectStorage
     */
    public $connections;

    /**
     * the lifetime of the inactive connections
     * @var int
     */
    public $max_time;

    /**
     * run the server
     */
    public static function run() {
        self::parseCommand();
    }

    /**
     * parse the command parameters
     */
    public static function parseCommand() {
        global $argc, $argv;

        echo "parse the command parameters...\r\n";
        CommandParser::parse($argv, array(
            'q' => 10,
            't' => 600,
        ));

        $ip = CommandParser::getArg(1);
        $port = CommandParser::getArg(2);
        $queues = CommandParser::getOpt('q');
        $max_time = CommandParser::getOpt('t');

        if(empty($ip) || empty($port)) {
            self::showHelp();
            exit;
        }

        echo "initialize the message queue server...\r\n";
        self::$server = new Self($queues, $max_time);

        try {
            self::$server->listen($ip, $port, 5);
        } catch (\RuntimeException $e) {
            exit($e->getMessage());
        }
    }

    /**
     * show the usage
     */
    public static function showHelp() {
        $help = <<<EOT
start the message queue server
Usage: php index.php [options] ip port
Options:
-q  max queues, default 10
-t  the lifetime of the inactive connections in seconds, default 3600
EOT;

        echo $help;
    }

    /**
     * Server constructor.
     * @param int $max_queues  the max number of queues in the server
     * @param int $max_time  the lifetime of the inactive connections in seconds
     */
    public function __construct($max_queues = 10, $max_time = 100) {
        $this->queues = new QueueManager($max_queues);
        $this->max_time = $max_time;
    }

    /**
     * listen for the incoming connections
     * @param string $ip
     * @param int $port
     * @param int $timeout
     */
    public function listen($ip, $port, $timeout = 5) {
        echo "OK, prepare accepting the incomming connections...\r\n";
        $stream = @stream_socket_server("tcp://$ip:$port", $errno, $errstr);
        if(!$stream) {
            throw new \RuntimeException("$errno: $errstr");
        }
        $this->stream = $stream;
        stream_set_blocking($this->stream, 0);
        $this->loop = EventLoopFactory::createLoop();
        $this->connectionTimeout = $timeout;
        $this->connections = new \SplObjectStorage();

        $this->loop->add($this->stream, EventLoopInterface::EV_READ, array($this, 'handleConnection'));
        $this->loop->add($this->max_time, EventLoopInterface::EV_TIMER, array($this, 'clearInactiveConnections'));
        $this->loop->run();
    }

    /**
     * handle the incoming connection
     */
    public function handleConnection() {
        try {
            $connection = new Connection($this);
        } catch (\RuntimeException $e) {
            return;
        }

        stream_set_blocking($connection->stream, 0);
        $this->loop->add($connection->stream, EventLoopInterface::EV_READ, array($connection, 'handleMessage'));
        $this->connections->attach($connection);
    }

    /**
     * clear the inactive connections
     */
    public function clearInactiveConnections() {
        $now = time();

        $closed = 0;
        foreach($this->connections as $connection) {
            if($now - $connection->last_time >= $this->max_time) {
                $connection->send('the connection is terminated by the server.');
                $connection->close();
                $closed++;
            }
        }
    }
}