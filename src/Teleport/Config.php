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


class Config
{
    /** @var array */
    private $config;

    public function __construct(array $config = array())
    {
        $this->config = $config;
    }

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

    public function set($key, $value)
    {
        $this->config[(string)$key] = $value;
    }

    public function find($search = null)
    {
        $results = null;
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

    public function merge(array $config = array())
    {
        $this->config = array_merge($this->config, $config);
    }
}
