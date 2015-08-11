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

use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

/**
 * This class compiles Teleport into a distributable Phar.
 *
 * @package Teleport
 */
class Compiler
{
    /**
     * @var string The alias given to the phar that is created.
     */
    private $alias;
    /**
     * @var string The root path of the project being compiled.
     */
    private $path;
    /**
     * @var string The version of the project being compiled.
     */
    private $version;
    /**
     * @var string The date the version of the project being compiled.
     */
    private $versionDate;

    /**
     * Construct a new Compiler instance.
     *
     * @param string $path The root path of the project being compiled.
     * @param string $alias The alias of the phar created.
     */
    public function __construct($path = '', $alias = 'teleport.phar')
    {
        $this->alias = $alias;
        if (empty($path) && $path !== '0') $path = getcwd();
        $this->path = $path;
    }

    /**
     * Compile Teleport into a Phar for distribution.
     *
     * @throws \RuntimeException If there is an error during compilation of the phar.
     */
    public function compile($name = 'teleport.phar')
    {
        $this->cleanse($name);

        $this->getVersion();

        echo "building {$name} version {$this->version} ({$this->versionDate})" . PHP_EOL;

        $phar = $this->preparePhar($name);

        $this->addSrc($phar);

        $this->addTpl($phar);

        $this->addAutoload($phar);

        $this->addDependencies($phar);

        $this->addTeleportBin($phar);

        $phar->setStub($this->getStub());

        $this->addLicense($phar);

        $this->writePhar($phar);

        unset($phar);
    }

    /**
     * Cleanse the environment before compiling the phar.
     *
     * @param string $name The filename of the phar being compiled.
     */
    private function cleanse($name)
    {
        if (file_exists($name)) {
            unlink($name);
        }
    }

    /**
     * Get version data from the Git repository for identifying the phar.
     *
     * @throws \RuntimeException If a problem occurs getting version data from
     * the git binary.
     */
    private function getVersion()
    {
        $process = new Process('git log --pretty="%H" -n1 HEAD', __DIR__);
        if ($process->run() != 0) {
            throw new \RuntimeException("Can't run git log. You must ensure to run compile from the teleport git repository clone and that git binary is available.");
        }
        $this->version = trim($process->getOutput());

        $process = new Process('git log -n1 --pretty=%ci HEAD', __DIR__);
        if ($process->run() != 0) {
            throw new \RuntimeException("Can't run git log. You must ensure to run compile from the teleport git repository clone and that git binary is available.");
        }
        $date = new \DateTime(trim($process->getOutput()));
        $date->setTimezone(new \DateTimeZone('UTC'));
        $this->versionDate = $date->format('Y-m-d H:i:s');

        $process = new Process('git describe --tags HEAD');
        if ($process->run() == 0) {
            $this->version = trim($process->getOutput());
        }
    }

    /**
     * Prepare a phar object for adding files.
     *
     * @param string $name The filename for the phar.
     *
     * @return \Phar The phar object ready to add files to.
     */
    private function preparePhar($name)
    {
        /* start building the Phar */
        $phar = new \Phar($name, 0, $this->alias);
        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $phar->startBuffering();
        return $phar;
    }

    /**
     * Add php resources from the src/ directory.
     *
     * @param \Phar $phar
     */
    private function addSrc($phar)
    {
        if (file_exists($this->path . '/src')) {
            $src = new Finder();
            $src->files()->ignoreVCS(true)->ignoreDotFiles(true)->name('*.php')->notName('Compiler.php')->notName('Composer.php')->in($this->path . '/src');

            foreach ($src as $file) {
                $this->addFile($phar, $file);
            }
        }
    }

