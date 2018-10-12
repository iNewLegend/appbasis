<?php
/**
 * @file: controllers/_NAME.php
 * @author: Name <email@email.com>
 */

namespace Controllers;

class __NAME
{
    /**
     * Instance of Ip Module
     * 
     * @var \Modules\Ip
     */
    private $ip;

    /**
     * Instance of Logger Module
     *
     * @var \Modules\Logger
     */
    private $logger;

    /**
     * Function __construct() : Construct __NAME Controller
     * 
     * @param \Modules\Ip     $ip     
     * @param \Modules\Logger $logger   
     */
    public function __construct(\Modules\Ip $ip, \Modules\Logger $logger)
    {
        $this->ip     = $ip;
        $this->logger = $logger;
    }

    /**
     * Function index() : Default method of Controller
     * 
     * @internal used as default by \Core\Handler
     *
     * @return string
     */
    public function index()
    {
        return __FILE__ . ':' . __LINE__;
    }

    /**
     * Function hook() : Used to create custom hook
     * 
     * @internal used as default by \Core\Handler
     *
     * @param string $method
     *
     * @return array
     */
    public function hook(string $method)
    {
        $this->logger->debug("method: `{$method}`");

        $default = ['code' => 'failed'];

        return $default;
    }

    /**
     * Function disconnect() : Optional function that used for disconnect extra objects
     * 
     * @internal used as default by \Core\Core
     *
     * @return void
     */
    public function disconnect()
    {
    }
} // EOF controllers/_NAME.php
