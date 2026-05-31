# Filament Deploy Indicator

Show the current application environment (ENV) and optional latest deployment info in your Filament topbar.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/arnautdev/filament-deploy-indicator.svg?style=flat-square)](https://packagist.org/packages/arnautdev/filament-deploy-indicator)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/arnautdev/filament-deploy-indicator/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/arnautdev/filament-deploy-indicator/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/arnautdev/filament-deploy-indicator/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/arnautdev/filament-deploy-indicator/actions?query=workflow%3A%22Fix+PHP+code+styling%22+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/arnautdev/filament-deploy-indicator.svg?style=flat-square)](https://packagist.org/packages/arnautdev/filament-deploy-indicator)

## Features

- Shows current `APP_ENV` (mapped to a short label like `PROD`, `STAGE`, `LOCAL`) with a color-coded badge.
- Optional hint next to the label: commit hash, deploy time, git tag, or branch name.
- Click the badge to see full deployment info: commit, branch, author, message, tag, deploy time.
- Copy commit hash to clipboard with one click.
- Reads deployment metadata from a JSON file (default: `storage/app/private/deploy-info.json`).
- Can auto-generate the JSON from git on first request, or generate it during deployment via Artisan command.
- Records every deploy to an append-only history log and shows the last few in the dropdown.

## Requirements

- PHP ^8.2
- Filament ^4.0 or ^5.0

## Installation

```bash
composer require arnautdev/filament-deploy-indicator
```

## Register the plugin

Add the plugin to your panel provider:

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

Show the indicator only to specific users:

```php
FilamentDeployIndicatorPlugin::make()
    ->visible(fn (): bool => auth()->user()?->is_admin === true),
```

## Configuration

Everything is configurable two ways: publish the config file, or chain fluent
methods on the plugin (Filament style). Fluent methods override the published
config at runtime.

### Fluent configuration

```php
FilamentDeployIndicatorPlugin::make()
    ->setPosition('panels::topbar.start')        // render hook position
    ->setCacheTtl(300)                            // cache seconds
    ->setFilePath(storage_path('app/deploy.json'))// read path
    ->setWritePath(storage_path('app/deploy.json'))// write path (auto-gen / command)
    ->setGitRoot(base_path())                     // git repo root
    ->setAutoGenerateWhenMissing(true)            // generate from git if missing

    // ENV badge
    ->setDefaultLabel('ENV')                      // fallback label
    ->setDefaultColor('gray')                     // fallback color
    ->setEnvMap([                                 // replace whole map
        'production' => ['label' => 'PROD', 'color' => 'danger'],
    ])
    ->mapEnv('qa', 'QA', 'warning')               // add/override one env

    // Topbar hint
    ->setTopbarHint('commit')                     // null|commit|deployed_at|tag|branch
    ->setCommitLength(7)                           // commit hash chars
    ->setDateFormat('d.m H:i')                     // deployed_at format

    // Deploy history
    ->setHistoryEnabled(true)
    ->setHistoryPath(storage_path('app/private/deploy-history.jsonl'))
    ->setHistoryMaxEntries(100)
    ->setHistoryShowInDropdown(5)

    // Conditional visibility
    ->visible(fn (): bool => auth()->user()?->is_admin === true),
```

| Method                       | Config key                    |
| ---------------------------- | ----------------------------- |
| `setPosition()`              | `position`                    |
| `setCacheTtl()`              | `cache_ttl`                   |
| `setFilePath()`              | `file_path`                   |
| `setWritePath()`             | `write_path`                  |
| `setGitRoot()`               | `git_root`                    |
| `setAutoGenerateWhenMissing()` | `auto_generate_when_missing` |
| `setDefaultLabel()`          | `default.label`               |
| `setDefaultColor()`          | `default.color`               |
| `setEnvMap()`                | `env_map`                     |
| `mapEnv()`                   | `env_map.{env}`               |
| `setTopbarHint()`            | `topbar.show`                 |
| `setCommitLength()`          | `topbar.commit_length`        |
| `setDateFormat()`            | `topbar.date_format`          |
| `setHistoryEnabled()`        | `history.enabled`             |
| `setHistoryPath()`           | `history.path`                |
| `setHistoryMaxEntries()`     | `history.max_entries`         |
| `setHistoryShowInDropdown()` | `history.show_in_dropdown`    |
| `visible()`                  | (runtime only)                |

### Published config file

```bash
php artisan vendor:publish --tag="filament-deploy-indicator-config"
```

### Main options

| Option                       | Default                              | Description                                        |
| ---------------------------- | ------------------------------------ | -------------------------------------------------- |
| `position`                   | `GLOBAL_SEARCH_BEFORE`               | Filament render hook position                      |
| `cache_ttl`                  | `30`                                 | Cache time in seconds                              |
| `file_path`                  | `storage/app/private/deploy-info.json` | Path to read deployment JSON from               |
| `write_path`                 | `null` (falls back to `file_path`)   | Path to write generated JSON to                    |
| `auto_generate_when_missing` | `true`                               | Generate JSON using git if file is missing         |
| `git_root`                   | `base_path()`                        | Root of the git repository (env: `DEPLOY_INDICATOR_GIT_ROOT`) |
| `env_map`                    | See config                           | Mapping of environment → label + Filament color    |
| `topbar.show`                | `'commit'`                           | `null`, `'commit'`, `'deployed_at'`, `'tag'`, `'branch'` |
| `topbar.commit_length`       | `7`                                  | Number of commit hash characters to show           |
| `topbar.date_format`         | `'d.m H:i'`                          | PHP date format for `deployed_at` hint             |
| `history.enabled`            | `true`                               | Record deploys to an append-only history log       |
| `history.path`               | `storage/app/private/deploy-history.jsonl` | Path of the JSONL history file               |
| `history.max_entries`        | `100`                                | How many entries to keep (older ones are trimmed)  |
| `history.show_in_dropdown`   | `5`                                  | How many recent deploys to show in the dropdown    |

---

## Generating deployment info

The plugin reads deployment metadata from a JSON file. There are two ways to generate it.

### Option 1: During deployment (recommended)

Run the command as part of your deployment pipeline. Git data (commit, branch, author, message) is read automatically. This also gives accurate deployment timestamps.

```bash
php artisan deploy-indicator:write --env=production
```

Any option you pass overrides the git value. For example, to set a custom author:

```bash
php artisan deploy-indicator:write \
  --env=production \
  --author="CI Bot" \
  --deployed-at="$(date '+%Y-%m-%d %H:%M:%S')"
```

### Option 2: Auto-generate on first request

Set `auto_generate_when_missing = true` in config (default). The JSON will be generated from git automatically on the first request if the file is missing. Useful for local development.

---

## Deploy history

Every time deploy info is written (via `deploy-indicator:write` or auto-generate), the package appends a snapshot to an append-only JSONL log at `storage/app/private/deploy-history.jsonl`.

- **Deduped by commit hash** — running the command twice for the same commit does not create duplicate entries.
- **Retention** — capped at `history.max_entries` (default `100`). Older entries are trimmed automatically.
- **Shown in the dropdown** — under the current deploy info, a "Recent deploys" section lists the last `history.show_in_dropdown` entries (default `5`) in `commit · author · deployed_at` format.
- **Disable** — set `history.enabled` to `false` in config.

Each line in the JSONL file is a self-contained JSON object, e.g.:

```json
{"environment":"production","deployed_at":"2026-04-27 10:00:00","commit":"abc123","branch":"main","author":"Dmitry","commit_message":"...","recorded_at":"2026-04-27 10:00:01"}
```

---

## CI/CD integration examples

### GitHub Actions

Git data is read automatically. Pass `--commit-url` to make the commit hash clickable in the dropdown.

```yaml
- name: Write deploy info
  run: |
    php artisan deploy-indicator:write \
      --env=production \
      --commit-url="${{ github.server_url }}/${{ github.repository }}/commit/${{ github.sha }}"
```

### GitLab CI

```yaml
deploy:
  script:
    - |
      php artisan deploy-indicator:write \
        --env=production \
        --commit-url="$CI_PROJECT_URL/-/commit/$CI_COMMIT_SHA"
```

### Shell / custom script

```bash
php artisan deploy-indicator:write --env=production
```

---

## Verify your setup

Run the check command to see the current state of the plugin configuration:

```bash
php artisan deploy-indicator:check
```

Example output:

```
Filament Deploy Indicator — Setup Check

  ✓ Config file published
  ✓ Git repository detected at: /var/www/html
  ✓ Git info readable (commit: abc1234, branch: main)
  ✓ deploy-info.json found at: storage/app/private/deploy-info.json
  ✓ deploy-info.json is valid JSON
  ✓ Write path is writable: storage/app/private

Current deployment info:
 +--------------+-----------------------+
 | Key          | Value                 |
 +--------------+-----------------------+
 | environment  | production            |
 | deployed_at  | 2026-03-05 10:00:00   |
 | commit       | abc1234...            |
 | branch       | main                  |
 | author       | Dmitry                |
 +--------------+-----------------------+
```

---

## Deployment JSON format

The plugin reads a JSON file with this structure:

```json
{
  "environment": "production",
  "deployed_at": "2026-03-04 16:30:00",
  "commit": "33de817f4b2c3a1e9d0f8c7b5e2a4d6f8b1c3e5a",
  "branch": "main",
  "author": "Dmitry",
  "commit_message": "initial release",
  "commit_url": "https://github.com/your/repo/commit/33de817",
  "tag": "v1.0.0"
}
```

All fields are optional. Default location: `storage/app/private/deploy-info.json`.

---

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