    /**
     * Add tpl.json and php resources from the tpl/ directory.
     *
     * @param \Phar $phar
     */
    private function addTpl($phar)
    {
        $json = new Finder();
        $json->files()->ignoreVCS(true)->ignoreDotFiles(true)->name('*.tpl.json')->in($this->path . '/tpl');

        foreach ($json as $file) {
            $this->addFile($phar, $file, false);
        }

        $php = new Finder();
        $php->files()->ignoreVCS(true)->ignoreDotFiles(true)->name('*.php')->in($this->path . '/tpl');

        foreach ($php as $file) {
            $this->addFile($phar, $file);
        }
    }

    /**
     * Add the Composer autoload infrastructure.
     *
     * @param \Phar $phar
     */
    private function addAutoload($phar)
    {
        $this->addFile($phar, new \SplFileInfo($this->path . '/vendor/autoload.php'));
        $this->addFile($phar, new \SplFileInfo($this->path . '/vendor/composer/autoload_classmap.php'));
        $this->addFile($phar, new \SplFileInfo($this->path . '/vendor/composer/autoload_files.php'));
        $this->addFile($phar, new \SplFileInfo($this->path . '/vendor/composer/autoload_namespaces.php'));
        $this->addFile($phar, new \SplFileInfo($this->path . '/vendor/composer/autoload_psr4.php'));
        $this->addFile($phar, new \SplFileInfo($this->path . '/vendor/composer/autoload_real.php'));
        if (file_exists($this->path . '/vendor/composer/include_paths.php')) {
            $this->addFile($phar, new \SplFileInfo($this->path . '/vendor/composer/include_paths.php'));
        }
        $this->addFile($phar, new \SplFileInfo($this->path . '/vendor/composer/ClassLoader.php'));

        /* add react/promise */
        $react = new Finder();
        $react->files()->ignoreVCS(true)->ignoreDotFiles(true)->name('*.php')->in($this->path . '/vendor/react/promise/src');
        foreach ($react as $file) {
            $this->addFile($phar, $file);
        }
    }

    /**
     * Add required dependencies.
     *
     * @param \Phar $phar
     */
    private function addDependencies($phar)
    {
        /* add teleport if this is a project using it as a library */
        if (file_exists($this->path . '/vendor/modxcms/teleport/src/Teleport/Teleport.php')) {
            $teleport = new Finder();
            $teleport->files()->ignoreVCS(true)->ignoreDotFiles(true)->name('*.php')->notName('Compiler.php')->notName('Composer.php')->in($this->path . '/vendor/modxcms/teleport/src');
            foreach ($teleport as $file) {
                $this->addFile($phar, $file);
            }
        }

        /* add symfony/filesystem */
        $filesystem = new Finder();
        $filesystem->files()->ignoreVCS(true)->ignoreDotFiles(true)->name('*.php')->in($this->path . '/vendor/symfony/filesystem');
        foreach ($filesystem as $file) {
            $this->addFile($phar, $file);
        }

        /* add symfony/finder */
        $finder = new Finder();
        $finder->files()->ignoreVCS(true)->ignoreDotFiles(true)->name('*.php')->in($this->path . '/vendor/symfony/finder');
        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }

        /* add react libs */
        $react = new Finder();
        $react->files()->ignoreVCS(true)->ignoreDotFiles(true)->name('*.php')->in($this->path . '/vendor/react/child-process');
        foreach ($react as $file) {
            $this->addFile($phar, $file);
        }
        $react = new Finder();
        $react->files()->ignoreVCS(true)->ignoreDotFiles(true)->name('*.php')->in($this->path . '/vendor/react/event-loop');
        foreach ($react as $file) {
            $this->addFile($phar, $file);
        }
        $react = new Finder();
        $react->files()->ignoreVCS(true)->ignoreDotFiles(true)->name('*.php')->in($this->path . '/vendor/react/http');
        foreach ($react as $file) {
            $this->addFile($phar, $file);
        }
        $psr7 = new Finder();
        $psr7->files()->ignoreVCS(true)->ignoreDotFiles(true)->name('*.php')->in($this->path . '/vendor/guzzlehttp/psr7/src');
        foreach ($psr7 as $file) {
            $this->addFile($phar, $file);
        }
        $react = new Finder();
        $react->files()->ignoreVCS(true)->ignoreDotFiles(true)->name('*.php')->in($this->path . '/vendor/react/promise/src');
        foreach ($react as $file) {
            $this->addFile($phar, $file);
        }
        $react = new Finder();
        $react->files()->ignoreVCS(true)->ignoreDotFiles(true)->name('*.php')->in($this->path . '/vendor/react/socket/src');
        foreach ($react as $file) {
            $this->addFile($phar, $file);
        }
        $react = new Finder();
        $react->files()->ignoreVCS(true)->ignoreDotFiles(true)->name('*.php')->in($this->path . '/vendor/react/stream/src');
        foreach ($react as $file) {
            $this->addFile($phar, $file);
        }

