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

class Controller extends \Modules\Loader
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
     * Function __construct() : Construct Controller
     * Tells to parent to load it.
     * 
     * @param string          $name      
     * @param \Core\Container $container 
     * @param bool|boolean    $autoLoad  
     */
    public function __construct(string $name, \Core\Container $container, bool $autoLoad = false)
    {
        $this->logger = new \Modules\Logger(self::class, \Services\Config::get('logger')->core_controller);

        parent::__construct(
            ucfirst($name),
            self::PATH . $name . '.php',
            self::SPACE . ucfirst($name),
            $container,
            $autoLoad
        );
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
     * Function getGuards() : Get guards that used by the controller.
     * 
     * @return array|boolean
     */
    public function getGuards()
    {
        $return = false;

        try {
            $controllerRef = new \ReflectionClass('\\' . $this->fullName);

            $controllerConstructor = $controllerRef->getConstructor();

            $constructorParameters = $controllerConstructor->getParameters();

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

        return $return;
    }


    /**
     * Function methodExists() : Check if the method exist in the controller. & and is callable
     *
     * @param string $method
     * 
     * @return boolean
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
} // EOF core/controller.php
