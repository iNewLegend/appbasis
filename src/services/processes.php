<?php
/**
 * @file: services/processes.php
 * @author: Name <email@email.com>
 */

namespace Services;

class Processes
{
    /**
     * Self instance
     *
     * @var \Services\Processes
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
     * @return \Services\Processes | null
     */
    public static function get()
    {
        if (empty(self::$instance)) {

            self::$instance = new Processes($logger, $database);
        }

        return self::$instance;
    }

    /**
     * Function __construct() : Construct Processes Service
     *
     * @param \Modules\Logger $logger
     * @param \Modules\Database $database
     */
    public function __construct(\Modules\Logger $logger, \Modules\Database $database)
    {
        $this->logger = $logger;

        $this->config = \Services\Config::get("Processes");

        $this->initialize();
    }

    /**
     * Function initialize() : Initialize Processes Service
     * 
     * @return void
     */
    private function initialize()
    {
        $this->logger->debug("loaded");
    }

} // EOF services/processes.php
