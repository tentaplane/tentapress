@extends('tentapress-admin::layouts.shell')

@section('title', $mode === 'create' ? 'Upload File' : 'Edit Media')

@section('content')
    <div
        class="tp-editor space-y-6"
        x-data="{
            previewModalOpen: false,
            previewModalSrc: '',
            previewModalLabel: '',
            openPreviewModal(src, label = '') {
                if (!src) {
                    return;
                }
                this.previewModalSrc = src;
                this.previewModalLabel = label;
                this.previewModalOpen = true;
            },
            closePreviewModal() {
                this.previewModalOpen = false;
            }
        }"
        x-on:keydown.escape.window="closePreviewModal()"
        data-media-preview-modal>
        <div class="tp-page-header">
            <div>
                <h1 class="tp-page-title">
                    {{ $mode === 'create' ? 'Upload File' : 'Edit Media' }}
                </h1>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 {{ $mode === 'edit' ? 'lg:grid-cols-2' : 'lg:grid-cols-4' }}">
            <div class="space-y-6 {{ $mode === 'edit' ? 'lg:order-2' : 'lg:col-span-3' }}">
                <div class="tp-metabox">
                    <div class="tp-metabox__body space-y-4">
                        <form
                            method="POST"
                            action="{{ $mode === 'create' ? route('tp.media.store') : route('tp.media.update', ['media' => $media->id]) }}"
                            enctype="multipart/form-data"
                            class="space-y-4"
                            id="media-form">
                            @csrf
                            @if ($mode === 'edit')
                                @method('PUT')
                            @endif

                            @if ($mode === 'create')
                                <div
                                    class="tp-field"
                                    x-data="{
                                    isDragging: false,
                                    fileName: '',
                                    setFileName(input) {
                                        const file = input.files && input.files[0] ? input.files[0] : null;
                                        this.fileName = file ? file.name : '';
                                    },
                                    handleDrop(event) {
                                        this.isDragging = false;
                                        const files = event.dataTransfer ? event.dataTransfer.files : null;
                                        if (!files || files.length === 0) {
                                            return;
                                        }

                                        const transfer = new DataTransfer();
                                        transfer.items.add(files[0]);
                                        this.$refs.fileInput.files = transfer.files;
                                        this.setFileName(this.$refs.fileInput);
                                    }
                                }">
                                <label class="tp-label">File</label>
                                <label
                                    class="group relative mt-2 flex min-h-44 cursor-pointer flex-col items-center justify-center gap-3 overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-gradient-to-br from-slate-50 via-white to-sky-50 px-6 py-8 text-center transition hover:border-sky-400 hover:from-sky-50 hover:to-indigo-50"
                                    :class="isDragging ? 'border-sky-500 ring-4 ring-sky-100' : ''"
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="handleDrop($event)">
                                    <input
                                        x-ref="fileInput"
                                        type="file"
                                        name="file"
                                        class="sr-only"
                                        required
                                        @change="setFileName($event.target)" />

                                    <div class="rounded-full bg-sky-100 p-3 text-sky-700 ring-1 ring-sky-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-6 w-6 fill-current" aria-hidden="true">
                                            <path
                                                d="M11 3a1 1 0 0 1 2 0v8.59l2.3-2.3a1 1 0 1 1 1.4 1.42l-4 3.99a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.41l2.3 2.29V3ZM5 15a1 1 0 0 1 1 1v2a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-2a1 1 0 1 1 2 0v2a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3v-2a1 1 0 0 1 1-1Z" />
                                        </svg>
                                    </div>

                                    <div class="space-y-1">
                                        <p class="text-sm font-semibold text-slate-800">
                                            Drop a file here or click to browse
                                        </p>
                                        <p class="text-xs text-slate-500">
                                            Images, documents, and other assets
                                        </p>
                                    </div>

                                    <div
                                        class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-600 shadow-sm"
                                        x-show="fileName"
                                        x-cloak
                                        x-text="'Selected: ' + fileName"></div>
                                </label>
                                    <div class="tp-help">Upload an image, document, or asset.</div>
                                </div>
                            @endif

                            <div class="tp-field">
                                <label class="tp-label">Title</label>
                                <input name="title" class="tp-input" value="{{ old('title', $media->title) }}" />
                                <div class="tp-help">Defaults to the original filename.</div>
                            </div>

                            <div class="tp-field">
                                <label class="tp-label">Alt text</label>
                                <input name="alt_text" class="tp-input" value="{{ old('alt_text', $media->alt_text) }}" />
                                <div class="tp-help">Describe the media for accessibility.</div>
                            </div>

                            <div class="tp-field">
                                <label class="tp-label">Caption</label>
                                <textarea name="caption" rows="4" class="tp-textarea">{{ old('caption', $media->caption) }}</textarea>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="tp-metabox">
                    <div class="tp-metabox__title">Actions</div>
                    <div class="tp-metabox__body space-y-3 text-sm">
                        <button type="submit" form="media-form" class="tp-button-primary w-full justify-center">
                            {{ $mode === 'create' ? 'Upload file' : 'Save changes' }}
                        </button>

                        @if ($mode === 'edit')
                            <form
                                method="POST"
                                action="{{ route('tp.media.destroy', ['media' => $media->id]) }}"
                                data-confirm="Delete this media file? This action cannot be undone.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="tp-button-danger w-full justify-center" aria-label="Delete media">
                                    Delete
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            @if ($mode === 'edit')
                <div class="space-y-6 lg:order-1">
                @php
                    $urlGenerator = app(\TentaPress\Media\Contracts\MediaUrlGenerator::class);
                    $url = $urlGenerator->url($media);
                    $mime = (string) ($media->mime_type ?? '');
                    $isImage = $mime !== '' && str_starts_with($mime, 'image/');
                    $previewUrl = $isImage ? ($urlGenerator->imageUrl($media, ['variant' => 'medium']) ?? $url) : $url;
                    $size = is_numeric($media->size ?? null) ? (int) $media->size : null;
                    $sizeLabel = $size ? number_format($size / 1024, 1).' KB' : '—';
                    $dimensions = $media->width && $media->height ? $media->width.'×'.$media->height : '—';
                    $sourceDimensions = $media->source_width && $media->source_height ? $media->source_width.'×'.$media->source_height : '—';
                    $optimizationStatus = (string) ($media->optimization_status ?? 'skipped');
                    $variantCount = is_array($media->variants) ? count($media->variants) : 0;
                    $variants = is_array($media->variants) ? $media->variants : [];
                @endphp
                <div class="tp-metabox">
                    <div class="tp-metabox__title">Details</div>
                    <div class="tp-metabox__body space-y-3 text-sm">
                        @if ($previewUrl && $isImage)
                            <button
                                type="button"
                                class="block w-full cursor-zoom-in rounded border border-slate-200"
                                x-on:click="openPreviewModal('{{ e($previewUrl) }}', '{{ e((string) ($media->original_name ?? 'Original image')) }}')">
                                <img src="{{ $previewUrl }}" alt="" class="w-full rounded object-cover" />
                            </button>
                        @endif

                        <div>
                            <span class="tp-muted">File:</span>
                            <span class="tp-code">{{ $media->original_name ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="tp-muted">Type:</span>
                            <span class="tp-code">{{ $mime !== '' ? $mime : '—' }}</span>
                        </div>
                        <div>
                            <span class="tp-muted">Size:</span>
                            <span class="tp-code">{{ $sizeLabel }}</span>
                        </div>
                        <div>
                            <span class="tp-muted">Dimensions:</span>
                            <span class="tp-code">{{ $dimensions }}</span>
                        </div>
                        <div>
                            <span class="tp-muted">Source dimensions:</span>
                            <span class="tp-code">{{ $sourceDimensions }}</span>
                        </div>
                        <div>
                            <span class="tp-muted">Optimization:</span>
                            <span class="tp-code">{{ strtoupper($optimizationStatus) }}</span>
                        </div>
                        <div>
                            <span class="tp-muted">Variants:</span>
                            <span class="tp-code">{{ $variantCount }}</span>
                        </div>
                        @if ($isImage)
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="tp-muted">Variant actions:</span>
                                    <form method="POST" action="{{ route('tp.media.variants.rebuild', ['media' => $media->id]) }}">
                                        @csrf
                                        <button type="submit" class="tp-button-secondary">Rebuild all variants</button>
                                    </form>
                                </div>

                                @if ($variants !== [])
                                    <div class="overflow-x-auto rounded border border-slate-200">
                                        <table class="min-w-full text-xs">
                                            <thead class="bg-slate-50">
                                                <tr>
                                                    <th class="px-2 py-2 text-left font-semibold text-slate-700">Variant</th>
                                                    <th class="px-2 py-2 text-left font-semibold text-slate-700">Dimensions</th>
                                                    <th class="px-2 py-2 text-left font-semibold text-slate-700">Size</th>
                                                    <th class="px-2 py-2 text-left font-semibold text-slate-700">Preview</th>
                                                    <th class="px-2 py-2 text-left font-semibold text-slate-700">Rebuild</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($variants as $variantKey => $variantData)
                                                    @php
                                                        $variantWidth = isset($variantData['width']) && is_numeric($variantData['width']) ? (int) $variantData['width'] : null;
                                                        $variantHeight = isset($variantData['height']) && is_numeric($variantData['height']) ? (int) $variantData['height'] : null;
                                                        $variantSize = isset($variantData['size']) && is_numeric($variantData['size']) ? (int) $variantData['size'] : null;
                                                        $variantUrl = $urlGenerator->imageUrl($media, ['variant' => (string) $variantKey]);
                                                    @endphp
                                                    <tr class="border-t border-slate-100">
                                                        <td class="px-2 py-2"><span class="tp-code">{{ $variantKey }}</span></td>
                                                        <td class="px-2 py-2">{{ $variantWidth && $variantHeight ? $variantWidth.'×'.$variantHeight : '—' }}</td>
                                                        <td class="px-2 py-2">{{ $variantSize ? number_format($variantSize / 1024, 1).' KB' : '—' }}</td>
                                                        <td class="px-2 py-2">
                                                            @if ($variantUrl)
                                                                <button
                                                                    type="button"
                                                                    class="tp-button-link"
                                                                    x-on:click="openPreviewModal('{{ e($variantUrl) }}', '{{ e((string) $variantKey) }}')">
                                                                    Open
                                                                </button>
                                                            @else
                                                                —
                                                            @endif
                                                        </td>
                                                        <td class="px-2 py-2">
                                                            <form method="POST" action="{{ route('tp.media.variants.rebuild', ['media' => $media->id]) }}">
                                                                @csrf
                                                                <input type="hidden" name="variant" value="{{ $variantKey }}" />
                                                                <button type="submit" class="tp-button-secondary">Rebuild</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-xs text-slate-500">No variants are currently built for this image.</div>
                                @endif
                            </div>
                        @endif
                        @if ($media->optimization_error)
                            <div>
                                <span class="tp-muted">Optimization error:</span>
                                <div class="tp-pre">{{ $media->optimization_error }}</div>
                            </div>
                        @endif
                        <div>
                            <span class="tp-muted">Uploaded:</span>
                            <span class="tp-code">{{ $media->created_at?->toDateTimeString() ?? '—' }}</span>
                        </div>

                        @if ($media->source)
                            <div>
                                <span class="tp-muted">Source:</span>
                                <span class="tp-code">{{ strtoupper($media->source) }}</span>
                            </div>
                            @if ($media->source_url)
                                <div>
                                    <span class="tp-muted">Source URL:</span>
                                    <a class="tp-button-link" href="{{ $media->source_url }}" target="_blank" rel="noopener">
                                        View source
                                    </a>
                                </div>
                            @endif
                            @if ($media->license)
                                <div>
                                    <span class="tp-muted">License:</span>
                                    @if ($media->license_url)
                                        <a class="tp-button-link" href="{{ $media->license_url }}" target="_blank" rel="noopener">
                                            {{ $media->license }}
                                        </a>
                                    @else
                                        <span class="tp-code">{{ $media->license }}</span>
                                    @endif
                                </div>
                            @endif
                            @if ($media->attribution)
                                <div>
                                    <span class="tp-muted">Attribution:</span>
                                    <div class="tp-pre">{{ $media->attribution }}</div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
                </div>
            @endif
        </div>

        <div
            class="fixed inset-0 z-50 hidden items-center justify-center bg-black/85 p-4"
            x-bind:class="previewModalOpen ? 'flex' : 'hidden'"
            x-on:click.self="closePreviewModal()">
            <button
                type="button"
                class="absolute right-4 top-4 rounded bg-white/10 px-3 py-2 text-sm font-semibold text-white hover:bg-white/20"
                x-on:click="closePreviewModal()">
                Close
            </button>
            <div class="max-h-[95vh] max-w-[95vw]">
                <img x-bind:src="previewModalSrc" x-bind:alt="previewModalLabel" class="max-h-[95vh] max-w-[95vw] rounded shadow-2xl" />
                <div class="mt-2 text-center text-xs text-white/80" x-text="previewModalLabel"></div>
            </div>
        </div>
    </div>
@endsection
