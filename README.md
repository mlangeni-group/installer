# Machinjiri Installer

[![Machinjiri](https://img.shields.io/badge/Machinjiri-PHP%20Framework-6958d6?style=for-the-badge)](https://github.com/mlangeni-group/machinjiri)
[![Installer version](https://img.shields.io/badge/Installer-1.4.8-5543c3?logo=packagist&logoColor=white)](https://github.com/mlangeni-group/installer/releases)
[![PHP requirement](https://img.shields.io/badge/PHP-%E2%89%A5%208.4-777bb4?logo=php&logoColor=white)](https://www.php.net/)
[![Composer requirement](https://img.shields.io/badge/Composer-2.x-885630?logo=composer&logoColor=white)](https://getcomposer.org/)
[![Required extensions](https://img.shields.io/badge/PHP%20extensions-json%20%7C%20mbstring%20%7C%20zip-3572A5)](#requirements)
[![License](https://img.shields.io/badge/License-MIT-2f9e74.svg)](./LICENSE)

The Machinjiri Installer creates a ready-to-develop [Machinjiri](https://github.com/mlangeni-group/machinjiri) PHP application. Use the guided prompts if you are new to the framework, or pass options to make project setup repeatable.

# Framework Documentation
Documentation for Machinjiri Framework can be found at [Machinjiri Documentation](https://github.com/mlangeni-group/machinjiri/wiki)

## Choose your setup

- **New to Machinjiri?** Follow [Quick start](#quick-start) and answer the prompts.
- **Prefer the terminal?** Use [non-interactive setup](#non-interactive-setup).
- **Automating or maintaining projects?** See [Command reference](#command-reference), [Debugging](#debugging-an-installation), and [Troubleshooting](#troubleshooting).

## Requirements

The installer checks these requirements before creating a project:

| Requirement | Version / extensions |
| --- | --- |
| PHP CLI | 8.4 or newer |
| Composer | 2.x, available as `composer` in your `PATH` |
| PHP extensions | `json`, `mbstring`, `zip`, `openssl`, `pdo`, `tokenizer`, `ctype` |

Check your environment:

```bash
php --version
composer --version
php -m
```

The PHP CLI is the PHP executable used by your terminal. It can be different from the PHP version used by your web server.

## Install the installer

### Recommended: install globally

This makes the `machinjiri` command available from any directory (once Composer's global binary directory is on your `PATH`):

```bash
composer global require machinjiri/installer
```

If Composer asks whether to allow the Machinjiri Installer plugin, confirm to let Composer install and run it.

### Install in an existing Composer project

From the root of a Composer project:

```bash
composer require --dev machinjiri/installer
```

Run the local executable from that project directory:

```bash
vendor/bin/machinjiri create my-app
```

## Quick start

With the global installation, start the guided setup:

```bash
machinjiri create
```

Follow the prompts to choose a project name, Machinjiri version, database, development dependencies, and starter kit. The installer uses the current directory as the parent of the new project.

Or provide a project directory name:

```bash
machinjiri create my-app
```

When installation completes, enter the project directory and follow the quick-start instructions printed by the installer:

```bash
cd my-app
```

Review the generated `.env` file and update settings such as database credentials before deploying the application.

## Non-interactive setup

For CI, scripts, or repeatable local setup, use `--no-interaction` and pass any settings you want to override:

```bash
machinjiri create my-app \
  --no-interaction \
  --database=sqlite \
  --starter=default \
  --description="My Machinjiri application" \
  --company="Example team"
```

Without additional options, non-interactive mode uses the defaults: project name `machinjiri-app`, SQLite, development dependencies enabled, and the `default` starter kit.

## Starter kits

| Kit | Description |
| --- | --- |
| `default` | Welcome page, routes, controller, and responsive light/dark interface |
| `blog` | Blog-oriented starter page and assets |
| `minimal` | Project structure without a full starter interface |

Choose a kit interactively, or pass it as an option:

```bash
machinjiri create my-blog --starter=blog
```

## Command reference

```text
machinjiri create [options] [name]
machinjiri new [options] [name]
```

`new` is an alias for `create`. Use `machinjiri create --help` to see the options supported by your installed version.

| Option | Description |
| --- | --- |
| `-f`, `--force` | Continue if the destination directory already exists. |
| `--m-version=VERSION` | Select the Machinjiri framework version or constraint to install. |
| `--dev` | Install development dependencies (the interactive default). |
| `--no-dev` | Skip development dependencies. |
| `--no-scripts` | Skip Composer scripts during dependency installation. |
| `--dry-run` | Preview the installation without creating project files. |
| `-n`, `--no-interaction` | Do not ask questions; use defaults for unspecified settings. |
| `--git` | Initialize Git and create an initial commit. |
| `--starter=NAME` | Select `default`, `blog`, or `minimal`. |
| `--prefer-cache` | Prefer Composer's cache when installing dependencies. |
| `--database=NAME` | Select `sqlite` or `mysql`. |
| `--description=TEXT` | Set the project description. |
| `--company=NAME` | Set the company or organization name. |
| `--keep-on-error` | Keep a partially created directory if installation fails. |
| `-v`, `-vv`, `-vvv` / `--verbose` | Show more installation details; `-vvv` includes the stack trace on failure. |

### Examples

Preview an installation before creating files:

```bash
machinjiri create my-app --dry-run --verbose
```

Choose a framework version and MySQL:

```bash
machinjiri create my-app --m-version='^2.3.1' --database=mysql
```

Create a blog project and initialize Git:

```bash
machinjiri create my-blog --starter=blog --git
```

## What gets created

The installer generates an application scaffold with common directories for controllers, routes, views, configuration, database files, public assets, and tests. The selected starter kit adds its own views, routes, and assets. The exact files can vary by installer and framework version.

## Debugging an installation

If the installer exits with an error:

1. Re-run the command with verbose output:

   ```bash
   machinjiri create my-app --verbose
   ```

   For a full stack trace, use:

   ```bash
   machinjiri create my-app -vvv
   ```

2. Read the error message and verify the requirements above. You can check the terminal PHP executable and its extensions with:

   ```bash
   which php
   php --version
   php -m
   which composer
   composer --version
   ```

   On Windows PowerShell, use `Get-Command php` and `Get-Command composer` instead of `which`.

3. If dependency installation fails, run `composer diagnose` and check your network connection and Composer configuration.
4. If the installer reports that the destination already exists, choose another project name or use `--force` only if you intend to use that directory.
5. If you need to inspect a failed installation, re-run with `--keep-on-error` and review the generated files. By default, the installer removes a partially created project after a failure.

## Troubleshooting

### `machinjiri: command not found` (or “not recognized” on Windows)

This usually means the installer is not installed globally, or Composer's global executable directory is missing from your shell's `PATH`.

**1. Confirm Composer and the global package are installed:**

```bash
composer --version
composer global show machinjiri/installer
```

If Composer is missing, install it from [getcomposer.org](https://getcomposer.org/). If the package is not listed, install it:

```bash
composer global require machinjiri/installer
```

**2. Ask Composer where its global executables are installed:**

```bash
composer config --global --absolute bin-dir
```

Check that this directory contains `machinjiri` (or `machinjiri.bat` on Windows):

```bash
ls -la "$(composer config --global --absolute bin-dir)"
```

**3. Add that directory to your `PATH`:**

On macOS or Linux, add the following to your shell startup file (`~/.zshrc` for zsh or `~/.bashrc` for bash), then open a new terminal:

```bash
export PATH="$(composer config --global --absolute bin-dir):$PATH"
```

You can apply the change to the current terminal without reopening it by running the same command directly in that terminal.

On Windows, copy the path printed by `composer config --global --absolute bin-dir` and add it to your user `Path` in **Environment Variables**. Open a new PowerShell or Command Prompt afterward.

**4. Check whether the command is now discoverable:**

```bash
command -v machinjiri
machinjiri --version
```

In PowerShell, run `Get-Command machinjiri`.

If you need to run it before changing `PATH`, call the executable directly:

```bash
php "$(composer config --global --absolute bin-dir)/machinjiri" create my-app
```

If you installed the package locally instead of globally, run `vendor/bin/machinjiri create my-app` from the directory containing that project's `vendor` folder.

### `php` or `composer` is not found

Install PHP CLI or Composer, then make sure its executable is on your `PATH`. Open a new terminal after changing environment variables. Verify with:

```bash
php --version
composer --version
```

If multiple PHP installations are present, check `which php` (macOS/Linux) or `Get-Command php` (PowerShell) and ensure the selected CLI version is PHP 8.4 or newer.

### Missing PHP extensions

The installer prints the names of any missing extensions. Enable or install those extensions for the **CLI PHP** shown by `php --version`, then confirm they appear in `php -m`.

## Contributing

Issues and pull requests are welcome: [github.com/mlangeni-group/installer](https://github.com/mlangeni-group/installer). Please include the command you ran, your PHP and Composer versions, and the complete error output when reporting an installation problem.

## License

This project is released under the [MIT License](./LICENSE).