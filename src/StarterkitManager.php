<?php

namespace Machinjiri\Installer;

use InvalidArgumentException;
use Machinjiri\Installer\StarterKits\DefaultKit;
use Machinjiri\Installer\StarterKits\Blog;

final class StarterkitManager
{
    public function __construct(private ?string $projectDir = null)
    {
    }

    public function install(string $starterKit, callable $write, array $options = []): void
    {
        if ($this->projectDir === null || trim($this->projectDir) === '') {
            throw new InvalidArgumentException('A project directory is required to install a starter kit.');
        }

        $files = match (strtolower($starterKit)) {
            'default' => $this->defaultFiles($options),
            'blog' => Blog::files(),
            'minimal' => [],
            default => throw new InvalidArgumentException('Unknown starter kit: ' . $starterKit),
        };

        foreach ($files as $file) {
            $write($this->projectDir . $file['file'], $file['template']);
        }
    }

    public static function startKits(): array
    {
        return [
            'default' => 'Default Starter kit',
            'blog' => 'Blog Starter kit',
            'minimal' => 'Minimal Starter kit - Just the structure created',
        ];
    }

    private function defaultFiles(array $options): array
    {
        DefaultKit::getOptions($options);

        return DefaultKit::files();
    }
}