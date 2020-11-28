<?php

/**
 * @file: core/core.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @todo: remove parameter $ip from constructor because it should be at $modules
 */

namespace Core;

class Core
{
    /**
     * Instance of Logger Module
     *
     * @var \Modules\Logger
     */
    private $logger;

    /**
     * Container Instance
     *
     * @var \Core\Container
     */
    private $container;

    /**
     * Core definitions that will be passed to container
     *
     * @var array
     */
    private $definitions = [];

    /**
     * \Core\Core Instance(s) count
     *
     * @var int $count
     */
    private static $count;

    /**
     * Function __construct() : Create new core, increase core count
     *
     * @param \Modules\Logger   $logger
     * @param \Modules\Ip       $ip
     * @param string            $name
     * @param array             $services
     * @param array             $modules
     */
    public function __construct(\Modules\Logger $logger = null, \Modules\Ip $ip, string $name = '', array $services = [], array $modules = [])
    {
        // set name
        if (empty($name)) {
            $name = self::class;
        }

        $this->name = $name;

        // set logger
        if (empty($logger)) {
            $logger = new \Modules\Logger($name . '_' . self::$count);
        }

        // attach logger to modules
        $modules[] = $logger;

        // attach ip module to modules
        $modules[] = $ip;

        $this->logger = $logger;

        // init core
        $this->initialize($services, $modules);

        // increase core instances count
        ++self::$count;
    }

    /**
     * Function __destruct() : Decrease's core count
     *
     */
    public function __destruct()
    {
        $this->logger->debug("destroying `" . self::class . '`');

        // ack controllers about disconnection
        foreach ($this->container->getNamespacePrefix('Controllers') as $controller) {
            if ($controller instanceof \Interfaces\Controller\Disconnect) {
                $controller->disconnect();
            }
        }

        // decrease core instances count
        --self::$count;
    }

    /**
     * Function initialize() : Initialize core
     *
     * @param array $services
     * @param array $modules
     *
     * @return void
     */
    private function initialize(array $services = [], array $modules = [])
    {
        // create container with debug status
        $this->container = new \Core\Container();

        $this->logger->notice("attempting register core modules");

        // attach modules into container
        $this->registerModules($modules);

        // register services
        $this->registerServices($services);

        // register the container and core definitions.
        $this->register();
    }

    /**
     * Function registerModules() : Set core modules to $this->definitions
     *
     * @param array $modules
     *
     * @return void
     */
    public function registerModules(array $modules = [])
    {
        $this->logger->notice("attempting register core modules");

        $definitions = [];

        foreach ($modules as $key => $module) {
            if (empty($module)) {
                if (!empty($this->logger)) {
                    $this->logger->critical(sprintf('trying to load empty module: `%s` with key index: `%d`', $module, $key));
                }
                continue;
            }

            $name = get_class($module);

            // # TODO: debug level
            $this->logger->debug("attaching module: `$name`");

            $definitions[$name] = $module;
        }

        $this->definitions = array_merge($definitions, $this->definitions);
    }

    /**
     * Function registerServices() : set core services to $this->definitions
     *
     * @param array $services
     *
     * @return void
     */
    public function registerServices(array $services)
    {
        $this->logger->notice("attempting register core services");

        $definitions = [];

        foreach ($services as $key => $service) {

            if (!is_array($service)) {
                if (is_object($service)) {
                    $key = get_class($service);
                }

                $this->logger->critical("service: `{$key}` is not array and not will be handled, skipping");
                continue;
            }

            $this->logger->notice("attempting to register service: `{$key}`");

            // check if service have get function
            if (!is_callable([$key, 'get'])) {
                $this->logger->critical(new \Exception("{$key}::get() is not callable, skipping"));
                continue;
            }

            // call get function
            $address = call_user_func_array([$key, 'get'], [$service]);

            if ($address) {
                // # TODO: debug level
                $this->logger->debug("attaching service: `{$key}`");
            } else {
                $this->logger->error("failed to register service: `{$key}`");

                continue;
            }

            $definitions[$key] = $address;
        }

        $this->definitions = array_merge($definitions, $this->definitions);
    }

    /**
     * Function register() : set core $this->definitions into container
     *
     * @return void
     */
    public function register()
    {
        $this->logger->notice("attempting register core definitions");

        // # DEBUG
        foreach ($this->definitions as $definition) {
            $class = get_class($definition);
            $this->logger->debug("definition: `{$class}`");
        }

        $this->container->merge($this->definitions);

        $this->logger->info("container registered!");
    }

    /**
     * Function executeGuard() : Execute New guard
     *
     * @param string $name
     *
     * @return bool
     */
    private function executeGuard(string $name)
    {
        $return = false;

        $this->logger->notice("attempting to execute guard: `{$name}`");

        /** @var \Interfaces\Guard\Run $guard */
        $guard = $this->container->create("Guards\\{$name}");

        if ($guard instanceof \Interfaces\Guard\Run) {
            if ($guard->run()) {
                $return = true;

                $this->logger->info("guard: `{$name}` run successfully");
            }
        } else {
            $this->logger->error("guard: `{$name}` is not available.");
        }

        return $return;
    }

