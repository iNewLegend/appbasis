<?php
/**
 * @file: core/container.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * 
 * @propose: this file is container for modules or services class instances, called
 * on each new connection, when seeking for service or controller, it will use the container
 * to create new instance with resolved prototype
 */
namespace Core;

class Container
{
    /**
     * Instance of Logger Module
     *
     * @var \Modules\Logger
     */
    private $logger = null;

    /**
     * Where we place all instances
     *
     * @var array
     */
    private $container = [];

    /**
     * Function __construct() : Construct Container
     */
    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Function __destruct : Destruct
     *
     */
    public function __destruct()
    {
        $this->logger->debug("destroying `" . self::class . '`');
    }

    /**
     * Function initialize() : Initialize Container
     *
     * @return void
     */
    private function initialize()
    {
        $this->logger = new \Modules\Logger(self::class, \Services\Config::get('logger')->core_container);
    }

    /**
     * Function check() : Check if class exist
     *
     * @param string $className
     * 
     * @return bool
     */
    private function exist(string $className)
    {
        $return = true;

        if (!class_exists($className)) {
            $this->logger->error("class: `{$className}` does not exist");
            $return = false;
        }

        return $return;
    }

    /**
     * Function getDependencies() : Get prototype->constructor dependencies
     *
     * @param \ReflectionClass $refClass
     * 
     * @return \ReflectionClass[]
     */
    private function getDependencies(\ReflectionClass $refClass)
    {
        $dependency = [];

        $constructor = $refClass->getConstructor();

        if (!$constructor) {
            $this->logger->warn("class: `{$refClass->getName()}` have no constructor");
            return $dependency;
        }

        /** @var \ReflectionParameter[] $params */
        $params = $constructor->getParameters();

        foreach ($params as $key => $param) {
            // # critical
            $dependency[] = $param->getClass();
        }

        return $dependency;
    }

    /**
     * Function resolveDependencies() : Get any matched $dependencies in $this->container
     *
     * @param array $dependencies
     * 
     * @return array
     */
    private function resolveDependencies(array $dependencies)
    {
        /** @var \ReflectionParameter[] $dependencies */

        $resolved = [];

        foreach ($dependencies as $dependency) {
            $this->logger->debug("resolving dependency: `{$dependency->getName()}`");

            foreach ($this->container as $key => $resolveObject) {
                if ($key == $dependency->getName()) {
                    $resolved[] = $resolveObject;
                }
            }
        }

        return $resolved;
    }

    /**
     * Function createInstance() : Create new instance
     *
     * @throws \Exception
     * @param string $className
     * 
     * @return mixed
     */
    private function createInstance(string $className)
    {
        $refClass = new \ReflectionClass($className);

        $dependencies = $this->getDependencies($refClass);

        $resolved = [];

        if (!empty($dependencies)) {
            $resolved = $this->resolveDependencies($dependencies);
        }

        $resolvedCount = count($resolved);

        // get difference between dependencies and resolved objects.
        $diff = [];

        foreach ($resolved as $key => $resolve) {
            foreach ($dependencies as $dependency) {
                if ($dependency->getName() === get_class($resolve)) {
                    $diff[] = $dependency;
                    break;
                }
            }
        }

        /** @var \ReflectionClass[] $diff */
        $diff = array_diff($dependencies, $diff);

        $dependenciesCount = count($dependencies);

        if ($dependenciesCount != $resolvedCount) {
            if ($this->logger) {
                $this->logger->error("cannot resolve all parameters, dependencies: `{$dependenciesCount}` resolved: `$resolvedCount`");
            }

            // print difference
            $warnings = [];

            foreach ($diff as $key => $diffVal) {
                $warnings[] = "cannot resolve: `{$diffVal->getName()}`";

                if ($this->logger) {
                    $this->logger->warning(end($warnings));
                }

            }

            // # notice: check later
            throw new \Exception(json_encode($warnings));
        }

        if ($this->logger) {
            $this->logger->notice("attempting to create new class: `{$refClass->getName()}`");
        }

        // create the requested instance class with resolved arguments
        $instance = $refClass->newInstanceArgs($resolved);

        // save the session for later
        $this->set($refClass->getName(), $instance);

        if ($this->logger) {
            if (gettype($instance) === 'object') {
                $objName = get_class($instance);
                $this->logger->debug("return obj: `{$objName}`");
            } else {
                $instanceDebug = var_export($instance, true);
                $this->logger->debug("return: `{$instanceDebug}`");
            }
        }

        return $instance;
    }

    /**
     * Function get() : Get instance by class name
     * @todo Something with this function its not only get but it also create dissonance
     * 
     * @param string $className
     * 
     * @return mixed
     */
    public function get(string $className)
    {
        // # better: this->logger->backTree("requesting $className");
        $this->logger->notice("getting: `{$className}`");

        if (isset($this->container[$className])) {
            $this->logger->debug("exist: `{$className}`");

            $object = $this->container[$className];

            if ($this->logger) {
                if (gettype($object) === 'object') {
                    $objName = get_class($object);
                    $this->logger->debug("return obj: `{$objName}`");
                } else {
                    $objectDebug = var_export($object, true);
                    $this->logger->critical("return: `{$objectDebug}`");
                }
            }

            // # warning:
            return $object;
        }

        return $this->createInstance($className);
    }

    /**
     * Function getNamespacePrefix() : Return all objects with specific prefix from the container
     * 
     * @param  string $namespace
     * 
     * @return mixed
     */
    public function getNamespacePrefix(string $namespace)
    {
        $this->logger->debug("namespace: `{$namespace}`");

        $return = [];

        $namespace .= '\\';

        foreach($this->container as $instance) {
            if (strstr(get_class($instance), $namespace)) {
                $return [] = $instance;
            }
        }
        
        return $return;
    }

    /**
     * Function set() : Set definition into container
     *
     * @param string $name
     * @param mixed $value
     * 
     * @return void
     */
    public function set(String $name, $value)
    {
        $this->container[$name] = $value;

        if ($this->logger) {
            // $this->logger->backTree()
            $this->logger->debug("set name: `{$name}` into container");
        }
    }

    /**
     * Function merge() : Merge container definitions
     *
     * @param array $definitions
     * 
     * @return void
     */
    public function merge(array $definitions)
    {
        if ($this->logger) {
            $this->logger->debugJson([
                'definitions' => $definitions,
                'container'   => $this->container,
            ], "definitions <> container");

            $this->container = array_merge($definitions, $this->container);
        }
    }
} // EOF core/container.php
