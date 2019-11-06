<?php
/**
 * This file is part of the teleport package.
 * Copyright (c) MODX, LLC
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport;

use Exception;
use MODX\Revolution\modX;
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
     * @var modX A MODX instance being operated on by this Teleport instance.
     */
    protected $modx;
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
     * @param bool $forceNew If true, a new instance of Teleport is created and replaces the existing singleton.
     *
     * @return Teleport
     * @throws ConfigException
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
     * Load JSON profile data into a PHP stdObject instance.
     *
     * @param string $profile A valid stream or file location for the profile.
     *
     * @throws InvalidProfileException If a valid code cannot be found in the
     * profile.
     * @return \stdClass A stdObject representation of the JSON profile data.
     */
    public static function loadProfile($profile)
    {
        $decoded = json_decode(file_get_contents($profile));
        if (!empty($decoded->code)) {
            $decoded->code = str_replace(array('-', '.'), array('_', '_'), $decoded->code);
        } else {
            throw new InvalidProfileException("Error getting 'code' from profile {$profile}", E_USER_ERROR);
        }
        return $decoded;
    }

    /**
     * Get a MODX reference for Teleport to operate on.
     *
     * IMPORTANT: Only one modX instance can be instantiated with Teleport in
     * a single PHP execution cycle.
     *
     * @param \stdClass|null $profile An object of properties describing the modX instance.
     * @param array          $options An array of initialization options.
     * @param array          &$results An optional results array reference.
     *
     * @throws InvalidMODXException If the MODX instance could not be initialized.
     * @return modX A MODX instance.
     */
    public function getMODX($profile = null, array $options = array(), array &$results = array())
    {
        if ($profile === null && !$this->modx instanceof modX) {
            throw new InvalidMODXException("No profile or MODX instance was provided");
        }
        if (!$this->modx instanceof modX) {
            try {
                define('MODX_CORE_PATH', $profile->properties->modx->core_path);
                define('MODX_CONFIG_KEY', !empty($profile->properties->modx->config_key)
                    ? $profile->properties->modx->config_key : 'config');

                require MODX_CORE_PATH . 'vendor/autoload.php';

                $logTarget = $this->getConfig()->get('log_target', $options, array('target' => 'ARRAY', 'target_options' => array('var' => &$results)));
                $logLevel = $this->getConfig()->get('log_level', $options, modX::LOG_LEVEL_INFO);
                $config = array(
                    'log_target' => $logTarget,
                    'log_level' => $logLevel,
                    'cache_db' => false,
                );

                $this->modx = new modX('', $config);
                $this->modx->setLogLevel($config['log_level']);
                $this->modx->setLogTarget($config['log_target']);
                $this->modx->setOption('cache_db', $config['cache_db']);
                $this->modx->getVersionData();
                $this->modx->initialize('mgr', $config);
                $this->modx->setLogLevel($config['log_level']);
                $this->modx->setLogTarget($config['log_target']);
                $this->modx->setOption('cache_db', $config['cache_db']);
            } catch (Exception $e) {
                throw new InvalidMODXException("Error initializing MODX: " . $e->getMessage(), $e->getCode(), $e);
            }
        }
        return $this->modx;
    }

    /**
     * Set a MODX instance to use with Teleport explicitly.
     *
     * @param modX $modx A MODX instance to attach to the Teleport instance.
     * @param array $options An array of options to override the profile.
     *
     * @throws InvalidMODXException
     * @return resource A stream resource containing the profile ready for reading.
     */
    public function setMODX(modX $modx, array $options = array())
    {
        if ($this->modx instanceof modX) {
            throw new InvalidMODXException('MODX already set; you cannot set another MODX instance');
        }
        if (!$modx instanceof modX) {
            throw new InvalidMODXException('Attempt to set an invalid MODX instance');
        }
        $this->modx = $modx;

        $profileName = $this->getConfig()->get('profile_name', $options, $this->modx->getOption('site_name'));
        $profileCode = $this->getConfig()->get('profile_code', $options, $this->modx->getOption('host'));

        $profile = array(
            'name' => $profileName,
            'code' => $profileCode,
            'properties' => array(
                'modx' => array(
                    'core_path' => $this->modx->getOption('core_path', null, MODX_CORE_PATH),
                    'config_key' => $this->modx->getOption('config_key', null, MODX_CONFIG_KEY),
                    'context_mgr_path' => $this->modx->getOption('manager_path', null, MODX_MANAGER_PATH),
                    'context_mgr_url' => $this->modx->getOption('manager_url', null, MODX_MANAGER_URL),
                    'context_connectors_path' => $this->modx->getOption('connectors_path', null, MODX_CONNECTORS_PATH),
                    'context_connectors_url' => $this->modx->getOption('connectors_url', null, MODX_CONNECTORS_URL),
                    'context_web_path' => $this->modx->getOption('base_path', null, MODX_BASE_PATH),
                    'context_web_url' => $this->modx->getOption('base_url', null, MODX_BASE_URL),
                ),
            ),
        );

        return 'data://text/plain;base64,' . base64_encode(json_encode($profile, JSON_PRETTY_PRINT));
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
            $requestClass = $this->getConfig()->get('Request.class', null, 'Teleport\Request\CLIRequest');
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
                } catch (Exception $e) {
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
     * @throws ConfigException
     */
    protected function __construct(array $options = array())
    {
        $this->setConfig($options);
        $this->registerStreamHandlers();
    }
}
