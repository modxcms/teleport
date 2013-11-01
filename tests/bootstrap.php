<?php
/**
 * This file is part of the teleport package.
 *
 * Copyright (c) MODX, LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

error_reporting(E_ALL);

$loader = require __DIR__.'/../src/bootstrap.php';
$loader->add('Teleport\\Test', __DIR__);

require __DIR__.'/Teleport/TestCase.php';
