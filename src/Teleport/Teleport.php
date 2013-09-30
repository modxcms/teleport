<?php
/**
 * This file is part of the teleport package.
 *
 * Copyright (c) Jason Coward <jason@opengeek.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport;


use Teleport\Request\RequestInterface;

class Teleport
{
    /** @var Teleport */
    private static $instance = null;

    /** @var modX */
    public $modx;
    /** @var Config */
    private $config;
    /** @var RequestInterface */
    private $request;

    public static function instance(array $options = array(), $forceNew = false)
    {
        if (!self::$instance instanceof Teleport || $forceNew === true) {
            self::$instance = new Teleport($options);
        } else {
            self::$instance->setConfig($options);
        }
        return self::$instance;
    }

    public function &getConfig()
    {
        if (!$this->config instanceof Config) {
            $this->config = new Config();
        }
        return $this->config;
    }

    public function setConfig(array $options = array())
    {
        $this->getConfig()->merge($options);
    }

    public function getRequest()
    {
        $requestClass = $this->getConfig()->get('Request.class');
        if (!$this->request instanceof $requestClass) {
            $this->request = new $requestClass();
        }
    }

    private function __construct(array $options = array())
    {
        $this->setConfig($options);
    }
}
