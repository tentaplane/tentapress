@extends('tentapress-admin::layouts.shell')

@section('title', 'Import Review')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Review import</h1>
            <p class="tp-description">Confirm what will be imported and choose the import mode.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('tp.import.index') }}" class="tp-button-secondary">Back</a>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__title">Bundle summary</div>
        <div class="tp-metabox__body space-y-4">
            <div class="tp-panel">
                <div class="grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">Schema</div>
                        <div class="mt-1">v{{ $meta['schema_version'] ?? 1 }}</div>
                    </div>
                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">Generated</div>
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
                        <div class="tp-muted text-xs font-semibold uppercase">SEO rows</div>
                        <div class="mt-1">{{ (int) ($summary['seo'] ?? 0) }}</div>
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
                                <span class="tp-muted">No theme metadata</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('tp.import.run') }}"
                class="space-y-4"
                data-confirm="Run import now?">
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
                                    Existing slugs will not be overwritten — new pages will be created with
                                    <code class="tp-code">-2</code>
                                    ,
                                    <code class="tp-code">-3</code>
                                    , … suffixes.
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
                                    Only create missing keys. Existing keys stay unchanged.
                                </span>
                            </span>
                        </label>

                        <label class="mt-2 flex items-center gap-3">
                            <input type="radio" name="settings_mode" class="tp-checkbox" value="overwrite" />
                            <span class="text-sm">
                                <span class="font-semibold">Overwrite</span>
                                <span class="tp-muted mt-1 block text-xs">
                                    Existing keys will be updated to match the bundle.
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
                                Creates posts from the bundle. Existing slugs will be de-duped with
                                <code class="tp-code">-2</code>
                                ,
                                <code class="tp-code">-3</code>
                                , … suffixes.
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
                            <span class="font-semibold">Import media metadata</span>
                            <span class="tp-muted mt-1 block text-xs">
                                Creates media rows if the file path is unique. Files are not copied in v0.
                            </span>
                        </span>
                    </label>

                    <div class="tp-divider"></div>

                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="include_seo" class="tp-checkbox" value="1" />
                        <span class="text-sm">
                            <span class="font-semibold">Import SEO data</span>
                            <span class="tp-muted mt-1 block text-xs">
                                Best effort in v0. SEO rows import only where the referenced page or post ID exists in
                                this installation.
                            </span>
                        </span>
                    </label>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="tp-button-primary">
                        Run import
                    </button>
                    <a href="{{ route('tp.import.index') }}" class="tp-button-secondary">Cancel</a>
                </div>

                <div class="tp-muted text-xs">After import, the temporary bundle is deleted from storage.</div>
            </form>
        </div>
    </div>
@endsection
