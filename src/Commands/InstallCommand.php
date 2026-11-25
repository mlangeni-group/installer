<?php

namespace Machinjiri\Installer\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Machinjiri\Installer\ComposerExecutable;
use Machinjiri\Installer\InstallationManager;
use Machinjiri\Installer\InstallationSummary;
use Machinjiri\Installer\Installer;
use Machinjiri\Installer\InstallerLogger;
use Machinjiri\Installer\ProjectValidator;
use Machinjiri\Installer\StarterkitManager;
use Machinjiri\Installer\VersionManager;

class InstallCommand extends Command
{
    protected static $defaultName = 'create';
    protected static $defaultDescription = 'Create a Machinjiri project';

    /**
     * @param list<string>|null $composerCommand
     */
    public function __construct(private ?array $composerCommand = null)
    {
        parent::__construct(self::$defaultName);
        $this->setDescription(self::$defaultDescription);
        $this->setAliases(['new']);
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Create a Machinjiri application with an interactive setup wizard, or pass options for repeatable and automated installs.')
            ->addArgument('name', InputArgument::OPTIONAL, 'Project directory name (use --name with named commands)')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Project directory name')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Replace an existing destination directory')
            ->addOption('m-version', null, InputOption::VALUE_REQUIRED, 'Machinjiri framework version constraint')
            ->addOption('framework-version', null, InputOption::VALUE_REQUIRED, 'Alias for --m-version')
            ->addOption('dev', null, InputOption::VALUE_NONE, 'Install development dependencies')
            ->addOption('no-dev', null, InputOption::VALUE_NONE, 'Skip development dependencies')
            ->addOption('no-scripts', null, InputOption::VALUE_NONE, 'Skip Composer scripts during dependency installation')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Validate and preview without creating files or directories')
            ->addOption('no-interaction', 'n', InputOption::VALUE_NONE, 'Use defaults and do not prompt')
            ->addOption('git', null, InputOption::VALUE_NONE, 'Initialize Git and create an initial commit')
            ->addOption('starter', null, InputOption::VALUE_REQUIRED, 'Starter kit', null, array_keys(StarterkitManager::startKits()))
            ->addOption('prefer-cache', null, InputOption::VALUE_NONE, 'Deprecated compatibility option; Composer manages its cache automatically')
            ->addOption('database', null, InputOption::VALUE_REQUIRED, 'Database driver', null, VersionManager::supportedDatabaseDrivers())
            ->addOption('description', null, InputOption::VALUE_REQUIRED, 'Project description')
            ->addOption('company', null, InputOption::VALUE_REQUIRED, 'Company or organization name')
            ->addOption('keep-on-error', null, InputOption::VALUE_NONE, 'Keep a partially created directory if installation fails');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $isDryRun = $input->getOption('dry-run');
        $isVerbose = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;

        $this->displayWelcome($io, $output);

        if (!$this->checkEnvironment($io)) {
            return Command::FAILURE;
        }

        try {
            $configuration = ProjectCreationOptions::fromInput($input, $io, $isVerbose);
        } catch (\InvalidArgumentException $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }
        if ($configuration->deprecatedPreferCache) {
            $io->warning('--prefer-cache is deprecated and has no effect; Composer manages its cache automatically. It will be removed in the next major release.');
        }

        $workingDirectory = getcwd();
        if ($workingDirectory === false) {
            $io->error('Unable to determine the current working directory.');
            return Command::FAILURE;
        }
        $projectName = $configuration->name;
        $targetDir = $workingDirectory . DIRECTORY_SEPARATOR . $projectName;
        $logger = new InstallerLogger($targetDir, $io, $configuration->verbose);
        $manager = new InstallationManager();
        $projectDirectoryCreated = false;
        $force = $configuration->force;

        // Handle existing directory
        if (is_link($targetDir)) {
            $io->error("The destination must not be a symbolic link: {$targetDir}");
            return Command::FAILURE;
        }

        if ((is_file($targetDir) || is_link($targetDir)) && !is_dir($targetDir)) {
            $io->error("The destination exists and is not a directory: {$targetDir}");
            return Command::FAILURE;
        }

