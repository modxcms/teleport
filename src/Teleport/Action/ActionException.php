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
 * Represents an Exception in a Teleport Action
 *
 * @package Teleport\Action
 */
class ActionException extends \Exception
{
    /** @var array */
    protected $results;

    /**
     * Create a RequestException instance.
     *
     * @param string $message The exception message.
     * @param array &$results The array of results from the action.
     * @param \Exception|null $previous The previous Exception.
     */
    public function __construct($message, array &$results = array(), $previous = null)
    {
        parent::__construct($message, E_USER_ERROR, $previous);
        $this->results =& $results;
    }

    /**
     * Get the results reference provided to this exception.
     *
     * @return array The results array.
     */
    public function getResults()
    {
        return $this->results;
    }
}
