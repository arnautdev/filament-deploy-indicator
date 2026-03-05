<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

it('writes deploy-info.json using deploy-indicator:write command', function () {
    $path = storage_path('app/private/deploy-info.json');

    // чтобы не зависеть от package config по умолчанию
    config()->set('filament-deploy-indicator.write_path', $path);

    if (File::exists($path)) {
        File::delete($path);
    }

    File::ensureDirectoryExists(dirname($path));

    $exitCode = Artisan::call('deploy-indicator:write', [
        '--env' => 'testing',
        '--deployed-at' => '2026-03-05 15:00:00',
        '--commit' => 'abc123',
        '--branch' => 'main',
        '--author' => 'Dmitry',
        '--message' => 'Hello',
        '--path' => $path, // явный path, чтобы точно туда записало
    ]);

    expect($exitCode)->toBe(0)
        ->and(File::exists($path))->toBeTrue();

    $data = json_decode(File::get($path), true);

    expect($data)->toBeArray()
        ->and($data['environment'])->toBe('testing')
        ->and($data['deployed_at'])->toBe('2026-03-05 15:00:00')
        ->and($data['commit'])->toBe('abc123')
        ->and($data['branch'])->toBe('main')
        ->and($data['author'])->toBe('Dmitry')
        ->and($data['commit_message'])->toBe('Hello');
});
