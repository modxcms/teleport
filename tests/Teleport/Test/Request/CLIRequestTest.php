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


use Teleport\Request\CLIRequest;

class CLIRequestTest extends RequestTestCase {
    public function setUp()
    {
        parent::setUp();
        $this->fixture = new CLIRequest;
    }
}
