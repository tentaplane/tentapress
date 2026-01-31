@extends('tentapress-admin::layouts.shell')

@section('title', $mode === 'create' ? 'Upload Media' : 'Edit Media')

@section('content')
    <div class="tp-editor space-y-6">
        <div class="tp-page-header">
            <div>
                <h1 class="tp-page-title">
                    {{ $mode === 'create' ? 'Upload Media' : 'Edit Media' }}
                </h1>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
            <div class="space-y-6 lg:col-span-3">
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
                            <div class="tp-field">
                                <label class="tp-label">File</label>
                                <input type="file" name="file" class="tp-input" required />
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
        </div>

        <div class="space-y-6 lg:sticky lg:top-6 lg:self-start">
            <div class="tp-metabox">
                <div class="tp-metabox__title">Actions</div>
                <div class="tp-metabox__body space-y-3 text-sm">
                    <button type="submit" form="media-form" class="tp-button-primary w-full justify-center">
                        {{ $mode === 'create' ? 'Upload Media' : 'Save Changes' }}
                    </button>

                    @if ($mode === 'edit')
                        <form
                            method="POST"
                            action="{{ route('tp.media.destroy', ['media' => $media->id]) }}"
                            onsubmit="return confirm('Delete this media file? This cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="tp-button-danger w-full justify-center" aria-label="Delete media">
                                Delete
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            @if ($mode === 'edit')
                @php
                    $urlGenerator = app(\TentaPress\Media\Contracts\MediaUrlGenerator::class);
                    $url = $urlGenerator->url($media);
                    $mime = (string) ($media->mime_type ?? '');
                    $isImage = $mime !== '' && str_starts_with($mime, 'image/');
                    $size = is_numeric($media->size ?? null) ? (int) $media->size : null;
                    $sizeLabel = $size ? number_format($size / 1024, 1).' KB' : '—';
                    $dimensions = $media->width && $media->height ? $media->width.'×'.$media->height : '—';
                @endphp
                <div class="tp-metabox">
                    <div class="tp-metabox__title">Details</div>
                    <div class="tp-metabox__body space-y-3 text-sm">
                        @if ($url && $isImage)
                            <img src="{{ $url }}" alt="" class="w-full rounded border border-slate-200 object-cover" />
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
                            <span class="tp-muted">Uploaded:</span>
                            <span class="tp-code">{{ $media->created_at?->toDateTimeString() ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
