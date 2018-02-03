<?php
namespace Wangjian\MQServer\Test;

use PHPUnit\Framework\TestCase;
use Wangjian\MQServer\Protocol\MQServerProtocol;
use Wangjian\MQServer\Connection\Connection;
use Wangjian\MQServer\Connection\BufferedConnection;

class MQServerProtocolTest extends TestCase
{
    protected $protocol;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->protocol = new MQServerProtocol();
    }

    public function testDecodeWhenDataIsEnough()
    {
        $buffer = new BufferedConnection(Connection::connect('tcp://127.0.0.1:8888'));
        $buffer->connection()->write("new queue 1000\r\n");
        $buffer->pipe(100);
        $this->assertEquals('new queue 1000', $this->protocol->decode($buffer));
    }

    public function testDecodeWhenDataIsNotEnough()
    {
        $buffer = new BufferedConnection(Connection::connect('tcp://127.0.0.1:8888'));
        $buffer->connection()->write("new queue 1000");
        $buffer->pipe(100);
        $this->assertEquals(false, $this->protocol->decode($buffer));
    }

    public function testEncode()
    {
        $this->assertEquals("new queue 1000\r\n", $this->protocol->encode('new queue 1000'));
    }
}