<?php
/**
 * @file: core/object.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Core;

class OObject
{
    /**
     * This is the place where all the objects stored.
     *
     * @var array
     */
    public $data = [];

    /**
     * Instance of Logger Module
     *
     * @var \Modules\Logger
     */
    private $logger;

    /**
     * Function __construct: Create Object
     */
    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Function __destruct : Destruct Object
     *
     * @return void
     */
    public function __destruct()
    {
        if (!$this->logger) {
            return;
        }

        $this->logger->debug("destroying `" . self::class . '`');
    }

    /**
     * Function initialize() : Initialize
     *
     * @return void
     */
    public function initialize()
    {
        if (\Services\Config::get('logger')->core_object) {
            $this->logger = new \Modules\Logger(self::class);
        }
    }

    /**
     * Function set() : Set object name
     *
     * @param string $name
     * @param mixed $object
     *
     * @return void
     */
    public function set(string $name, $object)
    {
        $this->data[$name] = $object;

        // #notice: exit if no logger
        if (!$this->logger) {
            return;
        }

        if (is_object($object)) {
            $object = get_class($object);
        }

        $object = var_export($object, true);

        $this->logger->debug("name: `{$name}` object: `{$object}`");
    }

    /**
     * Function get() : Get object by $name
     *
     * @param string $name
     *
     * @return mixed
     */
    public function get(string $name)
    {
        if (!isset($this->data[$name])) {
            if ($this->logger) {
                $this->logger->warn("requested object: `$name` is not exist");
            }

            return null;
        }

        $return = $this->data[$name];

        if (!$this->logger) {
            return $return;
        }

        if (is_object($return)) {
            $return = get_class($return);
        } elseif (is_array($return)) {
            $return = json_encode($return);
        }

        $return = var_export($return, true);

        $this->logger->debug("name: `{$name}` return: `{$return}`");

        return $this->data[$name];

    }
} // EOF core/object.php
