<?php
/**
 * @file: friends/reactphp/http.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Friends\React;

class Http
{
    /**
     * Instance of Handler Core
     *
     * @var \Core\Handler
     */
    private $handler;

    /**
     * Instance of Logger Module
     * 
     * @var \Modules\Logger
     */
    private $logger;

    /**
     * Server Engine
     *
     * @var \React\Http\Server
     */
    private $server;

    /**
     * Server port
     *
     * @var string
     */
    private $port;

    /**
     * Function __construct() : Initialize Friend Handler
     *
     * @param \Core\Handler                          $handler
     * @param string                                 $port
     * @param \React\EventLoop\StreamSelectLoop|null $loop
     *
     * @param bool|boolean                           $autoLoad
     */
    public function __construct(\Core\Handler $handler, string $port, \React\EventLoop\StreamSelectLoop $loop = null, bool $autoLoad = true)
    {
        $this->logger = new \Modules\Logger(self::class/*, \Services\Config::get('logger')->core_friends_react_http*/);

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
     * Function initialize() : Initialize Friend Handler
     *
     * @param  \React\EventLoop\StreamSelectLoop|null $loop
     *
     * @return void
     */
    public function initialize(\React\EventLoop\StreamSelectLoop $loop = null)
    {
        $debugLoop = var_export((bool) $loop, true);

        $this->logger->debug("port: `{$this->port}` loopPassed: `{$debugLoop}`");

        if (!$loop) {
            $loop = \React\EventLoop\Factory::create();
        }

        $this->server = new \React\Socket\Server('0.0.0.0:' . $this->port, $loop);

        $this->server->on('connection', function (\React\Socket\ConnectionInterface $client) {
            $this->_onConnect($client);
        });

        $this->server->on('error', function (\Exception $e) {
            $this->logger->error($e->getMessage());
        });

        // http implant
        $this->http = new \React\Http\Server(function (\React\Http\Io\ServerRequest $request) {
            return $this->_onHttp($request);
        });

        $this->http->on('error', function (\Exception $e) {
            $this->_onError($e);
        });

        $this->http->listen($this->server);

        $this->logger->notice("ready");
    }

    /**
     * Function _onConnect() : Called on client connection
     *
     * @param ConnectionInterface $client
     * @return void
     */
    private function _onConnect(\React\Socket\ConnectionInterface $client)
    {
        $ip = new \Modules\Ip($client->getRemoteAddress());

        $this->logger->info("new connection from: `{$ip}`");

        $client->on('data', function ($data) use ($client) {
            $this->_onData($client, $data);
        });

        $client->on('close', function () use ($client) {
            $this->_onDisconnect($client);
        });

        $client->on('error', function (\Exception $e) {
            $this->_onError($e);
        });
    }

    /**
     * Function: _onData() : Called when data received
     *
     * @param \React\Socket\ConnectionInterface $client
     * @param mixed $data
     *
     * @return void
     */
    private function _onData(\React\Socket\ConnectionInterface $client, $data)
    {
        $this->logger->info("new data from: `{$client->getRemoteAddress()}`");
        $this->logger->debugJson($data);
    }

    /**
     * Function: _onDisconnect() : Called when client connection is closing
     *
     * @param \React\Socket\ConnectionInterface $client
     *
     * @return void
     */
    private function _onDisconnect(\React\Socket\ConnectionInterface $client)
    {
        $this->logger->info("closing connection: `{$client->getRemoteAddress()}`");
    }

    /**
     * Function: _onError() : Called on error
     *
     * @param Exception $error
     *
     * @return void
     */
    private function _onError(\Exception $error)
    {
        //$this->logger->error($error);

        $this->handler->onError($error);
    }

    /**
     * Function: _onHttp() : Called on new http request
     *
     * @param \React\Socket\ConnectionInterface $request
     *
     * @return void
     */
    private function _onHttp(\React\Http\Io\ServerRequest $request)
    {
        $serverParams   = $request->getServerParams();
        $remoteFullAddr = $serverParams['REMOTE_ADDR'] . ':' . $serverParams['REMOTE_PORT'];

        $this->logger->info("address: `{$remoteFullAddr}` requesting: `" . $request->getUri()->getPath() . '`');

        $method = $request->getMethod();

        // options
        if ($method === 'OPTIONS') {
            return new \React\Http\Response(
                200, // HTTP_OK
                [
                    'Access-Control-Allow-Origin'  => '*',
                    'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, Hash',
                ],
                ''
            );
        }

        $params = '';
        // read params from post
        if ($method === 'POST' && $request->getParsedBody() == null) {
            $json = json_decode($request->getBody());

            if ($json) {
                foreach ($json as $param) {
                    if (!is_string($param)) {
                        continue;
                    }

                    $params .= $param . '/';
                }
            }
        }

        // Hash (dynamic later)
        // Also hash can be module like ip
        $hash = '';

        foreach ($request->getHeaders() as $name => &$values) {
            if ($name == 'hash') {

                $hash = $values[0];
            }

        }

        $cmd = $request->getUri()->getPath() . '/' . $params;

        // fake.
        $ip     = new \Modules\Ip('http://' . $remoteFullAddr);
        $object = new \Core\OObject();

        if (strlen($hash) == 40) {
            $object->set("hash", $hash);
        }

        $this->handler->onConnect($object, $ip);

        $output = $this->handler->onCommand($ip, new \Modules\Command($cmd), $hash);

        $this->handler->onDisconnect($ip);

        $status = 200; // HTTP_OK

        if (is_string($output)) {
            $contentType = 'text/plain';
        } else if (is_array($output)) {
            $output      = json_encode($output);
            $contentType = 'application/json';
        } else {
            $status      = 500; // HTTP_INTERNAL_SERVER_ERROR
            $contentType = 'text/plain';
        }

        // add debug level
        $this->logger->debugJson($output);

        return new \React\Http\Response(
            $status,
            [
                'Content-Type'                 => $contentType,
                'Access-Control-Allow-Origin'  => '*',
                'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, Hash',
            ],
            $output
        );
    }

} // EOF core/handler.php
