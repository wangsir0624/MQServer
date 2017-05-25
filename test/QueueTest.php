<?php
namespace Wangjian\MQServer\Test;

use PHPUnit\Framework\TestCase;
use Wangjian\MQServer\Queue\Queue;

class QueueTest extends TestCase {
    public function testInQueueAndUnQueue() {
        $queue = new Queue;
        $queue->inQueue(1);
        $this->assertEquals(1, $queue->unQueue());

        $queue->inQueue(1.35);
        $this->assertEquals(1.35, $queue->unQueue());

        $queue->inQueue(true);
        $this->assertEquals(true, $queue->unQueue());

        $queue->inQueue("test");
        $this->assertEquals("test", $queue->unQueue());

        $queue->inQueue([1, 2, 3, 4]);
        $this->assertEquals([1, 2, 3, 4], $queue->unQueue());

        $obj = new \StdClass;
        $obj->name = 'wangjian';
        $obj->age = 24;
        $queue->inQueue($obj);
        $this->assertEquals($obj, $queue->unQueue());
    }

    public function testInQueueAtTop() {
        $queue = new Queue();

        $queue->inQueue(1);
        $queue->inQueue(2, true);

        $this->assertEquals(2, $queue->unQueue());
        $this->assertEquals(1, $queue->unQueue());
    }

    public function testCount() {
        $queue = new Queue();

        $this->assertEquals(0, $queue->count());

        $queue->inQueue(1);
        $this->assertEquals(1, $queue->count());

        $queue->unQueue();
        $this->assertEquals(0, $queue->count());
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testInQueueWhenFull() {
        $queue = new Queue(0);

        $queue->inQueue(1);
    }

    /**
     * @expectedException \UnderflowException
     */
    public function testUnQueueWhenEmpty() {
        $queue = new Queue();

        $queue->unQueue();
    }
}