        $evenement = new Finder();
        $evenement->files()->ignoreVCS(true)->ignoreDotFiles(true)->name('*.php')->in($this->path . '/vendor/evenement/evenement/src');
        foreach ($evenement as $file) {
            $this->addFile($phar, $file);
        }
    }

    /**
     * Add the bin/teleport script.
     *
     * @param \Phar $phar
     */
    private function addTeleportBin($phar)
    {
        $content = file_get_contents($this->path . '/bin/teleport.php');
        $phar->addFromString('bin/teleport', $content);
    }

    /**
     * Get the stub code for the phar.
     *
     * @return string The stub PHP code.
     */
    private function getStub()
    {
        return <<<EOF
#!/usr/bin/env php
<?php
/**
 * This file is part of the teleport package.
 *
 * Copyright (c) MODX, LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Phar::mapPhar('{$this->alias}');
require 'phar://{$this->alias}/bin/teleport';

__HALT_COMPILER();
EOF;
    }

    /**
     * Add the LICENSE file if it exists.
     *
     * @param \Phar $phar
     */
    private function addLicense($phar)
    {
        if (is_readable($this->path . '/LICENSE')) {
            $this->addFile($phar, new \SplFileInfo($this->path . '/LICENSE'), false);
        }
    }

    /**
     * Commit all changes to and write the phar archive.
     *
     * @param \Phar $phar
     */
    private function writePhar($phar)
    {
        $phar->stopBuffering();
    }

    /**
     * Add a file to the Phar, optionally stripping whitespace.
     *
     * @param \Phar        $phar
     * @param \SplFileInfo $file
     * @param bool         $strip
     */
    private function addFile($phar, $file, $strip = true)
    {
        $path = str_replace($this->path . DIRECTORY_SEPARATOR, '', $file->getRealPath());

        $content = file_get_contents($file);
        if ($strip) {
            $content = $this->stripWhitespace($content);
        } elseif ('LICENSE' === basename($file)) {
            $content = "\n" . $content . "\n";
        }

        $content = str_replace('@version@', $this->version, $content);
        $content = str_replace('@versionDate@', $this->versionDate, $content);

        $phar->addFromString($path, $content);
    }

    /**
     * Removes whitespace from a PHP source string while preserving line numbers.
     *
     * @param  string $source A PHP string
     *
     * @return string The PHP string with the whitespace removed
     */
    private function stripWhitespace($source)
    {
        if (!function_exists('token_get_all')) {
            return $source;
        }

        $output = '';
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $output .= str_repeat("\n", substr_count($token[1], "\n"));
            } elseif (T_WHITESPACE === $token[0]) {
                // reduce wide spaces
                $whitespace = preg_replace('{[ \t]+}', ' ', $token[1]);
                // normalize newlines to \n
                $whitespace = preg_replace('{(?:\r\n|\r|\n)}', "\n", $whitespace);
                // trim leading spaces
                $whitespace = preg_replace('{\n +}', "\n", $whitespace);
                $output .= $whitespace;
            } else {
                $output .= $token[1];
            }
        }

        return $output;
    }
}
