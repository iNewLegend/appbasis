<?php
/**
 * @file: core/guard.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Core;

interface GuardInterface
{
    public function run();
}

class Guard extends \Modules\Loader
{
    const PATH   = 'guards/';
    const SPACE  = 'Guards\\';
    const PREFIX = 'Guard';

    /**
     * Instance of Logger Module
     * @var \Modules\Logger
     */
    private $logger;

    /**
     * The handler of the object
     *
     * @var GuardInterface
     */
    protected $handler;

    /**
     * Function __construct() : Connstruct Guard loader
     * @param string          $name
     * @param \Core\Container $container
     */
    public function __construct(string $name, \Core\Container $container)
    {
        $this->initialize();

        parent::__construct(
            ucfirst($name),
            self::PATH . $name . '.php',
            self::SPACE . ucfirst($name) . SELF::PREFIX,
            $container,
            true
        );
    }

    /**
     * Function __destruct() : Destruct Guard
     *
     * @return void
     */
    public function __destruct()
    {
        $this->logger->debug("destroying `" . self::class . '`');
    }

    /**
     * Function initialize() : Initialize Guard loader
     *
     * @return void
     */
    private function initialize()
    {
        $this->logger = new \Modules\Logger(self::class, \Services\Config::get('logger')->core_guard);

    }

    /**
     * Function run() : Runs Guard
     *
     * @return void
     */
    public function run()
    {
        $return = false;

        if ($this->handler) {
            $debugHandler = get_class($this->handler);

            $this->logger->debug("attempt run handler: `{$debugHandler}`");

            $return = $this->handler->run();
        }

        $this->logger->debug("handler return: `{$return}`");

        return $return;
    }

    /**
     * Function get() : Get's actual guard instance.
     *
     * @return void
     */
    public function get()
    {
        return $this->getHandler();
    }
} // EOF core/guard.php
