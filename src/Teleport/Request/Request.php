<?php
/**
 * This file is part of the teleport package.
 *
 * Copyright (c) MODX, LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport\Request;

use Teleport\Action\Action;

/**
 * Provides common features for all Teleport Request classes.
 *
 * @property string namespace
 * @property bool   verbose
 *
 * @package Teleport\Request
 */
class Request implements RequestInterface
{
    /** @var string */
    protected $action;
    /** @var array */
    protected $arguments = array();
    /** @var array */
    protected $results = array();

    /**
     * See if an argument or member variable isset for this request.
     *
     * @param string $name The name of the argument or member variable.
     *
     * @return bool TRUE if the argument or variable exists, FALSE otherwise.
     */
    public function __isset($name)
    {
        $isset = false;
        if (!empty($name)) {
            $isset = array_key_exists($name, $this->arguments);
        }
        return $isset;
    }

    /**
     * Get an argument or member variable from this request.
     *
     * @param string $name The name of the argument or member variable.
     *
     * @return mixed The value of the argument/variable, or null if not found.
     */
    public function __get($name)
    {
        $value = null;
        if (array_key_exists($name, $this->arguments)) {
            $value = $this->arguments[$name];
        }
        return $value;
    }

    /**
     * Set the value of an argument or member variable on this request.
     *
     * @param string $name The name of the argument or member variable.
     * @param mixed  $value The value to set for the argument or member variable.
     */
    public function __set($name, $value)
    {
        if (!empty($name)) {
            $this->arguments[$name] = $value;
        }
    }

    /**
     * Add a message to the request results.
     *
     * @param string $msg The message to add to the results.
     */
    public function addResult($msg)
    {
        $this->results[] = $msg;
    }

    /**
     * Get one or more arguments from the request.
     *
     * An empty array will return all arguments.
     *
     * @param array|string $key The argument key or an array of argument keys.
     *
     * @return array|mixed|null The value or an array of values for the requested
     * argument(s).
     */
    public function args($key = array())
    {
        if (is_array($key)) {
            $args = array();
            if (!empty($key)) {
                foreach ($key as $k) $args[$k] = $this->args($k);
            } else {
                $args = $this->arguments;
            }
            return $args;
        } elseif (is_string($key) && !empty($key)) {
            return $this->$key;
        } else {
            return null;
        }
    }

    /**
     * Get a reference to the results of this Request.
     *
     * @return array An array of results from this request.
     */
    public function &getResults()
    {
        return $this->results;
    }

    /**
     * Handle the requested action.
     *
     * @throws RequestException If an error occurs during processing of the action,
     * or an unknown action is requested.
     */
    public function handle(array $arguments)
    {
        $this->parseArguments($arguments);

        $actionClass = $this->getActionClass();
        if (class_exists($actionClass, true)) {
            try {
                /** @var \Teleport\Action\Action $handler */
                $handler = new $actionClass($this);
                $this->beforeHandle($handler);
                $handler->process();
                $this->afterHandle($handler);
            } catch (\Exception $e) {
                throw new RequestException($this, "Error handling {$this->action} Teleport request: " . $e->getMessage(), $e);
            }
        } else {
            throw new RequestException($this, "Unknown action {$this->action} specified in Teleport request.");
        }
    }

    /**
     * Request-specific logic executed before an action is handled.
     *
     * @param Action &$action The action to be processed.
     *
     * @throws RequestException If an error occurs which should stop processing of
     * the action.
     */
    public function beforeHandle(Action &$action) { }

    /**
     * Request-specific logic executed after an action is handled.
     *
     * @param Action &$action The action that was just processed.
     *
     * @throws RequestException If an error occurs.
     */
    public function afterHandle(Action &$action) { }

    /**
     * Handle an action through an APIRequest.
     *
     * @param array $arguments Arguments for an action request.
     *
     * @throws RequestException If an error occurs during processing of the action,
     * or an unknown action is requested.
     */
    public function request(array $arguments)
    {
        $request = new APIRequest();
        $request->handle($arguments);
    }

    /**
     * Log a result message and echo it if verbose is true.
     *
     * @param string $msg The result message.
     * @param bool   $timestamp Indicates if the log entry should include a timestamp.
     *
     * @return void
     */
    public function log($msg, $timestamp = true)
    {
        if ($timestamp) {
            $timestamp = strftime("%Y-%m-%d %H:%M:%S");
            $msg = "[{$timestamp}] {$msg}";
        }
        $this->addResult($msg);
        if ($this->verbose) echo $msg . PHP_EOL;
    }

    /**
     * Get the action requested.
     *
     * @return string The action requested.
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Parse the request arguments into a normalized format.
     *
     * @param array $args An array of arguments to parse.
     *
     * @return array The normalized array of parsed arguments.
     * @throws RequestException If no valid action argument is specified.
     */
    public function parseArguments(array $args)
    {
        if (!isset($args['action']) || empty($args['action'])) {
            throw new RequestException($this, "No valid action argument specified.");
        }
        $this->action = $args['action'];
        unset($args['action']);
        $this->arguments = $args;
        return $this->arguments;
    }

    /**
     * Get the Action class to handle.
     *
     * Uses Teleport\Action as the default namespace to look for the action
     * class in unless a namespace is specified in the arguments.
     *
     * @return string The fully-qualified class name of the Action.
     */
    private function getActionClass()
    {
        if (empty($this->namespace)) {
            $this->namespace = 'Teleport\\Action';
        }
        return $this->namespace . '\\' . str_replace('/', '\\', $this->action);
    }
}
