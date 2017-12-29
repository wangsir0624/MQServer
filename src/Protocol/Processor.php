<?php
namespace Wangjian\MQServer\Protocol;

use Wangjian\MQServer\Server;

class Processor {
    /**
     * process the command
     * @param string $command
     * @param Server $server
     * @param bool $write_log  whether write the command to the log file
     * @return string  returns the response
     */
    public static function process($command, Server $server, $write_log = true) {
        //write the command to the log file
        if($write_log) {
            $server->writeLog($command . "\r\n");
        }

        if(substr($command, 0, 4) == 'new ') {
            $tokens = explode(' ', $command);
            if(count($tokens) != 3) {
                return 'wrong';
            }
            list(, $queue, $max_items) = $tokens;

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
            $tokens = explode(' ', $command);
            if(count($tokens) != 2) {
                return 'wrong';
            }
            list(, $queue) = $tokens;

            $result = $server->queues->removeQueue($queue);

            if($result) {
                return 'deleted';
            } else {
                return 'not deleted';
            }
        } else if(substr($command, 0, 7) == 'exists ') {
            $tokens = explode(' ', $command);
            if(count($tokens) != 2) {
                return 'wrong';
            }
            list(, $queue) = $tokens;

            $result = $server->queues->existsQueue($queue);

            if($result) {
                return 'exists';
            } else {
                return 'not exists';
            }
        } else if(substr($command, 0, 3) == 'in ') {
            $tokens = explode(' ', $command);
            if(count($tokens) != 4) {
                return 'wrong';
            }
            list(, $queue, $item, $top) = $tokens;

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
            if(empty($queues)) {
                return 'wrong';
            }

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
        } else if(substr($command, 0, 5) == 'bout ') {
            $queues = explode(' ', substr($command, 5));
            if(empty($queues)) {
                return 'wrong';
            }

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

            return '';
        } else {
            return 'wrong';
        }
    }
}