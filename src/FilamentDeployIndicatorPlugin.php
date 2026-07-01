<?php

namespace Arnautdev\FilamentDeployIndicator;

use Arnautdev\FilamentDeployIndicator\Navigation\DevToolsNavigationBuilder;
use Arnautdev\FilamentDeployIndicator\Services\DeployInfoService;
use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\View;

class FilamentDeployIndicatorPlugin implements Plugin
{
    protected bool | Closure $isVisible = true;

    /**
     * Closure permission gates registered fluently, keyed by 'group' or
     * 'tool.{key}'. These take precedence over the config permission value
     * and let callers use logic that cannot live in a cached config file.
     *
     * @var array<string, Closure>
     */
    protected array $devToolsPermissionResolvers = [];

    public function getId(): string
    {
        return 'filament-deploy-indicator';
    }

    public function setGitRoot(string $path): static
    {
        config()->set('filament-deploy-indicator.git_root', $path);

        return $this;
    }

    public function setAutoGenerateWhenMissing(bool $autoGenerate): static
    {
        config()->set('filament-deploy-indicator.auto_generate_when_missing', $autoGenerate);

        return $this;
    }

    public function setFilePath(string $path): static
    {
        config()->set('filament-deploy-indicator.file_path', $path);

        return $this;
    }

    public function setWritePath(string $path): static
    {
        config()->set('filament-deploy-indicator.write_path', $path);

        return $this;
    }

    public function setCacheTtl(int $ttl): static
    {
        config()->set('filament-deploy-indicator.cache_ttl', $ttl);

        return $this;
    }

    public function setPosition(string $position): static
    {
        config()->set('filament-deploy-indicator.position', $position);

        return $this;
    }

    public function setDefaultLabel(string $label): static
    {
        config()->set('filament-deploy-indicator.default.label', $label);

        return $this;
    }

    public function setDefaultColor(string $color): static
    {
        config()->set('filament-deploy-indicator.default.color', $color);

        return $this;
    }

    /**
     * @param  array<string, array{label: string, color: string}>  $map
     */
    public function setEnvMap(array $map): static
    {
        config()->set('filament-deploy-indicator.env_map', $map);

        return $this;
    }

    public function mapEnv(string $environment, string $label, string $color): static
    {
        config()->set("filament-deploy-indicator.env_map.{$environment}", [
            'label' => $label,
            'color' => $color,
        ]);

        return $this;
    }

    public function setHistoryEnabled(bool $enabled = true): static
    {
        config()->set('filament-deploy-indicator.history.enabled', $enabled);

        return $this;
    }

    public function setHistoryPath(string $path): static
    {
        config()->set('filament-deploy-indicator.history.path', $path);

        return $this;
    }

    public function setHistoryMaxEntries(int $max): static
    {
        config()->set('filament-deploy-indicator.history.max_entries', $max);

        return $this;
    }

    public function setHistoryShowInDropdown(int $count): static
    {
        config()->set('filament-deploy-indicator.history.show_in_dropdown', $count);

        return $this;
    }

    public function setTopbarHint(?string $show): static
    {
        config()->set('filament-deploy-indicator.topbar.show', $show);

        return $this;
    }

    public function setCommitLength(int $length): static
    {
        config()->set('filament-deploy-indicator.topbar.commit_length', $length);

        return $this;
    }

    public function setDateFormat(string $format): static
    {
        config()->set('filament-deploy-indicator.topbar.date_format', $format);

        return $this;
    }

    public function devTools(bool $enabled = true): static
    {
        config()->set('filament-deploy-indicator.dev_tools.enabled', $enabled);

        return $this;
    }

    public function devToolsGroupLabel(string | Closure $label): static
    {
        config()->set('filament-deploy-indicator.dev_tools.group_label', $label);

        return $this;
    }

    public function devToolsGroupCollapsed(bool $collapsed = true): static
    {
        config()->set('filament-deploy-indicator.dev_tools.collapsed', $collapsed);

        return $this;
    }

    /**
     * Group-level gate. A string is stored in config (Gate ability); a Closure
     * is kept on the plugin so it survives config caching and can run logic.
     */
    public function devToolsPermission(string | Closure $permission): static
    {
        if ($permission instanceof Closure) {
            $this->devToolsPermissionResolvers['group'] = $permission;

            return $this;
        }

        config()->set('filament-deploy-indicator.dev_tools.permission', $permission);

        return $this;
    }

    /**
     * Replace the whole tools map.
     *
     * @param  array<string, array<string, mixed>>  $tools
     */
    public function setTools(array $tools): static
    {
        config()->set('filament-deploy-indicator.dev_tools.tools', $tools);

        return $this;
    }

    /**
     * Add or override a single tool.
     *
     * @param  array<string, mixed>  $config
     */
    public function addTool(string $key, array $config): static
    {
        config()->set("filament-deploy-indicator.dev_tools.tools.{$key}", $config);

        return $this;
    }

    /**
     * Per-tool gate. A string is stored in config (Gate ability); a Closure is
     * kept on the plugin so it survives config caching and can run logic.
     */
    public function toolPermission(string $key, string | Closure $permission): static
    {
        if ($permission instanceof Closure) {
            $this->devToolsPermissionResolvers["tool.{$key}"] = $permission;

            return $this;
        }

        config()->set("filament-deploy-indicator.dev_tools.tools.{$key}.permission", $permission);

        return $this;
    }

    public function getDevToolsPermissionResolver(string $key): ?Closure
    {
        return $this->devToolsPermissionResolvers[$key] ?? null;
    }

    public function visible(bool | Closure $condition = true): static
    {
        $this->isVisible = $condition;

        return $this;
    }

    private function isVisible(): bool
    {
        return (bool) value($this->isVisible);
    }

    public function register(Panel $panel): void
    {
        $builder = app(DevToolsNavigationBuilder::class);

        if ($builder->enabled()) {
            $panel->navigationGroups([$builder->group()]);
            $panel->navigationItems($builder->items());
        }

        $panel->renderHook(
            config('filament-deploy-indicator.position', PanelsRenderHook::GLOBAL_SEARCH_BEFORE),
            function (): string {
                if (! $this->isVisible()) {
                    return '';
                }

                $service = app(DeployInfoService::class);

                return View::make('filament-deploy-indicator::indicator', [
                    'deploy' => $service->get(),
                    'history' => $service->recentHistory(),
                ])->render();
            }
        );
    }

    public function boot(Panel $panel): void {}

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
