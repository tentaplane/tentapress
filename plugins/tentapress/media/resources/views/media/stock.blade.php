@extends('tentapress-admin::layouts.shell')

@section('title', 'Stock Library')

@section('content')
    <div
        x-data="{
            importUrl: '{{ route('tp.media.stock.import') }}',
            csrfToken: '{{ csrf_token() }}',
            selectedItems: {},
            importingKeys: {},
            isBulkImporting: false,
            feedbackMessage: '',
            feedbackType: 'success',
            previewOpen: false,
            previewTitle: '',
            previewAuthor: '',
            previewType: '',
            previewUrl: '',
            previewVideoUrl: '',
            previewPosterUrl: '',
            previewSourceUrl: '',
            previewLicense: '',
            openPreview(payload) {
                this.previewTitle = payload.title || 'Untitled';
                this.previewAuthor = payload.author || '';
                this.previewType = payload.type || 'image';
                this.previewUrl = payload.url || '';
                this.previewVideoUrl = payload.videoUrl || payload.url || '';
                this.previewPosterUrl = payload.posterUrl || payload.url || '';
                this.previewSourceUrl = payload.sourceUrl || '';
                this.previewLicense = payload.license || '';
                this.previewOpen = true;
            },
            closePreview() {
                this.previewOpen = false;
                this.previewUrl = '';
                this.previewVideoUrl = '';
                this.previewPosterUrl = '';
            },
            itemKey(payload) {
                return [payload.source || '', payload.id || '', payload.media_type || ''].join(':');
            },
            isImporting(payload) {
                const key = this.itemKey(payload);
                return this.importingKeys[key] === true;
            },
            setSelected(payload, checked) {
                const key = this.itemKey(payload);
                if (checked) {
                    this.selectedItems[key] = payload;
                    return;
                }

                delete this.selectedItems[key];
            },
            clearSelection() {
                this.selectedItems = {};
            },
            selectedCount() {
                return Object.keys(this.selectedItems).length;
            },
            showFeedback(message, type = 'success') {
                this.feedbackMessage = message;
                this.feedbackType = type;
            },
            async importOne(payload) {
                const key = this.itemKey(payload);
                this.importingKeys[key] = true;

                try {
                    const response = await fetch(this.importUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(payload),
                    });

                    const data = await response.json().catch(() => ({}));
                    if (!response.ok || data.ok !== true) {
                        this.showFeedback(data.message || 'Import failed.', 'error');
                        return false;
                    }

                    this.showFeedback(data.message || 'Asset imported.', 'success');
                    delete this.selectedItems[key];
                    return true;
                } catch (_error) {
                    this.showFeedback('Import failed (offline?).', 'error');
                    return false;
                } finally {
                    delete this.importingKeys[key];
                }
            },
            async importSelected() {
                const items = Object.values(this.selectedItems);
                if (items.length === 0) {
                    return;
                }

                this.isBulkImporting = true;
                let successCount = 0;

                for (const item of items) {
                    const ok = await this.importOne(item);
                    if (ok) {
                        successCount += 1;
                    }
                }

                this.isBulkImporting = false;
                if (successCount > 0) {
                    this.showFeedback(`Imported ${successCount} item${successCount === 1 ? '' : 's'} to Media.`, 'success');
                }
            }
        }"
        x-ref="previewRoot">
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Stock Library</h1>
            <p class="tp-description">Search and import media from connected stock sources.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('tp.media.index') }}" class="tp-button-secondary">Back to Media</a>
            <a href="{{ route('tp.media.stock.settings') }}" class="tp-button-secondary">Stock settings</a>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__body space-y-4">
            <form method="GET" action="{{ route('tp.media.stock') }}" class="grid gap-3 lg:grid-cols-7 lg:items-end">
                <div class="lg:col-span-2">
                    <label class="tp-label">Search</label>
                    <input name="q" value="{{ $query }}" class="tp-input" placeholder="Search stock libraries…" />
                </div>

                <div class="lg:col-span-1">
                    <label class="tp-label">Source</label>
                    <select name="source" class="tp-select">
                        @forelse ($sources as $source)
                            <option value="{{ $source->key() }}" @selected($sourceKey === $source->key())>
                                {{ $source->label() }}
                            </option>
                        @empty
                            <option value="">No sources enabled</option>
                        @endforelse
                    </select>
                </div>

                <div class="lg:col-span-1">
                    <label class="tp-label">Media type</label>
                    <select name="media_type" class="tp-select">
                        <option value="">Any</option>
                        @foreach ($sources as $source)
                            @if ($sourceKey === $source->key())
                                @foreach ($source->supportedMediaTypes() as $type)
                                    <option value="{{ $type }}" @selected($mediaType === $type)>
                                        {{ ucfirst($type) }}
                                    </option>
                                @endforeach
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-1">
                    <label class="tp-label">Orientation</label>
                    <select name="orientation" class="tp-select">
                        <option value="">Any</option>
                        <option value="landscape" @selected($orientation === 'landscape')>Landscape</option>
                        <option value="portrait" @selected($orientation === 'portrait')>Portrait</option>
                        <option value="square" @selected($orientation === 'square')>Square</option>
                    </select>
                </div>

                <div class="lg:col-span-1">
                    <label class="tp-label">Sort</label>
                    <select name="sort" class="tp-select">
                        <option value="">Relevant</option>
                        <option value="latest" @selected($sort === 'latest')>Latest</option>
                        <option value="popular" @selected($sort === 'popular')>Popular</option>
                    </select>
                </div>

                <div class="lg:col-span-7 flex gap-2">
                    <button class="tp-button-primary" type="submit">Search</button>
                    <a class="tp-button-secondary" href="{{ route('tp.media.stock') }}">Reset</a>
                </div>
            </form>
        </div>
    </div>

    @if (count($sources) === 0)
        <div class="tp-notice-warning">
            No stock sources are enabled. Add API keys in Stock settings to continue.
        </div>
    @endif

    @if ($results === null && $query !== '')
        <div class="tp-notice-warning">No source is available for this search.</div>
    @endif

    @if ($results && count($results->items) === 0)
        <div class="tp-metabox mt-4">
            <div class="tp-metabox__body tp-muted text-sm">No matching results found.</div>
        </div>
    @endif

    @if ($results && $results->offline)
        <div class="tp-notice-warning">
            You appear to be offline. Stock search results may be unavailable until you’re connected.
        </div>
    @endif

    @if ($results && count($results->items) > 0)
        @if ($attributionReminder)
            <div class="tp-notice-info">
                Attribution is recommended for most sources. Use the provided attribution text when you publish.
            </div>
        @endif

        <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
            <div class="text-xs text-black/60" x-text="`${selectedCount()} selected`"></div>
            <button
                type="button"
                class="tp-button-primary"
                :disabled="selectedCount() === 0 || isBulkImporting"
                @click="importSelected()">
                <span x-show="!isBulkImporting">Add selected to Media</span>
                <span x-show="isBulkImporting">Adding selected...</span>
            </button>
        </div>

        <div
            class="mt-3 rounded-md px-3 py-2 text-sm"
            x-show="feedbackMessage"
            x-cloak
            :class="feedbackType === 'error' ? 'tp-notice-warning' : 'tp-notice-info'">
            <span x-text="feedbackMessage"></span>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 mt-4">
            @foreach ($results->items as $item)
                @php
                    $importPayload = [
                        'source' => $item->provider,
                        'id' => $item->id,
                        'media_type' => $item->mediaType,
                    ];
                    $previewPayload = [
                        'title' => $item->title !== '' ? $item->title : 'Untitled',
                        'author' => $item->author,
                        'type' => $item->mediaType ?? 'image',
                        'url' => $item->previewUrl,
                        'videoUrl' => $item->mediaType === 'video' ? ($item->downloadUrl ?? $item->previewUrl) : null,
                        'posterUrl' => $item->previewUrl,
                        'sourceUrl' => $item->sourceUrl ?? '',
                        'license' => $item->license ?? '',
                    ];
                @endphp
                <div class="rounded-lg border border-black/10 bg-white shadow-sm overflow-hidden">
                    <div class="px-3 pt-3">
                        <label class="inline-flex items-center gap-2 text-xs text-black/70">
                            <input
                                type="checkbox"
                                class="tp-checkbox"
                                @change="setSelected({{ Js::from($importPayload) }}, $event.target.checked)" />
                            Select
                        </label>
                    </div>
                    <button
                        type="button"
                        class="border-b border-black/10 bg-slate-50 text-left w-full"
                        @click="openPreview({{ Js::from($previewPayload) }})">
                        @if ($item->previewUrl)
                            <img src="{{ $item->previewUrl }}" alt="" class="h-40 w-full object-cover" />
                        @else
                            <div class="flex h-40 items-center justify-center text-xs uppercase text-black/50">
                                {{ $item->mediaType ?? 'Asset' }}
                            </div>
                        @endif
                    </button>
                    <div class="p-3 space-y-2">
                        <div>
                            <p class="text-sm font-semibold text-[#1d2327] line-clamp-2">
                                {{ $item->title !== '' ? $item->title : 'Untitled' }}
                            </p>
                            <p class="text-xs text-black/60">{{ $item->author }}</p>
                        </div>

                        <div class="text-xs text-black/60">
                            {{ strtoupper($item->provider) }} · {{ $item->license ?? 'License' }}
                        </div>

                        @if ($item->attribution)
                            <div class="rounded bg-[#f6f7f7] p-2 text-xs text-black/70">
                                {{ $item->attribution }}
                            </div>
                        @endif

                        <button
                            type="button"
                            class="tp-button-primary w-full justify-center"
                            :disabled="isImporting({{ Js::from($importPayload) }})"
                            @click="importOne({{ Js::from($importPayload) }})">
                            <span x-show="!isImporting({{ Js::from($importPayload) }})">Add to Media</span>
                            <span x-show="isImporting({{ Js::from($importPayload) }})">Adding...</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($results->hasMore)
            <div class="mt-6 flex justify-center">
                <a
                    class="tp-button-secondary"
                    href="{{ route('tp.media.stock', array_merge(request()->query(), ['page' => $results->page + 1])) }}">
                    Load more
                </a>
            </div>
        @endif
    @endif

    <div
        class="fixed inset-0 z-50"
        x-show="previewOpen"
        x-cloak>
        <div class="absolute inset-0 bg-black/60" @click="closePreview()"></div>
        <div class="relative mx-auto flex h-full max-w-4xl items-center justify-center px-4 py-8">
            <div class="w-full overflow-hidden rounded-2xl bg-white shadow-xl">
                <div class="flex items-center justify-between gap-4 border-b border-black/10 px-4 py-3">
                    <div>
                        <p class="text-sm font-semibold text-[#1d2327]" x-text="previewTitle"></p>
                        <p class="text-xs text-black/60" x-text="previewAuthor"></p>
                    </div>
                    <button
                        type="button"
                        class="rounded-md border border-black/10 bg-white px-2.5 py-1 text-xs font-semibold text-black/70"
                        @click="closePreview()">
                        Close
                    </button>
                </div>
                <div class="bg-black">
                    <template x-if="previewType === 'video'">
                        <video class="w-full max-h-[70vh]" controls :src="previewVideoUrl" :poster="previewPosterUrl"></video>
                    </template>
                    <template x-if="previewType !== 'video'">
                        <img class="w-full max-h-[70vh] object-contain" :src="previewUrl" alt="" />
                    </template>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-xs text-black/60">
                    <div class="flex flex-wrap items-center gap-2">
                        <span x-text="previewType ? previewType.toUpperCase() : ''"></span>
                        <span x-show="previewLicense" x-text="previewLicense"></span>
                    </div>
                    <a
                        class="tp-button-link"
                        x-show="previewSourceUrl"
                        :href="previewSourceUrl"
                        target="_blank"
                        rel="noopener">
                        View source
                    </a>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
