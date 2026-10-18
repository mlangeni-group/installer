<?php

namespace Machinjiri\Installer;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use Machinjiri\Installer\Commands\InstallCommand;

class CommandProvider implements CommandProviderCapability
{
    public function getCommands()
    {
        return [
            new InstallCommand(),
        ];
    }
}