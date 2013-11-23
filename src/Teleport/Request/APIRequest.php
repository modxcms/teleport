<?php
/**
 * This file is part of the teleport package.
 *
 * Copyright (c) Jason Coward <jason@opengeek.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport\Request;

class APIRequest extends Request
{
    public function parseArguments(array $args)
    {
        if (!isset($args['action']) || empty($args['action'])) {
            throw new RequestException($this, "No valid action argument specified.");
        }
        $this->action = $args['action'];
        unset($args['action']);
        $this->arguments = $args;
        return $this->arguments;
    }
}
