<?php
/**
 * @file    : server/core/Core.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 * @todo    :
 */

namespace Core;

class Core
{
    /**
     * The container of DI
     *
     * @var \DI\ContainerBuilder
     */
    protected $container;
    
    /**
     * The monolog instance
     *
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * The controller instance
     *
     * @var object
     */
    protected $controller;

    /**
     * The name of controller
     *
     * @var string
     */
    protected $controllerName = 'welcome';

    /**
     * The method that will be called
     *
     * @var string
     */
    protected $method = 'index';

    /**
     * The path of controller
     *
     * @var string
     */
    protected $controllerPath;

    /**
     * The parameters of the method
     *
     * @var array
     */
    protected $methodParams = [];

    /**
     * Create a new APP instance
     *
     * @param  string    $cmd
     * @example location description
     * @throws Exception
     */
    public function __construct($cmd)
    {
        $container = \DI\ContainerBuilder::buildDevContainer();


        $cmd = $this->parseCmd($cmd);
        
        $this->controllerName = 'Controllers\\' . $cmd->controller;
        $this->controllerPath = 'controllers/' . $cmd->controller . '.php';

        $this->method = $cmd->method;
        $this->methodParams = $cmd->params;

        if (! file_exists($this->controllerPath)) {
            throw new \Exception("controller: '{$cmd->controller}' not found, in: " . __FILE__ . '(' . __LINE__. ')');
        }

        # load the controller
        require_once($this->controllerPath);

        # create container for DI
        $container = \DI\ContainerBuilder::buildDevContainer();
        
        # create controller instance
        $this->controller = $container->get($this->controllerName);

        if (! method_exists($this->controller, $this->method)) {
            throw new \Exception("method: '{$this->method}' not found in controller: '{$this->controllerName}, in: " . __FILE__ . '(' . __LINE__. ')');
        }

        $this->callMethod($this->controller, $this->method, $this->methodParams);
    }

    /**
     * Parse the cmd aka 'CMD?' aka $_GET['cmd']
     *
     * @param  string    $cmd
     * @return object
     */
    public function parseCmd($cmd)
    {
        $return = new \stdClass();

        # setting defaults
        $return->controller = $this->controllerName;
        $return->method = $this->method;
        $return->params = [];

        if (! empty($cmd) && is_string($cmd)) {
            # remove forward slash from the end
            $cmd = rtrim($cmd, '/');

            # removes all illegal URL characters from a string
            $cmd = explode('/', $cmd);

            # set controller
            if (isset($cmd[0])) {
                # only abc for controller name
                $cmd[0] = preg_replace("/[^a-zA-Z]+/", "", $cmd[0]);

                $return->controller = $cmd[0];
                unset($cmd[0]);
            }

            # set method
            if (isset($cmd[1])) {
                # only abc and digits for method name
                $cmd[1] = preg_replace("/[^a-zA-Z0-9]+/", "", $cmd[1]);

                $return->method = $cmd[1];
                unset($cmd[1]);
            }

            # set params
            if (! empty($cmd)) {
                foreach ($cmd as $key => $param) {
                    $cmd[$key] =  filter_var($param, FILTER_SANITIZE_STRING);
                }

                $return->params = array_values($cmd);
            }
        }

        return $return;
    }

    /**
     * Call a specific method
     *
     * @param  object   $controller
     * @param  object   $method
     * @param  array    $params
     * @return void
     */
    protected function callMethod($controller, $method, $params = [])
    {
        $r = call_user_func_array([$controller, $method], $params);
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
} // EOF App.php