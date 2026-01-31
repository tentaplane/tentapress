@extends('tentapress-admin::layouts.shell')

@section('title', 'Edit SEO')

@section('content')
    @php
        $mediaOptions = [];

        if (class_exists(\TentaPress\Media\Models\TpMedia::class)) {
            try {
        if (\Illuminate\Support\Facades\Schema::hasTable('tp_media')) {
            $urlGenerator = app(\TentaPress\Media\Contracts\MediaUrlGenerator::class);
            $items = \TentaPress\Media\Models\TpMedia::query()
                ->latest('created_at')
                ->limit(200)
                ->get(['id', 'title', 'original_name', 'path', 'mime_type', 'disk']);

                    foreach ($items as $item) {
                        $disk = (string) ($item->disk ?? 'public');
                        $path = trim((string) ($item->path ?? ''));
                        $mime = (string) ($item->mime_type ?? '');

                        if ($disk !== 'public' || $path === '' || ! str_starts_with($mime, 'image/')) {
                            continue;
                        }

                $url = $urlGenerator->imageUrl($item);
                $title = trim((string) ($item->title ?? ''));
                $original = trim((string) ($item->original_name ?? ''));
                $label = $title !== '' ? $title : ($original !== '' ? $original : 'Media #' . $item->id);

                if ($url !== null) {
                    $mediaOptions[] = [
                        'value' => $url,
                        'label' => $label,
                        'original_name' => $original,
                        'mime_type' => $mime,
                        'is_image' => true,
                    ];
                }
            }
        }
            } catch (\Throwable) {
                $mediaOptions = [];
            }
        }

        $mediaIndexUrl = \Illuminate\Support\Facades\Route::has('tp.media.index') ? route('tp.media.index') : '';

        $initialOg = (string) old('og_image', $seo->og_image ?? '');
        $initialTwitter = (string) old('twitter_image', $seo->twitter_image ?? '');
    @endphp

    <div class="tp-editor space-y-6">
        <div class="tp-page-header">
            <div>
                <h1 class="tp-page-title">Edit SEO</h1>
                <p class="tp-description">
                    <span class="font-semibold">{{ $page->title ?: '(Untitled)' }}</span>
                    <span class="tp-muted">— /{{ $page->slug }}</span>
                </p>
            </div>
        </div>
    </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
            <div class="space-y-6 lg:col-span-3">
                <div class="tp-metabox">
                    <div class="tp-metabox__title">Search</div>
                    <div class="tp-metabox__body">
                        <div
                            x-data="tpSeoMediaPage({
                                        options: @js($mediaOptions),
                                        indexUrl: @js($mediaIndexUrl),
                                        og: @js($initialOg),
                                        twitter: @js($initialTwitter),
                                    })"
                            x-init="init()">
                            <form
                                method="POST"
                                action="{{ route('tp.seo.pages.update', ['page' => $page->id]) }}"
                                class="space-y-5"
                                id="seo-form">
                                @csrf
                                @method('PUT')

                    <div class="tp-field">
                        <label class="tp-label">Title override</label>
                        <input name="title" class="tp-input" value="{{ old('title', $seo->title) }}" />
                        <div class="tp-help">Leave empty to use the title template.</div>
                    </div>

                    <div class="tp-field">
                        <label class="tp-label">Description</label>
                        <textarea name="description" class="tp-textarea" rows="4">
{{ old('description', $seo->description) }}</textarea
                        >
                    </div>

                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                        <div class="tp-field">
                            <label class="tp-label">Canonical URL</label>
                            <input
                                name="canonical_url"
                                class="tp-input"
                                value="{{ old('canonical_url', $seo->canonical_url) }}" />
                        </div>

                        <div class="tp-field">
                            <label class="tp-label">Robots</label>
                            <input name="robots" class="tp-input" value="{{ old('robots', $seo->robots) }}" />
                        </div>
                    </div>

                    <div class="tp-divider"></div>

                    <div class="tp-label">Open Graph</div>

                    <div class="tp-field">
                        <label class="tp-label">OG title</label>
                        <input name="og_title" class="tp-input" value="{{ old('og_title', $seo->og_title) }}" />
                    </div>

                    <div class="tp-field">
                        <label class="tp-label">OG description</label>
                        <textarea name="og_description" class="tp-textarea" rows="4">
{{ old('og_description', $seo->og_description) }}</textarea
                        >
                    </div>

                    <div class="tp-field space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <label class="tp-label">OG image</label>
                            <button type="button" class="tp-button-secondary" @click="open('og')">Choose image</button>
                        </div>

                        <input type="hidden" name="og_image" x-model="ogImage" />

                        <div class="space-y-1">
                            <input
                                class="tp-input"
                                type="text"
                                :value="ogImage"
                                placeholder="/storage/... or https://..."
                                @input="ogImage = $event.target.value" />
                            <div class="tp-help">Use the media library or paste a URL.</div>
                        </div>

                        <div
                            class="flex flex-wrap items-center gap-3 rounded border border-black/10 bg-white p-3"
                            x-show="ogImage"
                            x-cloak>
                            <img
                                x-show="isImage(ogImage)"
                                :src="ogImage"
                                alt=""
                                class="h-14 w-14 rounded border border-slate-200 object-cover" />
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-sm font-semibold" x-text="labelFor(ogImage)"></div>
                                <div class="tp-code truncate text-[11px]" x-text="ogImage"></div>
                            </div>
                            <button type="button" class="tp-button-link" @click="ogImage = ''">Clear</button>
                        </div>
                    </div>

                    <div class="tp-divider"></div>

                    <div class="tp-label">Twitter</div>

                    <div class="tp-field">
                        <label class="tp-label">Twitter title</label>
                        <input
                            name="twitter_title"
                            class="tp-input"
                            value="{{ old('twitter_title', $seo->twitter_title) }}" />
                    </div>

                    <div class="tp-field">
                        <label class="tp-label">Twitter description</label>
                        <textarea name="twitter_description" class="tp-textarea" rows="4">
{{ old('twitter_description', $seo->twitter_description) }}</textarea
                        >
                    </div>

                    <div class="tp-field space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <label class="tp-label">Twitter image</label>
                            <button type="button" class="tp-button-secondary" @click="open('twitter')">
                                Choose image
                            </button>
                        </div>

                        <input type="hidden" name="twitter_image" x-model="twitterImage" />

                        <div class="space-y-1">
                            <input
                                class="tp-input"
                                type="text"
                                :value="twitterImage"
                                placeholder="/storage/... or https://..."
                                @input="twitterImage = $event.target.value" />
                            <div class="tp-help">Leave blank to reuse the Open Graph image.</div>
                        </div>

                        <div
                            class="flex flex-wrap items-center gap-3 rounded border border-black/10 bg-white p-3"
                            x-show="twitterImage"
                            x-cloak>
                            <img
                                x-show="isImage(twitterImage)"
                                :src="twitterImage"
                                alt=""
                                class="h-14 w-14 rounded border border-slate-200 object-cover" />
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-sm font-semibold" x-text="labelFor(twitterImage)"></div>
                                <div class="tp-code truncate text-[11px]" x-text="twitterImage"></div>
                            </div>
                            <button type="button" class="tp-button-link" @click="twitterImage = ''">Clear</button>
                        </div>
                    </div>

                </form>

                <div
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
                    x-show="openModal"
                    x-cloak
                    @keydown.escape.window="close()"
                    @click.self="close()">
                    <div
                        class="flex max-h-[85vh] w-full max-w-5xl flex-col overflow-hidden rounded-lg border border-black/10 bg-white shadow-xl">
                        <div class="flex flex-wrap items-center gap-2 border-b border-black/10 px-4 py-3">
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-semibold">Select image</div>
                                <div
                                    class="tp-muted text-xs"
                                    x-text="target === 'og' ? 'Open Graph image' : 'Twitter image'"></div>
                            </div>
                            <div class="flex w-full gap-2 sm:w-auto">
                                <input
                                    class="tp-input w-full sm:w-80"
                                    type="search"
                                    placeholder="Search images..."
                                    x-model="search" />
                                <button type="button" class="tp-button-secondary" @click="close()">Close</button>
                            </div>
                        </div>

                        <div class="min-h-0 flex-1 overflow-auto bg-[#f6f7f7] p-4">
                            <div
                                class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-4"
                                x-show="filtered().length > 0">
                                <template x-for="opt in filtered()" :key="opt.value">
                                    <button
                                        type="button"
                                        class="flex h-full flex-col gap-2 rounded border border-black/10 bg-white p-2 text-left transition hover:border-black/20"
                                        @click="select(opt.value)">
                                        <div
                                            class="flex aspect-4/3 items-center justify-center overflow-hidden rounded border border-black/10 bg-slate-50">
                                            <img
                                                x-show="opt.is_image"
                                                :src="opt.value"
                                                alt=""
                                                class="h-full w-full object-cover" />
                                            <div x-show="!opt.is_image" class="tp-muted text-xs uppercase">File</div>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="truncate text-sm font-semibold" x-text="opt.label"></div>
                                            <div
                                                class="tp-muted truncate text-[11px]"
                                                x-text="opt.original_name"></div>
                                        </div>
                                    </button>
                                </template>
                            </div>

                            <div
                                class="tp-muted rounded border border-dashed border-black/15 bg-white p-6 text-center text-sm"
                                x-show="filtered().length === 0">
                                No images match that search yet.
                            </div>
                        </div>

                        <div
                            class="flex flex-wrap items-center justify-between gap-2 border-t border-black/10 px-4 py-3">
                            <a
                                x-show="indexUrl"
                                :href="indexUrl"
                                target="_blank"
                                rel="noopener"
                                class="tp-button-link">
                                Manage media
                            </a>
                            <div class="tp-muted text-xs">Selecting an image will insert a relative /storage URL.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6 lg:sticky lg:top-6 lg:self-start">
            <div class="tp-metabox">
                <div class="tp-metabox__title">Actions</div>
                <div class="tp-metabox__body space-y-2 text-sm">
                    <button type="submit" form="seo-form" class="tp-button-primary w-full justify-center">
                        Save SEO
                    </button>
                    <a href="{{ route('tp.seo.index') }}" class="tp-button-secondary w-full justify-center">Back</a>
                </div>
            </div>
        </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('tpSeoMediaPage', (opts) => ({
                options: Array.isArray(opts.options) ? opts.options.filter((o) => o && o.is_image) : [],
                indexUrl: typeof opts.indexUrl === 'string' ? opts.indexUrl : '',
                ogImage: typeof opts.og === 'string' ? opts.og : '',
                twitterImage: typeof opts.twitter === 'string' ? opts.twitter : '',

                openModal: false,
                search: '',
                target: 'og',

                init() {},

                open(target) {
                    this.target = target === 'twitter' ? 'twitter' : 'og';
                    this.search = '';
                    this.openModal = true;
                },

                close() {
                    this.openModal = false;
                    this.search = '';
                },

                select(value) {
                    const url = String(value || '').trim();
                    if (url === '') return;

                    if (this.target === 'twitter') {
                        this.twitterImage = url;
                    } else {
                        this.ogImage = url;
                    }

                    this.close();
                },

                matches(opt, query) {
                    const q = String(query || '')
                        .trim()
                        .toLowerCase();
                    if (q === '') return true;
                    const hay = [opt.label, opt.original_name, opt.mime_type, opt.value].join(' ').toLowerCase();
                    return hay.includes(q);
                },

                filtered() {
                    const query = this.search;
                    return this.options.filter((opt) => opt && this.matches(opt, query));
                },

                optionFor(value) {
                    const key = String(value || '').trim();
                    if (key === '') return null;
                    return this.options.find((opt) => opt && String(opt.value) === key) || null;
                },

                isImage(value) {
                    const opt = this.optionFor(value);
                    if (opt && opt.is_image !== undefined) {
                        return !!opt.is_image;
                    }
                    return /\.(png|jpe?g|gif|webp|svg)$/i.test(String(value || ''));
                },

                labelFor(value) {
                    const opt = this.optionFor(value);
                    if (opt && opt.label) return opt.label;
                    const key = String(value || '');
                    return key.split('/').pop() || key;
                },
            }));
        });
    </script>
@endsection
