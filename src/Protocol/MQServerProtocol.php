<?php
namespace Wangjian\MQServer\Protocol;

use Wangjian\MQServer\Connection\ConnectionInterface;

class MQServerProtocol implements ProtocolInterface {
    /**
     * get the protocol message length
     * @param string $buffer
     * @param ConnectionInterface $connection
     * @return int  returns the frame length when the buffer is ready. Notice: when the buffer is not ready and should wait for more data, returns 0
     */
    public static function input($buffer, ConnectionInterface $connection) {
        if(($pos = strpos($buffer, "\r\n")) !== false) {
            return $pos + 2;
        } else {
            return 0;
        }
    }

    /**
     * encode
     * @param string $buffer
     * @param ConnectionInterface $connection
     * @return string  returns the encoded buffer
     */
    public static function encode($buffer, ConnectionInterface $connection) {
        return $buffer."\r\n";
    }

    /**
     * decode
     * @param string $buffer
     * @param ConnectionInterface $connection
     * @return string  returns the original data
     */
    public static function decode($buffer, ConnectionInterface $connection) {
        $data = rtrim($buffer, "\r\n");

        return $data;
    }
}