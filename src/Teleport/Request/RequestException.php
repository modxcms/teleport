<?php
/**
 * This file is part of the teleport package.
 *
 * Copyright (c) Jason Coward <jason@opengeek.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport\Request;

/**
 * Represents an Exception in a Teleport Request
 *
 * @package Teleport\Request
 */
class RequestException extends \Exception
{
    /** @var AbstractRequest */
    protected $request;

    /**
     * Create a RequestException instance.
     *
     * @param AbstractRequest &$request The request triggering this exception.
     * @param string $message The exception message.
     * @param \Exception|null $previous The previous Exception.
     */
    public function __construct(AbstractRequest &$request, $message = '', $previous = null)
    {
        $this->request = &$request;
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
     * Get the results reference provided to this exception.
     *
     * @return array The results array.
     */
    public function getResults()
    {
        return $this->request->getResults();
    }
}
