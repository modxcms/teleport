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
 * Provides a CLI request handler for Teleport.
 *
 * @package Teleport\Request
 */
class CLIRequest extends AbstractRequest
{
    /**
     * Parse the CLI request arguments into a normalized format.
     *
     * @param array $args An array of CLI arguments to parse.
     *
     * @return array The normalized array of parsed arguments.
     * @throws RequestException If no valid action argument is specified.
     */
    public function parseArguments(array $args)
    {
        $this->action = null;
        $this->arguments = array();
        $parsed = array();
        $argument = reset($args);
        while ($argument) {
            if (strpos($argument, '=') > 0) {
                $arg = explode('=', $argument);
                $argKey = ltrim($arg[0], '-');
                $argValue = trim($arg[1], '"');
                $parsed[$argKey] = $argValue;
            } else {
                $parsed[ltrim($argument, '-')] = true;
            }
            $argument = next($args);
        }
        if (!isset($parsed['action']) || empty($parsed['action'])) {
            throw new RequestException($this, "No valid action argument specified.");
        }
        $this->action = $parsed['action'];
        unset($parsed['action']);
        return $parsed;
    }
}
