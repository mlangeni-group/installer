<?php

namespace Machinjiri\Installer;

use RuntimeException;
use Symfony\Component\Console\Style\SymfonyStyle;

class InstallerLogger
{
    private string $logFile;
    private SymfonyStyle $io;
    private array $logs = [];
    private bool $verbose;
    private int $persistedLogCount = 0;

    public function __construct(string $projectDir, SymfonyStyle $io, bool $verbose = false)
    {
        $this->logFile = $projectDir . DIRECTORY_SEPARATOR . '.installation.log';
        $this->io = $io;
        $this->verbose = $verbose;
    }

    public function info(string $message): void
    {
        $this->log('INFO', $message);
        if ($this->verbose) {
            $this->io->writeln("<fg=blue>ℹ</> {$message}");
        }
    }

    public function success(string $message): void
    {
        $this->log('SUCCESS', $message);
        if ($this->verbose) {
            $this->io->writeln("<fg=green>✓</> {$message}");
        }
    }

    public function warning(string $message): void
    {
        $this->log('WARNING', $message);
        $this->io->writeln("<fg=yellow>⚠</> {$message}");
    }

    public function error(string $message, ?\Throwable $exception = null): void
    {
        $this->log('ERROR', $message);
        $this->io->writeln("<fg=red>Error</> {$message}");

        if ($exception) {
            $this->logException($exception);
            if ($this->verbose) {
                $this->io->writeln("<fg=red>{$exception->getMessage()}</>");
            }
        }
    }

    public function logException(\Throwable $exception): void
    {
        $message = sprintf(
            "[%s] %s:%d - %s\nStacktrace:\n%s",
            get_class($exception),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getMessage(),
            $exception->getTraceAsString()
        );

        $this->log('EXCEPTION', $message);
    }

    private function log(string $level, string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] [{$level}] {$message}";

        $this->logs[] = $logEntry;

        if (!is_dir(dirname($this->logFile))) {
            return;
        }

        $pendingEntries = array_slice($this->logs, $this->persistedLogCount);
        if ($pendingEntries === []) {
            return;
        }

        $contents = implode(PHP_EOL, $pendingEntries) . PHP_EOL;
        if (file_put_contents($this->logFile, $contents, FILE_APPEND | LOCK_EX) === false) {
            throw new RuntimeException("Unable to write installation log: {$this->logFile}");
        }
        $this->persistedLogCount = count($this->logs);
    }

    public function getLogFile(): string
    {
        return $this->logFile;
    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    public function clear(): void
    {
        $this->logs = [];
        $this->persistedLogCount = 0;
        if (file_exists($this->logFile)) {
            if (!unlink($this->logFile)) {
                throw new RuntimeException("Unable to clear installation log: {$this->logFile}");
            }
        }
    }

    public function displaySummary(): void
    {
        if (file_exists($this->logFile)) {
            $this->io->writeln('');
            $this->io->note("Installation log saved to: {$this->logFile}");
        }
    }
}
