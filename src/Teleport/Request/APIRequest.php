<?php
/**
 * This file is part of the teleport package.
 *
 * Copyright (c) MODX, LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport\Request;

class APIRequest extends Request
{
    public function handle(array $arguments)
    {
        $this->parseArguments($arguments);
        
        $start = microtime(true);

        $loop = \React\EventLoop\Factory::create();

        $process = new \React\ChildProcess\Process($this->getCLICommand());

        $message = '';
        $request =& $this;
        $teleport = \Teleport\Teleport::instance();

        $process->on('exit', function($exitCode, $termSignal) use ($teleport, $request, $start, &$message) {
            $request->results = explode(PHP_EOL, rtrim($message, PHP_EOL));
            if ($request->args('debug') || $request->args('verbose')) {
                array_push($request->results, sprintf("request finished with exit code {$exitCode} in %2.4f seconds" . PHP_EOL, microtime(true) - $start));
            }
            if ($teleport->getConfig()->get('verbose', null, false) || $teleport->getConfig()->get('debug', null, false)) {
                echo sprintf("process finished with exit code {$exitCode} in %2.4f seconds" . PHP_EOL, microtime(true) - $start);
            }
        });

        $loop->addTimer(0.001, function($timer) use ($teleport, $request, $process, &$message) {
            if ($teleport->getConfig()->get('verbose', null, false) || $teleport->getConfig()->get('debug', null, false)) {
                echo "process started using cmd: {$process->getCommand()}" . PHP_EOL;
            }
            $process->start($timer->getLoop());

            $process->stdout->on('data', function($output) use ($teleport, $request, &$message) {
                $message .= $output;
                if ($teleport->getConfig()->get('verbose', null, false) || $teleport->getConfig()->get('debug', null, false)) {
                    echo $output;
                }
            });
            $process->stderr->on('data', function($output) use ($teleport, $request, &$message) {
                $message .= $output;
                if ($teleport->getConfig()->get('verbose', null, false) || $teleport->getConfig()->get('debug', null, false)) {
                    echo $output;
                }
            });
        });

        $loop->run();
    }

    protected function getCLICommand()
    {
        $command = "exec ";
        $command .= "php bin/teleport.php --action={$this->action} ";
        foreach ($this->args() as $argKey => $argVal) {
            if (is_bool($argVal)) {
                $command .= " --{$argKey}";
                $command .= $argVal !== true ? "=0 " : " "; 
            } elseif (is_string($argVal)) {
                $command .= " --{$argKey}=" . escapeshellarg($argVal) . " ";
            }
        }
        return trim($command);
    }
}