        if (!$force && is_dir($targetDir)) {
            if ($isDryRun) {
                $io->error("The destination directory already exists. Use --force to replace it.");
                return Command::FAILURE;
            }

            if (!$configuration->noInteraction) {
                $overwrite = $io->confirm("Directory '$projectName' already exists. Overwrite?", false);
                if (!$overwrite) {
                    $io->error('Installation aborted by user.');
                    return Command::FAILURE;
                }
                $force = true;
            } else {
                $io->error("Directory '$projectName' already exists. Use --force to overwrite.");
                return Command::FAILURE;
            }
        }

        $this->displayConfiguration($io, $configuration, $targetDir);

        if (!$configuration->noInteraction && !$isDryRun) {
            if (!$io->confirm('Create this project?', true)) {
                return Command::SUCCESS;
            }
        }

        $options = $configuration->installerOptions($force);

        try {
            if ($isDryRun) {
                $io->warning('Running in DRY-RUN mode. No files will be created.');
                $io->newLine();
            }

            $io->section('Creating your project');
            $spinner = $this->createSpinner($output, $isDryRun);
            $showSpinner = $output->isDecorated();

            $manager->registerStep('Install project', function () use ($io, $projectName, $options, $spinner, $showSpinner, $isDryRun, $isVerbose, $logger, &$projectDirectoryCreated): void {
                if ($showSpinner) {
                    $spinner->start();
                } else {
                    $io->text('Installing Machinjiri project...');
                }

                try {
                    $installer = new Installer($io, $isVerbose, $this->composerCommand);
                    $installer->setProgressCallback(function (int $step, string $message) use ($io, $spinner, $showSpinner): void {
                        $progressMessage = $step . ' - ' . $message;
                        if ($showSpinner) {
                            $spinner->setMessage($progressMessage);
                            $spinner->setProgress($step);
                        } else {
                            $io->text($progressMessage);
                        }
                    });
                    $installer->setDirectoryCreatedCallback(
                        static function () use (&$projectDirectoryCreated): void {
                            $projectDirectoryCreated = true;
                        }
                    );

                    $logger->info('Starting project scaffolding');
                    $installer->install($projectName, $options);
                } finally {
                    if ($showSpinner) {
                        $spinner->finish();
                    }
                    $io->newLine(2);
                }

                if ($isDryRun) {
                    $io->success("Dry-run completed successfully!");
                    $io->note("Run without --dry-run to create the project.");
                    return;
                }

                $logger->success('Project scaffolding completed');
            }, function () use ($targetDir, $logger, $options, &$projectDirectoryCreated): void {
                if (!$projectDirectoryCreated) {
                    return;
                }

                if ($options['keep-on-error']) {
                    $logger->warning("Retaining partial project for diagnosis: {$targetDir}");
                    return;
                }

                if (is_dir($targetDir)) {
                    InstallationManager::removeDirectory($targetDir);
                }
                $logger->warning('Rolled back partially created project files.');
            });

            $manager->executeAll();

            if ($isDryRun) {
                return Command::SUCCESS;
            }

            // Post-installation Git init
            if ($configuration->initializeGit) {
                $this->initializeGit($targetDir, $io);
            }

            $io->success($projectName . " created successfully");

            $summary = new InstallationSummary($targetDir, $projectName, $io);
            $summary->display();
            $summary->displayQuickStart();

            $logger->displaySummary();
            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            $logger->error($exception->getMessage(), $exception);
            if ($isVerbose) {
                $io->note("Full stack trace:");
                $io->writeln($exception->getTraceAsString());
            }
            return Command::FAILURE;
        }
    }

    private function checkEnvironment(SymfonyStyle $io): bool
    {
        $io->section('Checking your environment');

        // PHP version
        if (version_compare(PHP_VERSION, VersionManager::RECOMMENDED_PHP_VERSION, '<')) {
            $io->error("Machinjiri requires PHP " . VersionManager::RECOMMENDED_PHP_VERSION . " or higher. You have " . PHP_VERSION);
            return false;
        }

        // Required extensions
        $required = ProjectValidator::REQUIRED_EXTENSIONS;
        $missing = [];
        foreach ($required as $ext) {
            if (!extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }
        if (!empty($missing)) {
            $io->error("Missing required PHP extensions: " . implode(', ', $missing));
            return false;
        }
        $composerCommand = $this->composerCommand ?? ComposerExecutable::find();
        if ($composerCommand === null) {
            $io->error('Composer is not available. Please install Composer first.');
            return false;
        }

        try {
            $composerVersion = ComposerExecutable::getVersion($composerCommand);
        } catch (\Throwable $exception) {
            $io->error('Unable to run Composer: ' . $exception->getMessage());
            return false;
        }
        $this->composerCommand = $composerCommand;
        $io->table(
            ['Requirement', 'Status'],
            [
                ['PHP ' . VersionManager::RECOMMENDED_PHP_VERSION . '+', PHP_VERSION . ' — ready'],
                ['Required extensions', 'All present'],
                ['Composer 2+', $composerVersion],
            ]
        );
        return true;
    }

    private function createSpinner(OutputInterface $output, bool $dryRun): ProgressBar
    {
        $spinner = new ProgressBar($output);
        $spinner->setMaxSteps($dryRun ? 2 : 11);
        $spinner->setFormat(' %current%/%max% [%bar%] %message%');
        $spinner->setBarCharacter('<fg=green>⚬</>');
        $spinner->setEmptyBarCharacter(' ');
        $spinner->setProgressCharacter('➤');
        $spinner->setMessage('Preparing installation');
        return $spinner;
    }

    private function initializeGit(string $targetDir, SymfonyStyle $io): void
    {
        $io->writeln("\n<comment>Initializing Git repository...</comment>");
        $this->runGitCommand(['git', 'init'], $targetDir);

        // Create initial .gitignore if not present
        $gitignore = $targetDir . '/.gitignore';
        if (!file_exists($gitignore)) {
            if (file_put_contents($gitignore, "/vendor\n.env\n.DS_Store\n") === false) {
                throw new \RuntimeException("Unable to create Git ignore file: {$gitignore}");
            }
        }

        $this->runGitCommand(['git', 'add', '.'], $targetDir);
        $this->runGitCommand(
            ['git', 'commit', '-m', 'Initial commit from Machinjiri installer'],
            $targetDir
        );
        $io->success("Git repository initialized with initial commit.");
    }

    /**
     * @param list<string> $command
     */
    private function runGitCommand(array $command, string $workingDirectory): void
    {
        $process = new Process($command, $workingDirectory);
        $process->run();
        if (!$process->isSuccessful()) {
            $details = trim($process->getErrorOutput());
            if ($details === '') {
                $details = trim($process->getOutput());
            }
            throw new \RuntimeException(
                'Git command failed: ' . implode(' ', $command) . ($details !== '' ? "\n{$details}" : '')
            );
        }
    }

    private function displayWelcome(SymfonyStyle $io, OutputInterface $output): void
    {
        $io->title('Machinjiri project installer');
        $io->text([
            'Create a new application with a guided setup or a repeatable command.',
            '<info>machinjiri create --help</> shows all options.',
        ]);
        if ($output->isDecorated()) {
            $io->newLine();
        }
    }

    private function displayConfiguration(
        SymfonyStyle $io,
        ProjectCreationOptions $configuration,
        string $targetDirectory
    ): void {
        $io->section($configuration->dryRun ? 'Installation preview' : 'Review your project');
        $io->table(
            ['Setting', 'Value'],
            [
                ['Project', $configuration->name],
                ['Location', $targetDirectory],
                ['Framework', $configuration->frameworkVersion],
                ['Starter kit', $configuration->starter],
                ['Database', strtoupper($configuration->database)],
                ['Development dependencies', $configuration->installDev ? 'Yes' : 'No'],
                ['Git repository', $configuration->initializeGit ? 'Yes' : 'No'],
                ['Description', $configuration->description],
                ['Organization', $configuration->company],
            ]
        );
    }
}