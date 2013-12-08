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

class HttpServer extends Teleport {
    /**
     * Get a singleton instance of Teleport.
     *
     * @param array $options An associative array of Teleport Config options for the instance.
     * @param bool  $forceNew If true, a new instance of Teleport is created and replaces the existing singleton.
     *
     * @return HttpServer
     */
    public static function instance(array $options = array(), $forceNew = false)
    {
        if (self::$instance === null || $forceNew === true) {
            self::$instance = new HttpServer($options);
        } else {
            self::$instance->setConfig($options);
        }
        return self::$instance;
    }

    /**
     * Run the Teleport HTTP Server on the specified port.
     * 
     * @param int $port A valid port to run the Teleport HTTP Server on.
     *
     * @throws \RuntimeException If an invalid port is specified.
     */
    public function run($port)
    {
        $port = (integer)$port;
        if ($port < 1) {
            throw new \RuntimeException("Invalid port specified for Teleport HTTP Server", E_USER_ERROR);
        }

        /** @var \React\EventLoop\LibEventLoop $loop */
        $loop = \React\EventLoop\Factory::create();
        $socket = new \React\Socket\Server($loop);
        $http = new \React\Http\Server($socket);
        
        $server =& $this;

        $http->on('request', function ($request, $response) use ($server) {
            /** @var \React\Http\Request $request */
            /** @var \React\Http\Response $response */

            $arguments = $request->getQuery();
            $arguments['action'] = trim($request->getPath(), '/');

            if (strpos($arguments['action'], '.') === false) {
                $headers = array(
                    'Content-Type' => 'text/javascript'
                );
                try {
                    /** @var \Teleport\Request\Request $request */
                    $request = $server->getRequest('Teleport\\Request\\APIRequest');
                    $request->handle($arguments);
                    $results = $request->getResults();

                    $response->writeHead(200, $headers);
                    $response->end(json_encode(array('success' => true, 'message' => $results)));
                } catch (\Exception $e) {
                    $response->writeHead(500);
                    $response->end(json_encode(array('success' => false, 'message' => $e->getMessage())));
                }
            } else {
                if (is_readable(__DIR__ . '/../../html') && is_readable(__DIR__ . '/../../html/' . $arguments['action'])) {
                    $response->writeHead(200);
                    $response->end(file_get_contents(__DIR__ . '/../../html/' . $arguments['action']));
                } else {
                    $response->writeHead(404);
                    $response->end();
                }
            }
        });

        if ($this->getConfig()->get('verbose', null, false) || $this->getConfig()->get('debug', null, false)) {
            echo "teleport server initializing" . PHP_EOL;
        }
        $socket->listen($port);
        if ($this->getConfig()->get('verbose', null, false) || $this->getConfig()->get('debug', null, false)) {
            echo "teleport server listening on port {$port}" . PHP_EOL;
        }
        $loop->run();
    }
}
