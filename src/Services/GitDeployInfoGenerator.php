<?php

namespace Arnautdev\FilamentDeployIndicator\Services;

use Arnautdev\FilamentDeployIndicator\Services\Generators\Contracts\DeployInfoGenerator;
use Illuminate\Support\Facades\Process;

class GitDeployInfoGenerator implements DeployInfoGenerator
{
    public function canRun(): bool
    {
        $root = (string) config('filament-deploy-indicator.git_root', base_path());

        return is_dir($root . DIRECTORY_SEPARATOR . '.git');
    }

    public function generate(): array
    {
        if (! $this->canRun()) {
            return [];
        }

        $env = [
            'GIT_TERMINAL_PROMPT' => '0',
            'NO_COLOR' => '1',
        ];

        $commit = trim(Process::env($env)->run('git --no-pager rev-parse HEAD')->output());
        if ($commit === '') {
            return [];
        }

        $branchResult = Process::env($env)->run('git --no-pager rev-parse --abbrev-ref HEAD');
        $branch = $branchResult->successful() ? $this->clean($branchResult->output()) : null;

        $authorResult = Process::env($env)->run('git --no-pager log -1 --pretty=format:%an');
        $author = $authorResult->successful() ? $this->clean($authorResult->output()) : null;

        $messageResult = Process::env($env)->run('git --no-pager log -1 --pretty=format:%s');
        $message = $messageResult->successful() ? $this->clean($messageResult->output()) : null;

        $dateResult = Process::env($env)->run("git --no-pager log -1 --pretty=format:%cd --date=format:'%Y-%m-%d %H:%M:%S'");
        $commitDate = $dateResult->successful() ? $this->clean($dateResult->output()) : null;

        $tagProcess = Process::env($env)->run('git --no-pager describe --tags --exact-match');
        $tag = $tagProcess->successful() ? $this->clean($tagProcess->output()) : null;

        return array_filter([
            'environment' => app()->environment(),
            'deployed_at' => $commitDate,
            'commit' => $commit,
            'branch' => ($branch !== null && $branch !== 'HEAD') ? $branch : null,
            'author' => $author ?: null,
            'commit_message' => $message ?: null,
            'tag' => $tag ?: null,
        ], fn (bool | string | null $v): bool => ! is_null($v) && $v !== '');
    }

    private function clean(string $output): string
    {
        // Strip ANSI escape codes and trim whitespace
        return trim((string) preg_replace('/\x1b\[[0-9;]*m/', '', $output));
    }
}
