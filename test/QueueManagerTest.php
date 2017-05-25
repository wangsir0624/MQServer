<?php
namespace Wangjian\MQServer\Test;

use PHPUnit\Framework\TestCase;
use Wangjian\MQServer\Queue\QueueManager;
use Wangjian\MQServer\Queue\Queue;

class QueueManagerTest extends TestCase {
    protected $manager;

    public function setUp() {
        $this->manager = new QueueManager();
    }

    public function testCreateQueue() {
        $queue = $this->manager->createQueue(0);

        $this->assertInstanceOf(Queue::class, $queue);
        $this->assertEquals(true, $queue->isFull());
    }

    public function testAddQueueAndRemoveQueue() {
        $this->assertEquals(0, $this->manager->count());

        $queue = $this->manager->createQueue();
        $this->manager->addQueue('queue', $queue);
        $this->assertEquals(1, $this->manager->count());

        $result = $this->manager->addQueue('queue', $queue);
        $this->assertEquals(false, $result);

        $result = $this->manager->removeQueue('queue_not_exists');
        $this->assertEquals(false, $result);
        $this->assertEquals(1, $this->manager->count());

        $result = $this->manager->removeQueue('queue');
        $this->assertEquals(true, $result);
        $this->assertEquals(0, $this->manager->count());
    }

    /**
     * @expectedException \Exception
     */
    public function testAddQueueWhenFull() {
        $manager = new QueueManager(0);
        $queue = $manager->createQueue();
        $manager->addQueue('queue', $queue);
    }
}