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
    |
    */
    'write_path' => storage_path('app/private/deploy-info.json'),

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
