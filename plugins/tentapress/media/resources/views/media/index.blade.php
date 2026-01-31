@extends('tentapress-admin::layouts.shell')

@section('title', 'Media')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Media</h1>
            <p class="tp-description">Upload and manage media files.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('tp.media.create') }}" class="tp-button-primary">Upload</a>
        </div>
    </div>

    @php
        $urlGenerator = app(\TentaPress\Media\Contracts\MediaUrlGenerator::class);
    @endphp

    <div class="tp-metabox">
        <div class="tp-metabox__title">
            <form method="GET" action="{{ route('tp.media.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <input type="hidden" name="view" value="{{ $view }}" />
                <div class="flex-1"></div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex items-center gap-1">
                        <a
                            href="{{ route('tp.media.index', array_merge(request()->query(), ['view' => 'list'])) }}"
                            class="{{ $view === 'list' ? 'tp-button-secondary' : 'tp-button-secondary opacity-60 hover:opacity-100' }}">
                            List
                        </a>
                        <a
                            href="{{ route('tp.media.index', array_merge(request()->query(), ['view' => 'grid'])) }}"
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
            <div class="tp-metabox__body tp-muted text-sm">No media found.</div>
        @elseif ($view === 'grid')
            <div class="tp-metabox__body">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
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
                        @endphp
                        <div class="rounded border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-100">
                                @if ($url && $isImage)
                                    <img
                                        src="{{ $url }}"
                                        alt=""
                                        class="h-32 w-full rounded-t object-cover" />
                                @else
                                    <div class="flex h-32 items-center justify-center rounded-t bg-slate-50 text-xs uppercase text-slate-400">
                                        File
                                    </div>
                                @endif
                            </div>
                            <div class="space-y-2 p-3">
                                <div class="text-sm font-semibold">
                                    <a href="{{ route('tp.media.edit', ['media' => $item->id]) }}" class="hover:underline">
                                        {{ $title !== '' ? $title : ($originalName !== '' ? $originalName : 'Untitled') }}
                                    </a>
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ $mime !== '' ? $mime : '—' }} · {{ $sizeLabel }}
                                </div>
                                <div class="flex flex-wrap gap-3 text-xs text-slate-600">
                                    <a
                                        class="tp-button-link hover:text-slate-900"
                                        href="{{ route('tp.media.edit', ['media' => $item->id]) }}">
                                        Edit
                                    </a>
                                    <form
                                        method="POST"
                                        action="{{ route('tp.media.destroy', ['media' => $item->id]) }}"
                                        onsubmit="return confirm('Delete this media file?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="tp-button-link text-red-600 hover:text-red-700">
                                            Delete
                                        </button>
                                    </form>
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
                            @endphp
                            <tr class="tp-table__row">
                                <td class="tp-table__td">
                                    @if ($url && $isImage)
                                        <img src="{{ $url }}" alt="" class="h-12 w-12 rounded border border-slate-200 object-cover" />
                                    @else
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded border border-dashed border-slate-300 text-[10px] uppercase text-slate-500">
                                            File
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
                                            onsubmit="return confirm('Delete this media file?');">
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
