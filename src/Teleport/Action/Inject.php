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

use Teleport\Transport\Transport;

/**
 * Inject a Snapshot into a MODX Instance.
 *
 * @property \stdClass profile
 * @property string    source
 * @property bool      preserveWorkspace
 *
 * @package Teleport\Action
 */
class Inject extends Action
{
    /**
     * @var array The required arguments for this action.
     */
    protected $required = array('profile', 'source');
    /**
     * @var Transport The Transport package to be Injected into the \modX instance.
     */
    public $package;

    /**
     * Process the Inject action.
     *
     * @throws ActionException If an error is encountered during processing.
     */
    public function process()
    {
        parent::process();
        try {
            $this->profile = $this->loadProfile($this->profile);

            define('MODX_CORE_PATH', $this->profile->properties->modx->core_path);
            define('MODX_CONFIG_KEY', !empty($this->profile->properties->modx->config_key)
                ? $this->profile->properties->modx->config_key : 'config');

            $this->getMODX();

            $this->modx->setOption(\xPDO::OPT_SETUP, true);

            $this->modx->loadClass('transport.xPDOTransport', XPDO_CORE_PATH, true, true);
            $this->modx->loadClass('transport.xPDOVehicle', XPDO_CORE_PATH, true, true);
            $this->modx->loadClass('transport.xPDOObjectVehicle', XPDO_CORE_PATH, true, true);

            $transportName = basename($this->source);
            if (TELEPORT_BASE_PATH . 'workspace/' . $transportName !== realpath($this->source)) {
                if (!$this->pull($this->source, TELEPORT_BASE_PATH . 'workspace/' . $transportName)) {
                    throw new ActionException($this, "Error pulling {$this->source}");
                }
            } else {
                $this->preserveWorkspace = true;
            }

            $this->package = Transport::retrieve($this->modx, TELEPORT_BASE_PATH . 'workspace/' . $transportName, TELEPORT_BASE_PATH . 'workspace/');
            if (!$this->package instanceof Transport) {
                throw new ActionException($this, "Error extracting {$transportName} in workspace/");
            }

            $this->package->preInstall();

            if (!$this->package->install(array(\xPDOTransport::PREEXISTING_MODE => \xPDOTransport::REMOVE_PREEXISTING))) {
                throw new ActionException($this, "Error installing {$transportName}");
            }

            $this->package->postInstall();

            if ($this->modx->getCacheManager()) {
                $this->modx->cacheManager->refresh();
            }

            if (!$this->preserveWorkspace && $this->modx->getCacheManager()) {
                $this->modx->cacheManager->deleteTree($this->package->path . $transportName);
                @unlink($this->package->path . $transportName . '.transport.zip');
            }

            $this->request->log("Successfully injected {$transportName} into instance {$this->profile->code}");
        } catch (\Exception $e) {
            throw new ActionException($this, 'Error injecting snapshot: ' . $e->getMessage(), $e);
        }
    }
} 
