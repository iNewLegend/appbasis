<?php
/**
 * @file    : server/core/Controller.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 * @todo    : 
 */

namespace Core;

class Controller extends Loader
{
    const PATH = 'controllers/';
    const NAMESPACE = '\\Controllers\\';

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
			self::NAMESPACE . $name,
			$container,
			$autoLoad
		);
    }

    /**
     * Check if the method exist in the controller.
     *
     * @param string $method
     * @return boolean
     */
    public function methodExists($method)
    {
        return method_exists($this->handler, $method);
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
        $r = call_user_func_array([$this->handler, $method], $params);
        /*
         * If the the controller method returns array print it as json
         * Else just echo the result if its not empty
         */
        if (! empty($r)) {
            if (is_array($r)) {
                header('Content-Type: application/json');
                echo json_encode($r);
            } else {
                echo $r;
            }
        }
    }
} // EOF Controller.php