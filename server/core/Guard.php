<?php
/**
 * @file    : core/Guard.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Core;

/**
 * TODO: should be in better place
 */
interface IGuard
{
    public function run();
}

class Guard extends Loader
{
    const PATH       = 'guards/';
    const namespace  = '\\Guards\\';

    const PREFIX     = 'Guard';

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
            self::namespace  . $name . SELF::PREFIX,
            $container,
            $autoLoad
        );

    }

    /**
     * run the guard
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
