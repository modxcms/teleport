<?php
$start = microtime(true);

if (PHP_SAPI !== 'cli') {
    echo 'fatal: the teleport endpoint listener must be invoked via the CLI version of PHP; you are using the ' . PHP_SAPI . ' SAPI' . PHP_EOL;
    exit(E_USER_ERROR);
}

array_shift($argv);
$arg = function($idx = 1, $default = null) use ($argv) {
    if (is_array($argv)) {
        $current = 1;
        foreach ($argv as $arg) {
            if (preg_match('{^-}', $arg)) continue;
            if ($current === $idx) return $arg;
            $current++;
        }
    }
    return $default;
};

$opt = function($find, $default = false) use ($argv) {
    $value = $default;
    if (is_array($argv)) {
        $findPrefix = strlen($find) === 1 ? '-' : '--';
        $re = '{^' . $findPrefix . '(' . $find . ')=?(.*)?}';
        $matches = array();
        foreach ($argv as $opt) {
            if (preg_match($re, $opt, $matches)) {
                $value = true;
                if ($matches[2] !== '') {
                    $value = $matches[2];
                }
                break;
            }
        }
    }
    return $value;
};

if ($opt('debug')) {
    error_reporting(-1);
}

if (function_exists('ini_set')) {
    @ini_set('display_errors', 1);

    $memoryLimit = trim(ini_get('memory_limit'));

    if ($memoryLimit != -1) {
        $memoryInBytes = function ($value) {
            $unit = strtolower(substr($value, -1, 1));
            $value = (int)$value;
            switch ($unit) {
                case 'g':
                    $value *= 1024;
                case 'm':
                    $value *= 1024;
                case 'k':
                    $value *= 1024;
            }
            return $value;
        };

        // Increase memory_limit if it is lower than 512M
        if ($memoryInBytes($memoryLimit) < 512 * 1024 * 1024) {
            @ini_set('memory_limit', '512M');
        }
        unset($memoryInBytes);
    }
    unset($memoryLimit);
}

try {
    require_once __DIR__ . '/../src/bootstrap.php';

    $options = array(
        'debug' => $opt('debug'),
        'verbose' => $opt('verbose', true)
    );
    if (is_readable('config.php')) {
        $config = include 'config.php';
        if (is_array($config)) {
            $options = array_merge($config, $options);
        }
    }

    define('TELEPORT_BASE_PATH', rtrim($opt('base', getcwd()), '/') . '/');

    /** @var \Teleport\Beam\Endpoint $server */
    $server = \Teleport\Beam\Endpoint::instance($options);
    $server->run($arg(1, TELEPORT_BASE_PATH . 'profile/revo_22.profile.json'));

    printf("teleport endpoint listener stopped with exit code 0 in %2.4f seconds" . PHP_EOL, microtime(true) - $start);
    exit(0);
} catch (\Exception $e) {
    echo 'fatal: server error [' . get_class($e) . '] ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
    printf("teleport endpoint listener stopped with exit code {$e->getCode()} in %2.4f seconds" . PHP_EOL, microtime(true) - $start);
    exit($e->getCode());
}
