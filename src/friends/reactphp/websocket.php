<?php
/**
 * @file: friends/reactphp/websocket.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @todo: OnCommand, im not sure its the right place
 */

namespace Friends\React;

class WebSocket
{
    /**
     * Instance Of Logger Module
     *
     * @var \Modules\Logger
     */
    private $logger;

    /**
     * Instance Of Handler Core
     *
     * @var \Core\Handler
     */
    private $handler;

    /**
     * Instance Of Ratchet Server Engine
     *
     * @var \Ratchet\App
     */
    private $server;

    /**
     * Server Port
     *
     * @var string
     */
    private $port;

    /**
     * Function __construct() : Construct Friends\React WebSocket
     *
     * @param \Core\Handler                          $handler
     * @param string                                 $port
     * @param \React\EventLoop\StreamSelectLoop|null $loop
     * @param bool|boolean                           $autoLoad
     */
    public function __construct(\Core\Handler $handler, string $port, \React\EventLoop\StreamSelectLoop $loop = null, bool $autoLoad = true)
    {
        $this->handler = $handler;
        $this->port    = $port;

        if ($autoLoad) {
            $this->initialize($loop);
        }

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
     * Function initialize() : Initalize WebSocket
     * @param  \React\EventLoop\StreamSelectLoop|null $loop
     *
     * @return void
     */
    public function initialize(\React\EventLoop\StreamSelectLoop $loop = null)
    {
        $this->logger = new \Modules\Logger(self::class/*, \Services\Config::get('logger')->core_friends_react_http*/);

        $debugLoop = var_export((bool) $loop, true);

        $this->logger->debug("port: `{$this->port}` loopPassed: `{$debugLoop}`");

        // implant websocket
        $socket = new \React\Socket\Server('0.0.0.0:' . $this->port, $loop);
        $server = new \Ratchet\Server\IoServer(
            new \Ratchet\Http\HttpServer(
                new \Ratchet\WebSocket\WsServer(
                    new \Friends\React\WebSocket\OnCommand($this->logger, $this->handler)
                )
            ),
            $socket,
            $loop
        );
    }

    /**
     * Function postTo() : Send post (AppBasis Protocol) to specific client.
     *
     * @param  \Modules\Ip $ip
     * @param  string      $method
     * @param  mixed      $data
     *
     * @return void
     */
    public static function postTo(\Modules\Ip $ip, string $method, $data)
    {
        $clients = \Friends\React\WebSocket\OnCommand::$clients;

        $data = array_merge([
            'type'   => 'post',
            'method' => $method,
            // ----
        ], $data);

        $json = json_encode($data);

        if ($json == false) {
            \Core\Auxiliary::getGlobalLogger()->error("json_encode() failed, data:");
            \Core\Auxiliary::getGlobalLogger()->debugJson($data, "data");
            return;    
        }

        foreach ($clients as $client) {
            if ($client['ip'] !== $ip) {
                continue;
            }

            \Core\Auxiliary::getGlobalLogger()->debugJson($json, "sending to ip: `{$ip}`");

            $client['conn']->send($json);

            // # CRITICAL;
            return;
        }
    }

    /**
     * Function postToAll() : Send post (AppBasis Protocol) to all client(s).
     *
     * @param  string      $method
     * @param  mixed      $data
     *
     * @return void
     */
    public static function postToAll(string $method, $data)
    {
        $clients = \Friends\React\WebSocket\OnCommand::$clients;

        $data = array_merge([
            'type'   => 'post',
            'method' => $method,
            // ----
        ], $data);

        $data = json_encode($data);

        foreach ($clients as $client) {
            \Core\Auxiliary::getGlobalLogger()->debugJson($data, "sending to ip: `{$client['ip']}`");

            $client['conn']->send($data);
        }
    }
}

namespace Friends\React\WebSocket;

class AppBasis_WebSocket_Protocol
{
    /**
     * Type of packet, (post, get)
     *
     * @var string
     */
    private $type;

    /**
     * Name Of the command (controller)
     *
     * @var string
     */
    private $name;

    /**
     * Method name (function in controller)
     *
     * @var string
     */
    private $method;

    /**
     * Function __construct() : Construct WebSocket Protocol Header?
     * 
     * @param mixed         $type
     * @param string|null   $name
     * @param string|null   $method
     */
    public function __construct($type, string $name = null, string $method = null)
    {
        // this is parse client prototype to AppBasis_WebSocket_Protocol
        if (is_object($type) && $name == null && $method == null) {
            $name   = $type->name;
            $method = $type->method;

            // # CRITICAL;
            $type = $type->type;
        }

        $this->type   = $type;
        $this->name   = $name;
        $this->method = $method;
    }

