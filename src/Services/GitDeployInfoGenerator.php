<?php

namespace Arnautdev\FilamentDeployIndicator\Services;

use Illuminate\Support\Facades\Process;

class GitDeployInfoGenerator
{
    public static function canRun(): bool
    {
        $root = (string) config('filament-deploy-indicator.git_root', base_path());

        return is_dir($root . DIRECTORY_SEPARATOR . '.git');
    }

    public static function generate(): array
    {
        if (! self::canRun()) {
            return [];
        }

        $env = ['GIT_TERMINAL_PROMPT' => '0'];

        $commit = trim(Process::env($env)->run('git --no-pager rev-parse HEAD')->output());
        if ($commit === '') {
            return [];
        }

        $branchResult = Process::env($env)->run('git --no-pager rev-parse --abbrev-ref HEAD');
        $branch = $branchResult->successful() ? trim($branchResult->output()) : null;

        $authorResult = Process::env($env)->run('git --no-pager log -1 --pretty=format:%an');
        $author = $authorResult->successful() ? trim($authorResult->output()) : null;

        $messageResult = Process::env($env)->run('git --no-pager log -1 --pretty=format:%s');
        $message = $messageResult->successful() ? trim($messageResult->output()) : null;

        $dateResult = Process::env($env)->run("git --no-pager log -1 --pretty=format:%cd --date=format:'%Y-%m-%d %H:%M:%S'");
        $commitDate = $dateResult->successful() ? trim($dateResult->output()) : null;

        $tagProcess = Process::env($env)->run('git --no-pager describe --tags --exact-match');
        $tag = $tagProcess->successful() ? trim($tagProcess->output()) : null;

        return array_filter([
            'environment' => app()->environment(),
            'deployed_at' => $commitDate,
            'commit' => $commit,
            'branch' => ($branch !== null && $branch !== 'HEAD') ? $branch : null,
            'author' => $author ?: null,
            'commit_message' => $message ?: null,
            'tag' => $tag ?: null,
        ], fn ($v) => ! is_null($v) && $v !== '');
    }
}
