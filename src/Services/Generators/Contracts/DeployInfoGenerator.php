<?php

namespace Arnautdev\FilamentDeployIndicator\Services\Generators\Contracts;

interface DeployInfoGenerator
{
    /**
     * Whether this generator has what it needs to produce deploy info
     * in the current environment (e.g. a .git dir, or the expected env vars).
     */
    public function canRun(): bool;

    /**
     * Produce the deploy-info array. Must return [] when no data is available
     * so callers (and the composite driver) can fall back gracefully.
     *
     * @return array<string, string>
     */
    public function generate(): array;
}
