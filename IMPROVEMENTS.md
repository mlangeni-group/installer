# Machinjiri Installer: Improvements and Features

This document summarizes the installer capabilities and the improvements in version 1.4.8. For installation instructions and the complete CLI option list, see the [README](./README.md).

## Recent improvements

### Default starter kit

- Replaced the basic installation notice with a responsive welcome dashboard showing project details, readiness status, and first steps.
- Added light and dark themes. The page follows the operating-system preference unless the user chooses a theme, and saves that choice for later visits.
- Improved small-screen layouts, keyboard focus visibility, semantic page structure, and reduced-motion behavior.
- Escaped project-provided name, version, and date values before rendering them in the welcome view.

### Documentation

- Reorganized the README for both first-time users and developers who prefer scripted or non-interactive setup.
- Added Shields.io badges for Machinjiri, installer version, PHP and Composer requirements, extensions, and license.
- Added command examples, a CLI option reference, verbose debugging advice, and macOS/Linux/Windows instructions for resolving `machinjiri: command not found`.

## Installer capabilities

### Guided and scripted project creation

The `create` command prompts for project details and setup choices. The `new` command is an alias. For automation, pass a project name and `--no-interaction`, then provide any non-default values as options.

The installer supports:

- Machinjiri version selection with `--m-version`
- Development dependency selection with `--dev` or `--no-dev`
- `default`, `blog`, and `minimal` starter kits
- SQLite and MySQL database templates
- Optional Git initialization with `--git`
- Dry runs with `--dry-run`
- Composer cache preference with `--prefer-cache`
- Safe handling options for existing directories and failures: `--force` and `--keep-on-error`

Run `machinjiri create --help` for the installed command's complete option list.

### Validation and installation

Before creating an application, the command checks the PHP CLI version, required extensions, and Composer availability. The installer also validates project names, available disk space, and write permissions.

The scaffold process creates project directories and files, writes Composer and environment configuration, installs dependencies, generates an application key and database configuration, and checks that required project files were created.

### Logging and recovery

- `InstallationManager` runs registered installation steps and provides rollback callbacks.
- `InstallerLogger` records timestamped events in `.installation.log` in the target project directory. Verbose mode provides additional terminal output.
- Failed installations are cleaned up by default. Use `--keep-on-error` to retain the partially created project for investigation.
- Use `--verbose` or `-vvv` for more diagnostic output; the latter includes the exception stack trace.

### Post-install guidance

After a successful installation, the CLI reports the new project location and provides quick-start instructions. Review the generated `.env` file and configure the database and other environment-specific values before using the application.

## Requirements

The CLI currently checks for:

- PHP CLI 8.4 or newer
- Composer 2.x
- PHP extensions: `json`, `mbstring`, `zip`, `openssl`, `pdo`, `tokenizer`, and `ctype`

Use `php --version`, `php -m`, and `composer --version` to inspect the tools and extensions available to the terminal. The PHP CLI configuration may differ from the PHP installation used by a web server.

## Common diagnostic commands

Preview a setup without creating project files:

```bash
machinjiri create my-app --dry-run --verbose
```

Show the full stack trace for an installation failure:

```bash
machinjiri create my-app -vvv
```

Inspect the environment:

```bash
php --version
php -m
composer --version
composer diagnose
```

If `machinjiri` is not recognized by the shell, install the package globally and add Composer's global binary directory to `PATH`. Find that directory with:

```bash
composer config --global --absolute bin-dir
```

See the README's [command-not-found troubleshooting](./README.md#machinjiri-command-not-found-or-not-recognized-on-windows) for operating-system-specific steps.
