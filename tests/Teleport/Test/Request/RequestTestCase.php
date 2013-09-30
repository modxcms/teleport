<?php
/**
 * This file is part of the teleport package.
 *
 * Copyright (c) Jason Coward <jason@opengeek.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport\Test\Request;


use Teleport\Request\RequestInterface;
use Teleport\TestCase;

abstract class RequestTestCase extends TestCase {
    /** @var RequestInterface */
    public $fixture;

    public function testAddResult()
    {
        $this->fixture->addResult('result added');
        $result = $this->fixture->getResults();
        $result = array_pop($result);
        $this->assertEquals('result added', $result);
    }

    public function testArgs()
    {
        $this->assertEquals(array(), $this->fixture->args());
    }

    public function testGetResults()
    {
        $this->assertEquals(array(), $this->fixture->getResults());
    }

    public function testHandle()
    {
        $this->setExpectedException('Teleport\\Request\\RequestException');
        $this->fixture->handle(array('foo' => 'bar'));
    }

    public function testLog()
    {
        $this->fixture->log('logging message', false);
        $result = $this->fixture->getResults();
        $result = array_pop($result);
        $this->assertEquals('logging message', $result);
    }
}
