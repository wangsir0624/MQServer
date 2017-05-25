<?php
namespace Wangjian\MQServer\Protocol;

use Wangjian\MQServer\Connection\ConnectionInterface;

class MQServerProtocol implements ProtocolInterface {
    /**
     * get the protocol message length
     * @param $buffer
     * @param ConnectionInterface $connection
     * @return int return the frame length when the buffer is ready. Notice: when the buffer is not ready and should wait for more data, returns 0
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
     * @param $buffer
     * @param ConnectionInterface $connection
     * @return string  returns the encoded buffer
     */
    public static function encode($buffer, ConnectionInterface $connection) {
        $length = strlen($buffer);
        if(substr($buffer, $length-2) != "\r\n") {
            $buffer .= "\r\n";
        }

        return $buffer;
    }

    /**
     * decode
     * @param $buffer
     * @param ConnectionInterface $connection
     * @return string  returns the original data
     */
    public static function decode($buffer, ConnectionInterface $connection) {
        $data = rtrim($buffer, "\r\n");

        if(substr($data, 0, 4) == 'new ') {
            list(, $queue, $max_items) = explode(' ', $data);

            $result = $connection->server->queues->addQueue($queue, $max_items);

            if($result) {
                $connection->send("created");
            } else {
                $connection->send("not created");
            }
        } else if(substr($data, 0, 4) == 'del ') {

        } else if(substr($data, 0, 7) == 'exists ') {

        } else if(substr($data, 0, 3) == 'in ') {

        } else if(substr($data, 0, 4) == 'out ') {

        }
    }
}