<?php

namespace Machinjiri\Installer\Tests;

use Machinjiri\Installer\VersionManager;
use PHPUnit\Framework\TestCase;

final class VersionManagerTest extends TestCase
{
    public function testKnownFrameworkVersionsAreInstallable(): void
    {
        self::assertTrue(VersionManager::canInstall('^2.2.6'));
        self::assertTrue(VersionManager::canInstall('^2.3.2'));
        self::assertFalse(VersionManager::canInstall('^99.0'));
    }

    public function testInstallerVersionComesFromComposerRuntimeMetadata(): void
    {
        self::assertSame(
            \Composer\InstalledVersions::getPrettyVersion('machinjiri/installer') ?? 'dev',
            VersionManager::installerVersion()
        );
    }
}
