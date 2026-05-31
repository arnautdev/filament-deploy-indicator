# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

`arnautdev/filament-deploy-indicator` — a Filament v4/v5 plugin (Laravel package). It renders a color-coded badge in the Filament admin topbar showing the current `APP_ENV` plus optional deployment metadata (commit, branch, author, tag, deploy time), with a dropdown showing full deploy info and recent deploy history.

There is no host Laravel app here — it's a package developed against **Orchestra Testbench** + a **workbench** skeleton (`workbench/`, `testbench.yaml`).

## Commands

Composer scripts (defined in `composer.json`) are the canonical entry points:

```bash
composer test            # run Pest test suite
composer test:lint       # Pint in --test mode (no writes)
composer lint            # Pint, apply fixes
composer analyse         # PHPStan (larastan) at level in phpstan.neon.dist
composer refactor        # Rector, apply
composer test:refactor   # Rector --dry-run
composer serve           # build assets + boot the workbench app in browser
composer build           # build the workbench skeleton
```

Run a single test:

```bash
vendor/bin/pest tests/Unit/DeployHistoryServiceTest.php
vendor/bin/pest --filter="records a deploy"
```

Frontend assets (CSS/JS bundled via esbuild → `resources/dist/`):

```bash
npm run dev    # watch mode
npm run build  # production build
```

Assets must be rebuilt and committed to `resources/dist/` — that dir is what gets shipped and registered with `FilamentAsset`. `composer serve` runs `npm run build` for you.

## Architecture

Data flows: **git / JSON file → DeployInfoService → blade view → topbar**.

- **`FilamentDeployIndicatorServiceProvider`** — Spatie `PackageServiceProvider`. Registers config, translations, views, the two Artisan commands, and the compiled CSS asset. Binds `GitDeployInfoGenerator` and `DeployInfoService` as singletons.

- **`FilamentDeployIndicatorPlugin`** — the `Filament\Contracts\Plugin`. In `register()` it hooks a render callback into the panel at the configured `position` (a `PanelsRenderHook`), which renders `indicator.blade.php`. **Every `setXxx()` fluent method just writes to the `filament-deploy-indicator.*` config at runtime** — there is no separate property state except `isVisible` (the `visible()` Closure/bool). This is why fluent config overrides the published config file.

- **`DeployInfoService`** — the read path. `get()` returns the deploy array, cached under key `deploy-indicator:deploy-info` for `cache_ttl` seconds. If `file_path` is missing and `auto_generate_when_missing` is on, it calls the git generator, writes JSON to `write_path` (falls back to `file_path`), and records history. Always returns `[]` on any failure (logs a warning) — the view must tolerate empty data.

- **`GitDeployInfoGenerator`** — shells out via `Illuminate\Support\Facades\Process` to read commit/branch/author/message/date/tag. `canRun()` gates on a `.git` dir existing under `git_root`. Returns `[]` if git unavailable or HEAD can't resolve. ANSI codes are stripped in `clean()`.

- **`DeployHistoryService`** — the write/history path. Append-only **JSONL** at `history.path` (one JSON object per line). Deduped by comparing the new commit to the last recorded commit. Trims to `history.max_entries`. `recent()` returns the last `show_in_dropdown` entries reversed (newest first).

- **`resources/views/indicator.blade.php`** — maps `APP_ENV` → label+color via `env_map` (falling back to `default`), computes the topbar hint from `topbar.show`, and renders the Filament dropdown. Receives `$deploy` and `$history` from the plugin.

### Commands

- `deploy-indicator:write` (`WriteDeployInfoCommand`) — intended for the deploy pipeline. Starts from git data as the base, **CLI options override git values** (`--env`, `--commit`, `--author`, `--commit-url`, etc.), then writes JSON and records history.
- `deploy-indicator:check` (`CheckDeployIndicatorCommand`) — diagnostic: verifies config published, git detected, paths writable.

## Conventions

- **Config is the single source of truth at runtime.** When adding a new tunable, add it to `config/filament-deploy-indicator.php`, a `setXxx()` method on the plugin (writing to that config key), and document both in `README.md`'s method↔key table.
- PHP `^8.2`, `declare(strict_types=1)` in config; constructor property promotion; services depend on each other via constructor injection (resolved through the container singletons).
- All filesystem access goes through `Illuminate\Support\Facades\File`; git through the `Process` facade — both so tests can fake them.
- Tests are Pest, under `tests/Unit/`, extending `tests/TestCase.php` (Testbench + `WithWorkbench`). Use `composer test:lint` and `composer analyse` before considering work done; CI enforces Pint and PHPStan.

## Release & CI

- Versioning via **release-please** (`release-please-config.json`, `.release-please-manifest.json`) driven by **Conventional Commits**. PR titles are checked (`pr-title-check.yml` / semantic-pull-request) — commit and PR titles must be conventional (`feat:`, `fix:`, `build(deps):`, …).
- GitHub Actions: `run-tests.yml`, `phpstan.yml`, `lint.yml`, `fix-php-code-style-issues.yml`.

## Roadmap

`.ai/open-tasks.md` (in Bulgarian) tracks planned features: provider-aware commit URLs, dropdown health checks, a multi-source driver manager for deploy info (important for containerized deploys with no `.git`), and RBAC/per-env visibility. Consult it before designing related features.