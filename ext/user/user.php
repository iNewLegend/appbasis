<?php

/**
 * @file: ext/user/user.php
 * @author: Leonid vinikov <czf.leo123@gmail.com>
 */


class User_Plugin
{
    /**
     * Plugin dependencies on other plugins.
     *
     * @var array
     */
    public $dependencies = [
        Auth_Plugin::class
    ];

    /**
     * Instance Of <root>/AppBasis.php Logger
     *
     * @var \Modules\Logger
     */
    private $logger;

    /**
     * Function __construct() :  Construct Auth Plugin
     *
     * @param \Modules\Logger $logger
     */
    public function __construct(\Modules\Logger $logger)
    {
        $this->logger = $logger;

        $this->initialize();
    }

    /**
     * Function initialize() : Initialize plugin
     *
     * @return void
     */
    private function initialize()
    {
        $this->logger->debug("initialize");
    }

    /**
     * Function load() : Load the plugin
     *
     * @internal this is function called by AppBasis.php
     * 
     * @return bool
     */
    public function load()
    {
        $this->logger->debug("loading plugin");

        \Core\Auxiliary::extControllers([\Controllers\User::class]);

        return true;
    }
}
