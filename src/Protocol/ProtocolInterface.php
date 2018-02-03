<?php
namespace Wangjian\MQServer\Protocol;

interface ProtocolInterface
{
    /**
     * decode the protocol message
     * @param BufferInterface $buffer
     * @return bool|mixed  return false on failure
     */
    public function decode(BufferInterface $buffer);

    /**
     * encode data to protocol message
     * @param mixed $raw
     * @return string
     */
    public function encode($raw);
}