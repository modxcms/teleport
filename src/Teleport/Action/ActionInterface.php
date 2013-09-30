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

use Teleport\Request\RequestInterface;

/**
 * Defines the API contract for Teleport actions.
 *
 * @package Teleport\Action
 */
interface ActionInterface
{
    /**
     * Get a MODX reference to operate on.
     *
     * @return \modX A reference to a MODX instance.
     */
    public function &getMODX();

    /**
     * Get a reference to the Teleport request handler.
     *
     * @return RequestInterface The Teleport request handler.
     */
    public function &getRequest();

    /**
     * Process this action.
     *
     * @return void
     * @throws ActionException If an error is encountered processing this Action.
     */
    public function process();

    /**
     * Validate the arguments specified for this action.
     *
     * @return bool TRUE if the arguments are valid, FALSE otherwise.
     */
    public function validate();
}
