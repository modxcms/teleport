<?php
/**
 * This file is part of the teleport package.
 *
 * Copyright (c) Jason Coward <jason@opengeek.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport\Request;

/**
 * Provides common features for all Teleport Request classes.
 *
 * @package Teleport\Request
 */
abstract class AbstractRequest implements RequestInterface
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
     * @param mixed $value The value to set for the argument or member variable.
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
     * Get the action requested.
     *
     * @return string The action requested.
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Get the results of this request.
     *
     * @return array An array of results from this request.
     */
    public function getResults()
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

        $actionClass = "\\Teleport\\Action\\" . str_replace('/', '\\', $this->action);
        if (class_exists($actionClass, true)) {
            try {
                /** @var \Teleport\Action\AbstractAction $handler */
                $handler = new $actionClass($this);
                $handler->process();
            } catch (\Exception $e) {
                throw new RequestException("Error handling {$this->action} Teleport request: " . $e->getMessage(), $this->results, $e);
            }
        } else {
            throw new RequestException("Unknown action {$this->action} specified in Teleport request.", $this->results);
        }
    }

    /**
     * Log a result message and echo it if verbose is true.
     *
     * @param string $msg The result message.
     * @param bool $timestamp Indicates if the log entry should include a timestamp.
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
        if ($this->verbose) echo $msg;
    }

    /**
     * Parse the request arguments into a normalized format.
     *
     * @param array $args An array of arguments to parse.
     *
     * @return array The normalized array of parsed arguments.
     * @throws RequestException If no valid action argument is specified.
     */
    abstract public function parseArguments(array $args);
}
