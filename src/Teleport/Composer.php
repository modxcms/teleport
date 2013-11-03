<?php
/**
 * This file is part of the teleport package.
 *
 * Copyright (c) MODX, LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport;

use Composer\Script\Event;
use Symfony\Component\Finder\Finder;

/**
 * Provides Composer scripting callbacks.
 *
 * @package Teleport
 */
class Composer
{
    /**
     * Composer post-install-cmd callback.
     *
     * @param Event $event The composer Event object.
     */
    public static function postInstall(Event $event)
    {
        self::symLinkTpl($event);
    }

    /**
     * Composer post-update-cmd callback.
     *
     * @param Event $event The composer Event object.
     */
    public static function postUpdate(Event $event)
    {
        self::symLinkTpl($event);
    }

    /**
     * Create symlinks to teleport tpl files in local tpl directory.
     *
     * @param Event $event The composer Event object.
     */
    public static function symLinkTpl(Event $event)
    {
        $installPath = $event->getComposer()->getInstallationManager()->getInstallPath($event->getComposer()->getPackage());
        $target = dirname(dirname($installPath)) . '/tpl';

        /* symlink the tpl files from the teleport library */
        $sources = new Finder();
        $sources->files()->ignoreVCS(true)->ignoreDotFiles(true)->name('*.tpl.json')->name('*.php')->in(dirname(dirname(__DIR__)) . '/tpl');

        foreach ($sources as $source) {
            self::createSymLink($event, dirname(dirname(__DIR__)) . '/tpl', $source, $target);
        }
    }

    /**
     * Create a symlink to a source in a specified target directory.
     *
     * @param Event $event The composer Event object.
     * @param string $base The base path of the installation.
     * @param \SplFileInfo $source The source file info.
     * @param string $target The target link base path.
     */
    protected static function createSymLink($event, $base, $source, $target)
    {
        $filename = $source->getPathname();
        $relative = substr($filename, strlen($base) + 1);
        $target .= '/' . $relative;
        if (!is_dir(dirname($target))) {
            @mkdir(dirname($target), 0777, true);
        }
        if (!file_exists($target)) {
            symlink($filename, $target);
        }
    }
}
