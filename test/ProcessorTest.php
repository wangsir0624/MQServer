<?php
namespace Wangjian\MQServer\Test;

use PHPUnit\Framework\TestCase;
use Wangjian\MQServer\Server;
use Wangjian\MQServer\Protocol\Processor;

class ProcessorTest extends TestCase {
    protected $server;

    public function setUp() {
        $this->server = new Server(10);
    }

    public function testExistsQueue() {
        $this->assertEquals('not exists', Processor::process('exists queue', $this->server));

        Processor::process('new queue 1000', $this->server);
        $this->assertEquals('exists', Processor::process('exists queue', $this->server));
    }

    public function testAddQueue() {
        $this->assertEquals('created', Processor::process('new queue 1000', $this->server));
        $this->assertEquals('not created', Processor::process('new queue 1000', $this->server));
    }

    public function testRemoveQueue() {
        $this->assertEquals('not deleted', Processor::process('del queue', $this->server));

        Processor::process('new queue 10', $this->server);
        $this->assertEquals('deleted', Processor::process('del queue', $this->server));
    }

    public function testInQueueAndUnQueue() {
        $this->assertEquals('not stored', Processor::process('in queue 111 0', $this->server));

        Processor::process('new queue 10', $this->server);
        $this->assertEquals('stored', Processor::process('in queue 111 0', $this->server));
        $this->assertEquals('stored', Processor::process('in queue 222 0', $this->server));
        $this->assertEquals('stored', Processor::process('in queue 333 1', $this->server));

        $this->assertEquals('data 333', Processor::process('out queue', $this->server));
        $this->assertEquals('data 111', Processor::process('out queue', $this->server));
        $this->assertEquals('data 222', Processor::process('out queue', $this->server));
        $this->assertEquals('nodata', Processor::process('out queue', $this->server));
    }

    public function testWrongCommand() {
        $this->assertEquals('wrong', Processor::process('fdafdada', $this->server));
    }
}