<?php
/**
 * @file    : server/core/Loader.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 * @todo    :
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
     * Is the object avialable
     *
     * @var boolean
     */
    private $avialable = false;

    /**
     * Is the object Loaded
     *
     * @var boolean
     */
    private $loaded = false;

    /**
     * Initialize loader
     *
     * @param [type] $name
     * @param [type] $path
     * @param [type] $fullName
     * @param [type] $container
     * @param boolean $autoLoad
     */
    public function __construct($name, $path, $fullName, $container, $autoLoad = false)
    {
        $this->name = $name;
        $this->container = $container;
        $this->path = $path;
        $this->fullName = $fullName;

        if (file_exists($this->path)) {
            $this->avialable = true;
        }
    
        if ($autoLoad) {
            $this->load();
        }
    }

    /**
     * Function that load the file into container
     *
     * @return void
     */
    public function load()
    {
        if ($this->isAvialable() && require_once($this->path)) {
            $this->handler = $this->container->get($this->fullName);

            if ($this->handler) {
                return $this->loaded = true;
            }
        }

        return false;
    }

    /**
     * Checks if the handler avialable
     *
     * @return boolean
     */
    public function isAvialable()
    {
        return $this->avialable;
    }
    
    /**
     * Checks if the hanlder is loaded
     *
     * @return boolean
     */
    public function isLoaded()
    {
        return $this->loaded;
    }
} // EOF Loader.php    