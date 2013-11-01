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
    private $version;
    private $versionDate;

    /**
     * Compile Teleport into a Phar for distribution.
     *
     * @param string $into The name of the phar to build.
     *
     * @throws \RuntimeException If git execution fails to get the version information.
     */
    public function compile($into = 'teleport.phar')
    {
        if (file_exists($into)) {
            unlink($into);
        }

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

        echo "building {$into} version {$this->version} ({$this->versionDate})" . PHP_EOL;

        /* start building the Phar */
        $phar = new \Phar($into, 0, 'teleport.phar');
        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $phar->startBuffering();

        /* add src files */
        $src = new Finder();
        $src->files()->ignoreVCS(true)->ignoreDotFiles(true)->name('*.php')->notName('Compiler.php')->in(__DIR__ . '/..');

        foreach ($src as $file) {
            $this->addFile($phar, $file);
        }

        /* add tpl files */
        $tpl = new Finder();
        $tpl->files()->ignoreVCS(true)->ignoreDotFiles(true)->name('*.php')->name('*.tpl.json')->in(__DIR__ . '/../../tpl');

        foreach ($tpl as $file) {
            $this->addFile($phar, $file);
        }

        /* add composer autoloading infrastructure */
        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../../vendor/autoload.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../../vendor/composer/autoload_namespaces.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../../vendor/composer/autoload_classmap.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../../vendor/composer/autoload_real.php'));
        if (file_exists(__DIR__ . '/../../vendor/composer/include_paths.php')) {
            $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../../vendor/composer/include_paths.php'));
        }
        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../../vendor/composer/ClassLoader.php'));
        $this->addTeleportBin($phar);

        /* set the stub */
        $phar->setStub($this->getStub());

        $phar->stopBuffering();

        /* add LICENSE */
        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../../LICENSE'), false);

        unset($phar);
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
        $path = str_replace(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR, '', $file->getRealPath());

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
     * Add the bin/teleport script.
     *
     * @param \Phar $phar
     */
    private function addTeleportBin($phar)
    {
        $content = file_get_contents(__DIR__ . '/../../bin/teleport');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
        $phar->addFromString('bin/teleport', $content);
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

    private function getStub()
    {
        $stub = <<<'EOF'
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

Phar::mapPhar('teleport.phar');

EOF;

        // add warning once the phar is older than 30 days
        if (preg_match('{^[a-f0-9]+$}', $this->version)) {
            $warningTime = time() + 30 * 86400;
            $stub .= "define('COMPOSER_DEV_WARNING_TIME', $warningTime);\n";
        }

        return $stub . <<<'EOF'
require 'phar://teleport.phar/bin/teleport';

__HALT_COMPILER();
EOF;
    }
} 
