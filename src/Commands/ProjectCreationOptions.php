<?php

namespace Machinjiri\Installer\Commands;

use InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Machinjiri\Installer\ProjectValidator;
use Machinjiri\Installer\StarterkitManager;
use Machinjiri\Installer\VersionManager;

final readonly class ProjectCreationOptions
{
    public function __construct(
        public string $name,
        public string $description,
        public string $company,
        public string $frameworkVersion,
        public string $database,
        public bool $installDev,
        public string $starter,
        public bool $initializeGit,
        public bool $force,
        public bool $noScripts,
        public bool $dryRun,
        public bool $noInteraction,
        public bool $verbose,
        public bool $keepOnError,
        public bool $deprecatedPreferCache,
    ) {
    }

    public static function fromInput(InputInterface $input, SymfonyStyle $io, bool $verbose = false): self
    {
        $noInteraction = $input->getOption('no-interaction');
        $name = self::resolveName($input, $io, $noInteraction);
        $version = $input->getOption('m-version');
        $frameworkVersion = $input->getOption('framework-version');
        if ($version !== null && $frameworkVersion !== null) {
            throw new InvalidArgumentException('Use either --m-version or --framework-version, not both.');
        }

        $frameworkVersion ??= $version;
        if (($frameworkVersion === null || $frameworkVersion === '') && !$noInteraction) {
            $availableVersions = VersionManager::installable();
            $frameworkVersion = count($availableVersions['installable']) > 1
                ? $io->choice('Machinjiri framework version', $availableVersions['installable'], $availableVersions['minimum'])
                : $availableVersions['minimum'];
        } elseif ($frameworkVersion === null || $frameworkVersion === '') {
            $frameworkVersion = VersionManager::installable()['minimum'];
        }

        $description = $input->getOption('description');
        if ($description === null && !$noInteraction) {
            $description = $io->ask('Project description', 'A Machinjiri application');
        } else {
            $description ??= 'A Machinjiri application';
        }

        $company = $input->getOption('company');
        if ($company === null && !$noInteraction) {
            $company = $io->ask('Vendor or organization name', 'machinjiri');
        } else {
            $company ??= 'machinjiri';
        }

        $database = $input->getOption('database');
        if ($database === null && !$noInteraction) {
            $database = $io->choice('Database driver', ['sqlite' => 'SQLite', 'mysql' => 'MySQL'], 'sqlite');
        } else {
            $database ??= 'sqlite';
        }
        if (!in_array($database, ['sqlite', 'mysql'], true)) {
            throw new InvalidArgumentException('Database must be either "sqlite" or "mysql".');
        }

        if ($input->getOption('dev') && $input->getOption('no-dev')) {
            throw new InvalidArgumentException('Use either --dev or --no-dev, not both.');
        }
        $installDev = match (true) {
            $input->getOption('dev') => true,
            $input->getOption('no-dev') => false,
            $noInteraction => true,
            default => $io->confirm('Install development dependencies?', true),
        };

        $starter = $input->getOption('starter');
        if ($starter === null && !$noInteraction) {
            $starter = $io->choice('Starter kit', StarterkitManager::startKits(), 'default');
        } else {
            $starter ??= 'default';
        }
        if (!array_key_exists($starter, StarterkitManager::startKits())) {
            throw new InvalidArgumentException(
                'Unknown starter kit. Choose one of: ' . implode(', ', array_keys(StarterkitManager::startKits())) . '.'
            );
        }

        $projectValidator = new ProjectValidator($io);
        $projectValidator->validateProjectName($name);

        $dryRun = $input->getOption('dry-run');
        $initializeGit = $input->getOption('git');
        if (!$initializeGit && !$noInteraction && !$dryRun) {
            $initializeGit = $io->confirm('Initialize a Git repository and create an initial commit?', false);
        }

        return new self(
            name: $name,
            description: $description,
            company: $company,
            frameworkVersion: $frameworkVersion,
            database: $database,
            installDev: $installDev,
            starter: $starter,
            initializeGit: $initializeGit,
            force: $input->getOption('force'),
            noScripts: $input->getOption('no-scripts'),
            dryRun: $dryRun,
            noInteraction: $noInteraction,
            verbose: $verbose,
            keepOnError: $input->getOption('keep-on-error'),
            deprecatedPreferCache: $input->getOption('prefer-cache'),
        );
    }

    public function installerOptions(bool $force): array
    {
        return [
            'force' => $force,
            'version' => $this->frameworkVersion,
            'no-interaction' => $this->noInteraction,
            'dev' => $this->installDev,
            'no-dev' => !$this->installDev,
            'no-scripts' => $this->noScripts,
            'dry-run' => $this->dryRun,
            'verbose' => $this->verbose,
            'starter' => $this->starter,
            'database' => $this->database,
            'description' => $this->description,
            'company' => $this->company,
            'keep-on-error' => $this->keepOnError,
        ];
    }

    private static function resolveName(InputInterface $input, SymfonyStyle $io, bool $noInteraction): string
    {
        $argumentName = $input->getArgument('name');
        $optionName = $input->getOption('name');
        if ($argumentName !== null && $optionName !== null && $argumentName !== $optionName) {
            throw new InvalidArgumentException('Pass the project name either positionally or with --name, not both.');
        }

        $name = $optionName ?? $argumentName;
        if (($name === null || $name === '') && !$noInteraction) {
            $name = $io->ask('Project directory', 'machinjiri-app');
        } else {
            $name ??= 'machinjiri-app';
        }

        return $name;
    }
}
