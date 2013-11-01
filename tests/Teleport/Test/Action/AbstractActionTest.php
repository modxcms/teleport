<?php
/**
 * This file is part of the teleport package.
 *
 * Copyright (c) MODX, LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport\Test\Action;


use Teleport\Test\Request\MockRequest;

class AbstractActionTest extends ActionTestCase {
    /** @var MockAction */
    public $fixture;

    public function setUp()
    {
        parent::setUp();
        $this->fixture = new MockAction(new MockRequest);
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('Teleport\\Action\\ActionInterface', $this->fixture);
        $this->assertInstanceOf('Teleport\\Action\\Action', $this->fixture);
        $this->assertInstanceOf('Teleport\\Test\\Action\\MockAction', $this->fixture);
    }
}
