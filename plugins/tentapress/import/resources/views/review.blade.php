@extends('tentapress-admin::layouts.shell')

@section('title', 'Import Review')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Review import</h1>
            <p class="tp-description">Review what will be added and choose how settings should be handled.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('tp.import.index') }}" class="tp-button-secondary">Back to upload</a>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__title">Import summary</div>
        <div class="tp-metabox__body space-y-4">
            <div class="tp-panel">
                <div class="grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">File format</div>
                        <div class="mt-1">
                            @if (($meta['source_format'] ?? '') === 'wxr')
                                WordPress WXR ({{ $meta['wxr_version'] ?? 'unknown' }})
                            @else
                                TentaPress JSON bundle v{{ $meta['schema_version'] ?? 1 }}
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">Created</div>
                        <div class="mt-1">{{ $meta['generated_at_utc'] ?: 'Unknown' }}</div>
                    </div>

                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">Pages</div>
                        <div class="mt-1">{{ (int) ($summary['pages'] ?? 0) }}</div>
                    </div>
                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">Posts</div>
                        <div class="mt-1">{{ (int) ($summary['posts'] ?? 0) }}</div>
                    </div>
                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">Media</div>
                        <div class="mt-1">{{ (int) ($summary['media'] ?? 0) }}</div>
                    </div>
                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">Settings</div>
                        <div class="mt-1">{{ (int) ($summary['settings'] ?? 0) }}</div>
                    </div>

                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">SEO records</div>
                        <div class="mt-1">{{ (int) ($summary['seo'] ?? 0) }}</div>
                    </div>
                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">Categories</div>
                        <div class="mt-1">{{ (int) ($summary['categories'] ?? 0) }}</div>
                    </div>
                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">Tags</div>
                        <div class="mt-1">{{ (int) ($summary['tags'] ?? 0) }}</div>
                    </div>
                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">Enabled plugins</div>
                        <div class="mt-1">{{ (int) ($summary['enabled_plugins'] ?? 0) }}</div>
                    </div>

                    <div class="md:col-span-2">
                        <div class="tp-muted text-xs font-semibold uppercase">Theme</div>
                        <div class="mt-1">
                            @if (!empty($summary['theme_active_id']))
                                <code class="tp-code">{{ $summary['theme_active_id'] }}</code>
                            @else
                                <span class="tp-muted">No theme details found</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('tp.import.run') }}"
                class="space-y-4"
                data-confirm="Start import now?">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}" />

                <div class="tp-panel space-y-4">
                    <div>
                        <div class="tp-label mb-2">Pages</div>
                        <label class="flex items-center gap-3">
                            <input type="radio" name="pages_mode" class="tp-checkbox" value="create_only" checked />
                            <span class="text-sm">
                                <span class="font-semibold">Create only</span>
                                <span class="tp-muted mt-1 block text-xs">
                                    Existing page URLs are kept as-is. If needed, new pages use
                                    <code class="tp-code">-2</code>
                                    ,
                                    <code class="tp-code">-3</code>
                                    , etc.
                                </span>
                            </span>
                        </label>
                    </div>

                    <div class="tp-divider"></div>

                    <div>
                        <div class="tp-label mb-2">Settings</div>

                        <label class="flex items-center gap-3">
                            <input type="radio" name="settings_mode" class="tp-checkbox" value="merge" checked />
                            <span class="text-sm">
                                <span class="font-semibold">Merge</span>
                                <span class="tp-muted mt-1 block text-xs">
                                    Add only missing settings. Existing settings stay the same.
                                </span>
                            </span>
                        </label>

                        <label class="mt-2 flex items-center gap-3">
                            <input type="radio" name="settings_mode" class="tp-checkbox" value="overwrite" />
                            <span class="text-sm">
                                <span class="font-semibold">Overwrite</span>
                                <span class="tp-muted mt-1 block text-xs">
                                    Update existing settings to match this file.
                                </span>
                            </span>
                        </label>
                    </div>

                    <div class="tp-divider"></div>

                    <label class="flex items-center gap-3">
                        <input
                            type="checkbox"
                            name="include_posts"
                            class="tp-checkbox"
                            value="1"
                            @checked((int) ($summary['posts'] ?? 0) > 0)
                        />
                        <span class="text-sm">
                            <span class="font-semibold">Import posts</span>
                            <span class="tp-muted mt-1 block text-xs">
                                Add posts from this file. If needed, duplicate URLs use
                                <code class="tp-code">-2</code>
                                ,
                                <code class="tp-code">-3</code>
                                , etc.
                            </span>
                        </span>
                    </label>

                    <label class="flex items-center gap-3">
                        <input
                            type="checkbox"
                            name="include_media"
                            class="tp-checkbox"
                            value="1"
                            @checked((int) ($summary['media'] ?? 0) > 0)
                        />
                        <span class="text-sm">
                            <span class="font-semibold">Import media details</span>
                            <span class="tp-muted mt-1 block text-xs">
                                Add media records when the file path is unique. Files are not copied in this version.
                            </span>
                        </span>
                    </label>

                    <div class="tp-divider"></div>

                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="include_seo" class="tp-checkbox" value="1" />
                        <span class="text-sm">
                            <span class="font-semibold">Import SEO data</span>
                            <span class="tp-muted mt-1 block text-xs">
                                SEO data is imported when its related page or post already exists on this site.
                            </span>
                        </span>
                    </label>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="tp-button-primary">
                        Start import
                    </button>
                    <a href="{{ route('tp.import.index') }}" class="tp-button-secondary">Cancel</a>
                </div>

                <div class="tp-muted text-xs">The uploaded file is removed from temporary storage after import.</div>
            </form>
        </div>
    </div>
@endsection
