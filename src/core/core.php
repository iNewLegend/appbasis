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
     * @param \Modules\Logger $logger
     * @param \Modules\Ip $ip
     * @param string $name
     * @param array $services
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

        $this->logger = $logger;

        // Attach ip module to modules
        $modules[] = $ip;

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
        foreach ($this->getControllers() as $controller) {
            if (method_exists($controller, 'disconnect')) {
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

        $this->logger->debug("attempting register modules");

        // # register core modules
        $modules[] = $this->logger;

        // attach modules into container
        $this->registerModules($modules);

        $this->logger->debug("attempting register services");

        // # register services
        $this->services = $this->registerServices($services);

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
        $definitions = [];

        foreach ($services as $key => $service) {

            if (!is_array($service) /*&& ! is_object($service)*/ ) {
                if (is_object($service)) {
                    $key = get_class($service);
                }

                $this->logger->critical("service: `{$key}` is not array and not will be handled, skipping");
                continue;
            }

            $this->logger->debug("attempting to register service: `{$key}`");

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
        # notice: we dont attach core it self into container, currently bcoz it will cause a stuck in the release of core it self from server.
        /*$this->definitions = array_merge($this->definitions, ['Core\Core' => new class{
        // limited core access
        }]);*/

        // debug
        foreach ($this->definitions as $definition) {
            $class = get_class($definition);
            $this->logger->debug("definition: `{$class}`");
        }

        $this->container->merge($this->definitions);

        $this->logger->debug("container registered!");
    }

    /**
     * Function runGuard() : Loads new guard
     *
     * @throws \Exception
     *
     * @param string $name
     *
     * @return bool
     */
    private function runGuard(string $name)
    {
        $this->logger->debug("attempting to load: `{$name}` guard");

        // create guard
        $guard = new \Core\Guard($name, $this->container);

        // attempt to load & run guard
        if ($guard->isAvailable()) {
            if ($guard->load() && $guard->create() && $guard->run()) {
                $this->logger->debug("guard: `{$name}` run successfully");

                $guard = $guard->get();
            } else {
                $this->logger->error("guard: `{$name}` is available but cannot not loaded or run.");

                $guard = false;
            }

        } else {
            $this->logger->error("guard: `{$name}` is not available.");

            $guard = false;
        }

        return $guard;
    }

    /**
     * Function executeCommand() : Execute command
     *
     * @todo rewrite this function and reduce it size
     * @param mixed $cmd
     * @return mixed
     */
    public function executeCommand($cmd)
    {
        // print debug
        $debugCmd = strlen($cmd) > 50 ? substr($cmd, 0, 50) . "[ and more ... ]" : $cmd;
        $this->logger->debug($debugCmd);

        // parse command to object as needed
        if ($cmd instanceof \Modules\Command) {
            $this->cmd = $cmd;
        } elseif (gettype($cmd) == 'string') {
            $this->cmd = new \Modules\Command($cmd);
        }

        // #advise: each time you call a service or controller you should provide response call back.

        // get command name
        $name = $this->cmd->getName();
        $method = $this->cmd->getMethod();

        $this->logger->debug("checking if serivce: {$name}` exist in container");

        // do we have service with the same name as command?
        if ($this->checkService($name)) {
            $this->logger->debug("service: `{$name}` exist, trying to get service");

            $service = $this->getService($name);

            $this->logger->debug("searching for: `{$method}` method in service: `{$name}`");

            // this method exist in this service?
            if (\method_exists($service, $method)) {
                $this->logger->debug("calling service method: `{$name}::$method`");

                try {
                    return call_user_func_array([$service, $method], $this->cmd->getParameters());
                } catch (\Exception $e) {
                    $this->logger->error($e);
                }
            }

            $this->logger->debug("method: `{$method}` not found in service: `{$name}`");
        }

        // here we think that maybe it is controller
        $this->logger->debug("assuming command: `{$name}` is controller");

        // create controller
        $controller = new \Core\Controller($this->cmd->getName(), $this->container, false, false/* debug */ );

        // check if the controller is available
        if (!$controller->isAvailable()) {
            $e = "controller: `{$this->cmd->getName()}` not found";

            $this->logger->warning($e);

            throw new \Exception($e);
        }

        $this->logger->debug("controller: `{$this->cmd->getName()}` found");

        if (!$controller->load()) {
            $e = "controller: `{$this->cmd->getName()}` cannot be loaded";

            $this->logger->error($e);

            throw new \Exception($e);
        }

        $guards = $controller->getGuards();

        if (count($guards) > 0) {
            foreach ($guards as $guard) {
                //$this->logger->notice("attempt to run guard: `{$guard}`");
                $guardShortName = explode('\\', $guard)[1];
                $guardShortName = strtolower(str_replace('Guard', '', $guardShortName));

                $handler = $this->runGuard($guardShortName);

                if (!$handler) {
                    throw new \Exception("unable to run guard: `{$guard}`");
                }

                $this->container->set($guard, $handler);
            }
        }

        $controllerLoaded = false;

        // attempt to load controller
        try {
            if ($controllerLoaded = $controller->create()) {

                if (!$controller->methodExists($this->cmd->getMethod())) {
                    throw new \Exception("method: `{$this->cmd->getMethod()}` not found in controller: `{$this->cmd->getName()}` in: " . __FILE__ . '(' . __LINE__ . ')');
                }

                if ($this->cmd->isEmpty()) {
                    $this->logger->debug("call to controller `{$this->cmd->getName()}::{$this->cmd->getMethod()}`");
                } else {
                    $params_plain = var_export($this->cmd->getParams(), true);

                    $this->logger->debug("call to controller `{$this->cmd->getName()}::{$this->cmd->getMethod()}` with params:\n {$params_plain};");
                }

                // call to controller method
                return $controller->callMethod($this->cmd->getMethod(), $this->cmd->getParameters()) ? : '{}'; // empty json
            }
        } catch (\Exception $e) {
            if (false === $controllerLoaded) {
                $this->logger->error("fail to load controller: `{$this->cmd->getName()}`");
            }

            $this->logger->error($e);

            throw $e;
        }

        $this->logger->critical("system error on load controller: `{$this->cmd->getName()}`");
    }

    /**
     * Function checkService() : Check if service exist in core definitions
     *
     * @param string $name
     * @return bool
     */
    private function checkService(string $name)
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
     * Function getService() : Trying to get service from container.
     *
     * @param string $name
     * @return mixed
     */
    private function getService(string $name)
    {
        return $this->container->get("Services" . '\\' . $name);
    }

    /**
     * Function getObject() : Trying to get Core Object from Container
     *
     * @return \Core\OObject
     */
    public function getObject() : \Core\OObject
    {
        return $this->container->get("Core\OObject");
    }

    /**
     * Undocumented function
     *
     * @return mixed
     */
    public function getControllers()
    {
        return $this->container->getNamespacePrefix('Controllers');
    }
} // EOF core\core.php
