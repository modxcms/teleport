<?php
/**
* This file is part of the teleport package.
*
* Copyright (c) MODX, LLC
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Teleport;

use React\EventLoop\Factory;
use React\Http\Request;
use React\Http\Response;
use Teleport\Request\APIRequest;

class Server {
    private $port;

    public function __construct($port)
    {
        $port = (integer)$port;
        if ($port < 1) {
            throw new \InvalidArgumentException('Invalid server port specified', E_USER_ERROR);
        }
        $this->port = $port;
    }

    public function run()
    {
        /** @var \React\EventLoop\LibEventLoop $loop */
        $loop = Factory::create();
        $socket = new \React\Socket\Server($loop);
        $http = new \React\Http\Server($socket);

        $http->on('request', function ($request, $response) {
            /** @var Request $request */
            /** @var Response $response */
            
            $arguments = $request->getQuery();
            $arguments['action'] = trim($request->getPath(), '/');

            $headers = array(
                'Content-Type' => 'text/javascript'
            );
            try {
                $teleportRequest = new APIRequest();
                $teleportRequest->handle($arguments);
                $results = $teleportRequest->getResults();
                
                $response->writeHead(200, $headers);
                $response->end(json_encode(array('success' => true, 'message' => $results)));
            } catch (\Exception $e) {
                $response->writeHead(500);
                $response->end(json_encode(array('success' => false, 'message' => $e->getMessage())));
            }
        });

        $socket->listen($this->port);
        $loop->run();
    }
}
