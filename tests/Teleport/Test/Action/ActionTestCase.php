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


use Teleport\Action\ActionInterface;
use Teleport\TestCase;

abstract class ActionTestCase extends TestCase {
    /** @var ActionInterface */
    public $fixture;

    public function testGetMODX()
    {
        $this->fixture->getMODX();
    }

    public function testGetRequest()
    {
        $this->fixture->getRequest();
    }

    public function testProcess()
    {
        $this->fixture->process();
    }

    public function testValidate()
    {
        $this->fixture->validate();
    }
}
