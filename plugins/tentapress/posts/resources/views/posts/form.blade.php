@extends('tentapress-admin::layouts.shell')

@section('title', $mode === 'create' ? 'Add New Post' : 'Edit Post')

@section('content')
    <div class="tp-editor space-y-6">
        <div class="tp-page-header">
            <div>
                <h1 class="tp-page-title">
                    {{ $mode === 'create' ? 'Add New Post' : 'Edit Post' }}
                </h1>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
            <div class="space-y-6 lg:col-span-3">
                <div class="tp-metabox">
                    <div class="tp-metabox__body space-y-4">
                        <form
                            method="POST"
                            action="{{ $mode === 'create' ? route('tp.posts.store') : route('tp.posts.update', ['post' => $post->id]) }}"
                            class="space-y-4"
                            id="post-form">
                            @csrf
                            @if ($mode === 'edit')
                                @method('PUT')
                            @endif

                            <div
                                class="space-y-4"
                                x-data="{
                                    title: @js(old('title', $post->title)),
                                    slug: @js(old('slug', $post->slug)),
                                    titleTouched: false,
                                    slugTouched: false,
                                    isSlugValid() {
                                        return this.slug.trim() === '' || /^[a-z0-9-]+$/.test(this.slug)
                                    },
                                }">
                                <div class="tp-field">
                                    <label class="tp-label">Title</label>
                                    <input
                                        name="title"
                                        class="tp-input"
                                        value="{{ old('title', $post->title) }}"
                                        x-model="title"
                                        @blur="titleTouched = true"
                                        required />
                                    <div class="tp-help">Required.</div>
                                    <div
                                        class="tp-help text-red-600"
                                        x-show="titleTouched && title.trim().length === 0"
                                        x-cloak>
                                        Title is required.
                                    </div>
                                </div>

                                @php
                                    $themeLayouts = is_array($themeLayouts ?? null) ? $themeLayouts : [];
                                    $currentLayout = old('layout', $post->layout);
                                    $currentLayout = is_string($currentLayout) ? $currentLayout : '';
                                    $currentLayout = $currentLayout !== '' ? $currentLayout : 'default';
                                @endphp

                                <div class="grid gap-4 lg:grid-cols-2">
                                    <div class="tp-field">
                                        <label class="tp-label">Slug</label>
                                        <input
                                            name="slug"
                                            class="tp-input"
                                            value="{{ old('slug', $post->slug) }}"
                                            x-model="slug"
                                            @blur="slugTouched = true"
                                            placeholder="auto-generated if blank (on create)"
                                            pattern="[a-z0-9-]+"
                                            title="Lowercase, numbers, and dashes only."
                                            {{ $mode === 'create' ? '' : 'required' }} />
                                        <div class="tp-help">Lowercase, numbers, and dashes only.</div>
                                        <div
                                            class="tp-help text-red-600"
                                            x-show="slugTouched && slug.trim() !== '' && ! isSlugValid()"
                                            x-cloak>
                                            Use only lowercase letters, numbers, and dashes.
                                        </div>
                                    </div>

                                    <div class="tp-field">
                                        <label class="tp-label">Layout</label>

                                        @if (count($themeLayouts) > 0)
                                            <select name="layout" class="tp-select">
                                                @foreach ($themeLayouts as $layout)
                                                    @php
                                                        $key = isset($layout['key']) ? (string) $layout['key'] : '';
                                                        $label = isset($layout['label']) ? (string) $layout['label'] : $key;
                                                    @endphp

                                                    @if ($key !== '')
                                                        <option value="{{ $key }}" @selected($currentLayout === $key)>
                                                            {{ $label }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <div class="tp-help">Layouts come from the active theme manifest.</div>
                                        @else
                                            <input
                                                name="layout"
                                                class="tp-input"
                                                value="{{ $currentLayout }}"
                                                placeholder="default" />
                                            <div class="tp-help">
                                                No theme layouts
                                                found{{ ! empty($hasTheme) ? '' : ' (no active theme)' }} — using free
                                                text key.
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                @php
                                    $authors = is_array($authors ?? null) ? $authors : [];
                                    $authorId = (int) (old('author_id', $authorId ?? ($post->author_id ?? 0)) ?? 0);
                                    $publishedAt = old('published_at');
                                    if (! is_string($publishedAt) || $publishedAt === '') {
                                        $publishedAt = $post->published_at?->format('Y-m-d\\TH:i') ?? '';
                                    }
                                @endphp

                                <div class="grid gap-4 lg:grid-cols-2">
                                    <div class="tp-field">
                                        <label class="tp-label">Author</label>
                                        @if (count($authors) > 0)
                                            <select name="author_id" class="tp-select">
                                                <option value="">(Default)</option>
                                                @foreach ($authors as $author)
                                                    @php
                                                        $id = (int) ($author->id ?? 0);
                                                        $name = (string) ($author->name ?? '');
                                                        $email = (string) ($author->email ?? '');
                                                        $label = trim($name . ($email !== '' ? ' · ' . $email : ''));
                                                    @endphp

                                                    @if ($id > 0)
                                                        <option value="{{ $id }}" @selected($authorId === $id)>
                                                            {{ $label !== '' ? $label : 'User #' . $id }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <div class="tp-help">Defaults to the current user when left blank.</div>
                                        @else
                                            <input class="tp-input" value="No users available" disabled />
                                        @endif
                                    </div>

                                    <div class="tp-field">
                                        <label class="tp-label">Publish date</label>
                                        <input
                                            type="datetime-local"
                                            name="published_at"
                                            class="tp-input"
                                            value="{{ $publishedAt }}" />
                                        <div class="tp-help">Optional; used for published timestamp.</div>
                                    </div>
                                </div>
                            </div>

                            @component('tentapress-blocks::editor', [
                                'blocksEditorMode' => true,
                                'blocksJson' => $blocksJson,
                                'blockDefinitions' => $blockDefinitions ?? [],
                                'mediaOptions' => $mediaOptions ?? [],
                                'mediaIndexUrl' => \Illuminate\Support\Facades\Route::has('tp.media.index') ? route('tp.media.index') : '',
                            ])
                            @endcomponent
                        </form>
                    </div>
                </div>
            </div>

            <div class="space-y-6 lg:sticky lg:top-6 lg:self-start">
                <div class="tp-metabox">
                    <div class="tp-metabox__title">Status</div>
                    <div class="tp-metabox__body space-y-4 text-sm">
                        <div class="space-y-2">
                            <div>
                                <span class="tp-muted">Status:</span>
                                <span class="font-semibold">{{ ucfirst($post->status) }}</span>
                            </div>
                            <div>
                                <span class="tp-muted">Published:</span>
                                <span class="tp-code">{{ $post->published_at?->toDateTimeString() ?? '—' }}</span>
                            </div>
                            <div>
                                <span class="tp-muted">Updated:</span>
                                <span class="tp-code">{{ $post->updated_at?->toDateTimeString() ?? '—' }}</span>
                            </div>
                        </div>

                        <div class="tp-divider"></div>

                        <div class="space-y-2">
                            <button type="submit" form="post-form" class="tp-button-primary w-full justify-center">
                                {{ $mode === 'create' ? 'Create Post' : 'Save Changes' }}
                            </button>

                            @if ($mode === 'edit' && $post->status === 'draft')
                                <form method="POST" action="{{ route('tp.posts.publish', ['post' => $post->id]) }}">
                                    @csrf
                                    <button class="tp-button-primary w-full justify-center" type="submit">
                                        Publish
                                    </button>
                                </form>
                            @endif

                            @if ($mode === 'edit' && $post->status === 'published')
                                <form method="POST" action="{{ route('tp.posts.unpublish', ['post' => $post->id]) }}">
                                    @csrf
                                    <button class="tp-button-secondary w-full justify-center" type="submit">
                                        Unpublish
                                    </button>
                                </form>
                            @endif

                            @if ($mode === 'edit')
                                <form
                                    method="POST"
                                    action="{{ route('tp.posts.destroy', ['post' => $post->id]) }}"
                                    onsubmit="return confirm('Delete this post? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="tp-button-danger w-full justify-center"
                                        aria-label="Delete post">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                @includeIf('tentapress-seo::post-metabox', ['post' => $post, 'mode' => $mode])
            </div>
        </div>
    </div>
@endsection
