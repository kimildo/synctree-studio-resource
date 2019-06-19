<?php

namespace libraries\log;

use libraries\util\CommonUtil;
use \Monolog\Logger;
use \Monolog\Processor\UidProcessor;
use \Monolog\Handler\StreamHandler;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class LogMessage
{
    private static function getLogger()
    {
        $config = include APP_DIR . 'config/' . APP_ENV . '.php';

        $logger = new Logger($config['settings']['logger']['name']);
        $logger->pushProcessor(new UidProcessor());
        try {
            $streamHandler = new StreamHandler($config['settings']['logger']['path'], $config['settings']['logger']['level']);
            $logger->pushHandler($streamHandler);
        } catch (\Exception $ex) {
            $logger->error($ex);
        }

        return $logger;
    }

    private static function getMessage($logs, $class, $func, $line)
    {
        $dt = date('Y-m-d H:i:s') . "." . CommonUtil::getUsec();

        $message = "[" . $dt . "]";
        $message .= "[" . sprintf("%-18s", gethostname()) . "]";
        $message .= "[" . sprintf("%s", $class . "\\" . $func . "(line:" . $line . ")") . "]";
        $message .= $logs . "\n";

        return $message;
    }

    public static function info($logs, $class = null, $func = null, $line = null)
    {
        list($class, $func, $line) = self::getDebugInfo($class, $func, $line);
        $message = self::getMessage($logs, $class, $func, $line);

        $logger = self::getLogger();
        $logger->info($message);
    }

    public static function error($logs, $class = null, $func = null, $line = null)
    {
        list($class, $func, $line) = self::getDebugInfo($class, $func, $line);
        $message = self::getMessage($logs, $class, $func, $line);

        $logger = self::getLogger();
        $logger->error($message);
    }

    public static function debug($logs, $class = null, $func = null, $line = null)
    {
        list($class, $func, $line) = self::getDebugInfo($class, $func, $line);
        $message = self::getMessage($logs, $class, $func, $line);

        $logger = self::getLogger();
        $logger->debug($message);
    }

    private static function getDebugInfo($class, $func, $line)
    {
        $debugInfo = debug_backtrace();

        $class = empty($class) ? $debugInfo[2]['class'] : $class;
        $func = empty($func) ? $debugInfo[2]['function'] : $func;
        $line = empty($line) ? $debugInfo[1]['line'] : $line;

        return [$class, $func, $line];
    }
}
