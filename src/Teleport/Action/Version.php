<?php
/*
 * This file is part of the teleport package.
 *
 * Copyright (c) MODX, LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport\Action;


use Teleport\Teleport;

class Version extends Action
{
    public function process()
    {
        $this->request->log("Teleport version " . Teleport::VERSION . " " . Teleport::RELEASE_DATE, false);
    }
}
