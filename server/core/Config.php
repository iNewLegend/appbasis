<?php
/**
 * @file    : core/Config.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 * @todo    : add global config that will warn if not found
 * also check the constructor.
 */

namespace Core;

class Config
{
    /**
     * Self instance
     *
     * @var \Core\Config
     */
    private static $instance;

    /**
     * Array of Core\Config(s) indexes
     *
     * @var array
     */
    private $cacheIndex = [];

    /**
     * Array of Core\Config(s)
     *
     * @var array
     */
    private $cacheData = [];

    /**
     * The Logger instance
     *
     * @var \Core\Logger
     */
    protected $logger;

    /**
     * Initialize
     */
    public function __construct()
    {
        // should i check who is the owner ? yes. no direct construct
        $parent = get_called_class();

        if ($parent !== "Core\Config") {
            $this->error("`$parent` is using `Core\Config`");
        }

        $this->logger = new \Core\Logger(self::class);
    }

    /**
     * Take care of requested config
     *
     * @param string $config
     * @return void
     */
    private function handle($config)
    {
        # check if the requested config cached
        if (in_array($config, $this->cacheIndex)) {
            # if so, return it

            if (!empty($this->cacheData[$config])) {
                return $this->cacheData[$config];
            }

            $this->logger->warn("Config: `$config` exist in cacheIndex but not in cacheData.");
        }

        # save the config in memory
        $this->cacheIndex[$config] = $config;
        $this->cacheData[$config]  = "\\Config\\$config";

        # return and load new config into cache
        return $this->cacheData[$config] = new $this->cacheData[$config]();
    }

    /**
     * Return's requested config, if exist
     *
     * @param string $config
     * @return mixed
     */
    public function _get($config)
    {
        # Check if the config exist
        if (class_exists("\\Config\\$config")) {
            return $this->handle($config);
        }

        $this->logger->warn("Config: `$config` not exist.");

        return null;
    }

    /**
     * create it self, if needed
     * if config is requested return it
     * if it empty return it self instance
     *
     * @param string $config
     * @return mixed
     */
    public static function get($config = null)
    {
        if (self::$instance === null) {
            self::$instance = new Config();
        }

        if ($config) {
            return self::$instance->_get($config);
        }

        return self::$instance;
    }
} // EOF Config.php
