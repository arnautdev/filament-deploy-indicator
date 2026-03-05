<?php

namespace Arnautdev\FilamentDeployIndicator;

use Arnautdev\FilamentDeployIndicator\Commands\WriteDeployInfoCommand;
use Arnautdev\FilamentDeployIndicator\Testing\TestsFilamentDeployIndicator;
use Filament\Support\Assets\Asset;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentDeployIndicatorServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-deploy-indicator';

    public static string $viewNamespace = 'filament-deploy-indicator';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('arnautdev/filament-deploy-indicator');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/filament-deploy-indicator/{$file->getFilename()}"),
                ], 'filament-deploy-indicator-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsFilamentDeployIndicator);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'arnautdev/filament-deploy-indicator';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('filament-deploy-indicator', __DIR__ . '/../resources/dist/components/filament-deploy-indicator.js'),
            // Css::make('filament-deploy-indicator-styles', __DIR__ . '/../resources/dist/filament-deploy-indicator.css'),
            // Js::make('filament-deploy-indicator-scripts', __DIR__ . '/../resources/dist/filament-deploy-indicator.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            WriteDeployInfoCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_filament-deploy-indicator_table',
        ];
    }
}
