<?php
namespace Wangjian\MQServer\Test;

use PHPUnit\Framework\TestCase;
use Wangjian\MQServer\Connection\Connection;
use Wangjian\MQServer\Connection\BufferedConnection;

class BufferedConnectionTest extends TestCase
{
    protected $bufferedConnection;

    protected function setUp()
    {
        parent::setUp();

        $this->bufferedConnection = new BufferedConnection(Connection::connect('tcp://127.0.0.1:8888'));
        $this->bufferedConnection->connection()->write('Hello World');
        $this->bufferedConnection->pipe(100);
    }

    public function testWhenDataIsNotEnough()
    {
        $this->assertEquals(false, $this->bufferedConnection->peek(1000, false));
        $this->assertEquals('Hello', $this->bufferedConnection->peek(5, false));
        $this->assertEquals(false, $this->bufferedConnection->read(1000, false));
    }

    public function testWhenDataIsEnough()
    {
        $this->assertEquals(false, $this->bufferedConnection->peek(1000, false));
        $this->assertEquals('Hello', $this->bufferedConnection->peek(5, false));
        $this->assertEquals('Hello', $this->bufferedConnection->read(5, false));
        $this->assertEquals(' Worl', $this->bufferedConnection->read(5, false));
    }

    public function testConnection()
    {
        $this->assertInstanceOf(Connection::class, $this->bufferedConnection->connection());
    }

    public function testReadByteAndUnreadByte()
    {
        $this->assertEquals('Hello', $this->bufferedConnection->peek(5, false));
        $this->assertEquals(ord('H'), $this->bufferedConnection->readByte());
        $this->assertEquals('ello ', $this->bufferedConnection->peek(5, false));
        $this->bufferedConnection->unreadByte();
        $this->assertEquals('Hello', $this->bufferedConnection->peek(5, false));
    }
}