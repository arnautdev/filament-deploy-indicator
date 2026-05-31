<?php

namespace Arnautdev\FilamentDeployIndicator\Services\Generators;

use Arnautdev\FilamentDeployIndicator\Services\Generators\Contracts\DeployInfoGenerator;

/**
 * Ordered fallback over several generators.
 *
 * The first generator that can run AND returns non-empty data wins. Lets you
 * configure e.g. ['static', 'git']: use baked-in env vars in production
 * containers, fall back to live git data on local/CI checkouts.
 */
class CompositeDeployInfoGenerator implements DeployInfoGenerator
{
    /**
     * @param  array<int, DeployInfoGenerator>  $generators
     */
    public function __construct(
        protected array $generators,
    ) {}

    public function canRun(): bool
    {
        foreach ($this->generators as $generator) {
            if ($generator->canRun()) {
                return true;
            }
        }

        return false;
    }

    public function generate(): array
    {
        foreach ($this->generators as $generator) {
            if (! $generator->canRun()) {
                continue;
            }

            $data = $generator->generate();

            if ($data !== []) {
                return $data;
            }
        }

        return [];
    }
}
