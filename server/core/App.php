<?php
/**
 * @file    : server/core/App.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 * @todo    : add App and AppCommand in same folder app
 * Core\App()
 * Core\App\Command()
 * Possible ??
 */

namespace Core;

class App
{
    /**
     * The container of DI
     *
     * @var \DI\Container
     */
    private $container;
    
    /**
     * The controller instance
     *
     * @var \Core\Controller
     */
    private $controller;

    /**
     * The guard
     *
     * @var \Core\Guard
     */
    private $guard;

    /**
     * The AppCommand
     *
     * @var \Core\AppCommand
     */
    private $cmd;

    private $logger;

    private $config;
    
    private static $output = "null";
    private static $ip = null;  

    /**
     * Initialize App
     *
     * @param string $cmd
     * @param string $ip
     * @throws Exception
     */
    public function __construct($cmd, $ip)
    {
        $containerBuilder = new \DI\ContainerBuilder();
        $containerBuilder->useAutowiring(true);

        # Init logger
        $this->logger = new Logger(App::class);

        # Too lazy to sudy how PHP-DI working, and looks like this trick works for older php versions.
        # so this the way i set some of the globals for now.

        $containerBuilder->addDefinitions([
            'Core\Logger' => function ($name) {
                return $this->logger;
            },        
          
        ]);

        # Build php-di container.
        $this->container = $containerBuilder->build();

        # Load Base Config
        $this->config = Config::get();

        # Parase command
        $this->cmd = new AppCommand($cmd);
        
        # print before and after parse
        $this->logger->debug("cmd: `$cmd`");
        $this->logger->debug($this->cmd);

        # Create Controller
        $this->controller = new \Core\Controller($this->cmd->getName(), $this->container);

        # Check the IP
        self::$ip = new Ip($ip);

        # Check if the controller is avialable
        if (! $this->controller->isAvialable()) {
            throw new \Exception("controller: `{$this->cmd->getName()}` not found, in: " . __FILE__ . '(' . __LINE__. ')');
        }

        # Create guard
        $this->guard = new \Core\Guard($this->cmd->getName(), $this->container);

        # Attempt to load guard
        if ($this->guard->load()) {
            $this->guard->run();
        }

        # Attempt to load controller
        if ($this->controller->load()) {
            if (! $this->controller->methodExists($this->cmd->getMethod())) {
                throw new \Exception("method: `{$this->cmd->getMethod()}` not found in controller: `{$this->cmd->getMethod()}` in: " . __FILE__ . '(' . __LINE__. ')');
            }

            # Call to controller method
            self::$output = $this->controller->callMethod($this->cmd->getMethod(), $this->cmd->getParams());
        }
    }

    public static function getOutput()
    {
        return self::$output;
    }

    public static function getIp()
    {
        return self::$ip;
    }

} // EOF App.php