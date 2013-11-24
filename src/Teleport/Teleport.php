<?php
/**
 * This file is part of the teleport package.
 * Copyright (c) MODX, LLC
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
    protected static $instance = null;

    /**
     * @var Config An object containing the Teleport configuration options.
     */
    protected $config;
    /**
     * @var Request A Request instance controlled by this Teleport instance.
     */
    protected $request;
    /**
     * @var resource The default stream context used by Teleport.
     */
    protected $streamContext;

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
        if (self::$instance === null || $forceNew === true) {
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
     * Register stream handlers specified in the Teleport Config.
     *
     * @throws ConfigException If an error occurs attempting to register a
     * stream_handler configuration element.
     */
    protected function registerStreamHandlers()
    {
        $handlers = $this->getConfig()->get('stream_handlers', null, array());
        if (!is_array($handlers)) {
            throw new ConfigException('Invalid stream_handlers configuration', E_USER_ERROR);
        }

        $defaultStreamContext = array();
        foreach ($handlers as $protocol => $handler) {
            if (isset($handler['class'])) {
                try {
                    $flags = isset($handler['local']) ? !empty($handler['local']) : STREAM_IS_URL;
                    if (isset($handler['register_callback']) && is_callable($handler['register_callback'])) {
                        $registered = $handler['register_callback']($protocol, $handler);
                    } else {
                        if (in_array($protocol, stream_get_wrappers())) {
                            stream_wrapper_unregister($protocol);
                        }
                        $registered = stream_register_wrapper($protocol, $handler['class'], $flags);
                    }
                    if (isset($handler['options']) && is_array($handler['options'])) {
                        $defaultStreamContext[$protocol] = $handler['options'];
                    }
                } catch (\Exception $e) {
                    throw new ConfigException("Error registering stream_handler {$handler['class']} ({$protocol}://)", E_USER_ERROR, $e);
                }
                if (!$registered) {
                    throw new ConfigException("Could not register stream_handler {$handler['class']} ({$protocol}://)", E_USER_ERROR);
                }
            } else {
                throw new ConfigException("Invalid stream_handler configuration for protocol {$protocol}", E_USER_ERROR);
            }
        }

        $this->streamContext = stream_context_set_default($defaultStreamContext);
    }

    /**
     * Construct an instance of Teleport.
     *
     * @param array $options An associative array of Teleport Config options.
     */
    protected function __construct(array $options = array())
    {
        $this->setConfig($options);
        $this->registerStreamHandlers();
    }
}
