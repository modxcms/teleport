<?php
/**
 * This file is part of the teleport package.
 *
 * Copyright (c) MODX, LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport\Request;

/**
 * Represents an invalid Teleport Request Exception
 *
 * @package Teleport\Request
 */
class InvalidRequestException extends \Exception
{
    /** @var Request */
    protected $request;

    /**
     * Create an InvalidRequestException instance.
     *
     * @param Request         &$request The request triggering this exception.
     * @param string          $message The exception message.
     * @param \Exception|null $previous The previous Exception.
     */
    public function __construct(Request &$request, $message = '', $previous = null)
    {
        $this->request = & $request;
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
