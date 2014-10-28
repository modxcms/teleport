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

/**
 * Generate a Teleport Profile from a MODX Instance.
 *
 * @property string name
 * @property string code
 * @property string core_path
 * @property string config_key
 * @property string target
 * @property string push
 *
 * @package Teleport\Action
 */
class Profile extends Action
{
    protected $required = array('name', 'core_path');

    /**
     * Process this action.
     *
     * @throws \Teleport\Action\ActionException If an error is encountered processing this Action.
     * @return void
     */
    public function process()
    {
        parent::process();
        try {
            if (!array_key_exists('config_key', $this->request->args())) {
                $this->config_key = 'config';
            }
            if (!array_key_exists('code', $this->request->args())) {
                $this->code = str_replace(array('-', '.'), array('_', '_'), $this->name);
            }

            $profile = new \stdClass();
            $profile->properties = new \stdClass();
            $profile->properties->modx = new \stdClass();
            $profile->properties->modx->core_path = $this->core_path;
            $profile->properties->modx->config_key = $this->config_key;

            $this->getMODX($profile);

            $profile = array(
                'name' => $this->name,
                'code' => $this->code,
                'properties' => array(
                    'modx' => array(
                        'core_path' => $this->core_path,
                        'config_key' => $this->config_key,
                        'context_mgr_path' => $this->modx->getOption('manager_path', null, MODX_MANAGER_PATH),
                        'context_mgr_url' => $this->modx->getOption('manager_url', null, MODX_MANAGER_URL),
                        'context_connectors_path' => $this->modx->getOption('connectors_path', null, MODX_CONNECTORS_PATH),
                        'context_connectors_url' => $this->modx->getOption('connectors_url', null, MODX_CONNECTORS_URL),
                        'context_web_path' => $this->modx->getOption('base_path', null, MODX_BASE_PATH),
                        'context_web_url' => $this->modx->getOption('base_url', null, MODX_BASE_URL),
                    ),
                ),
            );

            $profileFilename = TELEPORT_BASE_PATH . 'profile' . DIRECTORY_SEPARATOR . $this->code . '.profile.json';
            $written = $this->modx->getCacheManager()->writeFile($profileFilename, $this->modx->toJSON($profile));

            if ($written === false) {
                throw new ActionException($this, "Error writing profile {$profileFilename}");
            }
            $this->request->log("Successfully wrote profile to {$profileFilename}");
            if ($this->target && $this->push) {
                if (!$this->push($profileFilename, $this->target)) {
                    throw new ActionException($this, "Error pushing profile {$profileFilename} to {$this->target}");
                }
                $this->request->log("Successfully pushed profile {$profileFilename} to {$this->target}");
                $this->request->log("{$this->target}", false);
            } else {
                $this->request->log("{$profileFilename}", false);
            }
        } catch (\Exception $e) {
            throw new ActionException($this, "Error generating profile: " . $e->getMessage(), $e);
        }
    }
}
