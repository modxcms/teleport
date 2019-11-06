<?php
/**
 * This file is part of the teleport package.
 *
 * Copyright (c) MODX, LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport\Action;

use Exception;
use stdClass;
use Teleport\Teleport;
use Teleport\Transport\Transport;
use xPDO\Transport\xPDOTransport;
use xPDO\xPDO;

/**
 * Inject a Snapshot into a MODX Instance.
 *
 * @property stdClass profile
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
            $this->profile = Teleport::loadProfile($this->profile);

            $this->getMODX($this->profile);

            $this->modx->setOption(xPDO::OPT_SETUP, true);

            $transportName = basename($this->source);
            if (TELEPORT_BASE_PATH . 'workspace' . DIRECTORY_SEPARATOR . $transportName !== realpath($this->source)) {
                if (!$this->pull($this->source, TELEPORT_BASE_PATH . 'workspace' . DIRECTORY_SEPARATOR . $transportName)) {
                    throw new ActionException($this, "Error pulling {$this->source}");
                }
            } else {
                $this->preserveWorkspace = true;
            }

            $this->package = Transport::retrieve($this->modx, TELEPORT_BASE_PATH . 'workspace' . DIRECTORY_SEPARATOR . $transportName, TELEPORT_BASE_PATH . 'workspace' . DIRECTORY_SEPARATOR);
            if (!$this->package instanceof Transport) {
                throw new ActionException($this, "Error extracting {$transportName} in workspace" . DIRECTORY_SEPARATOR);
            }

            $this->package->preInstall();

            if (!$this->package->install(array(xPDOTransport::PREEXISTING_MODE => xPDOTransport::REMOVE_PREEXISTING))) {
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
        } catch (Exception $e) {
            throw new ActionException($this, 'Error injecting snapshot: ' . $e->getMessage(), $e);
        }
    }
}
