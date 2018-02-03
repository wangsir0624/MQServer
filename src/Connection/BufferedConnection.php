<?php
namespace Wangjian\MQServer\Connection;

use Wangjian\MQServer\Protocol\BufferInterface;

class BufferedConnection implements BufferInterface
{
    /**
     * connection instance
     * @var Connection
     */
    protected $connection;

    /**
     * the read buffer
     * @var string
     */
    protected $buffer = '';

    /**
     * previously read byte
     * @var int
     */
    protected $previousByte = null;

    /**
     * BufferedConnection constructor
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * read data and remove it from the buffer
     * @param int $length
     * @param bool $untilDataEnough  whether block when buffered data is not enough.
     * @return bool|string  when buffered data is not enough and $untilDataEnough is false, return false.
     */
    public function read($length, $untilDataEnough = true)
    {
        $data = $this->peek($length, $untilDataEnough);

        if($data) {
            $this->buffer = substr($this->buffer, strlen($data));
        }

        return $data;
    }

    /**
     * read data without removing from the buffer
     * @param int $length
     * @param bool $untilDataEnough
     * @return bool|string
     */
    public function peek($length, $untilDataEnough = true)
    {
        if($untilDataEnough) {
            if($length <= $this->buffered()) {
                return substr($this->buffer, 0, $length);
            }

            $this->pipe($length - $this->buffered());
        } else {
            if ($length > $this->buffered()) {
                return false;
            }

            return substr($this->buffer, 0, $length);
        }
    }

    /**
     * read a byte from buffer
     * @return int
     */
    public function readByte()
    {
        return $this->previousByte = ord($this->read(1, true));
    }

    /**
     * unread the previously read byte
     * @return bool
     */
    public function unreadByte()
    {
        if(is_null($this->previousByte)) {
            return false;
        }

        $this->buffer = chr($this->previousByte) . $this->buffer;
        $this->previousByte = null;
        return true;
    }

    /**
     * send data from connection to buffer
     * @param int $length
     * @return int
     */
    public function pipe($length)
    {
        $data = $this->connection->read($length);
        $this->buffer .= $data;

        return strlen($data);
    }

    /**
     * the buffered data length
     * @return int
     */
    public function buffered()
    {
        return strlen($this->buffer);
    }

    /**
     * return the connection instance
     * @return Connection
     */
    public function connection()
    {
        return $this->connection;
    }
}