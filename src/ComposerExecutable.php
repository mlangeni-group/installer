<?php

namespace Machinjiri\Installer;

use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

final class ComposerExecutable
{
    /**
     * @return list<string>|null
     */
    public static function find(?string $workingDirectory = null): ?array
    {
        $workingDirectory ??= getcwd();
        if ($workingDirectory === false) {
            return null;
        }

        $localPhar = $workingDirectory . DIRECTORY_SEPARATOR . 'composer.phar';
        if (is_file($localPhar)) {
            return [PHP_BINARY, $localPhar];
        }

        $localExecutable = $workingDirectory . DIRECTORY_SEPARATOR . 'composer';
        if (is_file($localExecutable) && is_executable($localExecutable)) {
            return [$localExecutable];
        }

        $executable = (new ExecutableFinder())->find('composer');

        return $executable === null ? null : [$executable];
    }

    /**
     * @param list<string> $command
     */
    public static function getVersion(array $command): string
    {
        $process = new Process(array_merge($command, ['--version']));
        $process->setTimeout(10);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException('Unable to run Composer: ' . trim($process->getErrorOutput()));
        }

        $versionOutput = trim($process->getOutput());
        if (!preg_match('/Composer version\s+(\d+)\./i', $versionOutput, $matches)) {
            throw new RuntimeException('Unable to determine the Composer version.');
        }

        if ((int) $matches[1] < 2) {
            throw new RuntimeException("Composer 2.x or newer is required. Detected: {$versionOutput}");
        }

        return $versionOutput;
    }
}
