<?php

namespace Machinjiri\Installer;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use Machinjiri\Installer\Commands\ComposerInstallCommand;

class CommandProvider implements CommandProviderCapability
{
    public function getCommands(): array
    {
        return [
            new ComposerInstallCommand(),
        ];
    }
}