<?php
namespace Wangjian\MQServer;

use Wangjian\MQServer\EventLoop\EventLoopFactory;
use Wangjian\MQServer\EventLoop\EventLoopInterface;
use Wangjian\MQServer\Connection\Connection;
use Wangjian\MQServer\Queue\QueueManager;
use Wangjian\MQServer\Protocol\Processor;

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
     * the log file path
     * @var string
     */
    protected $log_file;

    /**
     * the log file handler
     * @var resource
     */
    protected $log_fd;

    protected $waitQueues = [];

    protected $waitMap = [];

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
            'p' => './data'
        ));

        $ip = CommandParser::getArg(1);
        $port = CommandParser::getArg(2);
        $log_file = CommandParser::getOpt('p');

        if(empty($ip) || empty($port)) {
            self::showHelp();
            exit;
        }

        echo "initialize the message queue server...\r\n";
        self::$server = new Self($log_file);

        echo "restore the server status...\r\n";
        self::$server->replayLog();

        echo "regenerate the log file...\r\n";
        self::$server->compressLog();

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
-p  the log file. it will be used to restore the server status where the server starts
EOT;

        echo $help;
    }

    /**
     * Server constructor.
     * @param int $max_queues  the max number of queues in the server
     * @param int $max_time  the lifetime of the inactive connections in seconds
     */
    public function __construct($log_file) {
        $this->log_file = $log_file;
        $this->queues = new QueueManager(100);
    }

    public function __destruct() {
        flock($this->fd, LOCK_UN);
        fclose($this->fd);
    }

    /**
     * listen for the incoming connections
     * @param string $ip
     * @param int $port
     * @param int $timeout
     */
    public function listen($ip, $port, $timeout = 5) {
        $stream = @stream_socket_server("tcp://$ip:$port", $errno, $errstr);
        if(!$stream) {
            throw new \RuntimeException("$errno: $errstr");
        }
        echo "OK, prepare accepting the incomming connections...\r\n";
        $this->stream = $stream;
        stream_set_blocking($this->stream, 0);
        $this->loop = EventLoopFactory::createLoop();
        $this->connectionTimeout = $timeout;
        $this->connections = new \SplObjectStorage();

        $this->loop->add($this->stream, EventLoopInterface::EV_READ, array($this, 'handleConnection'));
        $this->loop->add(600, EventLoopInterface::EV_TIMER, array($this, 'clearInactiveConnections'));
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

    /**
     * write data to the log file
     * @param string $log  log data
     * @return int  the writen bytes
     */
    public function writeLog($log) {
        return fwrite($this->fd, $log, strlen($log));
    }

    /**
     * replay the log to restore the server status
     */
    public function replayLog() {
        $fd = fopen($this->log_file, 'c+');
        if(!$fd) {
            throw new \RuntimeException('failed to open '.realpath($this->log_file));
        }
        flock($fd, LOCK_EX);

        while(!feof($fd)) {
            $command = fgets($fd);
            $command = substr($command, 0, strlen($command)-2);
            Processor::process($command, $this, false);
        }

        flock($fd, LOCK_UN);
        fclose($fd);
    }

    /**
     * compress the log file
     */
    public function compressLog() {
        $tmp = $this->log_file.'_temp';
        $fd = fopen($tmp, 'c');
        if(!$fd) {
            throw new \RuntimeException('failed to open '.realpath($tmp));
        }

        foreach($this->queues->queues as $name => $queue) {
            fwrite($fd, "new $name $queue->max_items\r\n");

            array_walk($queue->items, function($item) use ($fd, $name) {
                fwrite($fd, "in $name $item 0\r\n");
            });
        }

        flock($fd, LOCK_UN);
        fclose($fd);
        rename($tmp, $this->log_file);

        $fd = fopen($this->log_file, 'a');
        if(!$fd) {
            throw new \RuntimeException('failed to open '.realpath($this->log_file));
        }
        $this->fd = $fd;
        flock($this->fd, LOCK_EX);
    }
}