<?php
namespace Wangjian\MQServer\Protocol;

use Wangjian\MQServer\Connection\ConnectionInterface;

interface ProtocolInterface {
    /**
     * get the protocol message length
     * @param string $buffer
     * @param ConnectionInterface $connection
     * @return int
     */
    public static function input($buffer, ConnectionInterface $connection);

    /**
     * encode
     * @param string $buffer
     * @param ConnectionInterface $connection
     * @return string
     */
    public static function encode($buffer, ConnectionInterface $connection);

    /**
     * decode
     * @param string $buffer
     * @param ConnectionInterface $connection
     * @return mixed  returns the original data
     */
    public static function decode($buffer, ConnectionInterface $connection);
}