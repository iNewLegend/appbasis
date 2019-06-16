<?php
/**
 * @file: services/database.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Services\Database;

class Pool
{
    /**
     * Array Of Database Modules for pool
     *
     * @var \Modules\Database[]
     */
    private $databases = [];

    /**
     * Self Instance
     *
     * @var \Services\Database|null
     */
    private static $instance = null;

    /**
     * Function get() : Get self Service or Database Module 
     *
     * @return \Modules\Database|null
     */
    public static function get()
    {
        // # CRITICAL;

        if (empty(self::$instance)) {
            self::$instance = new Pool(new \Modules\Logger(self::class), true);

            return self::$instance;
        }

        return self::$instance->getOne();
    }

    /**
     * Function __construct : Construct Database Service
     * 
     * @param \Modules\Logger   $logger   
     * @param bool              $autoLoad 
     * @param mixed             $config   
     */
    public function __construct(\Modules\Logger $logger, $autoLoad = false, $config = null)
    {
        if (empty($config)) {
            $config = \Services\Config::get('database');
        }


        $this->logger = $logger;
        $this->config = $config;

        if ($autoLoad) {
            $this->initialize();
        }

        self::$instance = $this;
    }

    /**
     * Function initialize() : Initialize Database Service
     * 
     * @return void
     */
    private function initialize()
    {
        $this->logger->debug("Loaded");

        $this->config->protect('some_secret_key');

        $this->config = $this->config->getAll('some_secret_key');

        $uri = vsprintf('%s:%s@%s:3306/%s', [
            $this->config['username'],
            $this->config['password'],
            $this->config['host'],
            $this->config['name']

        ]);

        if ($this->test($uri)) {
            // # TODO: add cross platform
            preg_match_all('/^processor/m', file_get_contents('/proc/cpuinfo'), $cpuCount);

            $cpuCount = count($cpuCount[0]);
            $connectionsCount = $cpuCount * 2;

            // database threads as much as cpu(s) * 2
            for ($i = 0; $i < $connectionsCount; ++$i) {
                $this->databases[$i] = new \Modules\Database($uri, new \Modules\Logger(self::class . "_{$i}", \Services\Config::get('logger')->module_database), \Core\Auxiliary::getLoop());

                $token = $this->logger->callBackSet("DBConnection_{$i}", "connect", uniqid());

                $this->databases[$i]->connect(function ($error) use ($i, $token) {
                    if ($error) {
                        $this->logger->callBackFire($token, $error->getMessage());

                        return;
                    }

                    $this->logger->callBackFire($token, 'connected');
                });

                $this->logger->callBackDeclare($token);
            }

            $this->logger->info("cpu cores: {$cpuCount} db-connections: {$connectionsCount}");
            $this->logger->info('appbasis memory: ' . \Library\Helper::humanReadableSize(\memory_get_usage(true)));
        } else {
            $this->logger->critical("test database connection failed");
        }
    }

    /**
     * Function test() : make a test connection to db.
     * 
     * @param string $uri
     * 
     * @return bool
     */
    public function test(string $uri)
    {
        $return = false;

        $this->logger->info("uri: `{$uri}`");

        $startTime = microtime(true);

        $testConnection = new \Modules\Database($uri, new \Modules\Logger(__METHOD__));
        $loop = $testConnection->getLoop();

        $timeout = 3.00;

        $loop->addTimer($timeout, function () use ($loop) {
            $loop->stop();
        });

        // prepare callback logger
        $token = $this->logger->callBackSet("testConnection", "connect", uniqid());

        $testConnection->connect(function ($error = null) use ($loop, $token, &$return) {
            if ($error) {
                $this->logger->callBackFire($token, $error->getMessage());

                return;
            }

            $return = true;

            $this->logger->callBackFire($token, 'connected');


            $loop->stop();
        });

        $this->logger->callBackDeclare($token);

        // start the connection
        $testConnection->getLoop()->run();

        $debug = $return ? "true" : "false";

        $timeSpent = \Library\Helper::humanReadableTimeLeft($startTime);

        if (floatval($timeSpent) >= $timeout) {
            $this->logger->info("test connection reached timeout");
        }

        $this->logger->debug("is-connected: `{$debug}` time: `{$timeSpent}ms`");

        // review later
        unset($testConnection);

        return $return;
    }

    /**
     * Function getOne() : Get Instance of Database Module
     * 
     * @todo this just lame example, find smart logic.
     * 
     * @return \Modules\Database|null
     */
    public function getOne()
    {
        $return = null;

        $databases = [];

        foreach ($this->databases as $database) {
            if ($database->isConnected()) {
                $databases[] = $database;
            }
        }

        $activeDBCount = count($databases) - 1;

        if ($activeDBCount <= 0) {
            $this->logger->warning("no active database connection(s)");

            $return = null;
        } else {
            $choose = rand(0, $activeDBCount);

            $return = $databases[$choose];
        }

        return $return;
    }
} // EOF services/database.php
