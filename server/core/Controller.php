<?php
/**
 * @file    : core/Controller.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Core;

class Controller extends Loader
{
    const PATH       = 'controllers/';
    const namespace  = '\\Controllers\\';

    /**
     *  Initialize Controller loader
     *
     * @param string $name
     * @param \DI\Container $container
     * @param boolean $autoLoad
     */
    public function __construct($name, $container, $autoLoad = false)
    {
        parent::__construct($name,
            self::PATH . $name . '.php',
            self::namespace  . $name,
            $container,
            $autoLoad
        );

    }

    /**
     * Check if the method exist in the controller. & and callable
     *
     * @param string $method
     * @return boolean
     */
    public function methodExists($method)
    {
        if ($return = method_exists($this->handler, $method)) {
            return is_callable([$this->handler, $method]);
        }

        return $return;
    }

    /**
     * Call a specific method
     *
     * @param string $method
     * @param array $params
     * @return void
     */
    public function callMethod($method, $params = [])
    {
        return call_user_func_array([$this->handler, $method], $params);
    }
} // EOF Controller.php
