@extends('tentapress-admin::layouts.shell')

@section('title', 'Import Review')

@section('content')
    @php
        $isWxr = ($meta['source_format'] ?? '') === 'wxr';
    @endphp

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
                        <div class="tp-muted text-xs font-semibold uppercase">Unsupported items</div>
                        <div class="mt-1">{{ (int) ($summary['unsupported_items'] ?? 0) }}</div>
                    </div>
                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">Featured image refs</div>
                        <div class="mt-1">{{ (int) ($summary['featured_image_refs'] ?? 0) }}</div>
                    </div>
                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">Featured refs resolved</div>
                        <div class="mt-1">{{ (int) ($summary['featured_image_resolved'] ?? 0) }}</div>
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

            @php
                $unsupportedTypes = $summary['unsupported_types'] ?? [];
            @endphp

            @if (is_array($unsupportedTypes) && count($unsupportedTypes) > 0)
                <div class="tp-panel">
                    <div class="tp-label mb-2">Unsupported entity types</div>
                    <ul class="space-y-1 text-sm">
                        @foreach ($unsupportedTypes as $type => $count)
                            <li>
                                <code class="tp-code">{{ (string) $type }}</code>
                                <span class="tp-muted">({{ (int) $count }})</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $unsupportedSamples = $summary['unsupported_samples'] ?? [];
            @endphp

            @if (is_array($unsupportedSamples) && count($unsupportedSamples) > 0)
                <div class="tp-panel">
                    <div class="tp-label mb-2">Unsupported sample items</div>
                    <ul class="space-y-1 text-sm">
                        @foreach ($unsupportedSamples as $sample)
                            <li>
                                <code class="tp-code">{{ (string) ($sample['type'] ?? 'unknown') }}</code>
                                <span class="tp-muted">
                                    {{ (string) ($sample['title'] ?? 'Untitled') }}
                                    @if (!empty($sample['post_id']))
                                        · ID {{ (string) $sample['post_id'] }}
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $urlMappingsPreview = $summary['url_mappings_preview'] ?? [];
            @endphp

            @if (is_array($urlMappingsPreview) && count($urlMappingsPreview) > 0)
                <div class="tp-panel">
                    <div class="tp-label mb-2">URL mapping preview</div>
                    <div class="tp-muted mb-2 text-xs">First {{ count($urlMappingsPreview) }} mapped page/post URLs for redirect planning.</div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-black/10 text-left text-xs uppercase tracking-wide text-black/60">
                                    <th class="py-2 pr-4">Type</th>
                                    <th class="py-2 pr-4">Source</th>
                                    <th class="py-2 pr-4">Destination</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($urlMappingsPreview as $mapping)
                                    <tr class="border-b border-black/5 align-top">
                                        <td class="py-2 pr-4"><code class="tp-code">{{ (string) ($mapping['type'] ?? 'unknown') }}</code></td>
                                        <td class="py-2 pr-4">
                                            @if (!empty($mapping['source_url']))
                                                <code class="tp-code">{{ (string) $mapping['source_url'] }}</code>
                                            @elseif (!empty($mapping['source_post_id']))
                                                <span class="tp-muted">Post ID {{ (string) $mapping['source_post_id'] }}</span>
                                            @else
                                                <span class="tp-muted">Unknown source</span>
                                            @endif
                                        </td>
                                        <td class="py-2 pr-4"><code class="tp-code">{{ (string) ($mapping['destination_url'] ?? '') }}</code></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('tp.import.run') }}"
                class="space-y-4"
                id="tp-import-run-form"
                data-stream-url="{{ route('tp.import.run.stream') }}"
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
                        @if ($isWxr)
                            <input type="hidden" name="settings_mode" value="merge" />
                            <div class="tp-muted text-sm">
                                Settings are not included in WordPress WXR imports.
                            </div>
                        @else
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
                        @endif
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
                    <button type="submit" class="tp-button-primary" id="tp-import-run-submit">
                        Start import
                    </button>
                    <a href="{{ route('tp.import.index') }}" class="tp-button-secondary">Cancel</a>
                </div>

                <div class="tp-muted text-xs">The uploaded file is removed from temporary storage after import.</div>
            </form>

            <div id="tp-import-progress-panel" class="tp-panel hidden">
                <div class="tp-label mb-2">Live import progress</div>
                <div id="tp-import-progress-status" class="tp-muted text-sm">Waiting to start...</div>
                <ul id="tp-import-progress-list" class="mt-3 max-h-72 space-y-1 overflow-auto text-sm"></ul>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const form = document.getElementById('tp-import-run-form');
            const submitButton = document.getElementById('tp-import-run-submit');
            const panel = document.getElementById('tp-import-progress-panel');
            const statusNode = document.getElementById('tp-import-progress-status');
            const listNode = document.getElementById('tp-import-progress-list');

            if (!form || !submitButton || !panel || !statusNode || !listNode) {
                return;
            }

            const streamUrl = form.getAttribute('data-stream-url');
            if (!streamUrl || typeof fetch !== 'function') {
                return;
            }

            const appendLine = (line) => {
                const li = document.createElement('li');
                li.textContent = line;
                listNode.appendChild(li);
                listNode.scrollTop = listNode.scrollHeight;
            };

            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                panel.classList.remove('hidden');
                submitButton.setAttribute('disabled', 'disabled');
                submitButton.textContent = 'Importing...';
                statusNode.textContent = 'Import started...';
                listNode.innerHTML = '';

                try {
                    const response = await fetch(streamUrl, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/event-stream',
                            'X-CSRF-TOKEN': form.querySelector('input[name=\"_token\"]')?.value || '',
                        },
                        body: new FormData(form),
                    });

                    if (!response.ok || !response.body) {
                        throw new Error('Unable to start streaming import.');
                    }

                    const reader = response.body.getReader();
                    const decoder = new TextDecoder();
                    let buffer = '';

                    while (true) {
                        const { value, done } = await reader.read();
                        if (done) {
                            break;
                        }

                        buffer += decoder.decode(value, { stream: true });
                        const chunks = buffer.split('\n\n');
                        buffer = chunks.pop() || '';

                        for (const chunk of chunks) {
                            const dataLine = chunk.split('\n').find((line) => line.startsWith('data: '));
                            if (!dataLine) {
                                continue;
                            }

                            let payload = null;
                            try {
                                payload = JSON.parse(dataLine.slice(6));
                            } catch (_) {
                                continue;
                            }

                            if (!payload || typeof payload !== 'object') {
                                continue;
                            }

                            if (payload.event === 'started') {
                                statusNode.textContent = payload.message || 'Import started...';
                                appendLine('Started import');
                                continue;
                            }

                            if (payload.event === 'progress') {
                                const entity = payload.entity || 'item';
                                const status = payload.status || 'processed';
                                const index = payload.index || 0;
                                const total = payload.total || 0;
                                const label = payload.title || payload.slug || payload.path || '(untitled)';
                                appendLine(`[${entity}] ${status} (${index}/${total}) ${label}`);
                                statusNode.textContent = `Processing ${entity} ${index}/${total}...`;
                                continue;
                            }

                            if (payload.event === 'done') {
                                statusNode.textContent = payload.message || 'Import completed.';
                                appendLine('Import completed');
                                submitButton.textContent = 'Import complete';
                                return;
                            }

                            if (payload.event === 'error') {
                                statusNode.textContent = payload.message || 'Import failed.';
                                appendLine('Error: ' + statusNode.textContent);
                                submitButton.removeAttribute('disabled');
                                submitButton.textContent = 'Retry import';
                                return;
                            }
                        }
                    }

                    submitButton.removeAttribute('disabled');
                    submitButton.textContent = 'Retry import';
                } catch (error) {
                    const message = error instanceof Error ? error.message : 'Import failed.';
                    statusNode.textContent = message;
                    appendLine('Error: ' + message);
                    submitButton.removeAttribute('disabled');
                    submitButton.textContent = 'Retry import';
                }
            });
        })();
    </script>
@endsection
