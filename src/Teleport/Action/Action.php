<?php
/**
 * This file is part of the teleport package.
 *
 * Copyright (c) Jason Coward <jason@opengeek.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport\Action;

use Teleport\Request\Request;

/**
 * Provides baseline functionality for Teleport Actions.
 *
 * @package Teleport\Action
 */
abstract class Action implements ActionInterface
{
    /** @var array */
    protected $required = array();
    /** @var \Teleport\Request\Request */
    protected $request;
    /** @var \modX */
    protected $modx;

    /**
     * Construct a new Action instance.
     *
     * @param Request &$request The request implementation processing the action.
     */
    public function __construct(Request &$request)
    {
        $this->request =& $request;
    }

    /**
     * Get a request argument or member variable value from this Action.
     *
     * @param string $name The name of the argument or variable.
     *
     * @return array|mixed|null The value of the argument or variable.
     */
    public function __get($name)
    {
        return $this->request->args($name);
    }

    /**
     * Check if a request argument or member variable is set for this Action.
     *
     * @param string $name The name of the argument of variable.
     *
     * @return bool TRUE if the argument or member variable is set, FALSE otherwise.
     */
    public function __isset($name)
    {
        return isset($this->request->$name);
    }

    /**
     * Set an argument or member variable value for this Action.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        if (is_string($name) && $name !== '') {
            $this->request->$name = $value;
        }
    }

    /**
     * Get a MODX reference to operate on.
     *
     * @throws ActionException If a MODX instance could not be initialized.
     * @return \modX A reference to a MODX instance.
     */
    public function &getMODX()
    {
        if (!$this->modx instanceof \modX) {
            if (defined('MODX_CORE_PATH')) {
                try {
                    require MODX_CORE_PATH . 'model/modx/modx.class.php';
                    $results = $this->request->getResults();
                    $logTarget = $this->request->args('log_target') !== null
                        ? $this->request->args('log_target')
                        : array('target' => 'ARRAY', 'target_options' => array('var' => &$results));
                    $logLevel = $this->request->args('log_level') !== null
                        ? $this->request->args('log_level')
                        : \modX::LOG_LEVEL_INFO;
                    $config = array(
                        'log_target' => $logTarget,
                        'log_level' => $logLevel,
                        'cache_db' => false,
                    );
                    $this->modx = new \modX('', $config);
                    $this->modx->setLogLevel($config['log_level']);
                    $this->modx->setLogTarget($config['log_target']);
                    $this->modx->setOption('cache_db', $config['cache_db']);
                    $this->modx->getVersionData();
                    if (version_compare($this->modx->version['full_version'], '2.2.1-pl', '>=')) {
                        $this->modx->initialize('mgr', $config);
                    } else {
                        $this->modx->initialize('mgr');
                    }
                    $this->modx->setLogLevel($config['log_level']);
                    $this->modx->setLogTarget($config['log_target']);
                    $this->modx->setOption('cache_db', $config['cache_db']);
                } catch (\Exception $e) {
                    throw new ActionException($this, "Error initializing MODX: " . $e->getMessage(), $e);
                }
            } else {
                throw new ActionException($this, "Could not initialize MODX: MODX_CORE_PATH not defined.");
            }
        }
        return $this->modx;
    }

    /**
     * Get a reference to the Teleport request handler.
     *
     * @return \Teleport\Request\RequestInterface The Teleport request handler.
     */
    public function &getRequest()
    {
        return $this->request;
    }

    /**
     * Process this action.
     *
     * @throws \Teleport\Action\ActionException If an error is encountered processing this Action.
     * @return void
     */
    public function process()
    {
        $this->validate();
    }

    /**
     * Validate the arguments specified for this Action.
     *
     * @throws \Teleport\Action\ActionException If required arguments are not specified.
     * @return bool TRUE if the arguments are valid, FALSE otherwise.
     */
    public function validate()
    {
        if (!empty($this->required)) {
            $invalid = array_diff($this->required, array_keys($this->request->args()));
            if (!empty($invalid)) {
                foreach ($invalid as $argKey) {
                    $this->request->addResult("{$argKey} required for this request.");
                }
                throw new ActionException($this, "Required arguments " . implode(', ', $invalid) . " not specified.");
            }
        }
    }

    /**
     * Load JSON profile data into a PHP stdObject instance.
     *
     * @param string $profile A valid stream or file location for the profile.
     *
     * @throws ActionException If a valid code cannot be found in the profile.
     * @return \stdClass A stdObject representation of the JSON profile data.
     */
    protected function loadProfile($profile)
    {
        $decoded = json_decode(file_get_contents($profile));
        if (!empty($decoded->code)) {
            $decoded->code = str_replace(array('-', '.'), array('_', '_'), $decoded->code);
        }
        else {
            throw new ActionException($this, "Error getting 'code' from profile {$profile}");
        }
        return $decoded;
    }

    /**
     * Pull a source to a specified target location.
     *
     * @param string $source A valid stream URI or file path to the snapshot source.
     * @param string $target A valid stream URI or file path to copy the snapshot to.
     *
     * @return bool Returns true if the pull was completed successfully.
     */
    public function pull($source, $target)
    {
        $pulled = false;
        if ($this->modx->getCacheManager()) {
            $pulled = $this->modx->cacheManager->copyFile($source, $target, array('copy_preserve_permissions' => true));
        }
        return $pulled;
    }

    /**
     * Push a source to a specified target location.
     *
     * @param string $source A valid file or stream location source.
     * @param string $target A valid file or stream location target.
     *
     * @return bool Returns true if the source was pushed successfully to the target, false otherwise.
     */
    public function push($source, $target)
    {
        $pushed = false;
        if ($this->modx->getCacheManager()) {
            $pushed = $this->modx->cacheManager->copyFile($source, $target, array('copy_preserve_permissions' => true));
        }
        return $pushed;
    }
}
