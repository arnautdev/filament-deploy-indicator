<?php

declare(strict_types=1);

namespace Arnautdev\FilamentDeployIndicator\Navigation;

use Arnautdev\FilamentDeployIndicator\FilamentDeployIndicatorPlugin;
use Closure;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;

/**
 * Builds the Dev Tools navigation items and group from config.
 *
 * Config is read live inside the item closures (not captured) so cached or
 * runtime-overridden values apply per request. String permissions live in
 * config; Closure permissions are resolved from the plugin instance.
 */
class DevToolsNavigationBuilder
{
    /**
     * @return array<int, NavigationItem>
     */
    public function items(): array
    {
        if (! $this->enabled()) {
            return [];
        }

        // Resolved lazily: the plugin registers navigation during Panel
        // registration, before package translations are loaded. A Closure
        // defers __() to render time, when the group label is available.
        $group = fn (): string => $this->groupLabel();

        $items = [];
        $sort = 0;

        /** @var array<string, array<string, mixed>> $tools */
        $tools = config('filament-deploy-indicator.dev_tools.tools', []);

        foreach ($tools as $key => $tool) {
            $items[] = NavigationItem::make((string) ($tool['label'] ?? $key))
                ->icon($tool['icon'] ?? null)
                ->group($group)
                ->sort($sort++)
                ->url(
                    fn (): string => (string) config("filament-deploy-indicator.dev_tools.tools.{$key}.url"),
                    shouldOpenInNewTab: (bool) ($tool['open_in_new_tab'] ?? true),
                )
                ->visible(fn (): bool => $this->toolVisible($key));
        }

        return $items;
    }

    public function group(): NavigationGroup
    {
        return NavigationGroup::make(fn (): string => $this->groupLabel())
            ->collapsed((bool) config('filament-deploy-indicator.dev_tools.collapsed', true));
    }

    public function enabled(): bool
    {
        return (bool) config('filament-deploy-indicator.dev_tools.enabled', false);
    }

    protected function toolVisible(string $key): bool
    {
        if (! $this->enabled()) {
            return false;
        }

        $url = config("filament-deploy-indicator.dev_tools.tools.{$key}.url");

        if (blank($url)) {
            return false;
        }

        /** @var array<int, string>|null $environments */
        $environments = config("filament-deploy-indicator.dev_tools.tools.{$key}.environments");

        if ($environments !== null && ! app()->environment($environments)) {
            return false;
        }

        return $this->passesGate(config('filament-deploy-indicator.dev_tools.permission'), 'group')
            && $this->passesGate(config("filament-deploy-indicator.dev_tools.tools.{$key}.permission"), "tool.{$key}");
    }

    /**
     * A gate passes when: it is null (no restriction), a Closure that returns
     * truthy, or an ability string the authenticated user ->can().
     *
     * $resolverKey looks up a Closure gate registered fluently on the plugin,
     * which takes precedence over the config value.
     */
    protected function passesGate(mixed $permission, string $resolverKey): bool
    {
        $resolver = $this->permissionResolver($resolverKey);

        if ($resolver instanceof Closure) {
            return (bool) $resolver();
        }

        if ($permission instanceof Closure) {
            return (bool) $permission();
        }

        if (blank($permission)) {
            return true;
        }

        return (bool) auth()->user()?->can($permission);
    }

    protected function permissionResolver(string $key): ?Closure
    {
        try {
            return FilamentDeployIndicatorPlugin::get()->getDevToolsPermissionResolver($key);
        } catch (\Throwable) {
            // No current panel / plugin not registered: fall back to config gates.
            return null;
        }
    }

    protected function groupLabel(): string
    {
        /** @var string $label */
        $label = config(
            'filament-deploy-indicator.dev_tools.group_label',
            'filament-deploy-indicator::deploy-indicator.dev_tools.group',
        );

        $translated = __($label);

        return is_string($translated) ? $translated : $label;
    }
}
