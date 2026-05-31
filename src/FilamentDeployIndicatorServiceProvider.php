<?php

namespace Arnautdev\FilamentDeployIndicator;

use Arnautdev\FilamentDeployIndicator\Commands\CheckDeployIndicatorCommand;
use Arnautdev\FilamentDeployIndicator\Commands\WriteDeployInfoCommand;
use Arnautdev\FilamentDeployIndicator\Services\DeployInfoGeneratorManager;
use Arnautdev\FilamentDeployIndicator\Services\DeployInfoService;
use Arnautdev\FilamentDeployIndicator\Services\Generators\Contracts\DeployInfoGenerator;
use Arnautdev\FilamentDeployIndicator\Services\Generators\StaticDeployInfoGenerator;
use Arnautdev\FilamentDeployIndicator\Services\GitDeployInfoGenerator;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentDeployIndicatorServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-deploy-indicator';

    public static string $viewNamespace = 'filament-deploy-indicator';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('arnautdev/filament-deploy-indicator');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(GitDeployInfoGenerator::class);
        $this->app->singleton(StaticDeployInfoGenerator::class);
        $this->app->singleton(DeployInfoGeneratorManager::class);

        // Resolve the configured driver (git | static | composite array) into
        // the generator the rest of the package depends on.
        $this->app->singleton(
            DeployInfoGenerator::class,
            fn ($app): DeployInfoGenerator => $app->make(DeployInfoGeneratorManager::class)->make(),
        );

        $this->app->singleton(DeployInfoService::class);
    }

    public function packageBooted(): void
    {
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentIcon::register($this->getIcons());
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
            Css::make('filament-deploy-indicator-styles', __DIR__ . '/../resources/dist/filament-deploy-indicator.css'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            CheckDeployIndicatorCommand::class,
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
}
