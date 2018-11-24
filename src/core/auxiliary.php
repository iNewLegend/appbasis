<?php
/**
 * @file: core/auxiliary.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Core;

class Auxiliary
{
    /**
     * Global loop handler
     *
     * @var \React\EventLoop\StreamSelectLoop
     */
    private static $globalLoop = null;

    /**
     * Instance of Global Core
     *
     * @var \Core\Core
     */
    private static $globalCore = null;

    /**
     * Instance of Logger Module
     *
     * @var \Modules\Logger
     */
    private static $globalLogger = null;

    /**
     * Instance of Handler Core
     *
     * @var \Core\Handler
     */
    private static $handler;

    /**
     * Array of extra services that can be passed from startup.
     * @var array
     */
    public static $extServices = array();

    /**
     * Array of extra controllers that can be passed from startup.
     * @var array
     */
    public static $extControllers = array();

    /**
     * Bool, heartbeat used as (pointer from AppBasis.php) will run main loop till it false.
     *
     * @var boolean
     */
    private static $heartbeat;

    /**
     * Function boot : AppBasis initial function.
     * 
     * @param \Modules\Logger $logger
     *
     * @return void
     */
    public static function boot(\Modules\Logger $logger = null, & $heartbeat_address)
    {
        if (empty($logger)) {
            $logger = new \Modules\Logger(self::class);
        }

        self::$globalLogger = $logger;
        self::$heartbeat = & $heartbeat_address;
    }

    public static function extServices(array $services)
    {
        self::$extServices = $services;
    }

    public static function extControllers(array $controllers)
    {
        self::$extControllers = $controllers;
    }

    /**
     * Function auto() : Auto AppBasis Setup
     *
     * @todo this function should not work if boot not run before
     *
     * @param bool $createCore
     * @param bool $createLoop
     *
     * @return \stdClass
     */
    public static function auto(bool $createCore = false, bool $createLoop = false)
    {
        if (! self::$globalLogger) {
            self::shutdown(0, "auto() function cannot run without boot() first. ", __CLASS__, __FUNCTION__);
        }

        $return = new \stdClass();

        $return->core = false;
        $return->loop = false;

        self::$globalLogger->debugJson(func_get_args(), 'params');

        if ($createLoop) {
            self::createLoop();
            $return->loop = true;
        }

        if ($createCore) {
            self::$globalCore = new \Core\Core(self::$globalLogger, new \Modules\Ip('0.0.0.0'), self::class, array_merge([
                \Services\Database\Pool::class => [],
                \Services\Auth::class          => [],
            ], self::$extServices));

            if (self::$globalCore) {
                $return->core = true;
            }
        }

        self::$globalLogger->debugJson($return, 'return');

        return $return;
    }

    /**
     * Function attachFriend() : Attaching new friend engine with new handler.
     *
     * @param string $friend
     * @param string $port
     *
     * @return bool
     */
    public static function attachFriend(string $friend = '', string $port = '51190')
    {
        self::$globalLogger->debugJson(func_get_args(), 'params');

        if (!empty($friend)) {
            self::$handler = new \Core\Handler();
            $friend        = new $friend(self::$handler, $port, self::$globalLoop);

            return true;
        }

        return false;
    }

    /**
     * Function createLoop() : Create React EventLoop and return it
     *
     * @return React\EventLoop\StreamSelectLoop
     */
    public static function createLoop()
    {
        self::$globalLoop = \React\EventLoop\Factory::create();

        return self::$globalLoop;
    }

    /**
     * Function createLoop() : Get loop
     *
     * @return \React\EventLoop\StreamSelectLoop
     */
    public static function getLoop()
    {
        return self::$globalLoop;
    }

    /**
     * Function runLoop() : Run's global loop
     *
     * @return \React\EventLoop\StreamSelectLoop
     */
    public static function runLoop()
    {
        self::$globalLogger->info("run !");
        self::$globalLoop->run();
    }

    /**
     * Function getGlobalLogger() : Get global Logger
     *
     * @return \Modules\Logger
     */
    public static function getGlobalLogger()
    {
        return self::$globalLogger;
    }

    public static function heartbeat()
    {
        return self::$heartbeat;
    }

    /**
     * TODO: Undocumented function
     *
     * @param mixed $code
     * @param mixed $dump
     * @param string $callerClass
     * @param string $callerFunction
     *
     * @return void
     */
    public static function shutdown($code, $dump, string $callerClass = '', string $callerFunction = '')
    {
        $output = var_export(func_get_args(), true) . PHP_EOL;

        if (isset(self::$globalLogger)) {
            self::$globalLogger->info($output);

            exit($code);
        }

        echo $output;

        $e = new \Exception("No output handler was found. did you init global logger?", $code);

        exit($e);
    }
} // EOF core/auxiliary.php
