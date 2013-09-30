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


use Teleport\Request\RequestException;
use Teleport\Request\RequestInterface;

/**
 * Provides baseline functionality for Teleport Actions.
 *
 * @package Teleport\Action
 */
abstract class AbstractAction implements ActionInterface
{
    /** @var array  */
    protected $required = array();
    /** @var \Teleport\Request\RequestInterface */
    protected $request;
    /** @var \modX */
    protected $modx;

    /**
     * Construct a new AbstractAction instance.
     *
     * @param RequestInterface &$request The request implementation processing the action.
     */
    public function __construct(RequestInterface &$request)
    {
        $this->request =& $request;
    }

    /**
     * Get a request argument or member variable value from this Action.
     *
     * @param string $name The name of the argument or variable.
     *
     * @return array|mixed|null The value of the argument or variable.
     */
    public function __get($name)
    {
        return $this->request->args($name);
    }

    /**
     * Check if a request argument or member variable is set for this Action.
     *
     * @param string $name The name of the argument of variable.
     *
     * @return bool TRUE if the argument or member variable is set, FALSE otherwise.
     */
    public function __isset($name)
    {
        return isset($this->request->$name);
    }

    /**
     * Set an argument or member variable value for this Action.
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if (is_string($name) && $name !== '') {
            $this->request->$name = $value;
        }
    }

    /**
     * Get a MODX reference to operate on.
     *
     * @return \modX A reference to a MODX instance.
     */
    public function &getMODX()
    {
        return $this->modx;
    }

    /**
     * Get a reference to the Teleport request handler.
     *
     * @return \Teleport\Request\RequestInterface The Teleport request handler.
     */
    public function &getRequest()
    {
        return $this->request;
    }

    /**
     * Process this action.
     *
     * @throws \Teleport\Action\ActionException If an error is encountered processing this Action.
     * @return void
     */
    abstract public function process();

    /**
     * Validate the arguments specified for this action.
     *
     * @throws \Teleport\Request\RequestException If required arguments are not specified.
     * @return bool TRUE if the arguments are valid, FALSE otherwise.
     */
    public function validate()
    {
        if (!empty($this->required)) {
            $invalid = array_diff($this->required, array_keys($this->request->args()));
            if (!empty($invalid)) {
                foreach ($invalid as $argKey) {
                    $this->request->addResult("{$argKey} required for this request.");
                }
                throw new RequestException("Required arguments " . implode(',', $invalid) . " not specified.", $this->request->getResults());
            }
        }
    }
}
