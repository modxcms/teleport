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
    
    public function handle(array $arguments)
    {
        $this->parseArguments($arguments);
        
        $start = microtime(true);

        $loop = \React\EventLoop\Factory::create();

        $process = new \React\ChildProcess\Process($this->getCLICommand());

        $request =& $this;
        $message = '';
        $process->on('exit', function($exitCode, $termSignal) use ($request, $start, &$message) {
            $request->results = $request->results + explode(PHP_EOL, rtrim($message, PHP_EOL));
            if ($request->args('debug') || $request->args('verbose')) {
                array_push($request->results, sprintf("request finished with exit code {$exitCode} in %2.4f seconds" . PHP_EOL, microtime(true) - $start));
            }
        });

        $loop->addTimer(0.001, function($timer) use ($request, $process, &$message) {
            $process->start($timer->getLoop());
            
            $process->stdout->on('data', function($output) use ($request, &$message) {
                $message .= $output;
            });
            $process->stderr->on('data', function($output) use ($request, &$message) {
                $message .= $output;
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
