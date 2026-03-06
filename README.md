# Filament Deploy Indicator

Show the current application environment (ENV) and optional latest deployment info in your Filament topbar.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/arnautdev/filament-deploy-indicator.svg?style=flat-square)](https://packagist.org/packages/arnautdev/filament-deploy-indicator)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/arnautdev/filament-deploy-indicator/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/arnautdev/filament-deploy-indicator/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/arnautdev/filament-deploy-indicator/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/arnautdev/filament-deploy-indicator/actions?query=workflow%3A%22Fix+PHP+code+styling%22+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/arnautdev/filament-deploy-indicator.svg?style=flat-square)](https://packagist.org/packages/arnautdev/filament-deploy-indicator)

## Features

- Shows current `APP_ENV` (mapped to a short label like `PROD`, `STAGE`, `LOCAL`) in the Filament topbar.
- Optional small hint next to the label: commit hash or deploy time.
- Reads deployment metadata from a JSON file (default: `storage/app/private/deploy-info.json`).
- Can auto-generate the JSON when missing (using `git`), if enabled.

## Requirements

- PHP ^8.2
- Filament ^4.0 or ^5.0

## Installation

Install the package via Composer:

```bash
composer require arnautdev/filament-deploy-indicator
```

## Register the plugin
Add the plugin to your panel:

```php
use Arnautdev\FilamentDeployIndicator\FilamentDeployIndicatorPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentDeployIndicatorPlugin::make(),
        ]);
}
```

## Conditional visibility

You can control who can see the deploy indicator by using the `->visible()` method when registering the plugin.

### Show only for admins

```php
use Arnautdev\FilamentDeployIndicator\FilamentDeployIndicatorPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentDeployIndicatorPlugin::make()
                ->visible(fn (): bool => auth()->user()?->is_admin === true),
        ]);
}
```

## Configuration
Publish the config file:

```php
php artisan vendor:publish --tag="filament-deploy-indicator-config"
```
Config file: config/filament-deploy-indicator.php

## Main options
| Option                       | Description                            |
| ---------------------------- | -------------------------------------- |
| `position`                   | Filament render hook position          |
| `cache_ttl`                  | Cache time in seconds                  |
| `file_path`                  | Path to deployment JSON                |
| `auto_generate_when_missing` | Generate JSON using git if missing     |
| `write_path`                 | Where generated JSON should be written |
| `git_root`                   | Root of the git repository             |
| `env_map`                    | Mapping of env → label + color         |
| `topbar.show`                | `null`, `commit`, or `deployed_at`     |


---
## Generate deployment info manually

The package provides an Artisan command to generate the deployment metadata JSON file.

```bash
php artisan filament-deploy-indicator:write
```

## Deployment JSON format
The plugin reads a JSON file like:
```json
{
  "environment": "local",
  "deployed_at": "2026-03-04 16:30:00",
  "commit": "33de817",
  "branch": "feature/deploy-indicator",
  "author": "You",
  "commit_message": "Local test"
}
```
Default location:
```
storage/app/private/deploy-info.json
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Dmitry Arnaut](https://github.com/arnautdev)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
