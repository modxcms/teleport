<?php
/**
 * This file is part of the teleport package.
 *
 * Copyright (c) Jason Coward <jason@opengeek.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport\Test;


use Teleport\Teleport;
use Teleport\TestCase;

class TeleportTest extends TestCase
{
    public function testInstance()
    {
        $this->assertInstanceOf('Teleport\\Teleport', Teleport::instance());
        $this->assertInstanceOf('Teleport\\Teleport', Teleport::instance(array()));
        $this->assertInstanceOf('Teleport\\Teleport', Teleport::instance(array('foo' => 'bar')));
    }

    public function testSetConfig()
    {
        $instance = Teleport::instance();
        $instance->setConfig(array('foo' => 'bar'));
        $this->assertEquals('bar', $instance->getConfig()->get('foo'));
    }

    public function testGetConfig()
    {
        $instance = Teleport::instance(array('foo' => 'bar'));
        $config = $instance->getConfig();
        $this->assertInstanceOf('Teleport\\Config', $config);
        $this->assertEquals('bar', $config->get('foo'));
    }
}
