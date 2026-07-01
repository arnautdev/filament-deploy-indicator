<?php

use Arnautdev\FilamentDeployIndicator\FilamentDeployIndicatorPlugin;
use Arnautdev\FilamentDeployIndicator\Navigation\DevToolsNavigationBuilder;
use Illuminate\Support\Facades\Gate;
use Workbench\App\Models\User;

function devToolsBuilder(): DevToolsNavigationBuilder
{
    return app(DevToolsNavigationBuilder::class);
}

/**
 * @param  array<string, mixed>  $overrides
 */
function setTool(string $key, array $overrides = []): void
{
    config()->set("filament-deploy-indicator.dev_tools.tools.{$key}", array_merge([
        'label' => ucfirst($key),
        'icon' => 'heroicon-o-server-stack',
        'permission' => null,
        'url' => "/{$key}",
        'environments' => null,
        'open_in_new_tab' => true,
    ], $overrides));
}

function visibleToolLabels(): array
{
    return collect(devToolsBuilder()->items())
        ->filter(fn ($item) => $item->isVisible())
        ->map(fn ($item) => $item->getLabel())
        ->values()
        ->all();
}

beforeEach(function () {
    config()->set('filament-deploy-indicator.dev_tools.enabled', true);
    config()->set('filament-deploy-indicator.dev_tools.permission', null);
    config()->set('filament-deploy-indicator.dev_tools.tools', []);
    $this->app['env'] = 'testing';
});

it('fluent setters mutate config and chain', function () {
    $plugin = FilamentDeployIndicatorPlugin::make();

    expect($plugin->devTools(false))->toBe($plugin)
        ->and(config('filament-deploy-indicator.dev_tools.enabled'))->toBeFalse();

    $plugin->devToolsGroupLabel('Ops')->devToolsGroupCollapsed(false);

    expect(config('filament-deploy-indicator.dev_tools.group_label'))->toBe('Ops')
        ->and(config('filament-deploy-indicator.dev_tools.collapsed'))->toBeFalse();
});

it('adds and replaces tools', function () {
    FilamentDeployIndicatorPlugin::make()->addTool('grafana', [
        'label' => 'Grafana',
        'icon' => 'heroicon-o-chart-bar',
        'url' => '/grafana',
    ]);

    expect(config('filament-deploy-indicator.dev_tools.tools.grafana.label'))->toBe('Grafana');

    FilamentDeployIndicatorPlugin::make()->setTools([
        'only' => ['label' => 'Only', 'icon' => 'x', 'url' => '/only'],
    ]);

    expect(config('filament-deploy-indicator.dev_tools.tools'))->toHaveKeys(['only'])
        ->and(config('filament-deploy-indicator.dev_tools.tools'))->not->toHaveKey('grafana');
});

it('hides every tool when the group is disabled', function () {
    setTool('horizon');
    config()->set('filament-deploy-indicator.dev_tools.enabled', false);

    expect(devToolsBuilder()->enabled())->toBeFalse()
        ->and(devToolsBuilder()->items())->toBe([]);
});

it('hides a tool with an empty url', function () {
    setTool('horizon', ['url' => null]);

    expect(visibleToolLabels())->toBe([]);
});

it('restricts a tool by environment', function () {
    setTool('telescope', ['environments' => ['local']]);

    $this->app['env'] = 'production';
    expect(visibleToolLabels())->toBe([]);

    $this->app['env'] = 'local';
    expect(visibleToolLabels())->toBe(['Telescope']);
});

it('surfaces a tool in every environment when environments is null', function () {
    setTool('swagger', ['environments' => null]);

    $this->app['env'] = 'production';
    expect(visibleToolLabels())->toBe(['Swagger']);
});

it('hides all tools when the group permission string is denied', function () {
    setTool('horizon');
    config()->set('filament-deploy-indicator.dev_tools.permission', 'access-dev-tools');

    Gate::define('access-dev-tools', fn () => false);
    $this->actingAs(new User);

    expect(visibleToolLabels())->toBe([]);
});

it('shows tools when the group permission string is granted', function () {
    setTool('horizon');
    config()->set('filament-deploy-indicator.dev_tools.permission', 'access-dev-tools');

    Gate::define('access-dev-tools', fn () => true);
    $this->actingAs(new User);

    expect(visibleToolLabels())->toBe(['Horizon']);
});

it('gates a single tool by its own permission string', function () {
    setTool('horizon', ['permission' => 'access-horizon']);
    setTool('swagger', ['permission' => null]);

    Gate::define('access-horizon', fn () => false);
    $this->actingAs(new User);

    expect(visibleToolLabels())->toBe(['Swagger']);
});

it('honours the open_in_new_tab flag and url', function () {
    setTool('horizon', ['url' => '/horizon', 'open_in_new_tab' => false]);

    $item = devToolsBuilder()->items()[0];

    expect($item->getUrl())->toBe('/horizon')
        ->and($item->shouldOpenUrlInNewTab())->toBeFalse();
});

it('reports enabled state', function () {
    expect(devToolsBuilder()->enabled())->toBeTrue();

    config()->set('filament-deploy-indicator.dev_tools.enabled', false);

    expect(devToolsBuilder()->enabled())->toBeFalse();
});
