<?php
/**
 * This file is part of the teleport package.
 * Copyright (c) Jason Coward <jason@opengeek.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport;

use Teleport\Request\Request;

/**
 * The Teleport gateway class.
 *
 * @package Teleport
 */
class Teleport
{
    const VERSION = '@version@';
    const RELEASE_DATE = '@versionDate@';

    /**
     * @var Teleport The Teleport singleton instance.
     */
    private static $instance = null;

    /**
     * @var Config An object containing the Teleport configuration options.
     */
    private $config;
    /**
     * @var Request A Request instance controlled by this Teleport instance.
     */
    private $request;

    /**
     * Get a singleton instance of Teleport.
     *
     * @param array $options An associative array of Teleport Config options for the instance.
     * @param bool  $forceNew If true, a new instance of Teleport is created and replaces the existing singleton.
     *
     * @return Teleport
     */
    public static function instance(array $options = array(), $forceNew = false)
    {
        if (!self::$instance instanceof Teleport || $forceNew === true) {
            self::$instance = new Teleport($options);
        } else {
            self::$instance->setConfig($options);
        }
        return self::$instance;
    }

    /**
     * Get the Teleport Config object.
     *
     * @return Config
     */
    public function &getConfig()
    {
        if (!$this->config instanceof Config) {
            $this->config = new Config();
        }
        return $this->config;
    }

    /**
     * Merge options into the Teleport Config object.
     *
     * @param array $options An associative array of Teleport Config options.
     */
    public function setConfig(array $options = array())
    {
        $this->getConfig()->merge($options);
    }

    /**
     * Get a Teleport Request object of a specific class.
     *
     * @param string $requestClass The Request class to get an instance of.
     *
     * @return Request A Teleport Request object.
     */
    public function &getRequest($requestClass = '')
    {
        if (!is_string($requestClass) || $requestClass === '') {
            $requestClass = $this->getConfig()->get('Request.class', null, 'Teleport\\Request\\CLIRequest');
        }
        if (!$this->request instanceof $requestClass) {
            $this->request = new $requestClass();
        }
        return $this->request;
    }

    /**
     * Construct an instance of Teleport.
     *
     * @param array $options An associative array of Teleport Config options.
     */
    private function __construct(array $options = array())
    {
        $this->setConfig($options);
    }
}
