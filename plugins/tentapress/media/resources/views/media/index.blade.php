@extends('tentapress-admin::layouts.shell')

@section('title', 'Media')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Media</h1>
            <p class="tp-description">Upload and manage media files.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('tp.media.stock') }}" class="tp-button-secondary">Stock Library</a>
            <a href="{{ route('tp.media.optimizations') }}" class="tp-button-secondary">Optimizations</a>
            <a href="{{ route('tp.media.create') }}" class="tp-button-primary">Upload</a>
        </div>
    </div>

    @php
        $urlGenerator = app(\TentaPress\Media\Contracts\MediaUrlGenerator::class);
        $totalCount = method_exists($media, 'total') ? (int) $media->total() : $media->count();
    @endphp

    <div class="tp-metabox" data-media-index data-current-view="{{ $view }}">
        <div class="tp-metabox__title">
            <form method="GET" action="{{ route('tp.media.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <input type="hidden" name="view" value="{{ $view }}" />
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-black/5 px-3 py-1 text-xs font-semibold text-black/70">
                            {{ number_format($totalCount) }} files
                        </div>
                        <div class="text-xs text-black/60">
                            Showing {{ number_format($media->count()) }}{{ $totalCount > $media->count() ? ' of '.number_format($totalCount) : '' }}
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex items-center gap-1">
                        <a
                            href="{{ route('tp.media.index', array_merge(request()->query(), ['view' => 'list'])) }}"
                            data-media-view-link="list"
                            class="{{ $view === 'list' ? 'tp-button-secondary' : 'tp-button-secondary opacity-60 hover:opacity-100' }}">
                            List
                        </a>
                        <a
                            href="{{ route('tp.media.index', array_merge(request()->query(), ['view' => 'grid'])) }}"
                            data-media-view-link="grid"
                            class="{{ $view === 'grid' ? 'tp-button-secondary' : 'tp-button-secondary opacity-60 hover:opacity-100' }}">
                            Grid
                        </a>
                    </div>

                    <input
                        name="s"
                        value="{{ $search }}"
                        class="tp-input w-full sm:w-64"
                        placeholder="Search media…" />
                    <button class="tp-button-secondary" type="submit">Search</button>
                </div>
            </form>
        </div>

        @if ($media->count() === 0)
            <div class="tp-metabox__body">
                <div class="flex flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-black/10 bg-[#f6f7f7] px-6 py-10 text-center">
                    <div class="rounded-full bg-white p-3 text-black/60 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-6 w-6 fill-current" aria-hidden="true">
                            <path
                                d="M7 3a2 2 0 0 0-2 2v13a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7.83a2 2 0 0 0-.59-1.41l-2.83-2.83A2 2 0 0 0 16.17 3H7Zm0 2h9.17L19 7.83V18a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V5Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-[#1d2327]">No media yet</p>
                        <p class="text-xs text-black/60">Upload files or import from the Stock Library to get started.</p>
                    </div>
                    <div class="flex flex-wrap items-center justify-center gap-2">
                        <a href="{{ route('tp.media.create') }}" class="tp-button-primary">Upload Media</a>
                        <a href="{{ route('tp.media.stock') }}" class="tp-button-secondary">Browse Stock</a>
                    </div>
                </div>
            </div>
        @elseif ($view === 'grid')
            <div class="tp-metabox__body">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($media as $item)
                        @php
                            $disk = (string) ($item->disk ?? 'public');
                            $path = (string) ($item->path ?? '');
                            $url = $urlGenerator->url($item);
                            $mime = (string) ($item->mime_type ?? '');
                            $isImage = $mime !== '' && str_starts_with($mime, 'image/');
                            $size = is_numeric($item->size ?? null) ? (int) $item->size : null;
                            $sizeLabel = $size ? number_format($size / 1024, 1).' KB' : '—';
                            $title = (string) ($item->title ?? '');
                            $originalName = (string) ($item->original_name ?? '');
                            $typeLabel = $mime !== '' ? strtoupper(strtok($mime, '/')) : 'FILE';
                            $dateLabel = $item->created_at?->format('Y-m-d') ?? '—';
                        @endphp
                        <div class="group overflow-hidden rounded-2xl border border-black/10 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <div class="relative">
                                @if ($url && $isImage)
                                    <img
                                        src="{{ $url }}"
                                        alt=""
                                        class="aspect-[4/3] w-full object-cover" />
                                @else
                                    <div class="flex aspect-[4/3] items-center justify-center bg-gradient-to-br from-slate-50 via-white to-slate-100 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        {{ $typeLabel }}
                                    </div>
                                @endif
                                <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent opacity-0 transition group-hover:opacity-100"></div>
                                <div class="absolute inset-x-0 bottom-0 flex items-center justify-between gap-2 p-3 text-white opacity-0 transition group-hover:opacity-100">
                                    <a
                                        class="inline-flex items-center rounded-md bg-white/90 px-2.5 py-1 text-xs font-semibold text-[#1d2327] shadow-sm"
                                        href="{{ route('tp.media.edit', ['media' => $item->id]) }}">
                                        Edit
                                    </a>
                                    <form
                                        method="POST"
                                        action="{{ route('tp.media.destroy', ['media' => $item->id]) }}"
                                        data-confirm="Delete this media file?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-2.5 py-1 text-xs font-semibold text-white shadow-sm">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="space-y-2 p-3">
                                <div class="flex items-center justify-between gap-2">
                                    <a
                                        href="{{ route('tp.media.edit', ['media' => $item->id]) }}"
                                        class="text-sm font-semibold text-[#1d2327] hover:underline truncate">
                                        {{ $title !== '' ? $title : ($originalName !== '' ? $originalName : 'Untitled') }}
                                    </a>
                                    <span class="rounded-full bg-black/5 px-2 py-0.5 text-[11px] font-semibold text-black/60">
                                        {{ $typeLabel }}
                                    </span>
                                </div>
                                <div class="text-xs text-black/60 flex flex-wrap items-center gap-2">
                                    <span>{{ $mime !== '' ? $mime : '—' }}</span>
                                    <span>·</span>
                                    <span>{{ $sizeLabel }}</span>
                                    <span>·</span>
                                    <span>{{ $dateLabel }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="tp-table-wrap">
                <table class="tp-table">
                    <thead class="tp-table__thead">
                        <tr>
                            <th class="tp-table__th">Preview</th>
                            <th class="tp-table__th">Title</th>
                            <th class="tp-table__th">File</th>
                            <th class="tp-table__th">Type</th>
                            <th class="tp-table__th">Size</th>
                            <th class="tp-table__th">Uploaded</th>
                            <th class="tp-table__th text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="tp-table__tbody">
                        @foreach ($media as $item)
                            @php
                                $disk = (string) ($item->disk ?? 'public');
                                $path = (string) ($item->path ?? '');
                            $url = $urlGenerator->url($item);
                                $mime = (string) ($item->mime_type ?? '');
                                $isImage = $mime !== '' && str_starts_with($mime, 'image/');
                                $size = is_numeric($item->size ?? null) ? (int) $item->size : null;
                                $sizeLabel = $size ? number_format($size / 1024, 1).' KB' : '—';
                                $title = (string) ($item->title ?? '');
                                $originalName = (string) ($item->original_name ?? '');
                                $typeLabel = $mime !== '' ? strtoupper(strtok($mime, '/')) : 'FILE';
                            @endphp
                            <tr class="tp-table__row">
                                <td class="tp-table__td">
                                    @if ($url && $isImage)
                                        <div class="h-28 w-40 overflow-hidden rounded-2xl border border-black/10 bg-slate-50 shadow-sm">
                                            <img src="{{ $url }}" alt="" class="h-full w-full object-cover" />
                                        </div>
                                    @else
                                        <div
                                            class="flex h-28 w-40 items-center justify-center rounded-2xl border border-dashed border-black/20 bg-slate-50 text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                                            {{ $typeLabel }}
                                        </div>
                                    @endif
                                </td>
                                <td class="tp-table__td">
                                    <a
                                        class="tp-button-link"
                                        href="{{ route('tp.media.edit', ['media' => $item->id]) }}">
                                        {{ $title !== '' ? $title : ($originalName !== '' ? $originalName : 'Untitled') }}
                                    </a>
                                </td>
                                <td class="tp-table__td tp-muted">
                                    {{ $originalName !== '' ? $originalName : '—' }}
                                </td>
                                <td class="tp-table__td tp-muted">
                                    {{ $mime !== '' ? $mime : '—' }}
                                </td>
                                <td class="tp-table__td tp-muted">{{ $sizeLabel }}</td>
                                <td class="tp-table__td tp-muted">{{ $item->created_at?->format('Y-m-d') ?? '—' }}</td>
                                <td class="tp-table__td">
                                    <div class="flex justify-end gap-3 text-xs text-slate-600">
                                        <a
                                            class="tp-button-link hover:text-slate-900"
                                            href="{{ route('tp.media.edit', ['media' => $item->id]) }}">
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('tp.media.destroy', ['media' => $item->id]) }}"
                                            data-confirm="Delete this media file?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="tp-button-link text-red-600 hover:text-red-700">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="tp-metabox__body">
                {{ $media->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const root = document.querySelector('[data-media-index]');
            if (!root) {
                return;
            }

            const storageKey = 'tp.media.view';
            const params = new URLSearchParams(window.location.search);
            const queryView = params.get('view');
            const storedView = localStorage.getItem(storageKey);
            const currentView = root.dataset.currentView;

            if ((queryView === null || queryView === '') && (storedView === 'list' || storedView === 'grid') && storedView !== currentView) {
                params.set('view', storedView);
                const query = params.toString();
                window.location.replace(query === '' ? window.location.pathname : `${window.location.pathname}?${query}`);
                return;
            }

            if (queryView === 'list' || queryView === 'grid') {
                localStorage.setItem(storageKey, queryView);
            }

            root.querySelectorAll('[data-media-view-link]').forEach((link) => {
                link.addEventListener('click', () => {
                    const nextView = link.dataset.mediaViewLink;
                    if (nextView === 'list' || nextView === 'grid') {
                        localStorage.setItem(storageKey, nextView);
                    }
                });
            });
        })();
    </script>
@endpush
