<?php
/*
 * This file is part of the teleport package.
 *
 * Copyright (c) Jason Coward <jason@opengeek.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport\Beam;


use React\Promise\Deferred;
use React\Promise\LazyPromise;
use Teleport\InvalidProfileException;
use Teleport\Teleport;

/**
 * An Endpoint listener that polls a MODX instance for Teleport messages.
 *
 * @package Teleport\Beam
 */
class Endpoint extends Teleport
{
    /**
     * Get a singleton instance of the Teleport Endpoint listener.
     *
     * @param array $options An associative array of Teleport Config options for the instance.
     * @param bool  $forceNew If true, a new instance of Teleport is created and replaces the existing singleton.
     *
     * @return HttpServer
     */
    public static function instance(array $options = array(), $forceNew = false)
    {
        if (self::$instance === null || $forceNew === true) {
            self::$instance = new Endpoint($options);
        } else {
            self::$instance->setConfig($options);
        }
        return self::$instance;
    }

    public static function createTaskFactory(Endpoint $endpoint, $id, $def, $register)
    {
        return function () use ($endpoint, $id, $def, $register) {
            $deferred = new Deferred();

            try {
                $request = $endpoint->getRequest('Teleport\\Request\\APIRequest');
                $request->handle($def);

                $deferred->resolve($request->getResults());
            } catch (\Exception $e) {
                $deferred->reject($e->getMessage());
            }

            return $deferred->promise();
        };
    }

    /**
     * Bind the Teleport Endpoint listener to the specified MODX profile.
     *
     * @param string $profile A valid MODX profile describing the instance to
     * bind the Endpoint listener to.
     * @param array $options An array of options for the Endpoint.
     *
     * @throws \RuntimeException If an error occurs binding the listener to the
     * specified MODX instance.
     */
    public function run($profile, array $options = array())
    {
        try {
            $profile = self::loadProfile($profile);
            $modx = $this->getMODX($profile, array_merge(array('log_target' => 'ECHO'), $options));

            /** @var \modRegistry $registry */
            $registry = $modx->getService('registry', 'registry.modRegistry');
            $registerName = $this->getConfig()->get('endpoint.registerName', $options, 'endpoint');
            $registerClass = $this->getConfig()->get('endpoint.registerClass', $options, 'registry.modFileRegister');
            $registerOptions = $this->getConfig()->get('endpoint.registerOptions', $options, array('directory' => $registerName));
            $register = $registry->getRegister($registerName, $registerClass, $registerOptions);
            $register->connect();
        } catch (InvalidProfileException $e) {
            throw new \RuntimeException("Could not start Endpoint listener: {$e->getMessage()}", $e->getCode(), $e);
        } catch (\Exception $e) {
            throw new \RuntimeException("Could not start Endpoint listener: {$e->getMessage()}", $e->getCode(), $e);
        }

        $pollInterval = (float)$this->getConfig()->get('endpoint.pollInterval', $options, 1);

        /** @var \React\EventLoop\LibEventLoop $loop */
        $loop = \React\EventLoop\Factory::create();

        $self = &$this;

        /* poll MODX registry for Teleportation requests and act on them */
        $loop->addPeriodicTimer($pollInterval, function () use ($self, $profile, $modx, $register) {
            $register->subscribe('/queue/');
            $msgs = $register->read(array(
                'msg_limit' => 1,
                'poll_limit' => 1,
                'remove_read' => true,
                'include_keys' => true
            ));
            $register->unsubscribe('/queue/');
            if (!empty($msgs)) {
                $taskMsg = reset($msgs);
                $taskId = key($msgs);
                if ($self->getConfig()->get('verbose') || $self->getConfig()->get('debug')) {
                    echo "msg received: {$taskId} => " . print_r($taskMsg, true) . "\n";
                }

                $register->subscribe("/tasks/{$taskId}/");

                $task = Endpoint::createTaskFactory($self, $taskId, $taskMsg, $register);

                $promise = new LazyPromise($task);
                $promise->then(
                    function ($value) use ($self, $register, $taskId) {
                        if ($self->getConfig()->get('verbose') || $self->getConfig()->get('debug')) {
                            echo "{$taskId} resolved [value => " . print_r($value, true) . "]\n";
                        }
                        $register->send("/tasks/{$taskId}/", array(array('completed' => $value)));
                    },
                    function ($reason) use ($self, $register, $taskId) {
                        if ($self->getConfig()->get('verbose') || $self->getConfig()->get('debug')) {
                            echo "{$taskId} rejected [reason => " . print_r($reason, true) . "]\n";
                        }
                        $register->send("/tasks/{$taskId}/", array(array('failed' => $reason)));
                    },
                    function ($update) use ($self, $register, $taskId) {
                        if ($self->getConfig()->get('verbose') || $self->getConfig()->get('debug')) {
                            echo "{$taskId} progress [update => " . print_r($update, true) . "]\n";
                        }
                        $register->send("/tasks/{$taskId}/", array(array('progress' => $update)));
                    }
                );

                $register->unsubscribe("/tasks/{$taskId}/");
            }
        });

        $loop->run();
    }
} 
