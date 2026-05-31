<?php

namespace Arnautdev\FilamentDeployIndicator\Commands;

use Arnautdev\FilamentDeployIndicator\Services\DeployInfoService;
use Arnautdev\FilamentDeployIndicator\Services\GitDeployInfoGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckDeployIndicatorCommand extends Command
{
    protected $signature = 'deploy-indicator:check';

    protected $description = 'Check Filament Deploy Indicator configuration and environment';

    public function __construct(
        protected GitDeployInfoGenerator $generator,
        protected DeployInfoService $service,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Filament Deploy Indicator — Setup Check');
        $this->newLine();

        $configPublished = file_exists(config_path('filament-deploy-indicator.php'));
        $this->checkLine(
            $configPublished,
            'Config file published',
            'Config not published — run: php artisan vendor:publish --tag="filament-deploy-indicator-config"'
        );

        $gitRoot = config('filament-deploy-indicator.git_root', base_path());
        $gitAvailable = $this->generator->canRun();
        $this->checkLine(
            $gitAvailable,
            "Git repository detected at: {$gitRoot}",
            "No .git found at: {$gitRoot} — set DEPLOY_INDICATOR_GIT_ROOT or update git_root in config"
        );

        if ($gitAvailable) {
            $generated = $this->generator->generate();
            $gitReadable = $generated !== [];
            $shortCommit = substr($generated['commit'] ?? '', 0, 7);
            $this->checkLine(
                $gitReadable,
                "Git info readable (commit: {$shortCommit}, branch: " . ($generated['branch'] ?? 'detached') . ')',
                'Git info could not be read — ensure git binary is available and the repository has commits'
            );
        }

        $filePath = config('filament-deploy-indicator.file_path');
        $fileExists = $filePath && File::exists($filePath);
        $this->checkLine(
            $fileExists,
            "deploy-info.json found at: {$filePath}",
            "deploy-info.json not found at: {$filePath} — run: php artisan deploy-indicator:write --from-git"
        );

        if ($fileExists) {
            $data = json_decode(File::get($filePath), true);
            $jsonValid = is_array($data);
            $this->checkLine(
                $jsonValid,
                'deploy-info.json is valid JSON',
                'deploy-info.json contains invalid JSON — regenerate: php artisan deploy-indicator:write --from-git'
            );
        }

        $writePath = config('filament-deploy-indicator.write_path')
            ?: config('filament-deploy-indicator.file_path');
        $writeDir = dirname((string) $writePath);
        $writable = is_dir($writeDir) && is_writable($writeDir);
        $this->checkLine(
            $writable,
            "Write path is writable: {$writeDir}",
            "Write path not writable: {$writeDir} — check directory permissions"
        );

        $deploy = $this->service->get();
        if ($deploy !== []) {
            $this->newLine();
            $this->info('Current deployment info:');
            $this->table(
                ['Key', 'Value'],
                collect($deploy)->map(fn ($v, $k): array => [$k, $v])->values()->all()
            );
        }

        return self::SUCCESS;
    }

    private function checkLine(bool $ok, string $okMessage, string $failMessage): void
    {
        if ($ok) {
            $this->line("  <fg=green>✓</> {$okMessage}");
        } else {
            $this->line("  <fg=red>✗</> {$failMessage}");
        }
    }
}
