<?php
/**
 * This file is part of the teleport package.
 *
 * Copyright (c) Jason Coward <jason@opengeek.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport;

/**
 * A Teleport Config object.
 *
 * @package Teleport
 */
class Config
{
    /** @var array */
    private $config;

    /**
     * Construct a new instance of Config.
     *
     * @param array $config An associative array of Config options.
     */
    public function __construct(array $config = array())
    {
        $this->config = $config;
    }

    /**
     * Get a Config option value.
     *
     * @param string $key The option key to lookup a value for.
     * @param array|null $options An associative array of options to override the persistent config.
     * @param mixed $default A default value to return if the lookup fails.
     * @param callable|null $filter An optional callback to filter the value with.
     *
     * @return mixed The value of the option or the specified default.
     */
    public function get($key, $options = null, $default = null, $filter = null)
    {
        $value = $default;
        if (is_array($options) && array_key_exists($key, $options)) {
            $value = $options[$key];
        } elseif (array_key_exists($key, $this->config)) {
            $value = $this->config[$key];
        }
        if (is_callable($filter)) {
            $filter($value);
        }
        return $value;
    }

    /**
     * Set a Config option value.
     *
     * @param string $key The key identifying the option to set.
     * @param mixed $value The value to set for the option.
     */
    public function set($key, $value)
    {
        $this->config[(string)$key] = $value;
    }

    /**
     * Find Config options by searching the keys.
     *
     * Use null to return all Config options.
     *
     * @param string|null $search An optional string to search the option keys with, or null to return all options.
     *
     * @return array An array containing the options matching the search, or all options if null is passed.
     */
    public function find($search = null)
    {
        $results = array();
        if (empty($search)) {
            $results = $this->config;
        } elseif (is_scalar($search)) {
            $results = array_filter(
                $this->config,
                function($value, $key) use ($search) {
                    return strpos($key, $search) !== false;
                }
            );
        } elseif (is_array($search)) {
            foreach ($search as $key) {
                if (!is_array($results)) $results = array();
                $results[$key] = $this->get($key);
            }
        }
        return $results;
    }

    /**
     * Recursively merge an associative array of options into the existing options.
     *
     * @param array $config An associative array of Config options to merge with the existing options.
     */
    public function merge(array $config = array())
    {
        $this->config = array_merge_recursive($this->config, $config);
    }
}
