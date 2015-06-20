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

class Help extends Action
{
    public function process()
    {
        $this->request->log("Teleport version " . Teleport::VERSION . " " . Teleport::RELEASE_DATE, false);
        $help = <<<EOT
Usage:
 [options] [arguments]

Options:
 --help (-h)    Display this help message
 --version (-V) Display the Teleport version
 --action       Execute a Teleport Action with optional arguments

Available Actions:
 Extract        Extract assets and data from a MODX site into a transport package
 Help           Display this help message
 Inject         Inject an extracted transport package into a MODX site
 Packages/GC    Perform garbage collection on outdated packages in a MODX site
 Profile        Create a profile from a MODX core installation
 Pull           Pull a remote file source to a local target
 Push           Push a local file to a remote target
 UserCreate     Create a user in a MODX site
 Version        Display the Teleport version
EOT;
        $this->request->log($help, false);
    }
}
