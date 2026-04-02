<?php

namespace Arnautdev\FilamentDeployIndicator\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class DeployInfoService
{
    public function __construct(
        protected GitDeployInfoGenerator $generator,
    ) {}

    public function get(): array
    {
        $path = config('filament-deploy-indicator.file_path');
        $ttl = (int) config('filament-deploy-indicator.cache_ttl', 30);

        return Cache::remember('deploy-indicator:deploy-info', $ttl, function () use ($path) {
            if (! $path || ! File::exists($path)) {
                if (! config('filament-deploy-indicator.auto_generate_when_missing')) {
                    return [];
                }

                $generated = $this->generator->generate();

                if ($generated === []) {
                    return [];
                }

                $writePath = config('filament-deploy-indicator.write_path') ?: $path;

                File::ensureDirectoryExists(dirname($writePath));
                File::put(
                    $writePath,
                    json_encode($generated, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                );

                $path = $writePath;
            }

            $json = File::get($path);
            $data = json_decode($json, true);

            return is_array($data) ? $data : [];
        });
    }
}
