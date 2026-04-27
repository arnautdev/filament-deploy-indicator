<?php

namespace Arnautdev\FilamentDeployIndicator\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class DeployHistoryService
{
    public function record(array $entry): void
    {
        if (! $this->enabled()) {
            return;
        }

        $commit = $entry['commit'] ?? null;

        if (! is_string($commit) || $commit === '') {
            return;
        }

        $path = $this->path();

        if ($path === '') {
            return;
        }

        $maxEntries = max(1, (int) config('filament-deploy-indicator.history.max_entries', 100));

        $existing = $this->readAll($path);

        $lastCommit = $existing !== [] ? ($existing[array_key_last($existing)]['commit'] ?? null) : null;

        if ($lastCommit === $commit) {
            return;
        }

        $entry['recorded_at'] ??= now()->toDateTimeString();

        $existing[] = $entry;

        if (count($existing) > $maxEntries) {
            $existing = array_slice($existing, -$maxEntries);
        }

        $this->writeAll($path, $existing);
    }

    public function recent(?int $limit = null): array
    {
        if (! $this->enabled()) {
            return [];
        }

        $path = $this->path();

        if ($path === '' || ! File::exists($path)) {
            return [];
        }

        $limit ??= (int) config('filament-deploy-indicator.history.show_in_dropdown', 5);
        $limit = max(0, $limit);

        if ($limit === 0) {
            return [];
        }

        $entries = $this->readAll($path);

        return array_reverse(array_slice($entries, -$limit));
    }

    public function path(): string
    {
        return (string) config('filament-deploy-indicator.history.path', '');
    }

    public function enabled(): bool
    {
        return (bool) config('filament-deploy-indicator.history.enabled', true);
    }

    private function readAll(string $path): array
    {
        if (! File::exists($path)) {
            return [];
        }

        $contents = File::get($path);

        if ($contents === '') {
            return [];
        }

        $entries = [];

        foreach (preg_split("/\r\n|\n|\r/", $contents) ?: [] as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $decoded = json_decode($line, true);

            if (is_array($decoded)) {
                $entries[] = $decoded;
            }
        }

        return $entries;
    }

    private function writeAll(string $path, array $entries): void
    {
        File::ensureDirectoryExists(dirname($path));

        $lines = array_map(
            static fn (array $entry): string => json_encode($entry, JSON_UNESCAPED_SLASHES) ?: '',
            $entries,
        );

        $lines = array_filter($lines, static fn (string $line): bool => $line !== '');

        try {
            File::put($path, implode(PHP_EOL, $lines) . ($lines === [] ? '' : PHP_EOL));
        } catch (\Throwable $e) {
            Log::warning("filament-deploy-indicator: failed to write deploy history to [{$path}]: {$e->getMessage()}");
        }
    }
}
