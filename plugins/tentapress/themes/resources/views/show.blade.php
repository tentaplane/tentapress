@extends('tentapress-admin::layouts.shell')

@section('title', 'Theme Details')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">{{ $theme['name'] }}</h1>
            <p class="tp-description">
                <code class="tp-code">{{ $theme['id'] }}</code>
                @if (! empty($theme['version']))
                    <span class="mx-1">·</span>
                    <span class="tp-muted">v{{ $theme['version'] }}</span>
                @endif
            </p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('tp.themes.index') }}" class="tp-button-secondary">Back</a>

            @php
                $isActive = ($activeId !== null && $activeId === $theme['id']);
            @endphp

            @if (! $isActive)
                <form method="POST" action="{{ route('tp.themes.activate') }}">
                    @csrf
                    <input type="hidden" name="theme_id" value="{{ $theme['id'] }}" />
                    <button type="submit" class="tp-button-primary">Activate</button>
                </form>
            @else
                <span class="tp-button-disabled">Active</span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="space-y-5 lg:col-span-2">
            @if (! empty($screenshotUrl))
                <div class="tp-metabox">
                    <div class="tp-metabox__title">Screenshot</div>
                    <div class="tp-metabox__body">
                        <img
                            src="{{ $screenshotUrl }}"
                            alt=""
                            class="w-full rounded border border-black/10 bg-white" />
                    </div>
                </div>
            @endif

            <div class="tp-metabox">
                <div class="tp-metabox__title">Description</div>
                <div class="tp-metabox__body">
                    @if (! empty($theme['description']))
                        <div class="text-sm text-black/80">{{ $theme['description'] }}</div>
                    @else
                        <div class="tp-muted text-sm">No description provided.</div>
                    @endif
                </div>
            </div>

            <div class="tp-metabox">
                <div class="tp-metabox__title">Manifest</div>
                <div class="tp-metabox__body">
                    <pre class="tp-pre">{{ $theme['manifest_pretty'] }}</pre>
                </div>
            </div>
        </div>

        <div class="space-y-5">
            <div class="tp-metabox">
                <div class="tp-metabox__title">Details</div>
                <div class="tp-metabox__body space-y-2 text-sm">
                    <div>
                        <span class="tp-muted">Path:</span>
                        <code class="tp-code">{{ $theme['path'] }}</code>
                    </div>
                    <div>
                        <span class="tp-muted">Status:</span>
                        @if ($isActive)
                            <span class="font-semibold">Active</span>
                        @else
                            <span class="font-semibold">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="tp-metabox">
                <div class="tp-metabox__title">Layouts</div>
                <div class="tp-metabox__body">
                    @php
                        $layouts = is_array($theme['layouts'] ?? null) ? $theme['layouts'] : [];
                    @endphp

                    @if (count($layouts) === 0)
                        <div class="tp-muted text-sm">No layouts declared.</div>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach ($layouts as $layout)
                                @php
                                    $key = isset($layout['key']) ? (string) $layout['key'] : '';
                                    $label = isset($layout['label']) ? (string) $layout['label'] : $key;
                                @endphp

                                @if ($key !== '')
                                    <span
                                        class="inline-flex items-center rounded border border-black/10 bg-[#f6f7f7] px-2 py-1 text-xs text-black/70">
                                        {{ $label }}
                                        <span class="tp-code ml-1 text-[10px] text-black/50">{{ $key }}</span>
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
