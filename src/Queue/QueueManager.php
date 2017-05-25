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
    protected $queues = array();

    public function __construct($max_queues = 10) {
        $this->max_queues = $max_queues;
    }

    public function createQueue($max_items = 10000) {
        return new Queue($max_items);
    }

    public function addQueue($name, $max_items) {
        if($this->count() >= $this->max_queues) {
            throw new \Exception("can't create more queues");
        }

        if(!empty($this->queues[$name])) {
            return false;
        } else {
            $this->queues[$name] = $this->createQueue($max_items);
            return true;
        }
    }

    public function removeQueue($name) {
        if(!empty($this->queues[$name])) {
            unset($this->queues[$name]);
            return true;
        } else {
            return false;
        }
    }

    public function count() {
        return count($this->queues);
    }
}