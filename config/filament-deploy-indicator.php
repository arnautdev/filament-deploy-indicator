<?php

use Filament\View\PanelsRenderHook;

return [
    'position' => PanelsRenderHook::GLOBAL_SEARCH_BEFORE,

    'cache_ttl' => 30,

    'file_path' => storage_path('app/private/deploy-info.json'),

    'auto_generate_when_missing' => true,
    'write_path' => storage_path('app/private/deploy-info.json'),
    'git_root' => env('DEPLOY_INDICATOR_GIT_ROOT', dirname(__FILE__) . '/..'),

    'default' => ['label' => 'ENV', 'color' => 'gray'],
    'env_map' => [
        'production' => ['label' => 'PROD', 'color' => 'danger'],
        'prod' => ['label' => 'PROD', 'color' => 'danger'],
        'staging' => ['label' => 'STAGE', 'color' => 'warning'],
        'stage' => ['label' => 'STAGE', 'color' => 'warning'],
        'testing' => ['label' => 'TEST', 'color' => 'gray'],
        'dev' => ['label' => 'DEV', 'color' => 'info'],
        'local' => ['label' => 'LOCAL', 'color' => 'gray'],
    ],

    'topbar' => [
        // null | 'commit' | 'deployed_at'
        'show' => 'commit',

        'commit_length' => 7,

        'date_format' => 'd.m H:i',
    ],
];
