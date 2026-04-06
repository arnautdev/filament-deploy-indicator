@php
    $env = app()->environment();

    $map = config("filament-deploy-indicator.env_map.$env") ?? config('filament-deploy-indicator.default');
    $envLabel = $map['label'] ?? strtoupper($env);
    $color = $map['color'] ?? 'gray';

    $topbarShow = config('filament-deploy-indicator.topbar.show');
    $secondary = null;

    if ($topbarShow === 'commit') {
        $commit = data_get($deploy, 'commit');
        $len = (int) config('filament-deploy-indicator.topbar.commit_length', 7);
        $secondary = $commit
            ? (string) \Illuminate\Support\Str::of($commit)->limit($len, '')
            : null;
    }

    if ($topbarShow === 'deployed_at') {
        $deployedAt = data_get($deploy, 'deployed_at');
        $format = (string) config('filament-deploy-indicator.topbar.date_format', 'd.m H:i');
        $secondary = $deployedAt
            ? \Illuminate\Support\Carbon::parse($deployedAt)->format($format)
            : null;
    }

    if ($topbarShow === 'tag') {
        $secondary = data_get($deploy, 'tag');
    }

    if ($topbarShow === 'branch') {
        $secondary = data_get($deploy, 'branch');
    }
@endphp

<x-filament::dropdown width="sm" placement="bottom-start" class="fi-deploy-indicator">
        <x-slot name="trigger">
            <x-filament::button
                size="sm"
                :color="$color"
                icon="heroicon-m-server-stack"
                :tooltip="__('filament-deploy-indicator::deploy-indicator.click_to_view')"
                class="items-center gap-1 whitespace-nowrap"
            >
                ENV: {{ $envLabel }}
                @if ($secondary)
                    <span class="opacity-70">•</span>
                    <span class="font-mono opacity-80">{{ $secondary }}</span>
                @endif
                <x-heroicon-m-chevron-down class="w-3 h-3 opacity-70" />
            </x-filament::button>
        </x-slot>

        <x-filament::dropdown.header>
            {{ __('filament-deploy-indicator::deploy-indicator.deployment_info') }}
        </x-filament::dropdown.header>

        <x-filament::dropdown.list class="w-[420px] max-w-[90vw]">

            <x-filament::dropdown.list.item>
                <div class="text-xs text-gray-500">{{ __('filament-deploy-indicator::deploy-indicator.environment') }}</div>
                <div class="font-semibold">{{ $env }}</div>
            </x-filament::dropdown.list.item>

            <x-filament::dropdown.list.item>
                <div class="text-xs text-gray-500">{{ __('filament-deploy-indicator::deploy-indicator.deployed_at') }}</div>
                <div class="font-semibold">{{ $deploy['deployed_at'] ?? '-' }}</div>
            </x-filament::dropdown.list.item>

            <x-filament::dropdown.list.item>
                <div class="text-xs text-gray-500">{{ __('filament-deploy-indicator::deploy-indicator.branch') }}</div>
                <div class="font-semibold">{{ $deploy['branch'] ?? '-' }}</div>
            </x-filament::dropdown.list.item>

            @php
                $commit = data_get($deploy, 'commit');
                $commitUrl = data_get($deploy, 'commit_url');
            @endphp

            <x-filament::dropdown.list.item :href="$commitUrl">
                <div x-data="{ copied: false }" class="flex w-full items-start gap-2">
                    <div class="min-w-0 flex-1">
                        <div class="text-xs text-gray-500">{{ __('filament-deploy-indicator::deploy-indicator.commit') }}</div>
                        <div class="break-all font-mono text-sm">{{ $commit ?? '-' }}</div>
                    </div>
                    @if ($commit)
                        <button
                            type="button"
                            @click.prevent="navigator.clipboard.writeText('{{ $commit }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                            class="mt-0.5 flex-shrink-0 rounded p-1 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/5 dark:hover:text-gray-300"
                            :title="copied ? '{{ __('filament-deploy-indicator::deploy-indicator.copied') }}' : '{{ __('filament-deploy-indicator::deploy-indicator.copy') }}'"
                        >
                            <x-heroicon-m-clipboard-document x-show="!copied" class="h-4 w-4" />
                            <x-heroicon-m-check x-show="copied" class="h-4 w-4 text-green-500" />
                        </button>
                    @endif
                </div>
            </x-filament::dropdown.list.item>

            <x-filament::dropdown.list.item>
                <div class="text-xs text-gray-500">{{ __('filament-deploy-indicator::deploy-indicator.author') }}</div>
                <div class="font-semibold">{{ $deploy['author'] ?? '-' }}</div>
            </x-filament::dropdown.list.item>

            <x-filament::dropdown.list.item>
                <div class="text-xs text-gray-500">{{ __('filament-deploy-indicator::deploy-indicator.message') }}</div>
                <div class="font-semibold !break-words">{{ $deploy['commit_message'] ?? '-' }}</div>
            </x-filament::dropdown.list.item>

            @if (!empty($deploy['tag']))
                <x-filament::dropdown.list.item>
                    <div class="text-xs text-gray-500">{{ __('filament-deploy-indicator::deploy-indicator.tag') }}</div>
                    <div class="font-semibold break-words">{{ $deploy['tag'] }}</div>
                </x-filament::dropdown.list.item>
            @endif

        </x-filament::dropdown.list>
</x-filament::dropdown>
