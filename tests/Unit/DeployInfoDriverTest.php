<?php

use Arnautdev\FilamentDeployIndicator\Services\DeployInfoGeneratorManager;
use Arnautdev\FilamentDeployIndicator\Services\Generators\CompositeDeployInfoGenerator;
use Arnautdev\FilamentDeployIndicator\Services\Generators\Contracts\DeployInfoGenerator;
use Arnautdev\FilamentDeployIndicator\Services\Generators\StaticDeployInfoGenerator;
use Arnautdev\FilamentDeployIndicator\Services\GitDeployInfoGenerator;

/**
 * Set an env var so Laravel's env() / Env class can read it.
 */
function setDeployEnv(string $key, ?string $value): void
{
    if ($value === null) {
        putenv($key);
        unset($_ENV[$key], $_SERVER[$key]);

        return;
    }

    putenv("{$key}={$value}");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

afterEach(function (): void {
    foreach ([
        'DEPLOY_ENV', 'DEPLOY_AT', 'DEPLOY_COMMIT', 'DEPLOY_BRANCH',
        'DEPLOY_AUTHOR', 'DEPLOY_COMMIT_MESSAGE', 'DEPLOY_COMMIT_URL', 'DEPLOY_TAG',
    ] as $key) {
        setDeployEnv($key, null);
    }
});

it('static driver returns empty when no env vars are set', function (): void {
    expect((new StaticDeployInfoGenerator)->canRun())->toBeFalse()
        ->and((new StaticDeployInfoGenerator)->generate())->toBe([]);
});

it('static driver reads deploy info from env vars', function (): void {
    setDeployEnv('DEPLOY_COMMIT', 'abc1234');
    setDeployEnv('DEPLOY_BRANCH', 'main');
    setDeployEnv('DEPLOY_TAG', 'v1.2.3');

    $generator = new StaticDeployInfoGenerator;

    expect($generator->canRun())->toBeTrue();

    $data = $generator->generate();

    expect($data['commit'])->toBe('abc1234')
        ->and($data['branch'])->toBe('main')
        ->and($data['tag'])->toBe('v1.2.3')
        ->and($data)->toHaveKey('environment');
});

it('static driver canRun requires a commit or tag, not just a branch', function (): void {
    setDeployEnv('DEPLOY_BRANCH', 'main');

    expect((new StaticDeployInfoGenerator)->canRun())->toBeFalse();
});

it('static driver respects custom env var names from config', function (): void {
    config()->set('filament-deploy-indicator.drivers.static.commit', 'MY_SHA');
    setDeployEnv('MY_SHA', 'deadbeef');

    expect((new StaticDeployInfoGenerator)->generate())
        ->toMatchArray(['commit' => 'deadbeef']);

    setDeployEnv('MY_SHA', null);
});

it('manager resolves a single string driver', function (): void {
    $manager = app(DeployInfoGeneratorManager::class);

    expect($manager->make('git'))->toBeInstanceOf(GitDeployInfoGenerator::class)
        ->and($manager->make('static'))->toBeInstanceOf(StaticDeployInfoGenerator::class);
});

it('manager resolves an array driver into a composite', function (): void {
    $manager = app(DeployInfoGeneratorManager::class);

    expect($manager->make(['static', 'git']))->toBeInstanceOf(CompositeDeployInfoGenerator::class);
});

it('manager throws on an unknown driver', function (): void {
    app(DeployInfoGeneratorManager::class)->make('nope');
})->throws(InvalidArgumentException::class);

it('manager supports custom registered drivers', function (): void {
    $custom = new class implements DeployInfoGenerator
    {
        public function canRun(): bool
        {
            return true;
        }

        public function generate(): array
        {
            return ['commit' => 'custom'];
        }
    };

    $manager = app(DeployInfoGeneratorManager::class);
    $manager->extend('custom', fn () => $custom);

    expect($manager->make('custom'))->toBe($custom);
});

it('composite falls back to the next generator when the first yields nothing', function (): void {
    $empty = new class implements DeployInfoGenerator
    {
        public function canRun(): bool
        {
            return false;
        }

        public function generate(): array
        {
            return [];
        }
    };

    $filled = new class implements DeployInfoGenerator
    {
        public function canRun(): bool
        {
            return true;
        }

        public function generate(): array
        {
            return ['commit' => 'fallback'];
        }
    };

    $composite = new CompositeDeployInfoGenerator([$empty, $filled]);

    expect($composite->canRun())->toBeTrue()
        ->and($composite->generate())->toBe(['commit' => 'fallback']);
});

it('binds the configured driver to the DeployInfoGenerator contract', function (): void {
    config()->set('filament-deploy-indicator.driver', 'static');
    app()->forgetInstance(DeployInfoGenerator::class);

    expect(app(DeployInfoGenerator::class))->toBeInstanceOf(StaticDeployInfoGenerator::class);
});