    public function getType() { return $this->type; }
    public function getName() { return $this->name; }
    public function getMethod() { return $this->method; }
}

class OnCommand implements \Ratchet\MessageComponentInterface
{
    /**
     * Instance Of Logger Module
     *
     * @var \Modules\Logger
     */
    private $logger;

    /**
     * Instance Of Handler Core
     *
     * @var \Core\Handler
     */
    private $handler;

    /**
     * Clients Storage
     *
     * @var array
     */
    public static $clients = [];

    /**
     * Function __construct() : Construct OnCommand
     * 
     * @param \Modules\Logger $logger
     * @param \Core\Handler   $handler
     */
    public function __construct(\Modules\Logger $logger, \Core\Handler $handler)
    {
        $this->logger  = $logger;
        $this->handler = $handler;

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
        $this->logger->debug('Module started');
    }

    /**
     * Function onOpen() : Called on new connection
     *
     * @param  \Ratchet\ConnectionInterface $conn
     *
     * @return void
     */
    public function onOpen(\Ratchet\ConnectionInterface $conn)
    {
        $ip = new \Modules\Ip('ws://' . $conn->remoteAddress);

        $this->logger->info("new connection from: `{$ip}`");

        self::$clients[$conn->resourceId] = [
            'ip'   => $ip,
            'conn' => $conn,
        ];

        $object = new \Core\OObject();

        $this->handler->onConnect($object, $ip);
    }

    /**
     * Function onMessage() : Called on new message
     *
     * @param  \Ratchet\ConnectionInterface $from
     * @param  mixed                        $data
     *
     * @return void
     */
    public function onMessage(\Ratchet\ConnectionInterface $from, $data)
    {
        // this is now set here like that to be easy dev manage able, later cover it.
        $hash = '_WEBSOCKET_INITIAL_HASH_DEFINE_';

        $ip = self::$clients[$from->resourceId]['ip'];

        $this->logger->info("new message from: `{$ip}`");

        $tryJson = json_decode($data);

        if (json_last_error() === 0) {
            // json data
            $data = $tryJson;

            $this->logger->debugJson($data, 'data');

            // part of AppBasis websocket protocol
            if (isset($data->type) && isset($data->name) && isset($data->method)) {
                $protocol = new AppBasis_WebSocket_Protocol($data);

                $params = [];

                if ($data->type == 'post') {
                    if (isset($data->params)) {
                        $params = $data->params;

                        // setting hash from socket
                        if (isset($data->params->hash)) {
                            $hash = $data->params->hash;

                            unset($data->params->hash);
                        }
                    } else {
                        $this->logger->warning("address: `{$ip}` sending post without data params");
                    }
                }
                $data = new \Modules\Command("{$data->name}/{$data->method}", (array) $params);
            } else {
                $this->logger->warn("unknown json protocol: `{$data}`");
            }
        }

        $output = $this->handler->onCommand($ip, $data, $hash);

        // part of AppBasis websocket protocol
        if (isset($protocol) && get_class($data) == "Modules\Command") {
            // # NOTICE: this is need to be checked this is code should not be.
            if (is_string($output)) {
                $temp = $output;

                $output = array();

                $output['code'] = 'msg';
                $output['msg'] = $temp;
            }

            /** @var \Modules\Command $data */
            $output['type']   = $protocol->getType();
            $output['name']   = $protocol->getName();
            $output['method'] = $protocol->getMethod();
        }

        // from object to json object
        if (is_string($output)) {
            $output = json_encode($output, true);
        } elseif (is_array($output)) {
            $output = json_encode($output, true);
        }

        $this->logger->debugJson($output);

        // send data
        $from->send($output);
    }

    /**
     * Function onClose() : Called on client connection closing
     * @param  \Ratchet\ConnectionInterface $conn
     *
     * @return void
     */
    public function onClose(\Ratchet\ConnectionInterface $conn)
    {
        $ip = self::$clients[$conn->resourceId]['ip'];

        $this->logger->info("closing connection: `{$ip}`");

        $this->handler->onDisconnect($ip);

        unset(self::$clients[$conn->resourceId]);
    }

    /**
     * Function onError() : Called on client have error
     *
     * @param  \Ratchet\ConnectionInterface $conn
     * @param  \Exception                   $e
     *
     * @return void
     */
    public function onError(\Ratchet\ConnectionInterface $conn, \Exception $e)
    {
        $this->handler->onError($e);
        $conn->close();
    }
}

// EOF friends/reactphp/websocket.php
