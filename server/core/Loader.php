<?php
/**
 * @file    : core/Loader.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Core;

class Loader
{
    /**
     * The container of DI
     *
     * @var \DI\Container
     */
    protected $container;

    /**
     * The handler of the object
     *
     * @var object
     */
    protected $handler;

    /**
     * Object name
     *
     * @var string
     */
    private $name;

    /**
     * Object path
     *
     * @var string
     */
    private $path;

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
     * Initialize loader
     *
     * @param string $name
     * @param string $path
     * @param string $fullName
     * @param \DI\Container $container
     * @param boolean $autoLoad
     */
    public function __construct($name, $path, $fullName, $container, $autoLoad = false)
    {
        $this->name      = $name;
        $this->container = $container;
        $this->path      = $path;
        $this->fullName  = $fullName;

        if (file_exists($this->path)) {
            $this->available = true;
        }

        if ($autoLoad) {
            $this->load();
        }
    }

    /**
     * Function that load the file into container
     *
     * @return boolean
     */
    public function load()
    {
        if ($this->isAvailable() && require_once ($this->path)) {
            $this->handler = $this->container->get($this->fullName);

            if ($this->handler) {
                return $this->loaded = true;
            }
        }

        return false;
    }

    /**
     * Checks if the handler available
     *
     * @return boolean
     */
    public function isAvailable()
    {
        return $this->available;
    }

    /**
     * Checks if the handler is loaded
     *
     * @return boolean
     */
    public function isLoaded()
    {
        return $this->loaded;
    }
} // EOF Loader.php
