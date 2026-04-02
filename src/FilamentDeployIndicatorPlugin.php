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

                return View::make('filament-deploy-indicator::indicator', [
                    'deploy' => app(DeployInfoService::class)->get(),
                ])->render();
            }
        );
    }

    public function boot(Panel $panel): void
    {
        //
    }

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
