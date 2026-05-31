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

<x-filament::dropdown width="md" placement="bottom-start" shift class="fi-deploy-indicator">
    <x-slot name="trigger">
        <x-filament::button
            size="sm"
            :color="$color"
            icon="heroicon-m-server-stack"
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

    <x-filament::dropdown.list>
        <x-filament::dropdown.list.item>
            <div class="text-xs text-gray-500">{{ __('filament-deploy-indicator::deploy-indicator.environment') }}</div>
            <div class="font-semibold">{{ $env }}</div>
        </x-filament::dropdown.list.item>
    </x-filament::dropdown.list>

    <x-filament::dropdown.list>
        <x-filament::dropdown.list.item>
            <div class="text-xs text-gray-500">{{ __('filament-deploy-indicator::deploy-indicator.deployed_at') }}</div>
            <div class="font-semibold">{{ $deploy['deployed_at'] ?? '-' }}</div>
        </x-filament::dropdown.list.item>
    </x-filament::dropdown.list>

    <x-filament::dropdown.list>
        <x-filament::dropdown.list.item>
            <div class="text-xs text-gray-500">{{ __('filament-deploy-indicator::deploy-indicator.branch') }}</div>
            <div class="font-semibold">{{ $deploy['branch'] ?? '-' }}</div>
        </x-filament::dropdown.list.item>
    </x-filament::dropdown.list>

    @php
        $commit = data_get($deploy, 'commit');
        $commitUrl = data_get($deploy, 'commit_url');
        $commitJs = \Illuminate\Support\Js::from($commit);
    @endphp

    <x-filament::dropdown.list>
        <x-filament::dropdown.list.item tag="div">
            <div
                x-data="{
                    copied: false,
                    copy(text) {
                        const done = () => { this.copied = true; setTimeout(() => this.copied = false, 2000) }
                        if (navigator.clipboard && window.isSecureContext) {
                            navigator.clipboard.writeText(text).then(done).catch(() => {})
                        } else {
                            const ta = document.createElement('textarea')
                            ta.value = text
                            ta.style.position = 'fixed'
                            ta.style.opacity = '0'
                            document.body.appendChild(ta)
                            ta.select()
                            try { document.execCommand('copy'); done() } finally { ta.remove() }
                        }
                    },
                }"
                class="flex w-full items-start gap-2"
            >
                <div class="min-w-0 flex-1">
                    <div
                        class="text-xs text-gray-500">{{ __('filament-deploy-indicator::deploy-indicator.commit') }}</div>
                    @if ($commitUrl)
                        <a href="{{ $commitUrl }}" target="_blank" rel="noopener"
                           class="break-all font-mono font-semibold hover:underline">{{ $commit ?? '-' }}</a>
                    @else
                        <div class="break-all font-mono font-semibold">{{ $commit ?? '-' }}</div>
                    @endif
                </div>
                @if ($commit)
                    <x-filament::icon-button
                        icon="heroicon-m-clipboard-document"
                        color="gray"
                        size="sm"
                        x-show="!copied"
                        @click.prevent.stop="copy({{ $commitJs }})"
                        :label="__('filament-deploy-indicator::deploy-indicator.copy')"
                        :tooltip="__('filament-deploy-indicator::deploy-indicator.copy')"
                    />
                    <x-filament::icon-button
                        icon="heroicon-m-check"
                        color="success"
                        size="sm"
                        x-show="copied"
                        x-cloak
                        tag="span"
                        :label="__('filament-deploy-indicator::deploy-indicator.copied')"
                        :tooltip="__('filament-deploy-indicator::deploy-indicator.copied')"
                    />
                @endif
            </div>
        </x-filament::dropdown.list.item>
    </x-filament::dropdown.list>

    <x-filament::dropdown.list>
        <x-filament::dropdown.list.item>
            <div class="text-xs text-gray-500">{{ __('filament-deploy-indicator::deploy-indicator.author') }}</div>
            <div class="font-semibold">{{ $deploy['author'] ?? '-' }}</div>
        </x-filament::dropdown.list.item>
    </x-filament::dropdown.list>

    <x-filament::dropdown.list>
        <x-filament::dropdown.list.item>
            <div class="text-xs text-gray-500">{{ __('filament-deploy-indicator::deploy-indicator.message') }}</div>
            <div class="font-semibold break-words">{{ $deploy['commit_message'] ?? '-' }}</div>
        </x-filament::dropdown.list.item>
    </x-filament::dropdown.list>

    @if (!empty($deploy['tag']))
        <x-filament::dropdown.list>
            <x-filament::dropdown.list.item>
                <div class="text-xs text-gray-500">{{ __('filament-deploy-indicator::deploy-indicator.tag') }}</div>
                <div class="font-semibold break-words">{{ $deploy['tag'] }}</div>
            </x-filament::dropdown.list.item>
        </x-filament::dropdown.list>
    @endif

    @if (!empty($history))
        <x-filament::dropdown.list>
            <x-filament::dropdown.list.item>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    {{ __('filament-deploy-indicator::deploy-indicator.recent_deploys') }}
                </div>
                <div class="mt-2 space-y-1.5">
                    @foreach ($history as $entry)
                        @php
                            $entryCommit = data_get($entry, 'commit');
                            $entryShort = $entryCommit ? \Illuminate\Support\Str::limit($entryCommit, 7, '') : null;
                            $entryAuthor = data_get($entry, 'author');
                            $entryDate = data_get($entry, 'deployed_at') ?? data_get($entry, 'recorded_at');
                        @endphp
                        <div class="flex items-baseline gap-2 text-xs">
                            @if ($entryShort)
                                <span
                                    class="font-mono font-semibold text-gray-700 dark:text-gray-300">{{ $entryShort }}</span>
                            @endif
                            @if ($entryAuthor)
                                <span class="truncate text-gray-500">{{ $entryAuthor }}</span>
                            @endif
                            @if ($entryDate)
                                <span class="ml-auto whitespace-nowrap text-gray-400">{{ $entryDate }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-filament::dropdown.list.item>
        </x-filament::dropdown.list>
    @endif
</x-filament::dropdown>
