<?php
namespace Wangjian\MQServer\Connection;

use Wangjian\MQServer\EventLoop\EventLoopInterface;
use RuntimeException;
use Wangjian\MQServer\Protocol\MQServerProtocol;
use Wangjian\MQServer\Protocol\Processor;

class Connection implements ConnectionInterface {
    /**
     * the connection socket stream
     * @var resource
     */
    public $stream;

    /**
     * the server which this connection belongs to
     * @var ServerInterface
     */
    public $server;

    /**
     * receive buffer
     * @var string
     */
    public $recv_buffer = '';

    /**
     * receive buffer size
     * @var int
     */
    public $recv_buffer_size = 1048576;

    /*
     * the receiving time of the last message
     * @var int
     */
    public $last_time;

    /**
     * constructor
     * @param ServerInterface $server
     */
    public function __construct($server) {
        $this->server = $server;
        $this->stream = @stream_socket_accept($this->server->stream, $this->server->connectionTimeout, $peername);

        if(!$this->stream) {
            throw new RuntimeException('stream_socket_accept() failed');
        }

        $this->last_time = time();
        stream_set_read_buffer($this->stream, 0);
    }

    /**
     * send message to the client
     * @param sting buffer
     * @param string $raw  whether encode the buffer with the protocol
     * @return int the length of send data
     */
    public function send($buffer, $raw = false) {
        if($buffer) {
            if(!$raw) {
                $buffer = MQServerProtocol::encode($buffer, $this);
            }

            $len = strlen($buffer);
            $writeLen = 0;
            while (($data = fwrite($this->stream, substr($buffer, $writeLen), $len - $writeLen)) !== false) {
                $writeLen += $data;
                if ($writeLen >= $len) {
                    break;
                }
            }

            return $writeLen;
        }

        return 0;
    }

    /**
     * close the connection
     */
    public function close() {
        $this->server->connections->detach($this);

        $this->server->loop->delete($this->stream, EventLoopInterface::EV_READ);

        fclose($this->stream);
    }

    /**
     * called when the connection receive the client data
     */
    public function handleMessage() {
        $this->last_time = time();

        $buffer = fread($this->stream, $this->recv_buffer_size);

        if(!$buffer) {
            $this->close();
        }

        $this->recv_buffer .= $buffer;

        if(($length = MQServerProtocol::input($this->recv_buffer, $this)) != 0) {
            $buffer = substr($this->recv_buffer, 0, $length);
            $this->recv_buffer = substr($this->recv_buffer, $length);

            $command = trim(MQServerProtocol::decode($buffer, $this));
            $this->send(Processor::process($command, $this->server));
        }
    }

    /**
     * get the client address, including IP and port
     * @return string
     */
    public function getRemoteAddress() {
        return stream_socket_get_name($this->stream, true);
    }

    /**
     * get the client IP
     * @return string
     */
    public function getRemoteIp() {
        return substr($this->getRemoteAddress(), 0, strpos($this->getRemoteAddress(), ':'));
    }

    /**
     * get the client port
     * @return string
     */
    public function getRemotePort() {
        return substr($this->getRemoteAddress(), strpos($this->getRemoteAddress(), ':')+1);
    }
}