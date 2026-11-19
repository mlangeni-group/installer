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
            'minimum' => '^2.2.6',
            'installable' => [
                '^2.2.6' => "Machinjiri v2.2.6",
                '^2.2.7' => "Machinjiri v2.2.7",
                '^2.2.8' => "Machinjiri v2.2.8",
                '^2.2.9' => "Machinjiri v2.2.9",
                '^2.3.1' => "Machinjiri v2.3.1",
                '^2.3.2' => "Machinjiri v2.3.2",
                '^2.3.3' => "Machinjiri v2.3.3",
            ]
        ];
    }

    public static function canInstall(string $version): bool
    {
        return array_key_exists($version, self::installable()['installable']);
    }
}
