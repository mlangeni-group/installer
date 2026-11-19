<?php

namespace Machinjiri\Installer;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use Throwable;

/**
 * Manages installation steps and provides rollback capability
 */
class InstallationManager
{
    private array $steps = [];
    private array $executedSteps = [];
    private array $rollbackErrors = [];

    /**
     * Register an installation step
     * 
     * @param string $name
     * @param callable $execute
     */
    public function registerStep(string $name, callable $execute, ?callable $rollback = null): self
    {
        $this->steps[$name] = [
            'execute' => $execute,
            'rollback' => $rollback,
            'executed' => false,
        ];

        return $this;
    }

    /**
     * Execute a specific step
     * 
     * @param string $name
     */
    public function executeStep(string $name): bool
    {
        if (!isset($this->steps[$name])) {
            throw new RuntimeException("Installation step not found: {$name}");
        }

        $step = $this->steps[$name];

        $this->steps[$name]['executed'] = true;
        $this->executedSteps[] = $name;
        call_user_func($step['execute']);

        return true;
    }

    /**
     * Execute all registered steps in order
     */
    public function executeAll(): bool
    {
        foreach (array_keys($this->steps) as $stepName) {
            try {
                $this->executeStep($stepName);
            } catch (Throwable $exception) {
                if (!$this->rollback()) {
                    $rollbackMessages = [];
                    foreach ($this->rollbackErrors as $step => $error) {
                        $rollbackMessages[] = "{$step}: {$error->getMessage()}";
                    }
                    throw new RuntimeException(
                        "Installation failed and rollback was incomplete: " . implode('; ', $rollbackMessages),
                        0,
                        $exception
                    );
                }

                throw $exception;
            }
        }

        return true;
    }

    /**
     * Rollback all executed steps in reverse order
     * 
     */
    public function rollback(): bool
    {
        $this->rollbackErrors = [];
        $stepsToRollback = array_reverse($this->executedSteps);

        foreach ($stepsToRollback as $stepName) {
            $step = $this->steps[$stepName];

            if ($step['rollback']) {
                try {
                    call_user_func($step['rollback']);
                } catch (Throwable $exception) {
                    $this->rollbackErrors[$stepName] = $exception;
                }
            }
        }

        $this->executedSteps = [];

        return $this->rollbackErrors === [];
    }

    /**
     * @return list<string>
     */
    public function getExecutedSteps(): array
    {
        return $this->executedSteps;
    }

    /**
     */
    public function getTotalSteps(): int
    {
        return count($this->steps);
    }

    /**
     */
    public static function removeDirectory(string $dir): bool
    {
        if (is_link($dir)) {
            if (!unlink($dir)) {
                throw new RuntimeException("Failed to remove installation link: {$dir}");
            }

            return true;
        }

        if (!is_dir($dir)) {
            return false;
        }

        $realDirectory = realpath($dir);
        $workingDirectory = getcwd();
        if (
            $realDirectory === false
            || dirname($realDirectory) === $realDirectory
            || $realDirectory === $workingDirectory
        ) {
            throw new RuntimeException("Refusing to remove a protected directory: {$dir}");
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            if (!$file instanceof SplFileInfo) {
                continue;
            }

            $path = $file->getPathname();
            $removed = $file->isLink() || !$file->isDir()
                ? unlink($path)
                : rmdir($path);

            if (!$removed) {
                throw new RuntimeException("Failed to remove installation path: {$path}");
            }
        }

        if (!rmdir($dir)) {
            throw new RuntimeException("Failed to remove installation directory: {$dir}");
        }

        return true;
    }

    /**
     */
    public static function createDirectoryRollback(string $dir): callable
    {
        return static function () use ($dir): void {
            self::removeDirectory($dir);
        };
    }

    /**
     */
    public static function createFileRollback(string $file): callable
    {
        return static function () use ($file): void {
            if (file_exists($file)) {
                if (!unlink($file)) {
                    throw new RuntimeException("Failed to remove installation file: {$file}");
                }
            }
        };
    }
}
