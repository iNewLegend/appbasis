<?php

/**
 * @file: ext/auth/auth.php
 * @author: Leonid vinikov <czf.leo123@gmail.com>
 */

class Auth_Plugin
{
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
       
        \Core\Auxiliary::extServices([ \Services\Auth::class => [] ]);
        \Core\Auxiliary::extControllers([ \Controllers\Authorization::class ]);

        return true;
    }
}