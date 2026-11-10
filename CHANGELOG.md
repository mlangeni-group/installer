# Changelog

## 1.4.7 - 2026-11-10

### Added

- Redesigned the default starter kit welcome page as a responsive project dashboard with installation details and quick-start guidance.
- Added accessible light/dark theme switching that follows the operating-system preference by default and remembers the user's selection.
- Added Shields.io project, installer version, runtime, extensions, and license badges to the README.
- Expanded beginner and professional documentation, including setup examples, CLI reference, debugging guidance, and cross-platform `command not found` troubleshooting.

### Improved

- Escape generated project name, framework version, and installation date before including them in the welcome page.
- Updated the default starter kit styles for small screens, reduced-motion preferences, and consistent light/dark color palettes.

### Requirements

- PHP CLI 8.4 or newer
- Composer 2.x
- Required PHP extensions: json, mbstring, zip, openssl, pdo, tokenizer, and ctype

## 1.3.7 - 2026-08-09

### Highlights

This release strengthens installer reliability, validation, logging, and user experience for Machinjiri project creation.

### Added

- `ProjectValidator` for comprehensive project configuration validation
- `InstallationManager` with install-step orchestration and rollback support
- `InstallerLogger` with timestamped log levels, verbose output, and `.installation.log` generation
- `InstallationSummary` with post-install reporting and next-step guidance
- CLI options for `--dry-run`, `--verbose`, `--force`, `--no-dev`, and `--m-version`

### Improved

- More descriptive error messages and automatic failure logging
- Safer install flows with pre-install validation and cleanup on failure
- Clearer progress reporting and onboarding feedback
- Secure configuration handling with stricter `.env` permissions

### Requirements

- PHP 8.4 or newer
- Composer 2.x
- Required extensions: json, mbstring, openssl, zip, pdo, tokenizer, and ctype

### Upgrade Notes

To install or update to this version:

```bash
composer global require machinjiri/installer
```

Or, if you are using it as a project dependency:

```bash
composer require --dev machinjiri/installer
```

## 1.3.0 - 2026-07-22

### Highlights

This release focuses on improving the installer experience, clarity, and reliability for new Machinjiri project setups.

### Added

- A refreshed CLI banner with a cleaner Machinjiri presentation
- Integration of the installer logging system for better progress and error reporting
- Installation step management with rollback support for safer setup failures
- A post-install summary with quick-start guidance and next steps
- Expanded documentation and usage examples for common install scenarios

### Improved

- More polished command output and onboarding flow
- Better support for dry-run, verbose, and non-interactive installs
- Clearer project creation messaging and install guidance

### Requirements

- PHP 8.3 or newer
- Composer 2.x
- Required extensions: json, mbstring, openssl, zip, pdo, tokenizer, and ctype

### Upgrade Notes

To install or update to this version:

```bash
composer global require machinjiri/installer
```

Or, if you are using it as a project dependency:

```bash
composer require --dev machinjiri/installer
```
