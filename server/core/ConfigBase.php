<?php

namespace Core;

class ConfigBase
{
	private $logger;

    public function __construct() 
    {
        $this->logger = new \Core\Logger(get_called_class());
        $this->logger->debug("Config Loaded"); 
    }

    public function __get($param)
    {
    	$this->logger->error("param: `$param` not found");

    	return null;
    }
}