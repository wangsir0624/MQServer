<?php
namespace Wangjian\MQServer\Protocol;

use Wangjian\MQServer\Server;

class Processor {
    /**
     * process the command
     * @param string $command
     * @param Server $server
     * @return string  returns the response
     */
    public static function process($command, Server $server) {
        if(substr($command, 0, 4) == 'new ') {
            list(, $queue, $max_items) = explode(' ', $command);

            try {
                $result = $server->queues->addQueue($queue, $max_items);

                if ($result) {
                    return 'created';
                } else {
                    return 'not created';
                }
            } catch (\Exception $e) {
                return 'not created';
            }
        } else if(substr($command, 0, 4) == 'del ') {
            list(, $queue) = explode(' ', $command);

            $result = $server->queues->removeQueue($queue);

            if($result) {
                return 'deleted';
            } else {
                return 'not deleted';
            }
        } else if(substr($command, 0, 7) == 'exists ') {
            list(, $queue) = explode(' ', $command);

            $result = $server->queues->existsQueue($queue);

            if($result) {
                return 'exists';
            } else {
                return 'not exists';
            }
        } else if(substr($command, 0, 3) == 'in ') {
            list(, $queue, $item, $top) = explode(' ', $command);

            $queue = $server->queues->getQueue($queue);
            if(!$queue) {
                return 'not stored';
            } else {
                try {
                    $result = $queue->inQueue($item, $top);
                    return 'stored';
                } catch (\OutOfRangeException $e) {
                    return 'not stored';
                }
            }
        } else if(substr($command, 0, 4) == 'out ') {
            $queues = explode(' ', substr($command, 4));

            foreach($queues as $queue) {
                $queue = $server->queues->getQueue($queue);
                if($queue) {
                    try {
                        $result = $queue->unQueue();
                        return "data $result";
                    } catch (\UnderflowException $e) {
                        continue;
                    }
                }
            }

            return 'nodata';
        } else {
            return 'wrong';
        }
    }
}