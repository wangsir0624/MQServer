<?php
namespace Wangjian\MQServer\Test;

use Wangjian\MQServer\Server;

require_once __DIR__.'./../vendor/autoload.php';

$server = new Server(10);
$server->listen('127.0.0.1', 8000);