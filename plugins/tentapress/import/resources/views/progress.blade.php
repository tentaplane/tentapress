@extends('tentapress-admin::layouts.shell')

@section('title', 'Import Progress')

@section('content')
    @php
        $payload = is_array($payload ?? null) ? $payload : [];
    @endphp

    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Import progress</h1>
            <p class="tp-description">Import is running now. This page updates in real time.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('tp.import.index') }}" class="tp-button-secondary">Back to import</a>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__title">Live progress</div>
        <div class="tp-metabox__body space-y-4">
            <div id="tp-import-progress-panel" class="tp-panel">
                <div class="tp-label mb-2">Running import</div>
                <div id="tp-import-progress-status" class="tp-muted text-sm">Starting import...</div>
                <div class="mt-3 grid grid-cols-1 gap-2 text-xs sm:grid-cols-3">
                    <div class="rounded border border-black/10 bg-black/[0.02] px-3 py-2">
                        <div class="tp-muted uppercase">Pages</div>
                        <div id="tp-import-count-pages" class="mt-1 font-semibold">0/0</div>
                    </div>
                    <div class="rounded border border-black/10 bg-black/[0.02] px-3 py-2">
                        <div class="tp-muted uppercase">Posts</div>
                        <div id="tp-import-count-posts" class="mt-1 font-semibold">0/0</div>
                    </div>
                    <div class="rounded border border-black/10 bg-black/[0.02] px-3 py-2">
                        <div class="tp-muted uppercase">Media</div>
                        <div id="tp-import-count-media" class="mt-1 font-semibold">0/0</div>
                        <div id="tp-import-count-media-copied" class="tp-muted mt-1">Copied 0</div>
                    </div>
                </div>
                <ul id="tp-import-progress-list" class="mt-3 max-h-80 space-y-1 overflow-auto text-sm"></ul>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const streamUrl = {{ Js::from(route('tp.import.run.stream')) }};
            const runPayload = {{ Js::from($payload) }};
            const csrf = {{ Js::from(csrf_token()) }};

            const statusNode = document.getElementById('tp-import-progress-status');
            const listNode = document.getElementById('tp-import-progress-list');
            const countPagesNode = document.getElementById('tp-import-count-pages');
            const countPostsNode = document.getElementById('tp-import-count-posts');
            const countMediaNode = document.getElementById('tp-import-count-media');
            const countMediaCopiedNode = document.getElementById('tp-import-count-media-copied');

            if (!statusNode || !listNode || !countPagesNode || !countPostsNode || !countMediaNode || !countMediaCopiedNode) {
                return;
            }

            const formatCounter = (imported, skipped, failed, total) => `${imported + skipped + failed}/${total} (${imported} imported, ${skipped} skipped, ${failed} failed)`;

            const counters = {
                page: { imported: 0, skipped: 0, failed: 0, total: 0 },
                post: { imported: 0, skipped: 0, failed: 0, total: runPayload.include_posts ? 1 : 0 },
                media: { imported: 0, skipped: 0, failed: 0, total: runPayload.include_media ? 1 : 0, copied: 0 },
            };

            const renderCounters = () => {
                countPagesNode.textContent = formatCounter(counters.page.imported, counters.page.skipped, counters.page.failed, counters.page.total);
                countPostsNode.textContent = formatCounter(counters.post.imported, counters.post.skipped, counters.post.failed, counters.post.total);
                countMediaNode.textContent = formatCounter(counters.media.imported, counters.media.skipped, counters.media.failed, counters.media.total);
                countMediaCopiedNode.textContent = `Copied ${counters.media.copied}`;
            };

            const appendLine = (line, tone = 'default') => {
                const li = document.createElement('li');
                li.textContent = line;
                li.className = tone === 'error'
                    ? 'text-red-700'
                    : tone === 'success'
                        ? 'text-emerald-700'
                        : 'text-black/80';
                listNode.appendChild(li);
                listNode.scrollTop = listNode.scrollHeight;
            };

            const requestBody = new URLSearchParams();
            requestBody.set('token', String(runPayload.token || ''));
            requestBody.set('pages_mode', String(runPayload.pages_mode || 'create_only'));
            requestBody.set('settings_mode', String(runPayload.settings_mode || 'merge'));
            requestBody.set('include_posts', runPayload.include_posts ? '1' : '0');
            requestBody.set('include_media', runPayload.include_media ? '1' : '0');
            requestBody.set('include_seo', runPayload.include_seo ? '1' : '0');

            renderCounters();

            (async () => {
                try {
                    const response = await fetch(streamUrl, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/event-stream',
                            'X-CSRF-TOKEN': csrf,
                            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                        },
                        body: requestBody.toString(),
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
                                if (payload.kind === 'phase') {
                                    const phaseEntity = payload.entity || 'step';
                                    if (payload.status === 'started') {
                                        statusNode.textContent = `Starting ${phaseEntity} import...`;
                                        appendLine(`Starting ${phaseEntity} import`);
                                    } else if (payload.status === 'completed') {
                                        const created = Number(payload.created || 0);
                                        const skipped = Number(payload.skipped || 0);
                                        const failed = Number(payload.failed || 0);
                                        const copied = Number(payload.copied || 0);
                                        const variantsRefreshed = Number(payload.variants_refreshed || 0);
                                        const copiedText = phaseEntity === 'media' ? `, copied ${copied} files` : '';
                                        const variantsText = phaseEntity === 'media' ? `, refreshed ${variantsRefreshed} variants` : '';
                                        statusNode.textContent = `${phaseEntity} import completed`;
                                        appendLine(`Completed ${phaseEntity} import (${created} created, ${skipped} skipped, ${failed} failed${copiedText}${variantsText})`, failed > 0 ? 'error' : 'success');
                                    }
                                    continue;
                                }

                                const entity = payload.entity || 'item';
                                const status = payload.status || 'processed';
                                const index = payload.index || 0;
                                const total = payload.total || 0;
                                const label = payload.title || payload.slug || payload.path || '(untitled)';
                                const normalizedEntity = entity === 'page' || entity === 'post' || entity === 'media' ? entity : null;

                                if (normalizedEntity) {
                                    if (status === 'imported') {
                                        counters[normalizedEntity].imported += 1;
                                    } else if (status === 'skipped') {
                                        counters[normalizedEntity].skipped += 1;
                                    } else if (status === 'failed') {
                                        counters[normalizedEntity].failed += 1;
                                    }

                                    if (normalizedEntity === 'media' && payload.copied === true) {
                                        counters.media.copied += 1;
                                    }

                                    if (total > 0) {
                                        counters[normalizedEntity].total = total;
                                    }

                                    renderCounters();
                                }

                                const copiedSuffix = entity === 'media' && payload.copied === true ? ' [file copied]' : '';
                                const variantsSuffix = entity === 'media' && payload.variants_refreshed === true ? ' [variants refreshed]' : '';
                                const tone = status === 'failed' ? 'error' : status === 'skipped' ? 'default' : 'success';
                                appendLine(`[${entity}] ${status} (${index}/${total}) ${label}${copiedSuffix}${variantsSuffix}`, tone);
                                statusNode.textContent = `Processing ${entity} ${index}/${total}...`;
                                continue;
                            }

                            if (payload.event === 'done') {
                                statusNode.textContent = payload.message || 'Import completed.';
                                appendLine('Import completed', 'success');
                                return;
                            }

                            if (payload.event === 'error') {
                                statusNode.textContent = payload.message || 'Import failed.';
                                appendLine('Error: ' + statusNode.textContent, 'error');
                                return;
                            }
                        }
                    }
                } catch (error) {
                    const message = error instanceof Error ? error.message : 'Import failed.';
                    statusNode.textContent = message;
                    appendLine('Error: ' + message, 'error');
                }
            })();
        })();
    </script>
@endsection

