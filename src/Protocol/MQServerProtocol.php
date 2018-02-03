<?php
namespace Wangjian\MQServer\Protocol;


class MQServerProtocol implements ProtocolInterface {
    /**
     * decode the protocol message
     * @param BufferInterface $buffer
     * @return bool|mixed  return false on failure
     */
    public function decode(BufferInterface $buffer)
    {
        $data = $buffer->peek($buffer->buffered(), false);

        if(($pos = strpos($data, "\r\n")) !== false) {
            return rtrim($buffer->read($pos + 2, false), "\r\n");
        } else {
            return false;
        }
    }

    /**
     * encode data to protocol message
     * @param mixed $raw
     * @return string
     */
    public function encode($raw)
    {
        return $raw."\r\n";
    }
}