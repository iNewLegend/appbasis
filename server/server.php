<?php
/**
 * @file    : server.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 * @todo    :
 */

if (PHP_SAPI == 'cli') {
    require 'init.php';

    try {
        $server = new \Core\Server('127.0.0.1', 777);
    } catch (Exception $e) {
        exit($e->getMessage());
    }

    $server->binaryShare([
        'socket.io'                  => '../client/node_modules/socket.io-client/dist/socket.io.js',
        'socket.io/socket.io.js.map' => '../client/node_modules/socket.io-client/dist/socket.io.js.map',
        'favicon.ico'                => '', // ignore
    ]
    );

    $server->onData(function ($client, $data) {
        # TODO: Create online chat
    });

    $server->onHttp(function (React\Socket\ConnectionInterface $client, React\Http\ServerRequest $request, $data) use ($server) {
        $params = '';

        # TODO: implant security 
        
        if ($request->getMethod() === 'OPTIONS') {
            $client->write(new Symfony\Component\HttpFoundation\Response(
                '',
                \Symfony\Component\HttpFoundation\Response::HTTP_OK,
                [
                    'Access-Control-Allow-Origin'  => 'http://localhost:8080',
                    'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, Hash',
                ]
            ));

            $client->end();

            return;
        } 
        
        if ($request->getMethod() === 'POST') {
            $json = strstr($data, '{');
            $json = json_decode($json);

            if ($json) {
                foreach ($json as $param) {
                    if (!is_string($param)) {
                        continue;
                    }

                    $params .= $param . '/';
                }
            }
        }

        $cmd = $request->getUri()->getPath() . '/' . $params;

        try {
            $app    = new \Core\App($cmd, parse_url($client->getRemoteAddress(), PHP_URL_HOST));
            $output = $app->getOutput();
        } catch (\Exception $e) {
            $output = $e->getMessage();
            $temp   = var_export($output, true);
            $server->logFromExternal("Server Error: `$temp`");
        }

        $status = \Symfony\Component\HttpFoundation\Response::HTTP_OK;

        if (is_string($output)) {
            $contentType = 'text/plain';
        } else if (is_array($output)) {
            $output      = json_encode($output);
            $contentType = 'application/json';
        } else {
            $temp = var_export($output, true);
            $server->logFromExternal("Server output unknown datatype: `$temp`");
            $status = \Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        $client->write(new Symfony\Component\HttpFoundation\Response(
            $output,
            $status,
            [
                'Content-Type'                 => $contentType,
                'Access-Control-Allow-Origin'  => 'http://localhost:8080',
                'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, Hash',
            ]
        ));

        $client->end();

    });

    exit($server->run());
}
?>

Hey baby.
