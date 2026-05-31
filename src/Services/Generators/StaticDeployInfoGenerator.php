<?php

namespace Arnautdev\FilamentDeployIndicator\Services\Generators;

use Arnautdev\FilamentDeployIndicator\Services\Generators\Contracts\DeployInfoGenerator;
use Illuminate\Support\Env;

/**
 * Reads deploy info from environment variables instead of git.
 *
 * Ideal for containerized deploys (Docker / Kubernetes) where the `.git`
 * directory is not shipped in the production image. Bake the values into the
 * image at build time, e.g.:
 *
 *   ARG GIT_COMMIT
 *   ENV DEPLOY_COMMIT=$GIT_COMMIT
 *
 * The env var *names* are configurable; the values are read at runtime via
 * Env::get() so they keep working even when the Laravel config is cached.
 */
class StaticDeployInfoGenerator implements DeployInfoGenerator
{
    public function canRun(): bool
    {
        // We need at least a commit or a tag to consider this a real deploy.
        return $this->read('commit') !== null || $this->read('tag') !== null;
    }

    public function generate(): array
    {
        if (! $this->canRun()) {
            return [];
        }

        $data = [
            'environment' => $this->read('environment') ?? app()->environment(),
            'deployed_at' => $this->read('deployed_at'),
            'commit' => $this->read('commit'),
            'branch' => $this->read('branch'),
            'author' => $this->read('author'),
            'commit_message' => $this->read('commit_message'),
            'commit_url' => $this->read('commit_url'),
            'tag' => $this->read('tag'),
        ];

        return array_filter($data, static fn (?string $v): bool => $v !== null && $v !== '');
    }

    /**
     * Read the env var configured for the given logical field.
     */
    private function read(string $field): ?string
    {
        $map = (array) config('filament-deploy-indicator.drivers.static', []);
        $envKey = $map[$field] ?? null;

        if (! is_string($envKey) || $envKey === '') {
            return null;
        }

        // Read the live OS environment via Env::get() (not the env() helper):
        // values baked into a container image stay readable even when the
        // Laravel config is cached.
        $value = Env::get($envKey);

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
