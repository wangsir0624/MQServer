<?php
require __DIR__ . '/../../vendor/autoload.php';

use Wangjian\MQServer\Connection;

$server = stream_socket_server('tcp://127.0.0.1:8888');
if(!$server) {
    exit(1);
}

while(true) {
    try {
        $connection = Connection::accept($server);
    } catch(\Exception $e) {
        continue;
    }

    $data = $connection->read(1024);
    $connection->write($data);
    $connection->close();
}