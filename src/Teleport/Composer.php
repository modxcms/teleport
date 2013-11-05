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

use Composer\Script\CommandEvent;
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
     * @param CommandEvent $event The composer Event object.
     */
    public static function postInstall(CommandEvent $event)
    {
        self::symLinkTpl($event);
    }

    /**
     * Composer post-update-cmd callback.
     *
     * @param CommandEvent $event The composer Event object.
     */
    public static function postUpdate(CommandEvent $event)
    {
        self::symLinkTpl($event);
    }

    /**
     * Create symlinks to teleport tpl files in local tpl directory.
     *
     * @param CommandEvent $event The composer Event object.
     */
    public static function symLinkTpl(CommandEvent $event)
    {
        $config = self::getOptions($event);

        /* symlink the tpl files from the teleport library */
        $sources = new Finder();
        $sources->files()->ignoreVCS(true)->ignoreDotFiles(true)->name('*.tpl.json')->name('*.php')->in(dirname(dirname(__DIR__)) . '/tpl');

        foreach ($sources as $source) {
            self::createSymLink($event, dirname(dirname(__DIR__)) . '/tpl', $source, $config);
        }
    }

    /**
     * Create a symlink to a source in a specified target directory.
     *
     * @param CommandEvent $event The composer Event object.
     * @param string $base The base path of the installation.
     * @param \SplFileInfo $source The source file info.
     * @param array $config Project config options.
     */
    protected static function createSymLink($event, $base, $source, $config)
    {
        $filename = $source->getPathname();
        $relative = substr($filename, strlen($base) + 1);
        $target = $config['teleport-tpl-dir'] . '/' . $relative;
        if (!is_dir(dirname($target))) {
            @mkdir(dirname($target), 0777, true);
        }
        if ($config['teleport-tpl-update'] && file_exists($target)) {
            unlink($target);
        }
        if (!file_exists($target)) {
            symlink($filename, $target);
        }
    }

    /**
     * Get the config data from the extra definition in composer.json.
     *
     * @param CommandEvent $event The composer Event object.
     *
     * @return array An array of options from the package extra.
     */
    protected static function getOptions(CommandEvent $event)
    {
        $options = array_merge(
            array(
                'teleport-tpl-dir' => 'tpl',
                'teleport-tpl-update' => false
            ),
            $event->getComposer()->getPackage()->getExtra()
        );
        return $options;
    }
}
