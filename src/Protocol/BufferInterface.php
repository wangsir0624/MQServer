<?php
namespace Wangjian\MQServer\Protocol;

interface BufferInterface
{
    public function read($length, $untilDataEnough);

    public function peek($length, $untilDataEnough);

    public function readByte();

    public function unreadByte();
}