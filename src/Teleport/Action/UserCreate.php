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
 * Create a user in a MODX installation.
 *
 * @property \stdClass profile
 * @property string    username
 * @property string    password
 * @property string    passwordnotifymethod
 * @property string    passwordgenmethod
 * @property string    newpassword
 * @property string    specifiedpassword
 * @property string    confirmpassword
 *
 * @package Teleport\Action
 */
class UserCreate extends Action
{
    /**
     * @var array Defines the arguments required for the UserCreate action.
     */
    protected $required = array('profile', 'username');

    public function process()
    {
        parent::process();
        try {
            $this->profile = $this->loadProfile($this->profile);
            if (empty($this->passwordnotifymethod)) {
                $this->passwordnotifymethod = 's';
            }
            if (!empty($this->password)) {
                $this->passwordgenmethod = '';
                $this->newpassword = $this->password;
                $this->specifiedpassword = $this->password;
                $this->confirmpassword = $this->password;
            }

            define('MODX_CORE_PATH', $this->profile->properties->modx->core_path);
            define('MODX_CONFIG_KEY', !empty($this->profile->properties->modx->config_key)
                ? $this->profile->properties->modx->config_key : 'config');

            $this->getMODX();
            $this->modx->getService('error', 'error.modError');
            $this->modx->error->message = '';
            $this->modx->setOption(\xPDO::OPT_SETUP, true);

            /** @var \modProcessorResponse $response */
            $response = $this->modx->runProcessor('security/user/create', $this->request->args());
            if ($response->isError()) {
                throw new ActionException($this, implode("\n", $response->getAllErrors()) . "\n0");
            } else {
                $this->request->log("Created user for {$this->profile->name} with username {$this->username}: {$response->getMessage()}");
            }
            $this->request->log('1', false);
        } catch (\Exception $e) {
            throw new ActionException($this, "Error creating MODX user: {$e->getMessage()}", $e);
        }
    }
}
