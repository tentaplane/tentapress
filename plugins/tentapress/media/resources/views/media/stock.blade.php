@extends('tentapress-admin::layouts.shell')

@section('title', 'Stock Library')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Stock Library</h1>
            <p class="tp-description">Search and import images and videos.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('tp.media.index') }}" class="tp-button-secondary">Back to Media</a>
            <a href="{{ route('tp.media.stock.settings') }}" class="tp-button-secondary">Settings</a>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__body space-y-4">
            <form method="GET" action="{{ route('tp.media.stock') }}" class="grid gap-3 lg:grid-cols-4 lg:items-end">
                <div class="lg:col-span-2">
                    <label class="tp-label">Search</label>
                    <input name="q" value="{{ $query }}" class="tp-input" placeholder="Search stock libraries…" />
                </div>

                <div>
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

                <div>
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

                <div class="lg:col-span-4 flex gap-2">
                    <button class="tp-button-primary" type="submit">Search</button>
                    <a class="tp-button-secondary" href="{{ route('tp.media.stock') }}">Reset</a>
                </div>
            </form>
        </div>
    </div>

    @if (count($sources) === 0)
        <div class="tp-notice-warning">
            No stock sources are enabled. Add API keys in Settings to continue.
        </div>
    @endif

    @if ($results === null && $query !== '')
        <div class="tp-notice-warning">No provider available for this search.</div>
    @endif

    @if ($results && count($results->items) === 0)
        <div class="tp-metabox mt-4">
            <div class="tp-metabox__body tp-muted text-sm">No results found.</div>
        </div>
    @endif

    @if ($results && count($results->items) > 0)
        @if ($attributionReminder)
            <div class="tp-notice-info">
                Attribution is recommended for most sources. Use the provided attribution text when you publish.
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 mt-4">
            @foreach ($results->items as $item)
                <div class="rounded-lg border border-black/10 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-black/10 bg-slate-50">
                        @if ($item->previewUrl)
                            <img src="{{ $item->previewUrl }}" alt="" class="h-40 w-full object-cover" />
                        @else
                            <div class="flex h-40 items-center justify-center text-xs uppercase text-black/50">
                                {{ $item->mediaType ?? 'Asset' }}
                            </div>
                        @endif
                    </div>
                    <div class="p-3 space-y-2">
                        <div>
                            <p class="text-sm font-semibold text-[#1d2327]">
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

                        <form method="POST" action="{{ route('tp.media.stock.import') }}">
                            @csrf
                            <input type="hidden" name="source" value="{{ $item->provider }}" />
                            <input type="hidden" name="id" value="{{ $item->id }}" />
                            <input type="hidden" name="media_type" value="{{ $item->mediaType }}" />
                            <button type="submit" class="tp-button-primary w-full justify-center">
                                Add to Media
                            </button>
                        </form>
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
@endsection
