<?php
namespace Wangjian\MQServer\Queue;

use Countable;

class QueueManager implements Countable {
    /**
     * the max count of queues in the manager
     * @var int
     */
    protected $max_queues;

    /**
     * the queues array
     * @var array
     */
    public $queues = array();

    /**
     * QueueManager constructor.
     * @param int $max_queues  the max count of queues in the manager
     */
    public function __construct($max_queues = 10) {
        $this->max_queues = $max_queues;
    }

    /**
     * create a queue
     * @param int $max_items  the max count of items in the queue
     * @return Queue
     */
    public function createQueue($max_items = 10000) {
        return new Queue($max_items);
    }

    /**
     * add a queue to the manager
     * @param string $name  the queue name
     * @param int $max_items  the max count of items in the queue
     * @return bool  return true on success, and false on failure
     * @throws \Exception  when the queue count of the manager reaches the limit, throws an Exception
     */
    public function addQueue($name, $max_items) {
        if($this->count() >= $this->max_queues) {
            throw new \Exception("can't create more queues");
        }

        if($this->existsQueue($name)) {
            return false;
        } else {
            $this->queues[$name] = $this->createQueue($max_items);
            return true;
        }
    }

    /**
     * remove a queue from the manager
     * @param string $name  the queue name
     * @return bool  return true on success, and false on failure
     */
    public function removeQueue($name) {
        if($this->existsQueue($name)) {
            unset($this->queues[$name]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * whether the queue contained in the manager
     * @param string $name the queue name
     * @return bool
     */
    public function existsQueue($name) {
        return !empty($this->queues[$name]) && ($this->queues[$name] instanceof Queue);
    }

    /**
     * get a queue
     * @param string $name  the queue name
     * @return bool|Queue  return the queue. when the queue does not exists, return false.
     */
    public function getQueue($name) {
        if($this->existsQueue($name)) {
            return $this->queues[$name];
        } else {
            return false;
        }
    }

    /**
     * the queue count of the manager
     * @return int
     */
    public function count() {
        return count($this->queues);
    }
}