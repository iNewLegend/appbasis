<?php
/**
 * @file    : server/core/App.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 * @todo    :
 */

namespace Core;

class App
{
    /**
     * The container of DI
     *
     * @var \DI\Container
     */
    protected $container;
    
    /**
     * The controller instance
     *
     * @var \Core\Controller
     */
    protected $controller;

    /**
     * The guard
     *
     * @var \Core\Guard
     */
    protected $guard;

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
     * The parameters of the method
     *
     * @var array
     */
    protected $methodParams = [];
    
    /**
     * Initialize App
     *
     * @param string $cmd
     * @throws Exception
     */
    public function __construct($cmd)
    {
        $cmd = $this->parseCmd($cmd);

        # save the controller maybe for later use.
        $this->controllerName = $cmd->controller;
        $this->method = $cmd->method;
        $this->methodParams = $cmd->params;

        $this->container = \DI\ContainerBuilder::buildDevContainer();
        $this->controller = new \Core\Controller($cmd->controller, $this->container);

        if (! $this->controller->isAvialable()) {
            throw new \Exception("controller: `{$cmd->controller}` not found, in: " . __FILE__ . '(' . __LINE__. ')');
        }

        $this->guard = new \Core\Guard($cmd->controller, $this->container);

        if ($this->guard->load()) {
            $this->guard->run();
        }

        if ($this->controller->load()) {
            if (! $this->controller->methodExists($cmd->method)) {
                throw new \Exception("method: `{$cmd->method}` not found in controller: `{$cmd->controller}` in: " . __FILE__ . '(' . __LINE__. ')');
            }

            $this->controller->callMethod($this->method, $this->methodParams);
        }
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
} // EOF App.php