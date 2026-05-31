<?php

namespace Arnautdev\FilamentDeployIndicator\Services;

use Arnautdev\FilamentDeployIndicator\Services\Generators\CompositeDeployInfoGenerator;
use Arnautdev\FilamentDeployIndicator\Services\Generators\Contracts\DeployInfoGenerator;
use Arnautdev\FilamentDeployIndicator\Services\Generators\StaticDeployInfoGenerator;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

/**
 * Resolves the configured deploy-info driver into a DeployInfoGenerator.
 *
 * `driver` may be:
 *   - a string: 'git' | 'static' | a custom registered driver
 *   - an array: ['static', 'git'] for ordered fallback (CompositeDeployInfoGenerator)
 */
class DeployInfoGeneratorManager
{
    /**
     * Custom driver factories, keyed by name.
     *
     * @var array<string, callable(Container): DeployInfoGenerator>
     */
    protected array $customCreators = [];

    public function __construct(
        protected Container $container,
    ) {}

    /**
     * Register a custom driver.
     *
     * @param  callable(Container): DeployInfoGenerator  $callback
     */
    public function extend(string $driver, callable $callback): static
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * Build the generator for the given driver (defaults to config).
     *
     * @param  string|array<int, string>|null  $driver
     */
    public function make(string | array | null $driver = null): DeployInfoGenerator
    {
        $driver ??= config('filament-deploy-indicator.driver', 'git');

        if (is_array($driver)) {
            return new CompositeDeployInfoGenerator(
                array_map(fn (string $name): DeployInfoGenerator => $this->make($name), array_values($driver)),
            );
        }

        if (isset($this->customCreators[$driver])) {
            return ($this->customCreators[$driver])($this->container);
        }

        return match ($driver) {
            'git' => $this->container->make(GitDeployInfoGenerator::class),
            'static' => $this->container->make(StaticDeployInfoGenerator::class),
            default => throw new InvalidArgumentException("filament-deploy-indicator: unknown deploy info driver [{$driver}]."),
        };
    }
}
