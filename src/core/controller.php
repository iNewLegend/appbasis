<?php
/**
 * @file: core/controller.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 *
 * @propose: this file is container for modules or services class instances, called
 * on each new connection, when seeking for service or controller, it will use the container
 * to create new instance with resolved prototype
 */

namespace Core;

class Controller
{
    const PATH = 'controllers/';
    const SPACE = 'Controllers\\';

    /**
     * Instance of Logger Module
     *
     * @var \Modules\Logger
     */
    private $logger = null;

    /**
     * Is the object available
     *
     * @var bool
     */
    private $available = false;

    /**
     * Controller Name
     *
     * @var string
     */
    private $name;

    /**
     * Controller Full Name
     *
     * @var string
     */
    private $fullName;

    /**
     * Controller Full Path
     *
     * @var string
     */
    private $path;

    /**
     * Container Instance
     *
     * @var \Core\Container
     */
    private $container;

    /**
     * The Controller Handler
     *
     * @var mixed
     */
    private $handler = null;

    /**
     * Function __construct() : Construct Controller
     * Tells to parent to load it.
     * 
     * @param string            $name      
     * @param \Core\Container   $container 
     * @param bool              $autoLoad  
     */
    public function __construct(string $name, \Core\Container $container)
    {
        $this->logger = new \Modules\Logger(self::class, \Services\Config::get('logger')->core_controller);

        $this->name = $name;
        $this->container = $container;

        $this->initialize();
    }

    /**
     * Function __destruct() : Destruct
     *
     */
    public function __destruct()
    {
        $this->logger->debug("destroying `" . self::class . '`');
    }

    /**
     * Function initialize() : Initialize Loader
     *
     * @param bool $autoLoad
     *
     * @return void
     */
    private function initialize()
    {
        $this->fullName = self::SPACE . ucfirst($this->name);

        $this->logger->info("attempting to check that class: `{$this->fullName}` exist in declared controllers");

        $found = false;

        if (in_array($this->fullName, \Core\Auxiliary::getControllers())) {
            $this->logger->info("controller: `{$this->name}` found");
            $found = true;
        } else if (in_array($this->fullName, \Core\Auxiliary::getExtControllers())) {
            $this->logger->info("controller: `{$this->name}` found in extension folder");
            $found = true;
        }

        if ($found) {
            $this->available = true;
            $this->path = (new \ReflectionClass($this->fullName))->getFileName();
        }    
    }
    
    /**
     * Function create() : Create the handler using container
     *
     * @return void
     */
    public function create()
    {
        $return = false;
        
        if ($this->isAvailable()) {

            $this->handler = $this->container->create($this->fullName);

            if ($this->handler) {
                $return = true;
            }
        }

        return $return;
    }

    /**
     * Function getGuards() : Get guards that used by the controller.
     * 
     * @return array|bool
     */
    public function getGuards()
    {
        $return = false;

        $this->logger->info("attempting get guard for controller: `{$this->fullName}`");
        
        if ($this->isAvailable()) {
            try {
                $controllerRef = new \ReflectionClass($this->fullName);
    
                $controllerConstructor = $controllerRef->getConstructor();
    
                $return = [];
    
                /** @var \ReflectionParameter $dependency */
                foreach ($controllerConstructor->getParameters() as $dependency) {
                    $dependencyFullName = $dependency->getClass()->getName();
    
                    if (strstr($dependencyFullName, 'Guards\\')) {
                        $return[] = $dependencyFullName;
                    }
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }

        return $return;
    }


    /**
     * Function methodExists() : Check if the method exist in the controller. & and is callable
     *
     * @param string $method
     * 
     * @return bool
     */
    public function methodExists(string $method)
    {
        $this->logger->debug("method: `{$method}`");

        if ($return = method_exists($this->handler, $method)) {
            $return = is_callable([$this->handler, $method]);
        }

        $this->logger->debug("return: `{$return}`");

        return $return;
    }

    /**
     * Function callMethod : Call a specific method
     *
     * @param string $method
     * @param array $params
     * 
     * @return mixed
     */
    public function callMethod(string $method, array $params = [])
    {
        $handlerDbg = get_class($this->handler);

        $this->logger->info("calling to handler: `{$handlerDbg}` method: `{$method}`");

        return call_user_func_array([$this->handler, $method], $params);
    }

    /**
     * Function isAvailable() : Checks if the handler available
     *
     * @return bool
     */
    public function isAvailable()
    {
        return $this->available;
    }

} // EOF core/controller.php
