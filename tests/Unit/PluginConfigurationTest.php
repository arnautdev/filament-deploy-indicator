<?php

use Arnautdev\FilamentDeployIndicator\FilamentDeployIndicatorPlugin;

it('returns the same instance for fluent chaining', function () {
    $plugin = FilamentDeployIndicatorPlugin::make();

    expect($plugin->setCacheTtl(300))->toBe($plugin);
});

it('sets the render hook position', function () {
    FilamentDeployIndicatorPlugin::make()->setPosition('panels::topbar.start');

    expect(config('filament-deploy-indicator.position'))->toBe('panels::topbar.start');
});

it('sets the cache ttl', function () {
    FilamentDeployIndicatorPlugin::make()->setCacheTtl(300);

    expect(config('filament-deploy-indicator.cache_ttl'))->toBe(300);
});

it('sets the file path', function () {
    FilamentDeployIndicatorPlugin::make()->setFilePath('/tmp/deploy.json');

    expect(config('filament-deploy-indicator.file_path'))->toBe('/tmp/deploy.json');
});

it('sets the write path', function () {
    FilamentDeployIndicatorPlugin::make()->setWritePath('/tmp/write.json');

    expect(config('filament-deploy-indicator.write_path'))->toBe('/tmp/write.json');
});

it('sets the git root', function () {
    FilamentDeployIndicatorPlugin::make()->setGitRoot('/var/www/app');

    expect(config('filament-deploy-indicator.git_root'))->toBe('/var/www/app');
});

it('toggles auto generate when missing', function () {
    FilamentDeployIndicatorPlugin::make()->setAutoGenerateWhenMissing(false);

    expect(config('filament-deploy-indicator.auto_generate_when_missing'))->toBeFalse();
});

it('sets the default label and color', function () {
    FilamentDeployIndicatorPlugin::make()
        ->setDefaultLabel('APP')
        ->setDefaultColor('primary');

    expect(config('filament-deploy-indicator.default.label'))->toBe('APP')
        ->and(config('filament-deploy-indicator.default.color'))->toBe('primary');
});

it('replaces the whole env map', function () {
    $map = [
        'production' => ['label' => 'LIVE', 'color' => 'danger'],
    ];

    FilamentDeployIndicatorPlugin::make()->setEnvMap($map);

    expect(config('filament-deploy-indicator.env_map'))->toBe($map);
});

it('maps a single environment', function () {
    FilamentDeployIndicatorPlugin::make()->mapEnv('qa', 'QA', 'warning');

    expect(config('filament-deploy-indicator.env_map.qa'))
        ->toBe(['label' => 'QA', 'color' => 'warning']);
});

it('configures history', function () {
    FilamentDeployIndicatorPlugin::make()
        ->setHistoryEnabled(false)
        ->setHistoryPath('/tmp/history.jsonl')
        ->setHistoryMaxEntries(50)
        ->setHistoryShowInDropdown(3);

    expect(config('filament-deploy-indicator.history.enabled'))->toBeFalse()
        ->and(config('filament-deploy-indicator.history.path'))->toBe('/tmp/history.jsonl')
        ->and(config('filament-deploy-indicator.history.max_entries'))->toBe(50)
        ->and(config('filament-deploy-indicator.history.show_in_dropdown'))->toBe(3);
});

it('configures the topbar hint', function () {
    FilamentDeployIndicatorPlugin::make()
        ->setTopbarHint('branch')
        ->setCommitLength(10)
        ->setDateFormat('Y-m-d');

    expect(config('filament-deploy-indicator.topbar.show'))->toBe('branch')
        ->and(config('filament-deploy-indicator.topbar.commit_length'))->toBe(10)
        ->and(config('filament-deploy-indicator.topbar.date_format'))->toBe('Y-m-d');
});

it('hides the topbar hint with null', function () {
    FilamentDeployIndicatorPlugin::make()->setTopbarHint(null);

    expect(config('filament-deploy-indicator.topbar.show'))->toBeNull();
});