    /**
     * function getServiceExecute() : Get Execute able Service callback method
     *
     * @param \Modules\Command $cmd
     * 
     * @return callable|false
     */
    private function getServiceExecute(\Modules\Command $cmd)
    {
        $name = $cmd->getName();
        $method = $cmd->getMethod();

        $fullName = "Services" . '\\' . $name;

        $this->logger->notice("attempting to execute service: `{$name}`");

        if ($this->isServiceRegistered($fullName)) {
            $this->logger->warning("unable to execute service: `{$name}`, does not available");
            
            return false;
        }
        
        $service = $this->container->get($fullName);

        $this->logger->debug("searching for: `{$method}` method in service: `{$name}`");

        // this method exist in this service?
        if (\method_exists($service, $method)) {
            $this->logger->debug("calling service method: `{$name}::$method`");

            return function () use ($service, $method, $cmd) {
                call_user_func_array([$service, $method], $cmd->getArguments());
            };
        }

        $this->logger->debug("method: `{$method}` not found in service: `{$name}`");

        return false;
    }

    /**
     * function getControllerExecute() : Get Execute able Controller callback method
     *
     * @param \Modules\Command $cmd
     * 
     * @return callable|false
     */
    private function getControllerExecute(\Modules\Command $cmd)
    {
        $name = $cmd->getName();

        $this->logger->notice("attempting to execute controller: `{$name}`");

        $controller = new \Core\Controller($name, $this->container);

        if (!$controller->isAvailable()) {
            $this->logger->warning("unable to execute controller: `{$name}`, does not available");

            return false;
        }

        // get guards        
        $guards = $controller->getGuards();

        if (count($guards) > 0) {
            foreach ($guards as $guard) {
                $guardName = explode('\\', $guard)[1];
                $guardName = str_replace('Guard', '', $guardName);;
                if (!$this->executeGuard($guardName)) {
                    $error = "unable to execute guard: `{$guard}`";
                    $this->logger->error($error);

                    return false;
                }
            }
        }

        $method = $cmd->getMethod();
        $controllerLoaded = false;

        // attempt to load controller
        try {
            if ($controllerLoaded = $controller->create()) {
                if (!$controller->methodExists($method)) {
                    $error = "method: `{$method}` not found in controller: `{$name}` in: " . __FILE__ . '(' . __LINE__ . ')';

                    $this->logger->warning($error);
                    return false;
                }

                if ($cmd->isEmpty()) {
                    $this->logger->info("call to controller `{$name}::{$method}`");
                } else {
                    $params_plain = var_export($cmd->getParams(), true);

                    $this->logger->info("call to controller `{$name}::{$method}` with params:\n {$params_plain};");
                }

                // return callback
                return
                    function () use ($controller, $method, $cmd) {
                        return $controller->callMethod($method, $cmd->getArguments()) ?: '{}';
                    }; // empty json
            }
        } catch (\Exception $e) {
            if (false === $controllerLoaded) {
                $this->logger->error("fail to load controller: `{$method}`");
            }

            $this->logger->error($e);
        }

        return false;
    }

    /**
     * Function executeCommand() : Execute command
  
     * @param string|\Modules\Command $cmd
     * 
     * @return mixed
     */
    public function executeCommand($cmd)
    {
        // print debug
        $debugCmd = strlen($cmd) > 50 ? substr($cmd, 0, 50) . "[ and more ... ]" : $cmd;
        $this->logger->notice($debugCmd);

        // parse command to object as needed
        if ($cmd instanceof \Modules\Command) {
            $this->cmd = $cmd;
        } elseif (gettype($cmd) == 'string') {
            $this->cmd = new \Modules\Command($cmd);
        }

        $name = $this->cmd->getName();

        $this->logger->notice("attempting to execute command: `{$name}`");

        // do we have service with the same name as command?
        if ($result = $this->getServiceExecute($this->cmd)) {
            return $result();
        }

        // here we think that maybe it is controller
        $this->logger->debug("assuming command: `{$name}` is controller");

        if ($result = $this->getControllerExecute($this->cmd)) {
            return $result();
        }

        $this->logger->critical("faield to execute command: `{$cmd}`");
    }

    /**
     * Function isServiceRegistered() : Check if service exist in core definitions
     *
     * @param string $name
     * 
     * @return bool
     */
    private function isServiceRegistered(string $name)
    {
        $service = 'Services' . '\\' . $name;

        foreach ($this->definitions as $key => $definition) {
            if ($key == $service) {
                return true;
            }
        }

        return false;
    }

    /**
     * Function getObject() : Gget Core Object from Container
     *
     * @return \Core\OObject
     */
    public function getObject(): \Core\OObject
    {
        return $this->container->get("Core\OObject");
    }
} // EOF core\core.php
