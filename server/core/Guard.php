<?php
/**
 * @file    : server/core/Guard.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 * @todo    :
 */

namespace Core;

interface IGuard
{
    public function run();
}

class Guard extends Loader
{
    const PATH = 'guards/';
    const NAMESPACE = '\\Guards\\';
    const PREFIX = 'Guard';

    /**
     * Initialize Guard loader
     *
     * @param string $name
     * @param \DI\Container $container
     * @param boolean $autoLoad
     */
    public function __construct($name, $container, $autoLoad = false)
    {
        parent::__construct($name,
            self::PATH . ucfirst($name) . '.php',
            self::NAMESPACE . $name . SELF::PREFIX,
            $container,
            $autoLoad
        );
    }

    /**
     * Function do run the guard
     *
     * @return void
     */
    public function run()
    {
        if ($this->handler) {
            $this->handler->run();
        }
    }
} // EOF Guard.php