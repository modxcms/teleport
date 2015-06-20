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
 * Provides a CLI request handler for Teleport.
 *
 * @package Teleport\Request
 */
class CLIRequest extends Request
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
            if (isset($parsed['version']) ||  isset($parsed['V'])) {
                $this->action = 'Version';
                $this->arguments = $parsed;
                return $this->arguments;
            }
            if (isset($parsed['help']) || isset($parsed['h'])) {
                $this->action = 'Help';
                $this->arguments = $parsed;
                return $this->arguments;
            }
            throw new RequestException($this, "No valid action argument specified.");
        }
        $this->action = $parsed['action'];
        unset($parsed['action']);
        $this->arguments = $parsed;
        return $this->arguments;
    }

    public function beforeHandle(Action &$action)
    {
        if (!$this->switchUser()) {
            throw new RequestException($this, 'error switching user for teleport execution');
        }
    }

    /**
     * Switch the user executing the current process.
     *
     * If user arg is provided and the current user does not match, attempt to
     * switch to this user via posix_ functions.
     *
     * @return bool True if the user and group were successfully switched, the
     * process is already running as the requested user, or no user arg was
     * provided.
     */
    private function switchUser()
    {
        if (isset($this->user) && function_exists('posix_getpwnam')) {
            $current_user = @posix_getpwuid(@posix_getuid());
            if (!is_array($current_user)) {
                $this->log("user switch to {$this->user} failed: could not determine current username");
                return false;
            }
            if ($current_user['name'] !== $this->user) {
                $u = @posix_getpwnam($this->user);
                if (!is_array($u)) {
                    $this->log("user switch failed: could not find user {$this->user}");
                    return false;
                }
                if (!@posix_setuid($u['uid'])) {
                    $this->log("user switch failed: could not switch to {$this->user} using uid {$u['uid']}");
                    return false;
                }
                if (!@posix_setgid($u['gid'])) {
                    $this->log("warning: error switching group for {$this->user} to gid {$u['gid']}");
                }
                $current_user = @posix_getpwuid(@posix_getuid());
                if (is_array($current_user) && $current_user['name'] === $this->user) {
                    $this->log("user switch successful: teleport running as {$this->user}");
                }
            } else {
                $this->log("teleport already running as user {$this->user}...");
            }
        }
        return true;
    }
}
