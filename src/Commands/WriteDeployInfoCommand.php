<?php

namespace Arnautdev\FilamentDeployIndicator\Commands;

use Arnautdev\FilamentDeployIndicator\Services\GitDeployInfoGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class WriteDeployInfoCommand extends Command
{
    protected $signature = 'deploy-indicator:write
        {--from-git : Auto-fill data from git repository (if available)}
        {--env= : Environment name (e.g. production, staging)}
        {--deployed-at= : Deployed at datetime (default: now)}
        {--commit= : Commit hash}
        {--branch= : Branch name}
        {--author= : Author name}
        {--message= : Commit message}
        {--commit-url= : Commit URL}
        {--path= : Output path override}';

    protected $description = 'Write deploy-info.json for Filament Deploy Indicator';

    public function __construct(protected GitDeployInfoGenerator $generator)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $path = $this->option('path') ?: config('filament-deploy-indicator.write_path');

        $fromGit = (bool) $this->option('from-git');
        if ($fromGit) {
            $generated = $this->generator->generate();

            File::ensureDirectoryExists(dirname($path));
            File::put(
                $path,
                json_encode($generated, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );

            $this->info("Deploy info written to: {$path}");

            return self::SUCCESS;
        }

        $data = [
            'environment' => $this->option('env') ?: app()->environment(),
            'deployed_at' => $this->option('deployed-at') ?: now()->toDateTimeString(),
            'commit' => $this->option('commit'),
            'branch' => $this->option('branch'),
            'author' => $this->option('author'),
            'commit_message' => $this->option('message'),
            'commit_url' => $this->option('commit-url'),
        ];

        $data = array_filter($data, fn ($v) => ! is_null($v) && $v !== '');

        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info("Deploy info written to: {$path}");

        return self::SUCCESS;
    }
}
