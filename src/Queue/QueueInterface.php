<?php
namespace Wangjian\MQServer\Queue;

use OutOfRangeException;
use UnderflowException;

interface QueueInterface {
    /**
     * add an item at the last position of the queue
     * @param mix $item
     * @throws OutOfRangeException  adding an item to a full queue will throw an OutOfRangeException
     */
    public function inQueue($item);

    /**
     * get an item from a queue
     * @return mixed
     */
    public function unQueue();

    /**
     * whether the queue is empty
     * @return bool
     */
    public function isEmpty();

    /**
     * whether the queue is full
     * @return bool
     */
    public function isFull();
}