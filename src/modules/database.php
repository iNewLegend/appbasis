<?php
/**
 * @file: modules/database.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Modules;

class Database
{
    /**
     * Instances Count\
     *
     * @var int
     */
    private static $instances = 0;

    /**
     * Loop Handler
     *
     * @var \React\EventLoop\StreamSelectLoop
     */
    private $loop;

    /**
     * Database Connection
     *
     * @var \React\MySQL\Io\Connection
     */
    private $connection;

    /**
     * Function __construct() : Construct Database Module
     *
     * @param array                                 $config
     * @param \Modules\Logger                       $logger
     * @param \React\EventLoop\StreamSelectLoop     $loop
     * @param bool                                  $autoLoad
     */
    public function __construct(array $config, \Modules\Logger $logger = null, \React\EventLoop\StreamSelectLoop $loop = null, $autoLoad = true)
    {
        if (!$loop) {
            $loop = \React\EventLoop\Factory::create();
        }

        if (!$logger) {
            $logger = new \Modules\Logger(self::class, \Services\Config::get('logger')->module_database);
        }

        $this->loop   = $loop;
        $this->logger = $logger;

        $this->connection = new \React\MySQL\Io\Connection($this->loop, $config);

        if ($autoLoad) {
            $this->initialize();
        }
    }

    /**
     * Function __destruct(): Destruct
     */
    public function __destruct()
    {
        --self::$instances;

        $this->logger->debug('destroying this instance, now count: `' . self::$instances . '`');

    }

    /**
     * Function initialize() : Initialize Database Module
     *
     * @return void
     */
    private function initialize()
    {
        $this->logger->debug("Module loaded");

        // pinging db
        $this->loop->addPeriodicTimer(60, function () {
            $this->idleRunProc();
        });

        ++self::$instances;
    }

    /**
     * Function idleRunProc() : This function is proc, it runs idlie each x time.
     * used as database connection checker. (rewrite-later)
     *
     * @return void
     */
    private function idleRunProc()
    {
        // handle connection timeout due inactive.

        $token = $this->logger->callBackSet(self::class, "ping", uniqid());
        $ping  = $this->connection->ping();

        $ping->then(function () use ($token) {
            $this->logger->callBackFire($token, $this->getConnectionState());
        }, function (\Exception $e) use ($token) {
            $this->logger->callBackFire($token, $e->getMessage());
        });

        $this->logger->callBackDeclare($token);
    }

    /**
     * Function Connect() : (doc-later)
     *
     * @param callable $callback
     *
     * @return void
     */
    public function connect(callable $callback = null)
    {
        $this->connection->doConnect($callback);
    }

    /**
     * Function close()
     *
     * @param callable $callback
     *
     * @return void
     */
    public function close(callable $callback = null)
    {
        $this->connection->close($callback);
    }

    /**
     * Function query() : Run Promise Query
     *
     * @param string $query
     *
     * @return mixed
     */
    public function query(string $query)
    {
        $deferred = new \React\Promise\Deferred();

        $token = null;

        if (\Services\Config::get('logger')->module_database == \Config\Module_Database_Logs::DEEP) {
            $token = $this->logger->callBackSet("query", $query, uniqid());
        }
        
        $this->connection->query($query)->then(
            function (\React\MySQL\QueryResult $command) use ($deferred, $token) {
                if ($token) {
                    $this->logger->debugJson($command->resultRows, 'result');
                    $this->logger->callBackFire($token, $command->resultRows, 'halt-on-null');
                }

                $deferred->resolve($command);
            },
            function (\Exception $error) use ($deferred) {
                $deferred->reject($error);
            }
        );

        if ($token) $this->logger->callBackDeclare($token);

        return $deferred->promise();
    }

    /**
     * Function queryAwait() : Run query and wait for data to be returned.
     *
     * @param string $query
     * 
     * @return mixed
     */
    public function queryAwait($query)
    {
        // # TODO: add debug level
        $this->logger->debug($query, ['depth' => 3]);

        $request = $this->query($query);

        // i have no time
        $response = \Clue\React\Block\await($request, $this->getLoop());

        return $response;
    }

    /**
     * Function fakeLongQuery() : Fakes a query that takes time to to proceed
     *
     * @return mixed
     */
    public function fakeLongQuery()
    {
        return $this->queryAwait("SELECT SLEEP(20);");
    }

    /**
     * Function getConnectionState() : Get Connection State
     *
     * @param bool $named
     *
     * @return mixed
     */
    public function getConnectionState($named = true)
    {
        if (!$named) {
            return $this->connection->getState();
        }

        $class     = new \ReflectionClass(\React\MySQL\ConnectionInterface::class);
        $constants = array_flip($class->getConstants());

        return $constants[$this->connection->getState()];
    }

    /**
     * Function isConnected() : Check whatever the module connect to database
     *
     * @return bool
     */
    public function isConnected()
    {
        $state = $this->connection->getState();

        return $state === \React\MySQL\ConnectionInterface::STATE_CONNECTED || $state === \React\MySQL\ConnectionInterface::STATE_AUTHENTICATED
        ? true : false;
    }

    /**
     * Function getLoop() : Get working loop
     *
     * @return \React\EventLoop\StreamSelectLoop
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     * Function getConnection() : Get the connection to database
     *
     * @return \React\MySQL\Io\Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }
} // EOF modules/database.php
