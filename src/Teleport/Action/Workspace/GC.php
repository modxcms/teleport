<?php
/**
 * This file is part of the teleport package.
 *
 * Copyright (c) MODX, LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport\Action\Workspace;

use Teleport\Action\Action;
use Teleport\Action\ActionException;
use Teleport\Teleport;

/**
 * Clean-up the Teleport workspace
 *
 * @package Teleport\Action\Workspace
 */
class GC extends Action
{
    public function process()
    {
        parent::process();
        try {
            $this->deleteTree(TELEPORT_BASE_PATH . 'workspace');

            $this->request->log("Completed cleaning up the Teleport workspace");
        } catch (\Exception $e) {
            throw new ActionException($this, "Error cleaning up the Teleport workspace: {$e->getMessage()}", $e);
        }
    }

    private function deleteTree($dir)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $dir,
                \FilesystemIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        /** @var \SplFileInfo $fileInfo */
        foreach ($iterator as $filename => $fileInfo) {
            if ($fileInfo->isDir()) {
                rmdir($filename);
            } elseif ($fileInfo->getPath() === $dir && $fileInfo->getBasename() === '.gitignore') {
                continue;
            } else {
                unlink($filename);
            }
        }
    }
}
