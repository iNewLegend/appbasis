<?php
/**
 * @file    : core/Config.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Core;

/**
 * todo add global config that will be warn in logger if not fond.
 */

class Config
{
    private static $instance;

    private $cacheIndex = [];
    private $cacheData = [];

    protected $logger;

    public function __construct()
    {
        // should i check who is the owner ? yes. no direct construct 
        $parent = get_called_class();

        if($parent !== "Core\Config") {
            $this->error("`$parent` is using `Core\Config`");
        }

        $this->logger = new \Core\Logger(self::class);
    }

    private function handle($config)
    {
        if(in_array($config, $this->cacheIndex)) {
            if(! empty($this->cacheData[$config])) {
                return $this->cacheData[$config];
            }

            $this->logger->warn("Config: `$config` exist in cacheIndex but not in cacheData.");
        }

        $this->cacheIndex[$config] = $config;
        $this->cacheData[$config] = "\\Config\\$config";
        
        # return and load new config into cache
        return $this->cacheData[$config] = new $this->cacheData[$config]();
    }

    /**
     * return \\Config\\*$config if request $config exist
     * 
     * @param string $config
     * @return mixed
     */
    public function _get($config)
    {
        # Check if the config exist
        if(class_exists("\\Config\\$config")) {
            return $this->handle($config);
        }

        $this->logger->warn("Config: `$config` not exist.");

        return null;
    }

    /**
     * create it self, if needed
     * return \\Config\\*$config if function request $config is not empty
     * if it empty return it self instance
     *
     * @param string $config
     * @return mixed
     */
    public static function get($config = null)
    {
        if(self::$instance === null) {
            self::$instance = new Config();
        }

        if($config) {
            return self::$instance->_get($config);
        }

        return self::$instance;
    }
}