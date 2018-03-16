<?php
/**
 * @file    : core/ConfigBase.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Core;

class ConfigBase
{
    /**
     * The Logger instance
     *
     * @var \Core\Logger
     */
    private $logger;

    /**
     * Config Initialize
     */
    public function __construct()
    {
        $this->logger = new \Core\Logger(get_called_class());
        $this->logger->debug("Config Loaded");
    }

    /**
     * Called when requesting non exist member
     *
     * @param string $param
     * @return void
     */
    public function __get($param)
    {
        $this->logger->error("param: `$param` not found");

        return null;
    }
} // EOF ConfigBase.php
