<?php

namespace Machinjiri\Installer;

use Closure;
use RuntimeException;
use Machinjiri\Installer\Stubs\ConfigFiles;
use Machinjiri\Installer\Stubs\Database;
use Machinjiri\Installer\Stubs\Environment;
use Machinjiri\Installer\Stubs\Root;
use Machinjiri\Installer\Stubs\Publics;
use Machinjiri\Installer\Stubs\Bootstrap;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Installer
{
    private SymfonyStyle $io;
    private ?array $composerCommand;
    private string $projectName;
    private string $projectDir;
    private array $options;
    private ?Closure $progressCallback = null;
    private ?Closure $directoryCreatedCallback = null;
    private bool $verbose;
    private ProjectValidator $validator;
    private InstallerLogger $logger;
    private const MIN_DISK_SPACE_MB = 100;

    /**
     * @param list<string>|null $composerCommand
     */
    public function __construct(SymfonyStyle $io, bool $verbose = false, ?array $composerCommand = null)
    {
        $this->io = $io;
        $this->composerCommand = $composerCommand ?? ComposerExecutable::find();
        $this->verbose = $verbose;
    }

    public function install(string $projectName, array $options = []): void
    {
        $workingDirectory = getcwd();
        if ($workingDirectory === false) {
            throw new RuntimeException('Unable to determine the current working directory.');
        }

        $this->projectName = $projectName;
        $this->projectDir = rtrim($workingDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $projectName;
        $this->options = array_replace([
            'force' => false,
            'version' => VersionManager::installable()['minimum'],
            'no-interaction' => false,
            'dev' => true,
            'no-dev' => false,
            'no-scripts' => false,
            'dry-run' => false,
            'verbose' => $this->verbose,
            'starter' => 'default',
            'database' => 'sqlite',
            'description' => '',
            'company' => 'machinjiri',
            'keep-on-error' => false,
        ], $options);
        $this->validator = new ProjectValidator($this->io);
        $this->logger = new InstallerLogger($this->projectDir, $this->io, $this->options['verbose']);

        $this->progress(1, 'Validating system requirements...');
        $this->checkRequirements();
        $this->progress(2, 'Validating project configuration...');
        $this->validateProject($projectName);

        if ($this->options['dry-run']) {
            $this->logger->info("Dry run validated for project: {$projectName}");
            return;
        }

        $this->progress(3, 'Preparing project directory...');
        $this->prepareDirectory();
        $this->progress(4, 'Creating project structure...');
        $this->createProjectStructure();
        $this->progress(5, 'Creating project files...');
        $this->createFiles();
        $this->progress(6, 'Writing composer.json...');
        $this->writeComposerJson($projectName, $this->options['description']);
        $this->progress(7, 'Writing environment configuration...');
        $this->writeEnvironmentFile();
        $this->progress(8, 'Installing dependencies via Composer...');
        $this->runComposerInstall();
        $this->progress(9, 'Generating application key...');
        $this->generateAppKey();
        $this->progress(10, 'Generating database config...');
        $this->generateDatabaseConfig();
        $this->progress(11, 'Validating installation...');
        $this->validateInstallation();

        $this->logger->success('Installation completed successfully');
    }

    private function checkRequirements(): void
    {
        $this->validator->validatePhpVersion(VersionManager::RECOMMENDED_PHP_VERSION);
        $this->validator->validateExtensions(ProjectValidator::REQUIRED_EXTENSIONS);

        if ($this->composerCommand === null) {
            throw new RuntimeException('Composer is not installed or not found in PATH.');
        }

        ComposerExecutable::getVersion($this->composerCommand);
        $this->logger->info('All system requirements validated');
    }

    private function validateProject(string $projectName): void
    {
        $this->validator->validateProjectName($projectName);
        if (is_link($this->projectDir)) {
            throw new RuntimeException("The destination must not be a symbolic link: {$this->projectDir}");
        }
        if (file_exists($this->projectDir) && !is_dir($this->projectDir)) {
            throw new RuntimeException("The destination exists and is not a directory: {$this->projectDir}");
        }
        if (is_dir($this->projectDir) && !$this->options['force']) {
            throw new RuntimeException(
                "Directory {$this->projectDir} already exists. Use --force to overwrite."
            );
        }

        if (!is_string($this->options['version']) || trim($this->options['version']) === '') {
            throw new \InvalidArgumentException('A Machinjiri framework version is required.');
        }
        if (!in_array($this->options['database'], VersionManager::supportedDatabaseDrivers(), true)) {
            throw new \InvalidArgumentException('Database must be either "sqlite" or "mysql".');
        }
        if (!is_string($this->options['starter']) || !array_key_exists($this->options['starter'], StarterkitManager::startKits())) {
            throw new \InvalidArgumentException('The selected starter kit is not supported.');
        }
        if (!is_string($this->options['description']) || !is_string($this->options['company'])) {
            throw new \InvalidArgumentException('Project description and company name must be strings.');
        }
        $this->validator->validateDatabaseDriver($this->options['database']);

        $this->validator->validateDiskSpace($this->projectDir, self::MIN_DISK_SPACE_MB);
        $this->validator->validateWritePermissions($this->projectDir);

        $this->logger->info("Project validation passed for: {$projectName}");
    }

    private function prepareDirectory(): void
    {
        if (file_exists($this->projectDir) || is_link($this->projectDir)) {
            if (!is_dir($this->projectDir) || is_link($this->projectDir)) {
                throw new RuntimeException("The destination exists and is not a directory: {$this->projectDir}");
            }

            if (!$this->options['force']) {
                throw new RuntimeException(
                    "Directory {$this->projectDir} already exists. Use --force to overwrite."
                );
            }

            InstallationManager::removeDirectory($this->projectDir);
        }

        if (!mkdir($this->projectDir, 0755, true)) {
            throw new RuntimeException("Failed to create directory: {$this->projectDir}");
        }

        if ($this->directoryCreatedCallback !== null) {
            ($this->directoryCreatedCallback)();
        }
    }

    private function createProjectStructure(): void
    {
        $directories = [
          'bootstrap',
          'public',
          'public/src',
          'public/src/css',
          'public/src/js',
          'routes',
          'resources/views',
          'resources/views/layouts',
          'resources/views/partials',
          'database',
          'database/factories',
          'database/migrations',
          'database/seeders',
          'storage',
          'storage/app',
          'storage/uploads',
          'storage/framework',
          'storage/framework/queue',
          'storage/session',
          'storage/cache',
          'storage/logs',
          'app',
          'app/Http/Controllers',
          'app/Http/Middleware',
          'app/Models',
          'app/Providers',
          'app/Queue/Drivers',
          'tests/Unit',
          'tests/Features',
          'config',
          'config/services',
          'config/core',
        ];

        foreach ($directories as $directory) {
            $path = $this->projectDir . DIRECTORY_SEPARATOR . $directory;
            if (!is_dir($path)) {
                if (!mkdir($path, 0755, true) && !is_dir($path)) {
                    throw new RuntimeException("Failed to create directory: {$path}");
                }
            }
        }
    }

    private function writeComposerJson(string $projectName, string $description): void
    {
        $version = $this->options['version'];

        $vendor = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', $this->options['company']), '-'));
        $packageName = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', $projectName), '-'));
        $vendor = $vendor !== '' ? $vendor : 'machinjiri';
        $packageName = $packageName !== '' ? $packageName : 'machinjiri-app';
        
        $composerJson = [
            'name' => $vendor . '/' . $packageName,
            'description' => $description,
            'type' => 'project',
            'require' => [
                'php' => '^' . VersionManager::RECOMMENDED_PHP_VERSION,
                'machinjiri/framework' => $version,
            ],
            'require-dev' => [
                'phpunit/phpunit' => '^10.0',
            ],
            'autoload' => [
                'psr-4' => [
                    'App\\' => 'app/',
                    'Machinjiri\\Database\\' => 'database/',
                ],
            ],
            'autoload-dev' => [
                'psr-4' => [
                    'Machinjiri\\Tests\\' => 'tests/',
                ],
            ],
            'scripts' => [
                'test' => 'phpunit'
            ],
            'minimum-stability' => 'dev',
            'prefer-stable' => true,
        ];
    
        $json = json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $path = $this->projectDir . '/composer.json';
        if (file_put_contents($path, $json . PHP_EOL) === false) {
            throw new RuntimeException("Failed to write file: {$path}");
        }
    }
    
    private function validateInstallation(): void
    {
        $requiredFiles = [
          'bootstrap/app.php',
          'public/index.php',
          '.env',
          'composer.json',
          'composer.lock',
          'vendor/autoload.php',
        ];
        
        foreach ($requiredFiles as $file) {
            if (!file_exists($this->projectDir . '/' . $file)) {
                throw new \RuntimeException("Required file missing: {$file}");
            }
        }
    }

    public function setProgressCallback(callable $callback): void
    {
        $this->progressCallback = Closure::fromCallable($callback);
    }

    public function setDirectoryCreatedCallback(callable $callback): void
    {
        $this->directoryCreatedCallback = Closure::fromCallable($callback);
    }

    private function progress(int $step, string $message): void
    {
        if ($this->progressCallback) {
            ($this->progressCallback)($step, $message);
        }
    }

    private function writeEnvironmentFile(): void
    {
        $envPath = $this->projectDir . '/.env';
        $envContent = Environment::template($this->projectName);
        if (file_put_contents($envPath, $envContent) === false) {
            throw new RuntimeException("Failed to write file: {$envPath}");
        }

        if (!chmod($envPath, 0600)) {
            $this->logger->warning("Unable to restrict .env file permissions: {$envPath}");
        }

        $this->logger->info('.env file created');
    }

    private function runComposerInstall(): void
    {
        if ($this->composerCommand === null) {
            throw new RuntimeException('Composer is not installed or not found in PATH.');
        }

        $args = array_merge($this->composerCommand, [
            'install',
            '--no-interaction',
            '--prefer-dist',
            '--optimize-autoloader',
            '--quiet',
        ]);

        if ($this->options['no-dev'] ?? false) {
            $args[] = '--no-dev';
        }

        if ($this->options['no-scripts'] ?? false) {
            $args[] = '--no-scripts';
        }

        $process = new Process($args, $this->projectDir);
        $process->setTimeout(300);

        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->io->text($buffer);
            } else {
                $this->io->write($buffer);
            }
        });

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    private function generateAppKey(): void
    {
        $key = 'base64:' . base64_encode(random_bytes(32));
        $envPath = $this->projectDir . '/.env';
        
        if (!is_file($envPath)) {
            throw new RuntimeException("Environment file is missing: {$envPath}");
        }

        $envContent = file_get_contents($envPath);
        if ($envContent === false) {
            throw new RuntimeException("Unable to read environment file: {$envPath}");
        }
        $envContent = preg_replace('/^APP_KEY=.*$/m', "APP_KEY={$key}", $envContent);
        if ($envContent === null || file_put_contents($envPath, $envContent) === false) {
            throw new RuntimeException("Unable to write application key to environment file: {$envPath}");
        }
    }

    private function generateDatabaseConfig(): void
    {
        $envPath = $this->projectDir . '/.env';

        $config = match ($this->options['database']) {
            'sqlite' => Root::sqliteEnvTemplate(),
            'mysql' => Root::mysqlEnvTemplate(),
            'pgsql' => Root::pgsqlEnvTemplate(),
        };
        
        if (!is_file($envPath)) {
            throw new RuntimeException("Environment file is missing: {$envPath}");
        }

        $envContent = file_get_contents($envPath);
        if ($envContent === false) {
            throw new RuntimeException("Unable to read environment file: {$envPath}");
        }
        $envContent = preg_replace('/^DB_PLACEHOLDER=.*$/m', $config, $envContent);
        if ($envContent === null || file_put_contents($envPath, $envContent) === false) {
            throw new RuntimeException("Unable to write database configuration to environment file: {$envPath}");
        }
    }
    
    private function getTemplateData(): array
    {
        return [
            'version' => $this->options['version'],
            'project_name' => basename($this->projectDir),
            'date' => date('Y-m-d'),
        ];
    }

    private function createFiles(): void
    {
        $write = function (string $path, string $content, int $mode = 0644): void {
            if (file_exists($path) && !$this->options['force']) {
                return;
            }
            $dir = dirname($path);
            if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new RuntimeException("Failed to create directory for file: {$dir}");
            }
            if (file_put_contents($path, $content) === false) {
                throw new RuntimeException("Failed to write file: {$path}");
            }
            if (!chmod($path, $mode)) {
                $this->logger->warning("Unable to set permissions on generated file: {$path}");
            }
        };
        
        /* Starter kit Installation */
        (new StarterkitManager($this->projectDir))
        ->install($this->options['starter'], $write, $this->getTemplateData());
        
        /* Configuration files */
        $write($this->projectDir . '/config/services/providers.php', ConfigFiles::providersTemplate());
        $write($this->getCoreConfigDir() . '/app.php', ConfigFiles::appConfigTemplate());
        $write($this->getCoreConfigDir() . '/mail.php', ConfigFiles::mailConfigTemplate());
        $write($this->getCoreConfigDir() . '/database.php', ConfigFiles::databaseConfigTemplate($this->options['database']));
        $write($this->getCoreConfigDir() . '/cache.php', ConfigFiles::cacheConfigTemplate());
        $write($this->getCoreConfigDir() . '/queue.php', ConfigFiles::queueConfigFileTemplate());
        $write($this->getCoreConfigDir() . '/routing.php', ConfigFiles::routingConfigFileTemplate());
        $write($this->getCoreConfigDir() . '/filesystem.php', ConfigFiles::fileSystemConfigurationTemplate());
        $write($this->getCoreConfigDir() . '/auth.php', ConfigFiles::authConfigurationTemplate());
        $write($this->getCoreConfigDir() . '/oauth.php', ConfigFiles::OAuthConfigurationTemplate());
        $write($this->getCoreConfigDir() . '/ldap.php', ConfigFiles::ldapConfigurationTemplate());
        $write($this->getCoreConfigDir() . '/redis.php', ConfigFiles::redisConfigurationTemplate());
        $write($this->getCoreConfigDir() . '/sms.php', ConfigFiles::smsConfigurationTemplate());
        $write($this->getCoreConfigDir() . '/chat.php', ConfigFiles::chatConfigurations());
        
        /* Database Files */
        $write($this->projectDir . '/database/cache-prefetch-db.php', Database::dbCachePrefetchTemplate());
        
        /* App Bootstrap Files */
        $write($this->projectDir . '/bootstrap/app.php', Bootstrap::bootstrapTemplate(), 0444);
        
        /* Root Config Files */
        $write($this->projectDir . '/phpunit.xml', Root::phpunitTemplate());
        $write($this->projectDir . '/.htaccess', Root::rootHtaccess());
        $write($this->projectDir . '/.gitignore', Root::gitIgnoreTemplate());
        $write($this->projectDir . '/artisan', Root::artisanTemplate(), 0755);
        @chmod($this->projectDir . '/artisan', 0755);
        
        /* Public Entry Files */
        $write($this->projectDir . '/public/index.php', Publics::publicIndexTemplate(), 0444);
        
        $write($this->projectDir . '/public/.htaccess', Publics::publicHtaccess());
        if ($this->options['starter'] === 'default') {
            $write($this->projectDir . '/public/src/js/app.js', Publics::jsTemplate());
            $write($this->projectDir . '/public/src/css/app.css', Publics::cssTemplate());
        }
    }

    private function getCoreConfigDir(): string 
    {
        return $this->projectDir . "/config/core";
    }
  
}