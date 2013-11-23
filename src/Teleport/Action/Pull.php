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
 * Pull a source stream to a target stream.
 *
 * @property string   source
 * @property string   target
 * @property bool     overwriteTarget
 *
 * @package Teleport\Action
 */
class Pull extends Action
{
    /**
     * @var array Defines the arguments required for the Pull action.
     */
    protected $required = array('source', 'target');

    public function process()
    {
        parent::process();
        if (!$this->overwriteTarget && file_exists($this->target)) {
            throw new ActionException($this, "{$this->target} exists; use --overwriteTarget to Pull anyway");
        }
        try {
            $pulled = copy($this->source, $this->target);
            if (!$pulled) {
                throw new ActionException($this, "copy failed");
            }
            $this->request->log("Successfully pulled {$this->source} to {$this->target}");
        } catch (\Exception $e) {
            throw new ActionException($this, "Error pulling {$this->source} to {$this->target}", $e);
        }
    }
}
