<?php
namespace Wangjian\MQServer\Test;

use PHPUnit\Framework\TestCase;
use Wangjian\MQServer\CommandParser;

class CommandParserTest extends TestCase {
    protected function setUp() {
        $raw = ['index.php', '127.0.0.1', '3000', '-q', '20', '--enabled'];

        CommandParser::parse($raw, ['h' => 'test default']);
    }

    public function testGetArg() {
        $this->assertEquals('index.php', CommandParser::getArg(0));
        $this->assertEquals('127.0.0.1', CommandParser::getArg(1));
        $this->assertEquals('3000', CommandParser::getArg(2));
    }

    public function testGetOption() {
        $this->assertEquals('20', CommandParser::getOpt('q'));
        $this->assertEquals(true, CommandParser::getOpt('enabled'));
    }

    public function testDefaultOption() {
        $this->assertEquals('test default', CommandParser::getOpt('h'));
    }
}