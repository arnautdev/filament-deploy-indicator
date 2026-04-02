<?php

use Arnautdev\FilamentDeployIndicator\Services\DeployInfoService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

it('reads deploy info from configured file path', function () {
    Cache::flush();

    $path = storage_path('app/private/deploy-info.json');

    config()->set('filament-deploy-indicator.file_path', $path);
    config()->set('filament-deploy-indicator.cache_ttl', 30);

    File::ensureDirectoryExists(dirname($path));
    File::put($path, json_encode([
        'environment' => 'testing',
        'deployed_at' => '2026-03-05 10:00:00',
        'commit' => 'abc123',
        'branch' => 'main',
        'author' => 'Dmitry',
        'commit_message' => 'Test message',
    ], JSON_PRETTY_PRINT));

    $data = app(DeployInfoService::class)->get();

    expect($data)->toBeArray()
        ->and($data['commit'])->toBe('abc123')
        ->and($data['branch'])->toBe('main')
        ->and($data['author'])->toBe('Dmitry');
});
