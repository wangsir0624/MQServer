<?php
namespace Wangjian\MQServer;

use Wangjian\MQServer\EventLoop\EventLoopFactory;
use Wangjian\MQServer\EventLoop\EventLoopInterface;
use Wangjian\MQServer\Connection\Connection;
use Wangjian\MQServer\Queue\QueueManager;

class Server {
    public $queues;

    public $loop;

    public $connectionTimeout;

    public $stream;

    public $connections;

    public function __construct($max_queues = 10) {
        $this->queues = new QueueManager($max_queues);
    }

    public function listen($ip, $port, $timeout = 5) {
        $stream = stream_socket_server("tcp://$ip:$port", $errno, $errstr);
        if(!@stream) {
            throw new \RuntimeException("$errno: $errstr");
        }
        $this->stream = $stream;
        stream_set_blocking($this->stream, 0);
        $this->loop = EventLoopFactory::createLoop();
        $this->connectionTimeout = $timeout;
        $this->connections = new \SplObjectStorage();

        $this->loop->add($this->stream, EventLoopInterface::EV_READ, array($this, 'handleConnection'));
        $this->loop->run();
    }

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
}