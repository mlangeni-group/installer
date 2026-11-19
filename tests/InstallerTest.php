<?php

namespace Machinjiri\Installer\Tests;

use Machinjiri\Installer\Commands\InstallCommand;
use Machinjiri\Installer\InstallationManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class InstallerTest extends TestCase
{
    private string $temporaryDirectory;
    private string $previousDirectory;
    private string $mockComposer;

    protected function setUp(): void
    {
        $this->previousDirectory = getcwd();
        $temporaryDirectory = tempnam(sys_get_temp_dir(), 'machinjiri-installer-');
        self::assertNotFalse($temporaryDirectory);
        unlink($temporaryDirectory);
        mkdir($temporaryDirectory);
        $this->temporaryDirectory = $temporaryDirectory;
        $this->mockComposer = $temporaryDirectory . '/composer';
        $this->writeComposerStub();
        chdir($this->temporaryDirectory);
    }

    protected function tearDown(): void
    {
        chdir($this->previousDirectory);
        if (is_dir($this->temporaryDirectory)) {
            InstallationManager::removeDirectory($this->temporaryDirectory);
        }
    }

    public function testDryRunDoesNotCreateOrReplaceProjectFiles(): void
    {
        $existingDirectory = $this->temporaryDirectory . '/existing';
        mkdir($existingDirectory);
        file_put_contents($existingDirectory . '/keep.txt', 'keep');

        $tester = new CommandTester(new InstallCommand([$this->mockComposer]));
        $status = $tester->execute([
            '--name' => 'existing',
            '--framework-version' => '^2.2.6',
            '--no-interaction' => true,
            '--dry-run' => true,
            '--force' => true,
            '--prefer-cache' => true,
            '--starter' => 'minimal',
            '--database' => 'sqlite',
        ]);

        self::assertSame(0, $status, $tester->getDisplay());
        self::assertSame('keep', file_get_contents($existingDirectory . '/keep.txt'));
        self::assertStringContainsString('Dry-run completed successfully', $tester->getDisplay());
        self::assertStringContainsString('--prefer-cache is deprecated', $tester->getDisplay());
    }

    public function testCommandCreatesCompatibleDefaultScaffold(): void
    {
        $tester = new CommandTester(new InstallCommand([$this->mockComposer]));
        $status = $tester->execute([
            'name' => 'smoke-app',
            '--no-interaction' => true,
            '--starter' => 'default',
            '--database' => 'sqlite',
            '--description' => 'Smoke test project',
            '--company' => 'Example Team',
        ]);

        $projectDirectory = $this->temporaryDirectory . '/smoke-app';
        self::assertSame(0, $status, $tester->getDisplay());

        $composer = json_decode(
            file_get_contents($projectDirectory . '/composer.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        self::assertSame('example-team/smoke-app', $composer['name']);
        self::assertArrayHasKey('Machinjiri\\Database\\', $composer['autoload']['psr-4']);
        self::assertFileExists($projectDirectory . '/resources/views/welcome.view.php');
        self::assertFileExists($projectDirectory . '/vendor/autoload.php');
        self::assertFileExists($projectDirectory . '/composer.lock');
        self::assertStringContainsString('DB_CONNECTION=sqlite', file_get_contents($projectDirectory . '/.env'));
        self::assertMatchesRegularExpression('/^APP_KEY=base64:/m', file_get_contents($projectDirectory . '/.env'));
        self::assertSame(0600, fileperms($projectDirectory . '/.env') & 0777);
    }

    public function testFailedInstallCleansUpUnlessKeepOnErrorIsSet(): void
    {
        $this->writeComposerStub(failInstall: true);

        foreach ([false => 'cleanup-app', true => 'keep-app'] as $keepOnError => $projectName) {
            $tester = new CommandTester(new InstallCommand([$this->mockComposer]));
            $arguments = [
                'name' => $projectName,
                '--no-interaction' => true,
                '--starter' => 'minimal',
                '--database' => 'sqlite',
                '--description' => 'Failure test',
                '--company' => 'Example Team',
            ];
            if ($keepOnError) {
                $arguments['--keep-on-error'] = true;
            }

            self::assertSame(1, $tester->execute($arguments));
            $projectDirectory = $this->temporaryDirectory . '/' . $projectName;
            self::assertSame((bool) $keepOnError, is_dir($projectDirectory));
        }
    }

    private function writeComposerStub(bool $failInstall = false): void
    {
        $installBehavior = $failInstall
            ? "fwrite(STDERR, \"Simulated Composer failure\\n\");\n    exit(23);"
            : "mkdir('vendor', 0755, true);\n    file_put_contents('vendor/autoload.php', \"<?php\\n\");\n    file_put_contents('composer.lock', \"{}\\n\");\n    exit(0);";
        $script = "#!" . PHP_BINARY . "\n<?php\n"
            . "if ((\$argv[1] ?? '') === '--version') { echo \"Composer version 2.10.3 test\\n\"; exit(0); }\n"
            . "if ((\$argv[1] ?? '') === 'install') { {$installBehavior} }\n"
            . "exit(2);\n";

        file_put_contents($this->mockComposer, $script);
        chmod($this->mockComposer, 0755);
    }
}
