<?php
namespace Wangjian\MQServer\Connection;

use InvalidArgumentException;
use Wangjian\Foundation\EventLoop\EventLoopInterface;
use Exception;

class Connection
{
    /**
     * watch mode constants
     * @const int
     */
    const WATCH_READ = 1;
    const WATCH_WRITE = 2;

    /**
     * resource handler
     * @var resource
     */
    protected $stream;

    /**
     * whether the connection is closed
     * @var bool
     */
    protected $closed = false;

    /**
     * private constructor
     */
    private function __construct()
    {
    }

    /**
     * connect to a server
     * @param string $remoteSocket  the server host
     * @param int $timeout  the connect timeout
     * @return self
     * @throws Exception
     */
    public static function connect($remoteSocket, $timeout = 5)
    {
        $stream = stream_socket_client($remoteSocket, $errno, $errstr, $timeout);
        if(!is_resource($stream)) {
            throw new Exception('stream_socket_client() failed: ' . $errstr, $errno);
        }

        $connection = new self();
        $connection->stream = $stream;

        return $connection;
    }

    /**
     * accept a connection from a server socket
     * @param resource $stream  server socket
     * @param int $timeout  the connection timeout
     * @return self
     * @throws Exception
     */
    public static function accept($stream, $timeout = 0)
    {
        if($timeout > 0) {
            $stream = @stream_socket_accept($stream, $timeout);
        } else {
            $stream = @stream_socket_accept($stream);
        }

        if(!is_resource($stream)) {
            throw new Exception('stream_socket_server() failed');
        }

        $connection = new self();
        $connection->stream = $stream;

        return $connection;
    }

    /**
     * read data from connection
     * @param int $length  max length
     * @return string
     */
    public function read($length)
    {
        return fread($this->stream, $length);
    }

    /**
     * write data to connection
     * @param string $buffer
     * @param int $length
     * @return int
     */
    public function write($buffer, $length = null)
    {
        return fwrite($this->stream, $buffer, is_null($length) ? strlen($buffer) : $length);
    }

    /**
     * watch the connection for read/write event
     * @param EventLoopInterface $loop
     * @param callable $callback
     * @param array $args
     * @param int $mode
     * @return bool
     */
    public function watch(EventLoopInterface $loop, callable $callback, $args = [], $mode = self::WATCH_READ | self::WATCH_WRITE)
    {
        if(($mode & self::WATCH_READ) == self::WATCH_READ) {
            $loop->add($this->stream, EventLoopInterface::EV_READ, $callback, $args);
        }

        if(($mode & self::WATCH_WRITE) == self::WATCH_WRITE) {
            $loop->add($this->stream, EventLoopInterface::EV_WRITE, $callback, $args);
        }

        return true;
    }

    /**
     * close the connection
     * @return bool
     */
    public function close()
    {
        if($result = fclose($this->stream)) {
            $this->closed = true;
        }

        return $result;
    }


    public function __destruct()
    {
        if(!$this->closed) {
            fclose($this->stream);
        }
    }

    /**
     * set the I/O buffer size, including read buffer and write buffer
     * @param int $buffer
     * @return bool
     */
    public function setBuffer($buffer)
    {
        return $this->setReadBuffer($buffer) && $this->setWriteBuffer($buffer);
    }

    /**
     * set the read buffer size
     * @param int $buffer
     * @return bool
     */
    public function setReadBuffer($buffer)
    {
        return stream_set_read_buffer($this->stream, $buffer) === 0;
    }

    /**
     * set the write buffer size
     * @param int $buffer
     * @return bool
     */
    public function setWriteBuffer($buffer)
    {
        return stream_set_write_buffer($this->stream, $buffer) === 0;
    }

    /**
     * set blocking
     * @return bool
     */
    public function setBlocking()
    {
        return stream_set_blocking($this->stream, 1);
    }

    /**
     * set none blocking
     * @return bool
     */
    public function setNoneBlocking()
    {
        return stream_set_blocking($this->stream, 0);
    }

    /**
     * get the local address
     * @return string
     */
    public function getLocalAddress()
    {
        return stream_socket_get_name($this->stream, false);
    }

    /**
     * get the local ip
     * @return string
     */
    public function getLocalIp()
    {
        list($ip, ) = explode(':', $this->getLocalAddress(), 2);

        return $ip;
    }

    /**
     * get the local port
     * @return int
     */
    public function getLocalPort()
    {
        list(, $port) = explode(':', $this->getLocalAddress(), 2);

        return (int)$port;
    }

    /**
     * get the remote address
     * @return string
     */
    public function getRemoteAddress()
    {
        return stream_socket_get_name($this->stream, true);
    }

    /**
     * get the remote ip
     * @return string
     */
    public function getRemoteIp()
    {
        list($ip, ) = explode(':', $this->getRemoteAddress(), 2);

        return $ip;
    }

    /**
     * get the remote port
     * @return int
     */
    public function getRemotePort()
    {
        list(, $port) = explode(':', $this->getRemoteAddress(), 2);

        return (int)$port;
    }
}