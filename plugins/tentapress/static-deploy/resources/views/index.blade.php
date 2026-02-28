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

    <div class="grid gap-5 xl:grid-cols-3">
        <div class="space-y-5 xl:col-span-2">
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
                            <div>
                                <span class="tp-muted">Replacement rules:</span>
                                {{ (int) ($last['replacement_rules_applied'] ?? 0) }}
                            </div>
                            <div>
                                <span class="tp-muted">Files updated:</span>
                                {{ (int) ($last['replacement_files_updated'] ?? 0) }}
                            </div>
                            <div>
                                <span class="tp-muted">Matches:</span>
                                {{ (int) ($last['replacement_matches'] ?? 0) }}
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

            <div class="tp-metabox">
                <div class="tp-metabox__title">Replacement rules</div>
                <div class="tp-metabox__body space-y-4">
                    @if (!$canPersistRules)
                        <div class="tp-notice-warning mb-0">
                            Saved replacement rules need the Settings plugin to be enabled.
                        </div>
                    @else
                        <div class="grid gap-4 2xl:grid-cols-[minmax(0,1.6fr)_minmax(18rem,1fr)]">
                            <div class="tp-panel grid gap-4 text-sm sm:grid-cols-3 2xl:grid-cols-1">
                                <div class="space-y-2">
                                    <div class="tp-muted text-xs uppercase tracking-[0.18em]">Current state</div>
                                    <div class="text-2xl font-semibold">{{ $savedReplacementRuleCount }}</div>
                                    <div class="tp-help">
                                        {{ $savedReplacementRuleCount === 1 ? 'Saved rule ready for the next export.' : 'Saved rules ready for the next export.' }}
                                    </div>
                                </div>

                                <div class="space-y-2 border-t border-black/5 pt-4 sm:border-t-0 sm:border-l sm:pl-4 sm:pt-0 2xl:border-l-0 2xl:border-t 2xl:pl-0 2xl:pt-4">
                                    <div class="tp-muted text-xs uppercase tracking-[0.18em]">When they run</div>
                                    <div class="font-semibold">After the staged build, before ZIP creation</div>
                                    <div class="tp-help">
                                        Use rules to rewrite exported HTML, XML, CSS, JS, TXT, or JSON files without touching your source content.
                                    </div>
                                </div>

                                <div class="space-y-2 border-t border-black/5 pt-4 sm:border-t-0 sm:border-l sm:pl-4 sm:pt-0 2xl:border-l-0 2xl:border-t 2xl:pl-0 2xl:pt-4">
                                    <div class="tp-muted text-xs uppercase tracking-[0.18em]">How to disable</div>
                                    <div class="font-semibold">Reset to <code class="tp-code">[]</code></div>
                                    <div class="tp-help">
                                        An empty array disables replacements completely. File globs can target broad patterns like <code class="tp-code">*.html</code> or specific files like <code class="tp-code">sitemap.xml</code>.
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="tp-panel space-y-3">
                                    <div>
                                        <div class="tp-muted text-xs uppercase tracking-[0.18em]">Quick guide</div>
                                        <div class="mt-1 font-semibold">Rule object shape</div>
                                    </div>
                                    <ul class="list-disc space-y-2 pl-5 text-sm">
                                        <li><code class="tp-code">find</code> is the exact string to replace.</li>
                                        <li><code class="tp-code">replace</code> is the new value written into matching export files.</li>
                                        <li><code class="tp-code">files</code> is optional and accepts glob strings.</li>
                                    </ul>
                                </div>

                                <div class="tp-field">
                                    <label class="tp-label">Example payload</label>
                                    <textarea rows="12" class="tp-textarea font-mono text-xs" readonly>{{ $replacementRulesExample }}</textarea>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('tp.static.rules.update') }}" class="space-y-4">
                            @csrf

                            <div class="space-y-4">
                                <div class="tp-field">
                                    <label class="tp-label">Rules (JSON)</label>
                                    <textarea
                                        name="replacement_rules_json"
                                        rows="18"
                                        class="tp-textarea min-h-[26rem] font-mono text-xs leading-6">{{ $replacementRulesJson }}</textarea>
                                    <div class="tp-help mt-1 space-y-2">
                                        <p>Each rule needs <code class="tp-code">find</code> and <code class="tp-code">replace</code>. Add optional <code class="tp-code">files</code> globs to narrow the match scope.</p>
                                        <p>Leave <code class="tp-code">files</code> out to target exported text-like files automatically.</p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <button type="submit" name="rules_action" value="save" class="tp-button-primary">Save custom rules</button>
                                    <button type="submit" name="rules_action" value="load_example" class="tp-button-secondary">Load example</button>
                                    <button type="submit" name="rules_action" value="reset" class="tp-button-secondary">Reset to empty array</button>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-5 xl:col-span-1">
            <div class="tp-metabox">
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

            <div class="tp-metabox">
                <div class="tp-metabox__title">Stored exports</div>
                <div class="tp-metabox__body space-y-4">
                    @if (count($storedExports) === 0)
                        <div class="tp-muted text-sm">No stored exports yet.</div>
                    @else
                        <div class="tp-panel overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="tp-muted text-xs uppercase tracking-[0.18em]">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium">Archive</th>
                                        <th class="px-3 py-2 text-left font-medium">Generated</th>
                                        <th class="px-3 py-2 text-left font-medium">State</th>
                                        <th class="px-3 py-2 text-right font-medium">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($storedExports as $export)
                                        <tr class="border-t border-black/5 align-top">
                                            <td class="px-3 py-3">
                                                <div class="font-semibold">{{ $export['zip_name'] }}</div>
                                                <div class="tp-help mt-1">
                                                    <code class="tp-code">{{ $export['timestamp'] }}</code>
                                                </div>
                                            </td>
                                            <td class="px-3 py-3">{{ $export['generated_at_utc'] }}</td>
                                            <td class="px-3 py-3">
                                                @if ($export['is_latest'])
                                                    <span class="rounded-full bg-black/5 px-2 py-1 text-xs font-semibold">Latest</span>
                                                @else
                                                    <span class="tp-muted text-xs">Stored</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-3 text-right">
                                                <a
                                                    href="{{ route('tp.static.download.archive', ['timestamp' => $export['timestamp']]) }}"
                                                    class="tp-button-secondary">
                                                    Download
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
