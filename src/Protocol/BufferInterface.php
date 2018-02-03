<?php
namespace Wangjian\MQServer\Protocol;

interface BufferInterface
{
    /**
     * read data and remove it from the buffer
     * @param int $length
     * @param bool $untilDataEnough  whether block when buffered data is not enough.
     * @return bool|string  when buffered data is not enough and $untilDataEnough is false, return false.
     */
    public function read($length, $untilDataEnough);

    /**
     * read data without removing from the buffer
     * @param int $length
     * @param bool $untilDataEnough
     * @return bool|string
     */
    public function peek($length, $untilDataEnough);

    /**
     * read a byte from buffer
     * @return int
     */
    public function readByte();

    /**
     * unread the previously read byte
     * @return bool
     */
    public function unreadByte();

    /**
     * the buffered data length
     * @return int
     */
    public function buffered();
}