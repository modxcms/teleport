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

/**
 * Pushes a file to a target stream location.
 *
 * @property string   source
 * @property string   target
 * @property bool     removeSource
 * @property bool     overwriteTarget
 *
 * @package Teleport\Action
 */
class Push extends Action
{
    /**
     * @var array Defines the arguments required for the Push action.
     */
    protected $required = array('source', 'target');

    public function process()
    {
        parent::process();
        if (!$this->overwriteTarget && file_exists($this->target)) {
            throw new ActionException($this, "{$this->target} exists; use --overwriteTarget to Push anyway");
        }
        try {
            $pushed = copy($this->source, $this->target);
            if (!$pushed) {
                throw new ActionException($this, "copy failed");
            }
            if ($this->removeSource) {
                unlink($this->source);
            }
            $this->request->log("Successfully pushed {$this->source} to {$this->target}");
        } catch (Exception $e) {
            throw new ActionException($this, "Error pushing {$this->source} to {$this->target}", $e);
        }
    }
}
