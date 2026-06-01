<?php

namespace App\Services\Docker;

use App\Exceptions\DockerControlException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class DockerProcessRunner
{
    public function run(DockerStackConfig $stack, string $dockerArgs): string
    {
        if (! $stack->isConfigured()) {
            throw new DockerControlException($stack->configurationHint() ?? 'Docker stack is not configured.');
        }

        $binary = escapeshellcmd((string) config('docker.docker_binary', 'docker'));
        $inner = $binary.' '.$dockerArgs;

        if ($stack->usesSsh()) {
            $remote = 'cd '.escapeshellarg($stack->workDir).' && '.$inner;
            $command = [
                'ssh',
                '-o', 'BatchMode=yes',
                '-o', 'StrictHostKeyChecking=no',
                '-o', 'ConnectTimeout=10',
                '-i', $stack->sshKey,
                $stack->sshUser.'@'.$stack->sshHost,
                $remote,
            ];
            $cwd = null;
        } else {
            $command = $inner;
            $cwd = $stack->workDir !== '' ? $stack->workDir : null;
        }

        $process = is_array($command)
            ? new Process($command, null, null, null, (float) config('docker.timeout', 45))
            : Process::fromShellCommandline($command, $cwd, null, null, (float) config('docker.timeout', 45));

        try {
            $process->run();
        } catch (ProcessTimedOutException) {
            throw new DockerControlException('Docker command timed out.');
        }

        if (! $process->isSuccessful()) {
            $error = trim($process->getErrorOutput() ?: $process->getOutput());

            throw new DockerControlException($error !== '' ? $error : 'Docker command failed.');
        }

        return trim($process->getOutput());
    }
}
