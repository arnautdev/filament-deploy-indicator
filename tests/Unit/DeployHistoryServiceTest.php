<?php

use Arnautdev\FilamentDeployIndicator\Services\DeployHistoryService;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $path = storage_path('app/private/deploy-history.jsonl');

    if (File::exists($path)) {
        File::delete($path);
    }

    config()->set('filament-deploy-indicator.history.enabled', true);
    config()->set('filament-deploy-indicator.history.path', $path);
    config()->set('filament-deploy-indicator.history.max_entries', 100);
    config()->set('filament-deploy-indicator.history.show_in_dropdown', 5);
});

it('appends a deploy entry to the history file', function () {
    $service = app(DeployHistoryService::class);

    $service->record([
        'commit' => 'abc123',
        'author' => 'Dmitry',
        'deployed_at' => '2026-04-27 10:00:00',
    ]);

    $recent = $service->recent();

    expect($recent)->toHaveCount(1)
        ->and($recent[0]['commit'])->toBe('abc123')
        ->and($recent[0]['author'])->toBe('Dmitry')
        ->and($recent[0]['recorded_at'])->not->toBeEmpty();
});

it('skips appending when commit hash matches the last entry', function () {
    $service = app(DeployHistoryService::class);

    $service->record(['commit' => 'abc123', 'author' => 'Dmitry']);
    $service->record(['commit' => 'abc123', 'author' => 'Dmitry']);
    $service->record(['commit' => 'abc123', 'author' => 'Other']);

    expect($service->recent())->toHaveCount(1);
});

it('appends when commit hash differs from the last entry', function () {
    $service = app(DeployHistoryService::class);

    $service->record(['commit' => 'aaa']);
    $service->record(['commit' => 'bbb']);
    $service->record(['commit' => 'ccc']);

    $recent = $service->recent();

    expect($recent)->toHaveCount(3)
        ->and(array_column($recent, 'commit'))->toBe(['ccc', 'bbb', 'aaa']);
});

it('trims history to max_entries', function () {
    config()->set('filament-deploy-indicator.history.max_entries', 3);

    $service = app(DeployHistoryService::class);

    foreach (['a', 'b', 'c', 'd', 'e'] as $commit) {
        $service->record(['commit' => $commit]);
    }

    $recent = $service->recent(10);

    expect($recent)->toHaveCount(3)
        ->and(array_column($recent, 'commit'))->toBe(['e', 'd', 'c']);
});

it('returns empty array when history is disabled', function () {
    config()->set('filament-deploy-indicator.history.enabled', false);

    $service = app(DeployHistoryService::class);

    $service->record(['commit' => 'abc123']);

    expect($service->recent())->toBe([]);
});

it('respects the show_in_dropdown limit', function () {
    config()->set('filament-deploy-indicator.history.show_in_dropdown', 2);

    $service = app(DeployHistoryService::class);

    foreach (['a', 'b', 'c', 'd'] as $commit) {
        $service->record(['commit' => $commit]);
    }

    expect($service->recent())->toHaveCount(2)
        ->and(array_column($service->recent(), 'commit'))->toBe(['d', 'c']);
});

it('ignores entries without a commit hash', function () {
    $service = app(DeployHistoryService::class);

    $service->record(['author' => 'Dmitry']);
    $service->record(['commit' => '', 'author' => 'Dmitry']);

    expect($service->recent())->toBe([]);
});
