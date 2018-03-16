<?php
/**
 * @file    : core/App.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Core;

class App
{
    /**
     * Container of DI instance
     *
     * @var \DI\Container
     */
    private $container;

    /**
     * Controller instance
     *
     * @var \Core\Controller
     */
    private $controller;

    /**
     * The guard instance
     *
     * @var \Core\Guard
     */
    private $guard;

    /**
     * The AppCommand instance
     *
     * @var \Core\AppCommand
     */
    private $cmd;

    /**
     * The Logger instance
     *
     * @var \Core\Logger
     */
    private $logger;

    /**
     * The Config instance
     *
     * @var \Core\Config
     */
    private $config;

    /**
     * App output
     *
     * @var string
     */
    private $output = "null";

    /**
     * Current client ip
     *
     * @var string
     */
    private $ip = null;

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

        /*
         * no time to study how PHP-DI working, and looks like this trick works for older php versions.
         * so this the way i set some of the globals for now.
         */
        $containerBuilder->addDefinitions([
            'Core\App'    => function () {
                return $this;
            },
            'Core\Logger' => function ($name) {
                return $this->logger;
            },
        ]);

        # build php-di container.
        $this->container = $containerBuilder->build();

        # load Base Config
        $this->config = Config::get();

        # parse command
        $this->cmd = new AppCommand($cmd);

        # print before and after parse
        $this->logger->debug("cmd: `$cmd`");
        $this->logger->debug($this->cmd);

        # create controller
        $this->controller = new \Core\Controller($this->cmd->getName(), $this->container);

        # check the IP
        $this->ip = new Ip($ip);

        # check if the controller is available
        if (!$this->controller->isAvailable()) {
            throw new \Exception("controller: `{$this->cmd->getName()}` not found, in: " . __FILE__ . '(' . __LINE__ . ')');
        }

        # create guard
        $this->guard = new \Core\Guard($this->cmd->getName(), $this->container);

        # attempt to load guard
        if ($this->guard->load()) {
            $this->guard->run();
        }

        # attempt to load controller
        if ($this->controller->load()) {
            if (!$this->controller->methodExists($this->cmd->getMethod())) {
                throw new \Exception("method: `{$this->cmd->getMethod()}` not found in controller: `{$this->cmd->getMethod()}` in: " . __FILE__ . '(' . __LINE__ . ')');
            }

            # call to controller method
            $this->output = $this->controller->callMethod($this->cmd->getMethod(), $this->cmd->getParams());
        }
    }

    /**
     * Return app output
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Return user IP Address
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

} // EOF App.php
