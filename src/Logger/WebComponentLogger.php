<?php

namespace Gang\WebComponents\Logger;

use Psr\Log\LoggerInterface;

class WebComponentLogger
{
    private static $logger;
    private static $enabled = false;

    public static function setLogger(LoggerInterface $logger)
    {
        self::$logger = $logger;
    }

    public static function setEnabled(bool $enabled)
    {
        self::$enabled = $enabled;
    }

    private static function getLogger()
    {
        if (self::$logger === null) {
            self::$logger = new NullLogger();
        }
        return self::$logger;
    }

    public static function unsetLogger()
    {
        self::$logger = null;
    }

    public static function __callStatic($name, $arguments)
    {
        if (!self::$enabled) {
            return;
        }

        return call_user_func_array([self::getLogger(), $name], $arguments);
    }
}
