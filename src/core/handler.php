<?php
/**
 * @file: core/handler.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Core;

class Handler
{
    /**
     * Instance of Logger Module
     *
     * @var \Modules\Logger
     */
    private $logger;

    /**
     * Array which storing all the clients
     *
     * @var array
     */
    private $storage = [];

    /**
     * Function __construct() : Initialize Handler
     */
    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Function __destruct() : Destruct Handler
     *
     * @return void
     */
    public function __destruct()
    {
        $this->logger->debug("destroying `" . self::class . '`');
    }

    /**
     * Function initialize() : Initialize
     *
     * @return void
     */
    public function initialize()
    {
        $this->storage = new \SplObjectStorage();

        $this->logger = new \Modules\Logger(self::class, \Services\Config::get('logger')->core_handler);
    }

    /**
     * Function onConnect() : Called when new client connected.
     *
     * @param  \Core\OObject $object
     * @param  \Modules\Ip   $ip
     *
     * @return void
     */
    public function onConnect(\Core\OObject $object, \Modules\Ip $ip)
    {
        if ($this->logger) {
            $this->logger->info("new connection from: `{$ip}`");
        }

        // # Notice: this is temporal code, todo find a good way todo it
        // # we simple point to our core of our extra services that were loaded before
        // # so the core will load it.
        $services = array_merge([\Services\Auth::class => []], \Core\Auxiliary::$extServices);

        $core = new \Core\Core(null, $ip, 'Core', $services, [$object]);

        $this->storage[$ip] = $core;

    }

    /**
     * Function onCommand() : Called when new client send new command.
     *
     * @param  \Modules\Ip          $ip
     * @param  \Modules\Command     $command
     * @param  string               $hash
     *
     * @return string               The actual output of the command
     */
    public function onCommand(\Modules\Ip $ip, \Modules\Command $command, string $hash = '')
    {
        if ($this->logger) {
            $this->logger->debug("`{$ip}` command:`{$command}` hash:`{$hash}`");
        }

        /**
         * # Warning: `contains` is reduce performance
         */
        if ($this->storage->contains($ip)) {
            try {
                /** @var \Core\Core $core */
                $core = $this->storage[$ip];
                
                // part of AppBasis Protocol ?
                switch($command->getMethod())
                {
                    case 'auth':
                    {
                        $debugParams = var_export($command->getParameters(), true);
                        $this->logger->debug("auth cmd: params: `{$debugParams}`");

                        if (strlen($hash) == 40) {
                            $core->getObject()->set("hash", $hash);
                            
                            $output = ['code' => 'success'];
                        } else {
                            $output = ['code' => 'failed'];
                        }
                        
                    }
                    break;

                    case 'hook':
                    {
                        $cmd = $command->getName() . '/hook';
                        $debugParams = var_export($command->getParameters(), true);

                        $this->logger->debug("hooking cmd: `{$cmd}` params: `{$debugParams}`");

                        $command = new \Modules\Command($cmd, $command->getParameters());
                    }

                    default:
                        $output = $core->executeCommand($command);
                }
                
            } catch (\Exception $e) {
                $output = $e->getMessage();
            }
        } else {
            $output = "cannot start command ip: `{$ip}` not found in client list";
            if ($this->logger) {
                $this->logger->critical($output);
            }

        }

        return $output;
    }

    /**
     * Function onDisconnect() : Called when client disonnected.
     *
     * @param  \Modules\Ip $ip
     *
     * @return void
     */
    public function onDisconnect(\Modules\Ip $ip)
    {
        if ($this->logger) {
            $this->logger->info("ip: `{$ip}`");
        }

        unset($this->storage[$ip]);
    }

    /**
     * Function onError() : Called when error.
     *
     * @param  \Exception $error
     *
     * @return void
     */
    public function onError(\Exception $error)
    {
        if ($this->logger) {
            $this->logger->error($error);
        }

    }
} // EOF core/handler.php
