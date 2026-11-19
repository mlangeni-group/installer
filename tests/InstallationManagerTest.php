<?php

namespace Machinjiri\Installer\Tests;

use Machinjiri\Installer\InstallationManager;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class InstallationManagerTest extends TestCase
{
    private string $temporaryDirectory;

    protected function setUp(): void
    {
        $temporaryDirectory = tempnam(sys_get_temp_dir(), 'machinjiri-installer-');
        self::assertNotFalse($temporaryDirectory);
        unlink($temporaryDirectory);
        mkdir($temporaryDirectory);
        $this->temporaryDirectory = $temporaryDirectory;
    }

    protected function tearDown(): void
    {
        if (is_dir($this->temporaryDirectory)) {
            InstallationManager::removeDirectory($this->temporaryDirectory);
        }
    }

    public function testFailedStepIsIncludedInRollback(): void
    {
        $partialProject = $this->temporaryDirectory . '/partial';
        $manager = new InstallationManager();
        $manager->registerStep(
            'scaffold',
            static function () use ($partialProject): void {
                mkdir($partialProject);
                file_put_contents($partialProject . '/partial.txt', 'partial');
                throw new RuntimeException('Scaffold failed.');
            },
            static function () use ($partialProject): void {
                InstallationManager::removeDirectory($partialProject);
            }
        );

        try {
            $manager->executeAll();
            self::fail('Expected the installation step to fail.');
        } catch (RuntimeException $exception) {
            self::assertSame('Scaffold failed.', $exception->getMessage());
        }

        self::assertDirectoryDoesNotExist($partialProject);
        self::assertSame([], $manager->getExecutedSteps());
    }

    public function testDirectoryRemovalDoesNotFollowSymbolicLinks(): void
    {
        $outsideDirectory = $this->temporaryDirectory . '/outside';
        $projectDirectory = $this->temporaryDirectory . '/project';
        mkdir($outsideDirectory);
        mkdir($projectDirectory);
        file_put_contents($outsideDirectory . '/keep.txt', 'keep');
        symlink($outsideDirectory, $projectDirectory . '/outside-link');

        self::assertTrue(InstallationManager::removeDirectory($projectDirectory));
        self::assertFileExists($outsideDirectory . '/keep.txt');
        self::assertSame('keep', file_get_contents($outsideDirectory . '/keep.txt'));
    }

    public function testProtectedDirectoriesCannotBeRemoved(): void
    {
        $this->expectException(RuntimeException::class);
        InstallationManager::removeDirectory(getcwd());
    }
}
