<?php
/**
 * @file: controllers/test.php
 * @author: Name <email@email.com>
 */

namespace Controllers;

class Test
{
    /**
     * Instance of Ip Module
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
     * Instace Of Database Module
     * 
     * @var \Modules\Database
     */
    private $database;

    /**
     * Function __construct() : Construct Test Controller
     * @param \Modules\Ip     $ip     
     * @param \Modules\Logger $logger   
     */
    public function __construct(\Modules\Ip $ip, \Modules\Logger $logger)
    {
        $this->ip     = $ip;
        $this->logger = $logger;

        $this->database = \Services\Database\Pool::get();
    }

    /**
     * Function index() : Default method of Controller
     *
     * @return string
     */
    public function index()
    {
        return __FILE__ . ':' . __LINE__;
    }

    /**
     * Function db() : Test long database query
     *
     * @return string
     */
    public function db()
    {
        return $this->database->fakeLongQuery();
    }
} // EOF controllers/test.php
