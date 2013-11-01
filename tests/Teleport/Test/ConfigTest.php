<?php
/**
 * This file is part of the teleport package.
 *
 * Copyright (c) MODX, LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport\Test;


use Aws\CloudFront\Exception\Exception;
use Teleport\Config;
use Teleport\TestCase;

class ConfigTest extends TestCase {
    /**
     * Test getting an instance of Teleport\Config
     *
     * @param array $config
     *
     * @dataProvider providerConstruct
     */
    public function testConstruct($config)
    {
        $this->assertInstanceOf('Teleport\\Config', new Config($config));
    }
    public function providerConstruct()
    {
        return array(
            array(
                array(),
            ),
            array(
                array('foo' => 1, 'bar' => false),
            ),
        );
    }
}
