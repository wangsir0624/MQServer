<?php
namespace Wangjian\MQServer\Queue;

use Countable;
use OutOfRangeException;
use UnderflowException;

class Queue implements QueueInterface, Countable {
    /*
     * the max count of the queue
     * @var int
     */
    protected $max_items;

    /**
     * the items in the queue
     * @var array
     */
    protected $items = array();

    /**
     * Queue constructor.
     * @param int $max_items
     */
    public function __construct($max_items = 10000) {
        $this->max_items = $max_items;
    }

    /**
     * add an item at the last position of the queue
     * @param mix $item
     * @param bool $top  whether add the item at the top of the queue
     * @throws OutOfRangeException  adding an item to a full queue will throw an OutOfRangeException
     */
    public function inQueue($item, $top = false) {
        if($this->isFull()) {
            throw new OutOfRangeException('the queue is full');
        }
        if($top) {
            array_unshift($this->items, $item);
        } else {
            array_push($this->items, $item);
        }
    }

    /**
     * get an item from a queue
     * @return mixed
     */
    public function unQueue() {
        if($this->isEmpty()) {
            throw new UnderflowException('the queue is empty');
        }

        $item = array_shift($this->items);

        return $item;
    }

    /**
     * whether the queue is empty
     * @return bool
     */
    public function isEmpty() {
        return $this->count() <= 0;
    }

    /**
     * whether the queue is full
     * @return bool
     */
    public function isFull() {
        return $this->count() >= $this->max_items;
    }

    /**
     * get the current item count of the queue
     * @return int
     */
    public function count() {
        return count($this->items);
    }
}