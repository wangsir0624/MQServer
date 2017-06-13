<?php
namespace Wangjian\MQServer;

class CommandParser {
    protected static $opts = array();

    protected static $default_opts = array();

    protected static $args = array();

    public static function parse($raw, $default_opts = array()) {
        self::$opts = array();
        self::$args = array();
        self::$default_opts = $default_opts;

        for($i = 0, $count = count($raw); $i < $count; $i++) {
            if(empty($raw[$i])) {
                continue;
            }

            if(substr($raw[$i], 0, 2) == '--') {
                $optname = substr($raw[$i], 2);
                if($optname != '') {
                    self::$opts[$optname] = true;
                }
            } else if(substr($raw[$i], 0, 1) == '-') {
                $optname = substr($raw[$i], 1);
                if($optname != '') {
                    self::$opts[$optname] = @$raw[$i+1];
                }
                unset($raw[$i+1]);
            } else {
                self::$args[] = $raw[$i];
            }
        }
    }

    public static function getOpt($optname) {
        return isset(self::$opts[$optname]) ? self::$opts[$optname] : (isset(self::$default_opts[$optname]) ? self::$default_opts[$optname] : null);
    }

    public static function getArg($index) {
        return @self::$args[$index];
    }
}