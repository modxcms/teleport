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

/**
 * Pushes a file to a target stream location.
 *
 * @property string   source
 * @property string   target
 * @property resource profile
 *
 * @package Teleport\Action
 */
class Push extends Action
{
    /**
     * @var array Defines the arguments required for the Push action.
     */
    protected $required = array('source', 'target', 'profile');

    public function process()
    {
        parent::process();
        try {
            $pushed = false;

            if (empty($this->profile) or !file_exists($this->profile)) {
                throw new ActionException($this, "Profile does not exist at: " . $this->profile);
            }

            $this->profile = $this->loadProfile($this->profile);
            define('MODX_CORE_PATH', $this->profile->properties->modx->core_path);
            define('MODX_CONFIG_KEY', !empty($this->profile->properties->modx->config_key)
                ? $this->profile->properties->modx->config_key : 'config');

            $this->getMODX();
            $this->modx->getService('error', 'error.modError');
            $this->modx->error->message = '';
            $this->modx->setOption(\xPDO::OPT_SETUP, true);

            if ($this->modx->getCacheManager()) {
                $pushed = $this->modx->cacheManager->copyFile($this->source, $this->target, array('copy_preserve_permissions' => true));
            }
            $this->request->log($this->target, false);

            $this->request->log($pushed ? '1' : '0', false);
        } catch (\Exception $e) {
            throw new ActionException($this, "Error pushing {$this->source} to {$this->target}", $e);
        }
    }
}
