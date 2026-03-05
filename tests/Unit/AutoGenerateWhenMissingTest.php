<?php

namespace Arnautdev\FilamentDeployIndicator\Tests;

use Arnautdev\FilamentDeployIndicator\Services\DeployInfoService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    Cache::flush();
});

it('generates deploy-info.json when missing and auto-generate is enabled', function () {

    $filePath = storage_path('app/private/deploy-info.json');
    $writePath = storage_path('app/private/deploy-info.json');

    // Ensure file does not exist
    if (File::exists($filePath)) {
        File::delete($filePath);
    }

    config()->set('filament-deploy-indicator.file_path', $filePath);
    config()->set('filament-deploy-indicator.write_path', $writePath);
    config()->set('filament-deploy-indicator.auto_generate_when_missing', true);
    config()->set('filament-deploy-indicator.cache_ttl', 30);

    $data = DeployInfoService::get();

    expect($data)->toBeArray()->and(File::exists($writePath))->toBeTrue();

    $written = json_decode(File::get($writePath), true);
    expect($written)->toBeArray();
});
