@extends('tentapress-admin::layouts.shell')

@section('title', 'Static Deploy')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Static Deploy</h1>
            <p class="tp-description">
                Generate a downloadable static ZIP file of your site.
            </p>
        </div>

        @if ($last && !empty($last['zip_path']) && is_file($last['zip_path']))
            <div class="flex gap-2">
                <a href="{{ route('tp.static.download') }}" class="tp-button-primary">Download latest ZIP</a>
            </div>
        @endif
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__title">Last build</div>
        <div class="tp-metabox__body space-y-3">
            @if (!$last)
                <div class="tp-muted text-sm">No builds yet.</div>
            @else
                <div class="tp-panel space-y-1 text-sm">
                    <div>
                        <span class="tp-muted">Timestamp:</span>
                        <span class="font-semibold">{{ $last['timestamp'] ?? '' }}</span>
                    </div>
                    <div>
                        <span class="tp-muted">Generated:</span>
                        {{ $last['generated_at_utc'] ?? '' }}
                    </div>
                    <div>
                        <span class="tp-muted">Pages:</span>
                        {{ (int) ($last['pages_written'] ?? 0) }} / {{ (int) ($last['pages_total'] ?? 0) }}
                    </div>
                    <div>
                        <span class="tp-muted">ZIP:</span>
                        <code class="tp-code">{{ basename((string) ($last['zip_path'] ?? '')) }}</code>
                    </div>
                </div>

                @php
                    $warnings = is_array($last['warnings'] ?? null) ? $last['warnings'] : [];
                @endphp

                @if (count($warnings) > 0)
                    <div class="tp-notice-warning">
                        <div class="mb-2 font-semibold">Warnings found ({{ count($warnings) }})</div>
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach ($warnings as $w)
                                <li>{{ $w }}</li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="tp-notice-success mb-0">No warnings found.</div>
                @endif
            @endif
        </div>
    </div>

    <div class="tp-metabox mt-5">
        <div class="tp-metabox__title">Generate build</div>
        <div class="tp-metabox__body space-y-4">
            <form
                method="POST"
                action="{{ route('tp.static.generate') }}"
                class="space-y-4"
                data-confirm="Generate a new static build now?">
                @csrf

                <div class="tp-panel space-y-3">
                    <label class="flex items-center gap-3">
                        <input type="checkbox" class="tp-checkbox" name="include_favicon" value="1" checked />
                        <span class="text-sm"><span class="font-semibold">Include favicon.ico</span></span>
                    </label>

                    <label class="flex items-center gap-3">
                        <input type="checkbox" class="tp-checkbox" name="include_robots" value="1" checked />
                        <span class="text-sm"><span class="font-semibold">Include robots.txt</span></span>
                    </label>

                    <label class="flex items-center gap-3">
                        <input type="checkbox" class="tp-checkbox" name="compress_html" value="1" />
                        <span class="text-sm">
                            <span class="font-semibold">Compress HTML files</span>
                            <span class="tp-muted mt-1 block text-xs">
                                Removes extra spaces and comments (keeps pre/textarea/script/style as-is).
                            </span>
                        </span>
                    </label>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="tp-button-primary">
                        Generate static build
                    </button>
                </div>

                <div class="tp-muted text-xs">
                    Output:
                    <code class="tp-code">storage/app/tp-static/</code>
                </div>
            </form>
        </div>
    </div>
@endsection
