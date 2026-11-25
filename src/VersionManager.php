<?php

namespace Machinjiri\Installer;

use Composer\InstalledVersions;

final class VersionManager
{
    public const RECOMMENDED_PHP_VERSION = '8.4.0';

    public static function installerVersion(): string
    {
        if (InstalledVersions::isInstalled('machinjiri/installer')) {
            return InstalledVersions::getPrettyVersion('machinjiri/installer') ?? 'dev';
        }

        return 'dev';
    }

    /**
     * @return array{minimum: string, installable: array<string, string>}
     */
    public static function installable(): array
    {
        return [
            'minimum' => '^2.3.5',
            'installable' => [
                '^2.3.5' => "Machinjiri v2.3.5"
            ]
        ];
    }

    public static function canInstall(string $version): bool
    {
        return array_key_exists($version, self::installable()['installable']);
    }

    public static function supportedDatabaseDrivers(): array 
    {
        return ['sqlite', 'mysql', 'pgsql'];
    }
}
