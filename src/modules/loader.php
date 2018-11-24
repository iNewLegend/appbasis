<?php
/**
 * @file: core/loader.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Modules;

class Loader
{
    /**
     * The container of DI
     *
     * @var \Core\Container
     */
    protected $container = null;

    /**
     * The handler of the object
     *
     * @var mixed
     */
    protected $handler = null;

    /**
     * Object name
     *
     * @var string
     */
    protected $name = '';

    /**
     * Object full namespace + class name
     *
     * @var string
     */
    protected $fullName = '';

    /**
     * Object path
     *
     * @var string
     */
    private $path = '';

    /**
     * Is the object available
     *
     * @var boolean
     */
    private $available = false;

    /**
     * Is the object Loaded
     *
     * @var boolean
     */
    private $loaded = false;

    /**
     * Logger instance
     *
     * @var \Modules\Logger
     */
    private $logger = null;

    /**
     * Function __construct() :
     *
     * @param string          $name
     * @param string          $path
     * @param string          $fullName
     * @param \Core\Container $container
     * @param boolean         $autoLoad
     */
    public function __construct(string $name, string $path, string $fullName, \Core\Container $container, $autoLoad = false)
    {
        $this->name      = $name;
        $this->container = $container;

        // we know that we are not dealing with something that needs base path
        if ($path[0] !== '/') {
            $this->path = \Library\Helper::basePath() . '/';
        }

        $this->path      .= $path;
        $this->fullName  = $fullName;

        $this->initialize($autoLoad);
    }

    /**
     * Function initialize() :
     *
     * @param  bool   $autoLoad
     *
     * @return void
     */
    private function initialize(bool $autoLoad)
    {
        if (\Services\Config::get('logger')->module_loader) {
            $this->logger = new \Modules\Logger(self::class);

            $this->logger->debug('autoLoad: `' . ($autoLoad ? 'true' : 'false') . '`');
        }

        if (file_exists($this->path)) {
            $this->available = true;
        }

        if ($this->logger) {
            $debug = var_export($this->available, true);
            $this->logger->debug("path: `{$this->path}` available: `{$debug}`");
        }

        if ($autoLoad) {
            $this->load();
        }

    }

    /**
     * Function load() : Load object into container
     *
     * @return boolean
     */
    public function load()
    {
        if ($this->isAvailable() && require_once ($this->path)) {
            return true;
        }

        return false;
    }

    /**
     * Function create() :
     *
     * @return boolean
     */
    public function create()
    {
        if ($this->logger) {
            $this->logger->debug("requesting from container: `{$this->fullName}`");
        }

        $this->handler = $this->container->get($this->fullName);

        if ($this->logger) {
            $this->logger->okFailed((bool) $this->handler, get_called_class(), $this->handler);
        }

        if ($this->handler) {
            return $this->loaded = true;
        }
    }

    /**
     * Function getHandler() :
     *
     * @return mixed
     */
    protected function getHandler()
    {
        return $this->handler;
    }

    /**
     * Function isAvailable() : Checks if the handler available
     *
     * @return boolean
     */
    public function isAvailable()
    {
        return $this->available;
    }

    /**
     * Function isLoaded() : Checks if the handler is loaded
     *
     * @return boolean
     */
    public function isLoaded()
    {
        return $this->loaded;
    }
} // EOF Loader.php
