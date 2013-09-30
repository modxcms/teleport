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
 * Defines the API contract for a Teleport Request handler.
 *
 * @package Teleport\Request
 */
interface RequestInterface
{
    /**
     * Add a result message to the request results.
     *
     * @param string $msg The message to add to the results.
     *
     * @return void
     */
    public function addResult($msg);

    /**
     * Get one or more arguments from the request.
     *
     * An empty array should return all arguments.
     *
     * @param array|string $key An argument key or array of keys.
     *
     * @return array|mixed|null An argument or array of arguments from the request.
     */
    public function args($key = array());

    /**
     * Get an array of results from this request.
     *
     * @return array An array of result messages.
     */
    public function getResults();

    /**
     * Handle the requested action.
     *
     * @param array $arguments Arguments for an action request.
     *
     * @return void
     * @throws RequestException If an error occurs during processing of the action,
     * or an unknown action is requested.
     */
    public function handle(array $arguments);

    /**
     * Log a result message and echo it if verbose is true.
     *
     * @param string $msg The result message.
     * @param bool $timestamp Indicates if the log entry should include a timestamp.
     *
     * @return void
     */
    public function log($msg, $timestamp = true);
}
