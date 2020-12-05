<?php

namespace ProductsImporter\Services;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerService
{

    protected static $instance;

    private function __construct(){}

    public static function debug($message, array $context = [])
    {
        self::getLogger()->addDebug($message, $context);
    }

    /**
     * @return \Monolog\Logger
     * @throws \Exception
     */
    static public function getLogger()
    {
        if (!self::$instance) {
            self::configureInstance();
        }

        return self::$instance;
    }

    /**
     * @return Logger
     * @throws \Exception
     */
    protected static function configureInstance()
    {

        $dir = realpath('.') . DIRECTORY_SEPARATOR .'logs';
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }
        }

        $logger = new Logger('logger');
        $logger->pushHandler(new StreamHandler($dir . DIRECTORY_SEPARATOR . 'app.log', Logger::DEBUG));

        return self::$instance = $logger;
    }

    public static function info($message, array $context = [])
    {
        self::getLogger()->addInfo($message, $context);
    }

    public static function notice($message, array $context = [])
    {
        self::getLogger()->addNotice($message, $context);
    }

    public static function warning($message, array $context = [])
    {
        self::getLogger()->addWarning($message, $context);
    }

    public static function error($message, array $context = [])
    {
        self::getLogger()->addError($message, $context);
    }

    public static function critical($message, array $context = [])
    {
        self::getLogger()->addCritical($message, $context);
    }

    public static function alert($message, array $context = [])
    {
        self::getLogger()->addAlert($message, $context);
    }

    public static function emergency($message, array $context = [])
    {
        self::getLogger()->addEmergency($message, $context);
    }
}