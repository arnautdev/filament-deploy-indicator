<?php

namespace Arnautdev\FilamentDeployIndicator\Commands;

use Arnautdev\FilamentDeployIndicator\Services\DeployHistoryService;
use Arnautdev\FilamentDeployIndicator\Services\GitDeployInfoGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class WriteDeployInfoCommand extends Command
{
    protected $signature = 'deploy-indicator:write
        {--env= : Environment name (e.g. production, staging)}
        {--deployed-at= : Deployed at datetime (default: now)}
        {--commit= : Commit hash}
        {--branch= : Branch name}
        {--author= : Author name}
        {--message= : Commit message}
        {--commit-url= : Commit URL}
        {--path= : Output path override}';

    protected $description = 'Write deploy-info.json for Filament Deploy Indicator';

    public function __construct(
        protected GitDeployInfoGenerator $generator,
        protected DeployHistoryService $history,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $path = $this->option('path') ?: config('filament-deploy-indicator.write_path');

        // Start with git data as base (if available)
        $base = $this->generator->generate();

        // Manual options override git data
        $overrides = array_filter([
            'environment' => $this->option('env'),
            'deployed_at' => $this->option('deployed-at'),
            'commit' => $this->option('commit'),
            'branch' => $this->option('branch'),
            'author' => $this->option('author'),
            'commit_message' => $this->option('message'),
            'commit_url' => $this->option('commit-url'),
        ], fn (bool | float | int | string | array | null $v): bool => ! is_null($v) && $v !== '');

        $data = array_merge($base, $overrides);

        // Ensure environment and deployed_at always have values
        $data['environment'] ??= app()->environment();
        $data['deployed_at'] ??= now()->toDateTimeString();

        File::ensureDirectoryExists(dirname((string) $path));
        File::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->history->record($data);

        $this->info("Deploy info written to: {$path}");

        return self::SUCCESS;
    }
}
