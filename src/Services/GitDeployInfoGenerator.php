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

        $commit = trim(Process::run('git rev-parse HEAD')->output());
        if ($commit === '') {
            return [];
        }

        $branch = trim(Process::run('git rev-parse --abbrev-ref HEAD')->output());
        $author = trim(Process::run('git log -1 --pretty=format:%an')->output());
        $message = trim(Process::run('git log -1 --pretty=format:%s')->output());
        $commitDate = trim(Process::run("git log -1 --pretty=format:%cd --date=format:'%Y-%m-%d %H:%M:%S'")->output());

        // tag if available on commit
        $tagProcess = Process::run('git describe --tags --exact-match');
        $tag = $tagProcess->successful() ? trim($tagProcess->output()) : null;

        return array_filter([
            'environment' => app()->environment(),
            'deployed_at' => $commitDate,
            'commit' => $commit,
            'branch' => $branch !== 'HEAD' ? $branch : null,
            'author' => $author ?: null,
            'commit_message' => $message ?: null,
            'tag' => $tag ?: null,
        ], fn ($v) => ! is_null($v) && $v !== '');
    }
}
