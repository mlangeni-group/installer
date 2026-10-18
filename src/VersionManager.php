<?php

namespace Machinjiri\Installer;

class VersionManager {

    public const INSTALLER_VERSION = "1.4.3";
    public const RECOMMENDED_PHP_VERSION = "8.4.0";

    public static function installable (): array
    {
        return [
            'minimum' => '^2.2.6', // minimum installable machinjiri version
            'installable' => [
                '^2.2.6' => "machinjiri v2.2.6",
                '^2.2.7' => "machinjiri v2.2.7",
                '^2.2.8' => "machinjiri v2.2.8"
            ]
        ];
    }

    public static function canInstall(string $version): bool 
    {
        return in_array($version, self::installable());
    }
    
}
