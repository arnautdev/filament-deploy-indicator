<?php

declare(strict_types=1);

use Filament\View\PanelsRenderHook;

return [

    /*
    |--------------------------------------------------------------------------
    | Render hook position
    |--------------------------------------------------------------------------
    |
    | Where to render the indicator in Filament panel layout.
    | Default: before the global search in the topbar.
    |
    */
    'position' => PanelsRenderHook::GLOBAL_SEARCH_BEFORE,

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | How long (in seconds) the deploy info should be cached.
    | Useful to avoid hitting filesystem on every request.
    |
    */
    'cache_ttl' => 30,

    /*
    |--------------------------------------------------------------------------
    | Deploy info file paths
    |--------------------------------------------------------------------------
    |
    | Path to the JSON file that contains deployment metadata.
    | By default stored in: storage/app/private/deploy-info.json
    |
    */
    'file_path' => storage_path('app/private/deploy-info.json'),

    /*
    |--------------------------------------------------------------------------
    | Auto-generate when missing
    |--------------------------------------------------------------------------
    |
    | If enabled and the JSON file is missing, the package may generate it using git.
    | Requires access to the git repository and `git` binary on the server.
    |
    */
    'auto_generate_when_missing' => true,

    /*
    |--------------------------------------------------------------------------
    | Write path
    |--------------------------------------------------------------------------
    |
    | Where the auto-generated JSON should be saved.
    | When null, falls back to `file_path` above.
    |
    */
    'write_path' => null,

    /*
    |--------------------------------------------------------------------------
    | Git root
    |--------------------------------------------------------------------------
    |
    | Root directory of your git repository.
    | Override via DEPLOY_INDICATOR_GIT_ROOT if your app lives in a subdirectory.
    |
    */
    'git_root' => env('DEPLOY_INDICATOR_GIT_ROOT', base_path()),

    /*
    |--------------------------------------------------------------------------
    | Deploy info driver
    |--------------------------------------------------------------------------
    |
    | Where deploy info is sourced from when auto-generating.
    |
    | - 'git'              => read live git data (needs a .git dir on the server)
    | - 'static'          => read env vars (great for Docker/Kubernetes images
    |                         that ship without .git — bake the values at build)
    | - ['static', 'git'] => ordered fallback: use env vars if present,
    |                         otherwise fall back to git
    |
    */
    'driver' => env('DEPLOY_INDICATOR_DRIVER', 'git'),

    /*
    |--------------------------------------------------------------------------
    | Driver options
    |--------------------------------------------------------------------------
    |
    | The `static` driver reads deploy info from environment variables. The
    | values below are the env var *names* it looks up (the values are read at
    | runtime, so this keeps working even when the Laravel config is cached).
    |
    | Example Dockerfile:
    |   ARG GIT_COMMIT
    |   ENV DEPLOY_COMMIT=$GIT_COMMIT
    |
    */
    'drivers' => [
        'static' => [
            'environment' => 'DEPLOY_ENV',
            'deployed_at' => 'DEPLOY_AT',
            'commit' => 'DEPLOY_COMMIT',
            'branch' => 'DEPLOY_BRANCH',
            'author' => 'DEPLOY_AUTHOR',
            'commit_message' => 'DEPLOY_COMMIT_MESSAGE',
            'commit_url' => 'DEPLOY_COMMIT_URL',
            'tag' => 'DEPLOY_TAG',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | ENV label and color mapping
    |--------------------------------------------------------------------------
    |
    | Map your Laravel environment to a short label + Filament color.
    |
    */
    'default' => [
        'label' => 'ENV',
        'color' => 'gray',
    ],

    'env_map' => [
        'production' => ['label' => 'PROD',  'color' => 'danger'],
        'prod' => ['label' => 'PROD',  'color' => 'danger'],

        'staging' => ['label' => 'STAGE', 'color' => 'warning'],
        'stage' => ['label' => 'STAGE', 'color' => 'warning'],

        'testing' => ['label' => 'TEST',  'color' => 'gray'],

        'dev' => ['label' => 'DEV',   'color' => 'info'],
        'local' => ['label' => 'LOCAL', 'color' => 'gray'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Deploy history
    |--------------------------------------------------------------------------
    |
    | Append-only JSONL log of past deploys. Each new deploy is recorded
    | (deduplicated by commit hash). Useful to see who/what/when in the
    | Filament topbar without leaving the admin.
    |
    */
    'history' => [
        'enabled' => true,
        'path' => storage_path('app/private/deploy-history.jsonl'),
        'max_entries' => 100,
        'show_in_dropdown' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Topbar hint
    |--------------------------------------------------------------------------
    |
    | Optionally display a tiny hint near the ENV label.
    | - null          => show nothing
    | - 'commit'      => show short commit hash
    | - 'deployed_at' => show deployment time
    | - 'tag'         => show git tag (e.g. v1.2.3)
    | - 'branch'      => show branch name
    |
    */
    'topbar' => [
        'show' => 'commit',

        // How many characters of the commit hash to display.
        'commit_length' => 7,

        // Format used when showing deployed_at.
        'date_format' => 'd.m H:i',
    ],

];
