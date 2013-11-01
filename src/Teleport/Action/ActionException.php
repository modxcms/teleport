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
 * Represents an Exception in a Teleport Action
 *
 * @package Teleport\Action
 */
class ActionException extends \Exception
{
    /** @var Action */
    protected $action;

    /**
     * Create a RequestException instance.
     *
     * @param Action          &$action The action instance triggering this exception.
     * @param string          $message The exception message.
     * @param \Exception|null $previous The previous Exception.
     */
    public function __construct(Action &$action, $message = '', $previous = null)
    {
        $this->action = & $action;
        $code = E_USER_ERROR;
        if ($previous instanceof \Exception) {
            if (!is_string($message) || $message === '') {
                $message = $previous->getMessage();
            }
            $code = $previous->getCode();
        }
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the results of the action provided to this exception.
     *
     * @return array The results array.
     */
    public function getResults()
    {
        return $this->action->getRequest()->getResults();
    }
}
