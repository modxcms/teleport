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
        self::copyTpl($event);
    }

    /**
     * Composer post-update-cmd callback.
     *
     * @param Event $event The composer Event object.
     */
    public static function postUpdate(Event $event)
    {
        self::copyTpl($event);
    }

    /**
     * Copy teleport tpl files in local tpl directory.
     *
     * @param Event $event The composer Event object.
     */
    public static function copyTpl(Event $event)
    {
        $config = self::getOptions($event);

        /* symlink the tpl files from the teleport library */
        $sources = new Finder();
        $sources->files()->ignoreVCS(true)->ignoreDotFiles(true)->name('*.tpl.json')->name('*.php')->in(dirname(dirname(__DIR__)) . '/tpl');

        foreach ($sources as $source) {
            self::copyFile($event, dirname(dirname(__DIR__)) . '/tpl', $source, $config);
        }
    }

    /**
     * Copy a source file from teleport/tpl to the teleport-tpl-dir.
     *
     * @param Event $event The composer Event object.
     * @param string $base The base path of the installation.
     * @param \SplFileInfo $source The source file info.
     * @param array $config Project config options.
     */
    protected static function copyFile(Event $event, $base, $source, $config)
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
            copy($filename, $target);
        }
    }

    /**
     * Get the config data from the extra definition in composer.json.
     *
     * @param Event $event The composer Event object.
     *
     * @return array An array of options from the package extra.
     */
    protected static function getOptions(Event $event)
    {
        $options = array_merge(
            array(
                'teleport-tpl-dir' => 'tpl',
                'teleport-tpl-update' => true
            ),
            $event->getComposer()->getPackage()->getExtra()
        );
        return $options;
    }
}
