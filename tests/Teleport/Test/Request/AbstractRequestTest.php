<?php
/**
 * This file is part of the teleport package.
 *
 * Copyright (c) MODX, LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport\Test\Request;

use Teleport\Request\RequestException;

class AbstractRequestTest extends RequestTestCase {
    /** @var MockRequest */
    public $fixture;

    public function setUp()
    {
        parent::setUp();
        $this->fixture = new MockRequest;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('Teleport\\Request\\RequestInterface', $this->fixture);
        $this->assertInstanceOf('Teleport\\Request\\Request', $this->fixture);
        $this->assertInstanceOf('Teleport\\Test\\Request\\MockRequest', $this->fixture);
    }

    /**
     * Test the mock parseArguments implementation.
     *
     * @param array|string $expected
     * @param array $arguments
     *
     * @dataProvider providerParseArguments
     */
    public function testParseArguments($expected, $arguments)
    {
        $this->assertEquals($expected, $this->fixture->parseArguments($arguments));
    }
    public function providerParseArguments()
    {
        return array(
            array(array(), array('action' => 'Test')),
            array(array('id' => 'Test', 'attribute' => 'value'), array('action' => 'Test', 'id' => 'Test', 'attribute' => 'value')),
        );
    }

    /**
     * Test the mock parseArguments implementation throws appropriate exceptions.
     *
     * @param array $arguments
     *
     * @expectedException \Teleport\Request\RequestException
     * @dataProvider providerParseArgumentsThrows
     */
    public function testParseArgumentsThrows($arguments)
    {
        $this->fixture->parseArguments($arguments);
    }
    public function providerParseArgumentsThrows()
    {
        return array(
            array(array()),
            array(array('id' => 'Test')),
        );
    }
}
