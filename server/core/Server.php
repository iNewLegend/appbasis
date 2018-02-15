<?php
/**
 * @file    : core/Server.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Core;

use React\Socket\ConnectionInterface;
use React\Http\ServerRequest;

use Symfony\Component\HttpFoundation\Response;

class Server
{
    protected $loop;
    protected $socket;
    protected $logger;
    protected $server;
    protected $app;

    protected $binaryShare = [];

    public $port;
    public $host;

    protected $on = [
        'data' => null,
        'http' => null,
    ];

    function __construct($host = '127.0.0.1', $port = '777')
    {
        $this->host = $host;
        $this->port = $port;

        # init.
        $this->logger = new \Core\Logger(self::class);
        $this->loop = \React\EventLoop\Factory::create();
        
        $this->socket = new \React\Socket\Server('tcp://' . $host . ':' . $port, $this->loop);
        
        $this->socket->on('connection', function (ConnectionInterface $client) {
            $this->_onConnect($client);
        });

        $this->socket->on('error', function (Exception $e) {
            $this->logger->error($e->getMessage());
        });
    }

    /**
     * binaryShare function - set the binary share array
     *
     * @param string $share
     * @return void
     */
    public function binaryShare($share)
    {
        $this->binaryShare = $share;
    }

    /**
     * logFromExternal function - used to access loger from owner
     *
     * @param string $message
     * @return void
     */
    public function logFromExternal($message)
    {
        $this->logger->info($message);
    }

    protected function _onConnect(ConnectionInterface $client)
    {
        $this->logger->info('' . $this->host . ':' .$this->port . ' - new connection from ' . $client->getRemoteAddress());

        $client->on('data', function ($data) use ($client) {
            
            $this->_onData($client, $data);
        });
    }

    protected function _onData($client, $data)
    {
        $httpRequest = null;

        try {
            $httpRequest = $this->parseRequest($data);
        } catch (\Exception $e) {
            # probaly not an http request.
            $this->logger->debug($e->getMessage());
        }
        
        if ($httpRequest) {
            return $this->_onHttp($client, $httpRequest, $data);
        }

        if ($this->on['data']) {
            return \call_user_func_array(this, $this->on['data'], [$client, $data]);
        }
    }

    protected function _onHttp(ConnectionInterface $client, ServerRequest $request, $data)
    {
        # if there is binary share
        if (! empty($this->binaryShare)) {
            $requestedPath = $request->getUri()->getPath();
            $requestedPath = trim($requestedPath, '/');

            foreach ($this->binaryShare as $key => $value) {
                if ($key == $requestedPath) {
                    if(file_exists($value)) {
                        $contentType = 'text/html';

                        switch(pathinfo($value, PATHINFO_EXTENSION)) {
                            case 'js':
                            case 'map':
                            $contentType = 'application/javascript';
                        }

                        $this->logger->debug($client->getRemoteAddress() .' - sharing `' . realpath($value) . '`');
                        
                        $response = new Response(file_get_contents($value), 200, ['content-type' => $contentType]);
                    } else {
                        $response = new Response('Share: `' . $value . '` not found', 404);
                    }

                    $client->write($response);
                    $client->end();
                    
                    return;
                }
            }
        }

        # if there is callback
        if ($this->on['http']) {
            return \call_user_func_array($this->on['http'], [$client, $request, $data]);
        }
    }

    public function onData(callable $funciton)
    {
        $this->on['data'] = $funciton;
    }

    public function onHttp(callable $function)
    {
        $this->on['http'] = $function;
    }

    public function run()
    {
        $this->logger->info('' . $this->host . ':' .$this->port . ' - Starting event LOOP.');
        $this->loop->run();
    }

    private function parseRequest($headers)
    {
        # NOTICE: Should i really import this function to the project? or it can be avoided.
        # FROM: react/http

        // additional, stricter safe-guard for request line
        // because request parser doesn't properly cope with invalid ones
        if (!preg_match('#^[^ ]+ [^ ]+ HTTP/\d\.\d#m', $headers)) {
            throw new \InvalidArgumentException('Unable to parse invalid request-line');
        }
        $lines = explode("\r\n", $headers);
        // parser does not support asterisk-form and authority-form
        // remember original target and temporarily replace and re-apply below
        $originalTarget = null;
        if (strpos($headers, 'OPTIONS * ') === 0) {
            $originalTarget = '*';
            $headers = 'OPTIONS / ' . substr($headers, 10);
        } elseif (strpos($headers, 'CONNECT ') === 0) {
            $parts = explode(' ', $headers, 3);
            $uri = parse_url('tcp://' . $parts[1]);
            // check this is a valid authority-form request-target (host:port)
            if (isset($uri['scheme'], $uri['host'], $uri['port']) && count($uri) === 3) {
                $originalTarget = $parts[1];
                $parts[1] = 'http://' . $parts[1] . '/';
                $headers = implode(' ', $parts);
            } else {
                throw new \InvalidArgumentException('CONNECT method MUST use authority-form request target');
            }
        }
            // parse request headers into obj implementing RequestInterface
            $request = \RingCentral\Psr7\parse_request($headers);
            // create new obj implementing ServerRequestInterface by preserving all
            // previous properties and restoring original request-target
            $serverParams = array(
            'REQUEST_TIME' => time(),
            'REQUEST_TIME_FLOAT' => microtime(true)
        );
        $target = $request->getRequestTarget();
      
        $request = new ServerRequest(
        $request->getMethod(),
        $request->getUri(),
        $request->getHeaders(),
        $request->getBody(),
        $request->getProtocolVersion(),
        $serverParams
        );
        $request = $request->withRequestTarget($target);
        // Add query params
        $queryString = $request->getUri()->getQuery();
        if ($queryString !== '') {
            $queryParams = array();
            parse_str($queryString, $queryParams);
            $request = $request->withQueryParams($queryParams);
        }
        $cookies = ServerRequest::parseCookie($request->getHeaderLine('Cookie'));
        if ($cookies !== false) {
            $request = $request->withCookieParams($cookies);
        }

        // re-apply actual request target from above
        if ($originalTarget !== null) {
            $request = $request->withUri(
            $request->getUri()->withPath(''),
            true
            )->withRequestTarget($originalTarget);
        }
        // only support HTTP/1.1 and HTTP/1.0 requests
        if ($request->getProtocolVersion() !== '1.1' && $request->getProtocolVersion() !== '1.0') {
            throw new \InvalidArgumentException('Received request with invalid protocol version', 505);
        }
        // ensure absolute-form request-target contains a valid URI
        if (strpos($request->getRequestTarget(), '://') !== false && substr($request->getRequestTarget(), 0, 1) !== '/') {
            $parts = parse_url($request->getRequestTarget());
            // make sure value contains valid host component (IP or hostname), but no fragment
            if (!isset($parts['scheme'], $parts['host']) || $parts['scheme'] !== 'http' || isset($parts['fragment'])) {
                throw new \InvalidArgumentException('Invalid absolute-form request-target');
            }
        }
        // Optional Host header value MUST be valid (host and optional port)
        if ($request->hasHeader('Host')) {
            $parts = parse_url('http://' . $request->getHeaderLine('Host'));
            // make sure value contains valid host component (IP or hostname)
            if (!$parts || !isset($parts['scheme'], $parts['host'])) {
                $parts = false;
            }
            // make sure value does not contain any other URI component
            unset($parts['scheme'], $parts['host'], $parts['port']);
            if ($parts === false || $parts) {
                throw new \InvalidArgumentException('Invalid Host header value');
            }
        }
        // always sanitize Host header because it contains critical routing information
        $request = $request->withUri($request->getUri()->withUserInfo('u')->withUserInfo(''));
        return $request;
    }
} // EOF Server.php