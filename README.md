# Machinjiri Installer

A Composer plugin and CLI installer for scaffolding new Machinjiri PHP applications quickly and consistently.

## What’s new

The installer has been expanded into a more robust project bootstrap workflow for Machinjiri applications. Recent improvements include:

- interactive project creation with guided prompts
- strict environment validation for PHP, Composer, required extensions, disk space, and writable directories
- safe install flows with `--dry-run`, `--force`, and `--keep-on-error`
- starter kit selection (`default` and `blog`)
- database template selection (`sqlite` and `mysql`)
- project version selection via `--m-version` and current Machinjiri compatibility checks
- optional Git initialization and initial commit support
- automatic `.env` generation with restricted permissions
- installation logging via `.installation.log` and a final summary/next-steps output
- compatibility with Composer plugin execution through the `machinjiri create` / `machinjiri new` commands

## Requirements

Before running the installer, make sure your environment meets these requirements:

- PHP 8.4 or newer
- Composer 2.x
- Required PHP extensions: `json`, `mbstring`, `openssl`, `zip`, `pdo`, `tokenizer`, and `ctype`

The current installer release targets Machinjiri framework versions in the `2.2.x` line and is prepared for the latest 8.4-compatible setup.

## Installation

### Global installation (recommended)

```bash
composer global require machinjiri/installer
```

### As a project dependency

```bash
composer require --dev machinjiri/installer
```

## Quick start

Create a new project interactively:

```bash
machinjiri create
```

Create a project directly from the command line:

```bash
machinjiri create my-app
```

You can also use the alias:

```bash
machinjiri new my-app
```

## Command options

The `create` command supports the following options:

```bash
machinjiri create [name] [options]
```

Options:

- `-f, --force` — overwrite an existing directory if it already exists
- `--m-version=VERSION` — install a specific Machinjiri framework version
- `--dev` — include development dependencies
- `--no-dev` — skip development dependencies
- `--no-scripts` — skip Composer scripts during install
- `--dry-run` — preview the installation without creating files
- `-n, --no-interaction` — skip prompts and use defaults
- `--git` — initialize a Git repository and make the initial commit
- `--starter=NAME` — choose a starter kit (`default` or `blog`)
- `--prefer-cache` — prefer Composer cache when available
- `--database=sqlite|mysql` — choose the database configuration template
- `--description=TEXT` — set the project description
- `--company=NAME` — set the company or organization name
- `--keep-on-error` — preserve a partially created project if installation fails
- `-v, --verbose` — show more detailed output

## Common examples

### 1. Interactive setup

```bash
machinjiri create
```

### 2. Install a specific Machinjiri framework version

```bash
machinjiri create my-app --m-version='^2.2.8'
```

### 3. Non-interactive install with SQLite

```bash
machinjiri create my-app --no-interaction --database=sqlite --starter=default
```

### 4. Create a blog starter app

```bash
machinjiri create my-blog --starter=blog --database=mysql
```

### 5. Preview installation without creating files

```bash
machinjiri create my-app --dry-run --verbose
```

### 6. Overwrite an existing directory

```bash
machinjiri create my-app --force
```

### 7. Keep partial files if setup fails

```bash
machinjiri create my-app --keep-on-error
```

## What the installer creates

A typical project scaffold includes:

```text
your-project/
├── app/
│   ├── Controllers/
│   ├── Jobs/
│   ├── Middleware/
│   ├── Models/
│   ├── Webhooks/
│   ├── Tasks/
│   └── Providers/
├── bootstrap/
│   └── app.php
├── config/
│   ├── core/
│   │   ├── app.php
│   │   ├── auth.php
│   │   └── routing.php
│   └── services/ 
│       └── providers.php
├── database/
│   ├── factories/
│   ├── migrations/
│   ├── seeders/
│   └── cache-prefetch-db.php
├── public/
│   ├── src/
│   │   ├── css/
│   │   └── js/
│   └── index.php
├── resources/
│   └── views/
├── routes/
├── storage/
├── tests/
├── artisan
├── composer.json
├── .env
└── phpunit.xml
```

The installer also generates starter-specific files based on the selected kit and writes the base configuration needed to start developing immediately.

## Starter kits

The installer currently supports:

- `default` — a general-purpose starter scaffold
- `blog` — a blog-oriented starter scaffold

Example:

```bash
machinjiri create my-blog --starter=blog
```

## Post-install notes

After a successful install:

- review the generated `.env` file and adjust any environment values
- inspect `.installation.log` if you need to debug a failed or unusual install
- start developing from the generated app skeleton

## Troubleshooting

### Command not found 

Make sure Composer is installed and available in your `PATH`.

1. Locate Your Composer Binary Directory
```bash
ls ~/.config/composer/vendor/bin
# OR
ls ~/.composer/vendor/bin
```

2. Update Your Shell Profile

If your path was .config/composer:bash

```bash
export PATH="$HOME/.config/composer/vendor/bin:$PATH"
```
Use code with caution.
If your path was .composer:bash
```bash
export PATH="$HOME/.composer/vendor/bin:$PATH"
```


### Missing PHP extensions

Install the required extensions before retrying:

```bash
# Ubuntu / Debian
sudo apt-get install php8.3-json php8.3-mbstring php8.3-openssl php8.3-zip

# CentOS / RHEL
sudo yum install php-json php-mbstring php-openssl

# macOS (Homebrew)
brew install php
```

### Existing directory error

If the target folder already exists, use `--force` to overwrite it:

```bash
machinjiri create my-app --force
```

### Permission denied

```bash
# Check directory permissions
ls -la /path/to/project

# Fix permissions (Linux/macOS)
sudo chown -R $USER:$USER /path/to/project
sudo chmod -R 755 /path/to/project/storage
```

### Composer install failed

```bash
# Check Composer version
composer --version

# Clear Composer cache
composer clear-cache

# Try with verbose output
machinjiri create my-app -vvv
```

## CI/CD usage

The installer supports non-interactive mode for automation:

```bash
# GitLab CI example
before_script:
  - composer global require machinjiri/installer
  - export PATH="$PATH:$HOME/.config/composer/vendor/bin:$PATH"
  - machinjiri create ${CI_PROJECT_NAME} --no-interaction --force --no-dev
  - cd ${CI_PROJECT_NAME}
  - composer install --no-dev --no-interaction
```

## Contributing

```bash
# Clone the repository
git clone https://github.com/mlangeni-group/installer.git
cd installer

# Install dependencies
composer install

# Smoke-test the installer locally
php bin/machinjiri create test-app --no-interaction --database=sqlite --starter=default
```

## License

This package is distributed under the MIT License.