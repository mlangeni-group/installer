<?php

namespace Machinjiri\Installer\Commands;

use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ComposerInstallCommand extends BaseCommand
{
    private InstallCommand $installCommand;

    public function __construct()
    {
        parent::__construct('create');
        $this->installCommand = new InstallCommand();
        $this->setDefinition($this->installCommand->getDefinition());
        $this->setAliases($this->installCommand->getAliases());
        $this->setDescription($this->installCommand->getDescription());
        $this->setHelp($this->installCommand->getHelp());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $definition = $this->installCommand->getDefinition();
        $parameters = [];
        foreach ($input->getArguments() as $name => $value) {
            if ($definition->hasArgument($name)) {
                $parameters[$name] = $value;
            }
        }
        foreach ($input->getOptions() as $name => $value) {
            if ($value !== null && $value !== false && $definition->hasOption($name)) {
                $parameters['--' . $name] = $value;
            }
        }

        $installInput = new ArrayInput($parameters, $definition);
        $installInput->setInteractive($input->isInteractive());

        return $this->installCommand->run($installInput, $output);
    }
}
