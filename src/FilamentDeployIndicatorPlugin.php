<?php

namespace Arnautdev\FilamentDeployIndicator;

use Arnautdev\FilamentDeployIndicator\Services\DeployInfoService;
use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\View;

class FilamentDeployIndicatorPlugin implements Plugin
{
    protected bool | Closure $isVisible = true;

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

    /**
     * Set the deploy info driver.
     *
     * @param  string|array<int, string>  $driver  'git', 'static', or an
     *                                             ordered fallback like ['static', 'git']
     */
    public function setDriver(string | array $driver): static
    {
        config()->set('filament-deploy-indicator.driver', $driver);

        return $this;
    }

    /**
     * Override the env var names the `static` driver reads.
     *
     * @param  array<string, string>  $map  e.g. ['commit' => 'GIT_SHA']
     */
    public function setStaticEnvMap(array $map): static
    {
        config()->set(
            'filament-deploy-indicator.drivers.static',
            array_merge(
                (array) config('filament-deploy-indicator.drivers.static', []),
                $map,
            ),
        );

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
