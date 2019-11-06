<?php
/**
* This file is part of the teleport package.
*
* Copyright (c) MODX, LLC
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Teleport\Beam;

use Exception;
use React\EventLoop\Factory;
use React\EventLoop\LibEventLoop;
use React\Http\Request;
use React\Http\Response;
use React\Http\Server as Server;
use React\Socket\Server as Socket;
use RuntimeException;
use Teleport\ConfigException;
use Teleport\Request\InvalidRequestException;
use Teleport\Teleport;

class HttpServer extends Teleport {
    /**
     * Get a singleton instance of Teleport.
     *
     * @param array $options An associative array of Teleport Config options for the instance.
     * @param bool $forceNew If true, a new instance of Teleport is created and replaces the existing singleton.
     *
     * @return HttpServer
     * @throws ConfigException
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
     * @throws RuntimeException If an invalid port is specified.
     */
    public function run($port)
    {
        $port = (integer)$port;
        if ($port < 1) {
            throw new RuntimeException("Invalid port specified for Teleport HTTP Server", E_USER_ERROR);
        }

        /** @var LibEventLoop $loop */
        $loop = Factory::create();
        $socket = new Socket($loop);
        $http = new Server($socket);

        $server =& $this;

        $http->on('request', function ($request, $response) use ($server) {
            /** @var Request $request */
            /** @var Response $response */

            $arguments = $request->getQuery();
            $arguments['action'] = trim($request->getPath(), '/');

            $headers = array(
                'Content-Type' => 'text/javascript'
            );
            if (isset($arguments['action']) && !empty($arguments['action']) && strpos($arguments['action'], '.') === false) {
                try {
                    /** @var \Teleport\Request\Request $request */
                    $request = $server->getRequest('Teleport\\Request\\APIRequest');
                    $request->handle($arguments);
                    $results = $request->getResults();

                    $response->writeHead(200, $headers);
                    $response->end(json_encode(array('success' => true, 'message' => $results)));
                } catch (InvalidRequestException $e) {
                    $response->writeHead(400, $headers);
                    $response->end(json_encode(array('success' => false, 'message' => $e->getMessage())));
                } catch (Exception $e) {
                    $response->writeHead(500, $headers);
                    $response->end(json_encode(array('success' => false, 'message' => $e->getMessage())));
                }
            } else {
                $response->writeHead(400, $headers);
                $response->end(json_encode(array('success' => false, 'message' => 'no valid action was specified')));
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
