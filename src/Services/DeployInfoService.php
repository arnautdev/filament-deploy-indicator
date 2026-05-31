<?php

namespace Arnautdev\FilamentDeployIndicator\Services;

use Arnautdev\FilamentDeployIndicator\Services\Generators\Contracts\DeployInfoGenerator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class DeployInfoService
{
    public function __construct(
        protected DeployInfoGenerator $generator,
        protected DeployHistoryService $history,
    ) {}

    public function get(): array
    {
        $path = config('filament-deploy-indicator.file_path');
        $ttl = (int) config('filament-deploy-indicator.cache_ttl', 30);

        return Cache::remember('deploy-indicator:deploy-info', $ttl, function () use ($path): array {
            if (! $path || ! File::exists($path)) {
                if (! config('filament-deploy-indicator.auto_generate_when_missing')) {
                    return [];
                }

                $generated = $this->generator->generate();

                if ($generated === []) {
                    Log::warning('filament-deploy-indicator: auto_generate_when_missing is enabled but no deploy info could be generated. Check that git is available and git_root is correct.');

                    return [];
                }

                $writePath = config('filament-deploy-indicator.write_path') ?: $path;

                File::ensureDirectoryExists(dirname($writePath));
                File::put(
                    $writePath,
                    json_encode($generated, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                );

                $this->history->record($generated);

                $path = $writePath;
            }

            $json = File::get($path);
            $data = json_decode($json, true);

            if (! is_array($data)) {
                Log::warning("filament-deploy-indicator: deploy-info.json at [{$path}] contains invalid JSON.");

                return [];
            }

            return $data;
        });
    }

    public function recentHistory(?int $limit = null): array
    {
        return $this->history->recent($limit);
    }
}
