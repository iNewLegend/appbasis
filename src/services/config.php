<?php
/**
 * @file: services/config.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @todo: add global config that will warn if not found
 * also check the constructor.
 * rewrite this service
 */

namespace Services;

class Config
{
    /**
     * Self instance
     *
     * @var \Services\Config
     */
    private static $instance;

    /**
     * Array of Services\Config(s) indexes
     *
     * @var array
     */
    private $cacheIndex = [];

    /**
     * Array of Services\Config(s)
     *
     * @var array
     */
    private $cacheData = [];

    /**
     * The Logger instance
     *
     * @var \Modules\Logger
     */
    private $logger;

    /**
     * Function get() : Get self Service Or Config
     *
     * @return mixed
     */
    public static function get($config = null)
    {
        $parent = get_called_class();

        if(null === self::$instance) {
            if(self::class == $parent) {
                self::$instance = new Config(new \Modules\Logger(self::class, \Config\Logger::$service_config));
            }
        }

        if ($config) {
            return self::$instance->_get($config);
        }

        return self::$instance;
    }
    

    /**
     * Function __construct() : Construct Config Service
     * 
     * @param \Modules\Logger $logger
     */
    public function __construct(\Modules\Logger $logger)
    {
        // should i check who is the owner ? yes. no direct construct
        $parent = get_called_class();

        if ($parent !== "Services\Config") {
            $logger->error("`$parent` is using `Services\Config`");
        }

        $this->logger = $logger;

        $this->initialize();
    }

    /**
     * Function initialize() : Initialize Config Service
     * 
     * @return void
     */
    private function initialize()
    {
        $this->logger->debug("loaded");
    }

    /**
     * Function handle() : Take care of requested config
     *
     * @param string $config
     * 
     * @return mixed
     */
    private function handle($config)
    {
        // check if the requested config cached
        if (in_array($config, $this->cacheIndex)) {
            // if so, return it

            if (!empty($this->cacheData[$config])) {
                return $this->cacheData[$config];
            }

            $this->logger->warn("Config: `$config` exist in cacheIndex but not in cacheData.");
        }

        // save the config in memory
        $this->cacheIndex[$config] = $config;
        $this->cacheData[$config]  = "\\Config\\$config";

        // return and load new config into cache
        return $this->cacheData[$config] = new $this->cacheData[$config]();
    }

    /**
     * Function _get() : Return's requested config, if exist
     *
     * @param string $config
     * 
     * @return mixed|null
     */
    public function _get($config)
    {
        // Check if the config exist
        $config = ucfirst($config);

        if (class_exists("\\Config\\{$config}")) {
            return $this->handle($config);
        }

        $this->logger->warn("Config: `$config` not exist.");

        return null;
    }
} // EOF services/config.php
