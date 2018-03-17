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
     * IP instance
     *
     * @var \Core\Ip
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
        $containerBuilder->useAnnotations(false);

        # Init logger
        $this->logger = new Logger(App::class);

        # check the IP
        $this->ip = new Ip($ip);

        # add definition(s) to container
        $containerBuilder->addDefinitions([
            'Core\App'      => function () {
                return $this;
            },
            'Core\Logger'   => function ($name) {
                return $this->logger;
            },
            'Core\Ip'       => function () {
                return $this->ip;
            },
            /* This is not good practice, can be handled in some ways, also check lazy injection */
            'Services\Auth' => function (\Models\Session $session, \Core\Ip $ip) {
                return new \Services\Auth($session, $this->ip);
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
     * Return IP instance
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

} // EOF App.php
