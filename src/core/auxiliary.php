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
     * Array of services
     * 
     * @var array
     */
    private static $services;

    /**
     * Array of extra services (plugin)
     * 
     * @var array
     */
    private static $extServices = array();

    /**
     * Array of controllers
     * 
     * @var array
     */
    private static $controllers = array();

    /**
     * Array of extra controllers (plugin)
     * 
     * @var array
     */
    private static $extControllers = array();

    /**
     * Bool, heartbeat used as (pointer from AppBasis.php) will run main loop till it false.
     *
     * @var bool
     */
    private static $heartbeat;

    /**
     * Function declareControllers(): Declare & Print All Available Controllers
     *
     * @return void
     */
    private static function declareControllers()
    {
        $contollersPath = __DIR__ . '/../' . Controller::PATH;

        self::$globalLogger->debug("scanning `{$contollersPath}` to set the available controllers`");

        foreach (scandir($contollersPath) as $fileName) {
            if ($fileName[0] == '.') continue;

            if (substr(strrchr($fileName, "."), 1) == 'php') {
                $controlerName = Controller::SPACE . ucfirst(basename($fileName, ".php"));

                self::$globalLogger->notice("attempting to set controller `{$controlerName}`");

                if (!class_exists($controlerName)) {
                    self::$globalLogger->warning("controller: `{$controlerName}` is found but class does not exist in memory.");
                    self::$globalLogger->okFailed(false, $controlerName, false);
                    continue;
                }

                self::$globalLogger->okFailed(true, $controlerName, true);

                self::$controllers[] = $controlerName;
            }
        }

        // # DEBUG 
        foreach (self::getExtControllers() as $controlerName) {
            self::$globalLogger->notice("attempting check plugin controller: `{$controlerName}`");

            if (!class_exists($controlerName)) {
                self::$globalLogger->warning("controller: `{$controlerName}` is found but class does not exist in memory.");
                self::$globalLogger->okFailed(false, $controlerName, false);
                continue;
            }

            self::$globalLogger->okFailed(true, $controlerName, true);
        }
    }

    /**
     * Function boot() : AppBasis initial function.
     * 
     * @param \Modules\Logger   $logger
     * @param bool              $refHeartbeat - refrence to hearbeat
     *
     * @return void
     */
    public static function boot(\Modules\Logger $logger = null, &$refHeartbeat)
    {
        if (empty($logger)) {
            $logger = new \Modules\Logger(self::class);
        }

        self::$globalLogger = $logger;
        self::$heartbeat = &$refHeartbeat;
    }

    /**
     * Function auto() : Auto AppBasis Setup
     * this function should not work if boot() does not run before
     *
     * @param bool $createCore
     * @param bool $createLoop
     * @param bool $defaultServices
     *
     * @return \stdClass
     */
    public static function auto(bool $createCore = false, bool $createLoop = false, bool $defaultServices = true)
    {
        if (!self::$globalLogger) {
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
            self::declareControllers();

            // # TODO: create something smarter and more dynamic.
            if ($defaultServices) {
                self::$services[\Services\Database\Pool::class] = [];
            };

            self::$globalCore = new \Core\Core(
                self::$globalLogger,
                new \Modules\Ip('0.0.0.0'),
                self::class,
                array_merge(
                    self::$services,
                    self::$extServices
                )
            );

            if (self::$globalCore) {
                $return->core = true;
            }
        }

        self::$globalLogger->debugJson($return, 'return');

        return $return;
    }

    /**
     * function extServices() : Add extra services.
     *
     * @param array $services
     * 
     * @return void
     */
    public static function extServices(array $services)
    {
        self::$extServices = array_merge(self::$extServices, $services);
    }

    /**
     * function extControllers() : Add extra controllers.
     *
     * @param array $controllers
     * 
     * @return void
     */
    public static function extControllers(array $controllers)
    {
        self::$extControllers = array_merge(self::$extControllers, $controllers);
    }


    /**
     * Function attachFriend() : Attaching new friend engine with new handler.
     *
     * @param string $friend
     * @param mixed $port
     *
     * @return bool
     */
    public static function attachFriend(string $friend = '', $port = '51190')
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
     * Function getLoop() : Get loop
     *
     * @return \React\EventLoop\StreamSelectLoop
     */
    public static function getLoop()
    {
        return self::$globalLoop;
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

    /**
     * Function getExtServices() : Return extra services
     *
     * @return array
     */
    public static function getExtServices()
    {
        return self::$extServices;
    }

    /**
     * Function getControllers() : Return controllers
     *
     * @return array
     */
    public static function getControllers()
    {
        return self::$controllers;
    }

    /**
     * Function getExtControllers() : Return extra controllers
     *
     * @return array
     */
    public static function getExtControllers()
    {
        return self::$extControllers;
    }

    /**
     * Function heartbeat() : Return heartbeat
     *
     * @return bool
     */
    public static function heartbeat()
    {
        return self::$heartbeat;
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
     * Function shutdown() : Shutdown AppBasis
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
