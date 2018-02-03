<?php
namespace Wangjian\MQServer\Test;

use PHPUnit\Framework\TestCase;
use Wangjian\MQServer\Connection\Connection;

class ConnectionTest extends TestCase
{
    protected $connection;

    protected function setUp()
    {
        parent::setUp();

        $this->connection = Connection::connect('tcp://127.0.0.1:8888');
    }

    public function testReadWrite()
    {
        $data = 'Hello World';
        $this->assertEquals(strlen($data), $this->connection->write($data));
        $this->assertEquals($data, $this->connection->read(1024));
    }

    public function testRemote()
    {
        $this->connection->write('Hello World');
        $this->assertEquals('127.0.0.1:8888', $this->connection->getRemoteAddress());
        $this->assertEquals('127.0.0.1', $this->connection->getRemoteIp());
        $this->assertEquals(8888, $this->connection->getRemotePort());
    }
}