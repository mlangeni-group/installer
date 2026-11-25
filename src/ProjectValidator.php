<?php

namespace Machinjiri\Installer;

use Symfony\Component\Console\Style\SymfonyStyle;

class ProjectValidator
{
    public const REQUIRED_EXTENSIONS = ['json', 'mbstring', 'zip', 'openssl', 'pdo', 'tokenizer', 'ctype'];

    private SymfonyStyle $io;

    public function __construct(SymfonyStyle $io)
    {
        $this->io = $io;
    }

    /**
     * Validate project name format
     * 
     * @param string $projectName
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function validateProjectName(string $projectName): bool
    {
        if (trim($projectName) === '') {
            throw new \InvalidArgumentException('Project name cannot be empty');
        }

        // Allow alphanumeric, dashes, underscores, and dots
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $projectName)) {
            throw new \InvalidArgumentException(
                "Project name '{$projectName}' contains invalid characters. " .
                "Only alphanumeric characters, dots, dashes, and underscores are allowed."
            );
        }

        // Check if name starts with alphanumeric
        if (!preg_match('/^[a-zA-Z0-9]/', $projectName)) {
            throw new \InvalidArgumentException(
                "Project name '{$projectName}' must start with an alphanumeric character."
            );
        }

        return true;
    }

    /**
     * Check available disk space
     * 
     * @param string $path
     * @param int $minimumMB Minimum required space in MB
     * @return bool
     * @throws \RuntimeException
     */
    public function validateDiskSpace(string $path, int $minimumMB = 100): bool
    {
        // Get parent directory if path doesn't exist yet
        $checkPath = $path;
        while (!is_dir($checkPath) && !is_file($checkPath)) {
            $parent = dirname($checkPath);
            if ($parent === $checkPath) {
                break;
            }
            $checkPath = $parent;
        }

        $freeSpace = disk_free_space($checkPath);

        if ($freeSpace === false) {
            throw new \RuntimeException('Unable to determine available disk space');
        }

        $freeMB = $freeSpace / (1024 * 1024);
        $minimumBytes = $minimumMB * 1024 * 1024;

        if ($freeSpace < $minimumBytes) {
            throw new \RuntimeException(
                "Insufficient disk space. Required: {$minimumMB}MB, Available: " . round($freeMB, 2) . "MB"
            );
        }

        return true;
    }

    /**
     * Validate directory is writable
     * 
     * @param string $path
     * @return bool
     * @throws \RuntimeException
     */
    public function validateWritePermissions(string $path): bool
    {
        $dir = is_dir($path) ? $path : dirname($path);

        // If directory doesn't exist, check parent
        while (!is_dir($dir) && $dir !== dirname($dir)) {
            $dir = dirname($dir);
        }

        if (!is_writable($dir)) {
            throw new \RuntimeException(
                "Directory is not writable: {$dir}. " .
                "Please check file permissions and try again."
            );
        }

        return true;
    }

    /**
     * Validate system extensions
     * 
     * @param array $extensions
     * @return bool
     * @throws \RuntimeException
     */
    public function validateExtensions(array $extensions): bool
    {
        $missing = [];

        foreach ($extensions as $extension) {
            if (!extension_loaded($extension)) {
                $missing[] = $extension;
            }
        }

        if (!empty($missing)) {
            throw new \RuntimeException(
                "Missing required PHP extensions: " . implode(', ', $missing)
            );
        }

        return true;
    }

    public function validateDatabaseDriver(string $database): bool
    {
        $extension = match ($database) {
            'sqlite' => 'pdo_sqlite',
            'mysql' => 'pdo_mysql',
            'pgsql' => 'pdo_pgsql',
            default => throw new \InvalidArgumentException("Unsupported database driver: {$database}"),
        };

        if (!extension_loaded($extension)) {
            throw new \RuntimeException(
                "The selected {$database} database requires the PHP extension: {$extension}"
            );
        }

        return true;
    }

    /**
     * Validate PHP version
     * 
     * @param string $minimumVersion
     * @return bool
     * @throws \RuntimeException
     */
    public function validatePhpVersion(string $minimumVersion = VersionManager::RECOMMENDED_PHP_VERSION): bool
    {
        if (!version_compare(PHP_VERSION, $minimumVersion, '>=')) {
            throw new \RuntimeException(
                "PHP version {$minimumVersion} or higher is required. " .
                "Current version: " . PHP_VERSION
            );
        }

        return true;
    }

    /**
     * Display validation summary
     * 
     * @param array $checks
     * @return void
     */
    public function displayValidationSummary(array $checks): void
    {
        $this->io->section('System Requirements Check');
        
        foreach ($checks as $name => $status) {
            if ($status) {
                $this->io->writeln("<fg=green>✓</> {$name}");
            } else {
                $this->io->writeln("<fg=red>Error</> {$name}");
            }
        }
    }
}
