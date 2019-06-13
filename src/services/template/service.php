<?php
/**
 * @file: services/_NAME.php
 * @author: Name <email@email.com>
 */

namespace Services;

class __NAME
{
    /**
     * Self instance
     *
     * @var \Services\__NAME
     */
    private static $instance;

    /**
     * Instance of Logger Module
     *
     * @var \Modules\Logger
     */
    private $logger;

    /**
     * Function get() : Get self service
     *
     * @return \Services\__NAME
     */
    public static function get()
    {
        if (empty(self::$instance)) {

            self::$instance = new __NAME($logger, $database);
        }

        return self::$instance;
    }

    /**
     * Function __construct() : Construct __NAME Service
     *
     * @param \Modules\Logger $logger
     * @param \Modules\Database $database
     */
    public function __construct(\Modules\Logger $logger, \Modules\Database $database)
    {
        $this->logger = $logger;

        $this->config = \Services\Config::get("__NAME");

        $this->initialize();
    }

    /**
     * Function initialize() : Initialize __NAME Service
     * 
     * @return void
     */
    private function initialize()
    {
        $this->logger->debug("loaded");
    }

} // EOF services/_NAME.php
