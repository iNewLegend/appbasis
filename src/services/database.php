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
     * @var \Services\Database
     */
    private static $instance = null;
    
    /**
     * Function get() : Get self Service or Database Module 
     *
     * @return \Modules\Database
     */
    public static function get()
    {
        // # CRITICAL;
        
        if(empty(self::$instance)) {
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
        if(empty($config)) {
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
        
        $config = [
            'dbname' => $this->config['name'],
            'user' => $this->config['username'],
            'passwd' => $this->config['password'],
            'host' => $this->config['host']
        ];

        if($this->test($config)) {
            // # TODO: add cross platform
            preg_match_all('/^processor/m', file_get_contents('/proc/cpuinfo'), $cpuCount);

            $cpuCount = count($cpuCount[0]);
            $connectionsCount = $cpuCount * 2;

            $this->logger->info("cores: {$cpuCount} db-connections: {$connectionsCount}");
            $this->logger->info('memory: ' . \Library\Helper::humanReadableSize(\memory_get_usage(true)));

            // database threads as much as cpu(s) * 2
            for($i = 0 ; $i < $connectionsCount ; ++$i) {
                $this->databases[$i] = new \Modules\Database($config, new \Modules\Logger(self::class . "_{$i}", \Services\Config::get('logger')->module_database), \Core\Auxiliary::getLoop());
                
                $token = $this->logger->callBackSet("DBConnection_{$i}", "connect", uniqid());

                $this->databases[$i]->connect(function($error) use ($i, $token) {
                    if($error) {
                        $this->logger->callBackFire($token, $error->getMessage());
                    } elseif ($this->databases[$i]->isConnected()) {
                        $this->logger->callBackFire($token, $this->databases[$i]->getConnectionState());
                    } else {
                        $this->logger->callBackFire($token, "Unknown error in line: `" . __LINE__ . "`");
                    }
                });

                $this->logger->callBackDeclare($token);
            }

            $this->logger->info('memory: ' . \Library\Helper::humanReadableSize(\memory_get_usage(true)));

        } else {
            $this->logger->critical("test database connection failed");
        }
    }

    /**
     * Function test() : make a test connection to db.
     * 
     * @param mixed $config
     * 
     * @return mixed
     */
    public function test($config)
    {
        $return = false;

        $this->logger->debugJson($config, 'config');

        $startTime = microtime(true);

        $testConnection = new \Modules\Database($config, new \Modules\Logger(__METHOD__));
        $loop = $testConnection->getLoop();

        $timeout = 3.00;

        $timer = $loop->addTimer($timeout, function() use($loop) {
            $loop->stop();    
        });
        
        // prepare callback logger
        $token = $this->logger->callBackSet("testConnection", "connect", uniqid());

        $testConnection->connect(function($error = null) use ($loop, $token, $testConnection) {
            if($error) {
                $this->logger->callBackFire($token, $error->getMessage());
            } else {
                $this->logger->callBackFire($token, $testConnection->getConnectionState());
            }

            $loop->stop();    
        });

        $this->logger->callBackDeclare($token);

        // start the connection
        $testConnection->getLoop()->run();
        
        $return = $testConnection->isConnected();
        $debug = $return ? "true" : "false";
        
        $timeSpent = \Library\Helper::humanReadableTimeLeft($startTime);
        
        if( floatval($timeSpent) >= $timeout) {
            $this->logger->info("test connection reached timeout");
        }

        $this->logger->debug("is-connected: `{$debug}` time: `{$timeSpent}ms`");

        // if connected
        if($return) {
            $this->logger->info($testConnection->getConnectionState());
        }

        // review later
        unset($testConnection);

        return $return;
    }

    /**
     * Function getOne() : Get Instance of Database Module
     * 
     * @todo this just lame example, find smart logic.
     * 
     * @return mixed
     */
    public function getOne()
    {
        $return = null;
        
        $databases = [];

        foreach($this->databases as $database) {
            if($database->isConnected()) {
                $databases [] = $database;
            }
        }

        $activeDBCount = count($databases) - 1;

        if($activeDBCount <= 0) {
            $this->logger->warning("no active database connection(s)");

            $return = null;
        } else {
            $choose = rand(0, $activeDBCount);

            $return = $databases[$choose];
        }
        
        return $return;
    }
} // EOF services/database.php
    