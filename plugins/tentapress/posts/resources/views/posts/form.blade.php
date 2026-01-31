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

                            @php
                                $blockDefinitions = is_array($blockDefinitions ?? null) ? $blockDefinitions : [];
                                $mediaOptions = is_array($mediaOptions ?? null) ? $mediaOptions : [];
                                $mediaIndexUrl = \Illuminate\Support\Facades\Route::has('tp.media.index') ? route('tp.media.index') : '';
                                $initialBlocksJson = old('blocks_json', $blocksJson);
                                $initialBlocksJson = is_string($initialBlocksJson) ? $initialBlocksJson : '[]';
                            @endphp

                            <div
                                class="tp-field space-y-4"
                                x-data="tpBlocksEditor({
                                            initialJson: @js($initialBlocksJson),
                                            definitions: @js($blockDefinitions),
                                            mediaOptions: @js($mediaOptions),
                                            mediaIndexUrl: @js($mediaIndexUrl),
                                        })"
                                x-init="init()">
                                <label class="tp-label">Blocks</label>

                                <div class="tp-panel space-y-4">
                                    <div
                                        class="flex flex-col items-center justify-between gap-2 sm:flex-row sm:items-center">
                                        <div>
                                            <select class="tp-select w-full sm:w-72" x-model="addType">
                                                <option value="">Add a block…</option>
                                                <template x-for="def in definitions" :key="def.type">
                                                    <option :value="def.type" x-text="def.name || def.type"></option>
                                                </template>
                                            </select>

                                            <button type="button" class="tp-button-secondary" @click="addBlock()">
                                                Add block
                                            </button>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-4 text-xs">
                                            <button type="button" class="tp-button-link" @click="expandAll()">
                                                Expand all
                                            </button>
                                            <button type="button" class="tp-button-link" @click="collapseAll()">
                                                Collapse all
                                            </button>
                                        </div>

                                        <button type="button" class="tp-button-link" @click="advanced = !advanced">
                                            <span x-text="advanced ? 'Hide Advanced' : 'Advanced'"></span>
                                        </button>
                                    </div>

                                    <div class="tp-notice-warning" x-show="jsonInvalid" x-cloak>
                                        The blocks JSON is invalid. Fix it in Advanced mode.
                                    </div>

                                    <div class="space-y-3">
                                        <template x-if="blocks.length === 0">
                                            <div class="rounded-md border border-slate-200 bg-white p-4 text-sm">
                                                <div class="font-semibold">Start with a block</div>
                                                <div class="tp-muted mt-1">Choose a block type above.</div>
                                            </div>
                                        </template>

                                        <div class="tp-metabox" x-show="advanced" x-cloak>
                                            <div class="tp-metabox__title">Advanced JSON</div>
                                            <div class="tp-metabox__body space-y-2">
                                                <textarea
                                                    class="tp-textarea font-mono text-xs"
                                                    rows="14"
                                                    x-model="advancedJson"
                                                    @blur="applyAdvancedJson()"></textarea>
                                            </div>
                                        </div>

                                        <template x-for="(block, index) in blocks" :key="block._key">
                                            <div
                                                class="tp-metabox bg-zinc-50"
                                                :class="{
                                                'opacity-60': dragIndex === index,
                                                'ring-2 ring-black/10': dragOverIndex === index && dragIndex !== index,
                                            }"
                                                @dragover.prevent="dragOver(index)"
                                                @dragleave="dragLeave(index)"
                                                @drop="dropOn(index)">
                                                <div
                                                    class="tp-metabox__title flex flex-wrap items-center justify-between gap-3">
                                                    <div class="flex min-w-0 items-center gap-2">
                                                        <button
                                                            type="button"
                                                            class="tp-button-link cursor-move text-slate-400 hover:text-slate-600"
                                                            draggable="true"
                                                            aria-label="Drag to reorder"
                                                            x-show="block._collapsed"
                                                            x-cloak
                                                            @dragstart="dragStart(index, $event)"
                                                            @dragend="dragEnd()">
                                                            <svg
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                fill="none"
                                                                viewBox="0 0 24 24"
                                                                stroke-width="1.5"
                                                                stroke="currentColor"
                                                                class="size-5">
                                                                <path
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"
                                                                    d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                                            </svg>
                                                        </button>

                                                        <div class="min-w-0">
                                                            <div class="flex flex-wrap items-center gap-2">
                                                                <span class="tp-muted text-xs">
                                                                    #
                                                                    <span x-text="index + 1"></span>
                                                                </span>
                                                                <span
                                                                    class="font-semibold"
                                                                    x-text="titleFor(block.type)"></span>
                                                                <span
                                                                    class="tp-muted text-xs font-normal"
                                                                    x-text="block.type"></span>
                                                                <template x-if="block.version">
                                                                    <span
                                                                        class="tp-muted text-xs font-normal"
                                                                        x-text="'v' + block.version"></span>
                                                                </template>
                                                                <template x-if="block.variant">
                                                                    <span
                                                                        class="tp-muted text-xs font-normal"
                                                                        x-text="block.variant"></span>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="flex flex-wrap items-center gap-2 text-xs">
                                                        <button
                                                            type="button"
                                                            class="tp-button-link"
                                                            @click="toggleCollapse(index)">
                                                            <span
                                                                x-text="block._collapsed ? 'Expand' : 'Collapse'"></span>
                                                        </button>
                                                        <span class="text-slate-300">|</span>
                                                        <button
                                                            type="button"
                                                            class="tp-button-link"
                                                            @click="duplicateBlock(index)">
                                                            Duplicate
                                                        </button>
                                                        <span class="text-slate-300">|</span>
                                                        <button
                                                            type="button"
                                                            class="tp-button-link"
                                                            @click="move(index, -1)"
                                                            :disabled="index === 0">
                                                            Move up
                                                        </button>
                                                        <button
                                                            type="button"
                                                            class="tp-button-link"
                                                            @click="move(index, +1)"
                                                            :disabled="index === blocks.length - 1">
                                                            Move down
                                                        </button>
                                                        <span class="text-slate-300">|</span>
                                                        <button
                                                            type="button"
                                                            class="tp-button-link text-red-600 hover:text-red-700"
                                                            @click="remove(index)">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>

                                                <div
                                                    class="tp-metabox__body space-y-4"
                                                    x-show="!block._collapsed"
                                                    x-cloak>
                                                    <template x-if="variantsFor(block.type).length > 0">
                                                        <div class="tp-field">
                                                            <label class="tp-label">Variant</label>
                                                            <select class="tp-select" x-model="block.variant">
                                                                <template
                                                                    x-for="variant in variantsFor(block.type)"
                                                                    :key="variant.key">
                                                                    <option
                                                                        :value="variant.key"
                                                                        x-text="variant.label || variant.key"></option>
                                                                </template>
                                                            </select>
                                                        </div>
                                                    </template>

                                                    <template x-if="fieldsFor(block.type).length > 0">
                                                        <div class="space-y-4">
                                                            <template
                                                                x-for="field in fieldsFor(block.type)"
                                                                :key="field.key">
                                                                <div class="tp-field">
                                                                    <label
                                                                        class="tp-label"
                                                                        x-text="field.label"></label>

                                                                    <template x-if="field.type === 'textarea'">
                                                                        <textarea
                                                                            class="tp-textarea"
                                                                            :rows="field.rows ? field.rows : 4"
                                                                            :placeholder="field.placeholder || ''"
                                                                            :value="getProp(index, field.key)"
                                                                            @input="setProp(index, field.key, $event.target.value)"></textarea>
                                                                    </template>

                                                                    <template x-if="field.type === 'select'">
                                                                        <select
                                                                            class="tp-select"
                                                                            :value="getProp(index, field.key)"
                                                                            @change="setProp(index, field.key, $event.target.value)">
                                                                            <template
                                                                                x-for="opt in selectOptions(field)"
                                                                                :key="opt.value">
                                                                                <option
                                                                                    :value="opt.value"
                                                                                    x-text="opt.label"></option>
                                                                            </template>
                                                                        </select>
                                                                    </template>

                                                                    <template x-if="field.type === 'toggle'">
                                                                        <label class="flex items-center gap-2 text-sm">
                                                                            <input
                                                                                type="checkbox"
                                                                                class="tp-checkbox"
                                                                                :checked="!!getPropRaw(index, field.key)"
                                                                                @change="setProp(index, field.key, $event.target.checked)" />
                                                                            <span
                                                                                x-text="field.toggle_label || 'Enabled'"></span>
                                                                        </label>
                                                                    </template>

                                                                    <template x-if="field.type === 'number'">
                                                                        <input
                                                                            class="tp-input"
                                                                            type="number"
                                                                            :min="field.min !== undefined ? field.min : null"
                                                                            :max="field.max !== undefined ? field.max : null"
                                                                            :step="field.step !== undefined ? field.step : null"
                                                                            :placeholder="field.placeholder || ''"
                                                                            :value="getProp(index, field.key)"
                                                                            @input="setProp(index, field.key, $event.target.value)" />
                                                                    </template>

                                                                    <template x-if="field.type === 'range'">
                                                                        <input
                                                                            class="tp-input"
                                                                            type="range"
                                                                            :min="field.min !== undefined ? field.min : null"
                                                                            :max="field.max !== undefined ? field.max : null"
                                                                            :step="field.step !== undefined ? field.step : null"
                                                                            :value="getProp(index, field.key)"
                                                                            @input="setProp(index, field.key, $event.target.value)" />
                                                                    </template>

                                                                    <template x-if="field.type === 'color'">
                                                                        <input
                                                                            class="tp-input h-10 p-1"
                                                                            type="color"
                                                                            :value="getProp(index, field.key)"
                                                                            @input="setProp(index, field.key, $event.target.value)" />
                                                                    </template>

                                                                    <template x-if="field.type === 'media'">
                                                                        <div class="space-y-3">
                                                                            <div
                                                                                class="flex flex-wrap items-center gap-2">
                                                                                <button
                                                                                    type="button"
                                                                                    class="tp-button-secondary"
                                                                                    @click="openMediaModal(index, field.key, 'single')">
                                                                                    Choose media
                                                                                </button>
                                                                                <a
                                                                                    x-show="mediaIndexUrl"
                                                                                    :href="mediaIndexUrl"
                                                                                    target="_blank"
                                                                                    rel="noopener"
                                                                                    class="tp-button-link">
                                                                                    Manage
                                                                                </a>
                                                                            </div>

                                                                            <div
                                                                                class="flex flex-wrap items-center gap-3 rounded border border-black/10 bg-white p-3"
                                                                                x-show="getProp(index, field.key)"
                                                                                x-cloak>
                                                                                <img
                                                                                    x-show="isMediaImage(getProp(index, field.key))"
                                                                                    :src="getProp(index, field.key)"
                                                                                    alt=""
                                                                                    class="h-14 w-14 rounded border border-slate-200 object-cover" />
                                                                                <div class="min-w-0 flex-1">
                                                                                    <div
                                                                                        class="truncate text-sm font-semibold"
                                                                                        x-text="mediaLabel(getProp(index, field.key))"></div>
                                                                                    <div
                                                                                        class="tp-code truncate text-[11px]"
                                                                                        x-text="getProp(index, field.key)"></div>
                                                                                </div>
                                                                                <button
                                                                                    type="button"
                                                                                    class="tp-button-link"
                                                                                    @click="setProp(index, field.key, '')">
                                                                                    Clear
                                                                                </button>
                                                                            </div>

                                                                            <div class="space-y-1">
                                                                                <input
                                                                                    class="tp-input"
                                                                                    type="text"
                                                                                    :value="getProp(index, field.key)"
                                                                                    placeholder="/storage/… or https://…"
                                                                                    @input="setProp(index, field.key, $event.target.value)" />
                                                                                <div class="tp-help">
                                                                                    Pick from media or paste a URL.
                                                                                </div>
                                                                            </div>

                                                                            <div
                                                                                class="tp-muted text-xs"
                                                                                x-show="mediaOptions.length === 0">
                                                                                Upload media first to select it here.
                                                                            </div>
                                                                        </div>
                                                                    </template>

                                                                    <template x-if="field.type === 'media-list'">
                                                                        <div class="space-y-3">
                                                                            <div
                                                                                class="flex flex-wrap items-center gap-2">
                                                                                <button
                                                                                    type="button"
                                                                                    class="tp-button-secondary"
                                                                                    @click="openMediaModal(index, field.key, 'multi')">
                                                                                    Choose images
                                                                                </button>
                                                                                <a
                                                                                    x-show="mediaIndexUrl"
                                                                                    :href="mediaIndexUrl"
                                                                                    target="_blank"
                                                                                    rel="noopener"
                                                                                    class="tp-button-link">
                                                                                    Manage
                                                                                </a>
                                                                            </div>

                                                                            <div
                                                                                class="grid grid-cols-2 gap-3 md:grid-cols-3"
                                                                                x-show="getMediaList(index, field.key).length > 0"
                                                                                x-cloak>
                                                                                <template
                                                                                    x-for="(url, mediaIdx) in getMediaList(index, field.key)"
                                                                                    :key="url + ':' + mediaIdx">
                                                                                    <div
                                                                                        class="space-y-2 rounded border border-black/10 bg-white p-2">
                                                                                        <div
                                                                                            class="flex aspect-4/3 items-center justify-center overflow-hidden rounded border border-slate-200 bg-slate-50">
                                                                                            <img
                                                                                                x-show="isMediaImage(url)"
                                                                                                :src="url"
                                                                                                alt=""
                                                                                                class="h-full w-full object-cover" />
                                                                                            <div
                                                                                                x-show="! isMediaImage(url)"
                                                                                                class="tp-muted text-xs">
                                                                                                File
                                                                                            </div>
                                                                                        </div>
                                                                                        <div
                                                                                            class="tp-muted truncate text-xs"
                                                                                            x-text="mediaLabel(url)"></div>
                                                                                        <div
                                                                                            class="flex items-center justify-between gap-2 text-xs">
                                                                                            <button
                                                                                                type="button"
                                                                                                class="tp-button-link text-red-600 hover:text-red-700"
                                                                                                @click="removeFromMediaList(index, field.key, mediaIdx)">
                                                                                                Remove
                                                                                            </button>
                                                                                            <a
                                                                                                :href="url"
                                                                                                target="_blank"
                                                                                                rel="noopener"
                                                                                                class="tp-button-link">
                                                                                                Open
                                                                                            </a>
                                                                                        </div>
                                                                                    </div>
                                                                                </template>
                                                                            </div>

                                                                            <div class="space-y-1">
                                                                                <textarea
                                                                                    class="tp-textarea font-mono text-xs"
                                                                                    rows="3"
                                                                                    :value="mediaListText(index, field.key)"
                                                                                    @blur="applyMediaListText(index, field.key, $event.target.value)"></textarea>
                                                                                <div class="tp-help">
                                                                                    One URL per line for advanced edits.
                                                                                </div>
                                                                            </div>

                                                                            <div
                                                                                class="tp-muted text-xs"
                                                                                x-show="mediaOptions.length === 0">
                                                                                Upload media first to select it here.
                                                                            </div>
                                                                        </div>
                                                                    </template>

                                                                    <template
                                                                        x-if="
                                                                        field.type !== 'textarea' &&
                                                                        field.type !== 'media' &&
                                                                        field.type !== 'media-list' &&
                                                                        field.type !== 'select' &&
                                                                        field.type !== 'toggle' &&
                                                                        field.type !== 'number' &&
                                                                        field.type !== 'range' &&
                                                                        field.type !== 'color'
                                                                    ">
                                                                        <input
                                                                            class="tp-input"
                                                                            :type="field.type === 'url' ? 'url' : 'text'"
                                                                            :placeholder="field.placeholder || ''"
                                                                            :value="getProp(index, field.key)"
                                                                            @input="setProp(index, field.key, $event.target.value)" />
                                                                    </template>

                                                                    <template x-if="field.help">
                                                                        <div
                                                                            class="tp-help"
                                                                            x-text="field.help"></div>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </template>

                                                    <template x-if="fieldsFor(block.type).length === 0">
                                                        <div class="tp-field">
                                                            <label class="tp-label">Props (JSON)</label>
                                                            <textarea
                                                                class="tp-textarea font-mono text-xs"
                                                                rows="10"
                                                                @blur="setPropsJson(index, $event.target.value)"
                                                                x-text="propsJson(index)"></textarea>
                                                            <div class="tp-help">
                                                                Unknown block type — edit props directly.
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    {{-- Single source of truth posted to backend --}}
                                    <textarea name="blocks_json" class="hidden" x-ref="hidden">
{{ $initialBlocksJson }}
                                </textarea
                                    >

                                    <div
                                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
                                        x-show="mediaModalOpen"
                                        x-cloak
                                        @keydown.escape.window="closeMediaModal()"
                                        @click.self="closeMediaModal()">
                                        <div
                                            class="flex max-h-[85vh] w-full max-w-6xl flex-col overflow-hidden rounded-lg border border-black/10 bg-white shadow-xl">
                                            <div
                                                class="flex flex-wrap items-center gap-2 border-b border-black/10 px-4 py-3">
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-sm font-semibold">Select media</div>
                                                    <div
                                                        class="tp-muted text-xs"
                                                        x-show="mediaModalMode === 'multi'">
                                                        Choose multiple images for galleries.
                                                    </div>
                                                </div>
                                                <div class="flex w-full gap-2 sm:w-auto">
                                                    <input
                                                        class="tp-input w-full sm:w-80"
                                                        type="search"
                                                        placeholder="Search media…"
                                                        x-model="mediaModalSearch" />
                                                    <button
                                                        type="button"
                                                        class="tp-button-secondary"
                                                        @click="closeMediaModal()">
                                                        Close
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="min-h-0 flex-1 overflow-auto bg-[#f6f7f7] p-4">
                                                <div
                                                    class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-4"
                                                    x-show="modalFilteredOptions().length > 0">
                                                    <template x-for="opt in modalFilteredOptions()" :key="opt.value">
                                                        <button
                                                            type="button"
                                                            class="group flex h-full flex-col gap-2 rounded border bg-white p-2 text-left transition"
                                                            :class="
                                                            modalIsSelected(opt.value)
                                                                ? 'border-[#2271b1] ring-2 ring-[#2271b1]'
                                                                : 'border-black/10 hover:border-black/20'
                                                        "
                                                            @click="
                                                            mediaModalMode === 'multi'
                                                                ? modalToggleSelection(opt.value)
                                                                : modalSelectSingle(opt.value)
                                                        ">
                                                            <div
                                                                class="flex aspect-4/3 items-center justify-center overflow-hidden rounded border border-black/10 bg-slate-50">
                                                                <img
                                                                    x-show="opt.is_image"
                                                                    :src="opt.value"
                                                                    alt=""
                                                                    class="h-full w-full object-cover" />
                                                                <div
                                                                    x-show="!opt.is_image"
                                                                    class="tp-muted text-xs uppercase">
                                                                    File
                                                                </div>
                                                            </div>
                                                            <div class="min-w-0">
                                                                <div
                                                                    class="truncate text-sm font-semibold"
                                                                    x-text="opt.label"></div>
                                                                <div
                                                                    class="tp-muted truncate text-[11px]"
                                                                    x-text="opt.original_name"></div>
                                                            </div>
                                                        </button>
                                                    </template>
                                                </div>

                                                <div
                                                    class="tp-muted rounded border border-dashed border-black/15 bg-white p-6 text-center text-sm"
                                                    x-show="modalFilteredOptions().length === 0">
                                                    No media matches that search yet.
                                                </div>
                                            </div>

                                            <div
                                                class="flex flex-wrap items-center justify-between gap-3 border-t border-black/10 px-4 py-3"
                                                x-show="mediaModalMode === 'multi'">
                                                <div class="tp-muted text-xs">
                                                    <span
                                                        class="font-semibold"
                                                        x-text="modalSelectionCount()"></span>
                                                    selected
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <button
                                                        type="button"
                                                        class="tp-button-secondary"
                                                        @click="modalClearSelection()">
                                                        Clear
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="tp-button-primary"
                                                        @click="modalApplyMulti()">
                                                        Use selected
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                    document.addEventListener('alpine:init', () => {
                                        Alpine.data('tpBlocksEditor', (opts) => ({
                                            definitions: Array.isArray(opts.definitions) ? opts.definitions : [],
                                            mediaOptions: Array.isArray(opts.mediaOptions) ? opts.mediaOptions : [],
                                            mediaIndexUrl:
                                                typeof opts.mediaIndexUrl === 'string' ? opts.mediaIndexUrl : '',
                                            addType: '',
                                            blocks: [],
                                            dragIndex: null,
                                            dragOverIndex: null,
                                            jsonInvalid: false,

                                            advanced: false,
                                            advancedJson: '',

                                            mediaModalOpen: false,
                                            mediaModalSearch: '',
                                            mediaModalIndex: null,
                                            mediaModalKey: '',
                                            mediaModalMode: 'single',
                                            mediaModalSelection: {},

                                            init() {
                                                this.advancedJson =
                                                    typeof opts.initialJson === 'string' ? opts.initialJson : '[]';

                                                const parsed = this.safeParseBlocks(this.advancedJson);
                                                this.blocks = parsed.blocks.map((b) => this.decorateBlock(b));
                                                this.jsonInvalid = !parsed.ok;

                                                this.sync();

                                                this.$watch(
                                                    'blocks',
                                                    () => {
                                                        this.sync();
                                                    },
                                                    { deep: true },
                                                );
                                            },

                                            decorateBlock(block) {
                                                const out = block && typeof block === 'object' ? block : {};

                                                out.type = typeof out.type === 'string' ? out.type : '';
                                                out.version =
                                                    typeof out.version === 'number'
                                                        ? out.version
                                                        : Number.isFinite(parseInt(out.version))
                                                          ? parseInt(out.version)
                                                          : null;

                                                if (!out.props || typeof out.props !== 'object') out.props = {};

                                                // UI-only keys
                                                out._collapsed = !!out._collapsed;
                                                out._key = out._key || this.uid();

                                                const def = this.defByType(out.type);

                                                // If version missing, use registry version if available, else 1
                                                if (!out.version) {
                                                    out.version = def && def.version ? parseInt(def.version) : 1;
                                                }

                                                const variants = this.variantsFor(out.type);
                                                if (variants.length) {
                                                    const keys = variants.map((v) => String(v.key));
                                                    const current =
                                                        typeof out.variant === 'string' ? out.variant.trim() : '';
                                                    out.variant = keys.includes(current)
                                                        ? current
                                                        : this.defaultVariantFor(out.type);
                                                } else {
                                                    out.variant = '';
                                                }

                                                // Apply shallow defaults for missing keys
                                                if (def && def.defaults && typeof def.defaults === 'object') {
                                                    out.props = this.mergeDefaults(def.defaults, out.props);
                                                }

                                                return out;
                                            },

                                            mergeDefaults(defaults, props) {
                                                const out = props && typeof props === 'object' ? props : {};
                                                for (const k in defaults) {
                                                    if (!Object.prototype.hasOwnProperty.call(out, k)) {
                                                        out[k] = this.deepClone(defaults[k]);
                                                    }
                                                }
                                                return out;
                                            },

                                            deepClone(value) {
                                                try {
                                                    return JSON.parse(JSON.stringify(value));
                                                } catch {
                                                    return value;
                                                }
                                            },

                                            uid() {
                                                return Math.random().toString(36).slice(2, 10);
                                            },

                                            defByType(type) {
                                                return this.definitions.find((d) => d.type === type);
                                            },

                                            fieldsFor(type) {
                                                const def = this.defByType(type);
                                                const fields = def && Array.isArray(def.fields) ? def.fields : [];
                                                return fields.filter((f) => f && f.key && f.label && f.type);
                                            },

                                            variantsFor(type) {
                                                const def = this.defByType(type);
                                                const variants = def && Array.isArray(def.variants) ? def.variants : [];
                                                return variants.filter((v) => v && v.key);
                                            },

                                            defaultVariantFor(type) {
                                                const def = this.defByType(type);
                                                const variants = this.variantsFor(type);
                                                if (def && def.default_variant) {
                                                    return String(def.default_variant);
                                                }
                                                if (variants.length) {
                                                    return String(variants[0].key || '');
                                                }
                                                return '';
                                            },

                                            selectOptions(field) {
                                                if (!field || !Array.isArray(field.options)) return [];
                                                return field.options.map((opt) => {
                                                    if (opt && typeof opt === 'object') {
                                                        return {
                                                            value: String(opt.value ?? ''),
                                                            label: String(opt.label ?? opt.value ?? ''),
                                                        };
                                                    }
                                                    return {
                                                        value: String(opt ?? ''),
                                                        label: String(opt ?? ''),
                                                    };
                                                });
                                            },

                                            mediaOption(value) {
                                                const key = String(value || '').trim();
                                                if (!key) return null;
                                                const found =
                                                    this.mediaOptions.find((opt) => opt && String(opt.value) === key) ||
                                                    null;
                                                if (found) {
                                                    return found;
                                                }
                                                return {
                                                    value: key,
                                                    label: key.split('/').pop() || key,
                                                    original_name: '',
                                                    mime_type: '',
                                                    is_image: /\\.(png|jpe?g|gif|webp|svg)$/i.test(key),
                                                };
                                            },

                                            isMediaImage(value) {
                                                const opt = this.mediaOption(value);
                                                if (!opt) return false;
                                                if (opt.is_image !== undefined) return !!opt.is_image;
                                                return /\\.(png|jpe?g|gif|webp|svg)$/i.test(String(opt.value || ''));
                                            },

                                            mediaLabel(value) {
                                                const opt = this.mediaOption(value);
                                                if (!opt) return '';
                                                if (opt.label) return opt.label;
                                                if (opt.original_name) return opt.original_name;
                                                return String(opt.value || '');
                                            },

                                            getMediaList(index, path) {
                                                const raw = this.getPropRaw(index, path);
                                                if (Array.isArray(raw)) {
                                                    return raw
                                                        .map((v) => String(v || '').trim())
                                                        .filter((v) => v !== '');
                                                }
                                                if (typeof raw === 'string' && raw.trim() !== '') {
                                                    return raw
                                                        .split(/[\\n,]/)
                                                        .map((v) => String(v || '').trim())
                                                        .filter((v) => v !== '');
                                                }
                                                return [];
                                            },

                                            setMediaList(index, path, list) {
                                                const values = Array.isArray(list)
                                                    ? list.map((v) => String(v || '').trim()).filter((v) => v !== '')
                                                    : [];
                                                this.setProp(index, path, values);
                                            },

                                            removeFromMediaList(index, path, mediaIdx) {
                                                const list = this.getMediaList(index, path);
                                                if (mediaIdx < 0 || mediaIdx >= list.length) return;
                                                list.splice(mediaIdx, 1);
                                                this.setMediaList(index, path, list);
                                            },

                                            mediaListText(index, path) {
                                                return this.getMediaList(index, path).join('\\n');
                                            },

                                            applyMediaListText(index, path, text) {
                                                if (typeof text !== 'string') {
                                                    return;
                                                }
                                                const list = text
                                                    .split(/[\\n,]/)
                                                    .map((v) => String(v || '').trim())
                                                    .filter((v) => v !== '');
                                                this.setMediaList(index, path, list);
                                            },

                                            openMediaModal(index, key, mode = 'single') {
                                                this.mediaModalIndex = Number.isFinite(index) ? index : null;
                                                this.mediaModalKey = String(key || '').trim();
                                                this.mediaModalMode = mode === 'multi' ? 'multi' : 'single';
                                                this.mediaModalSearch = '';

                                                if (this.mediaModalMode === 'multi' && this.mediaModalIndex !== null) {
                                                    const existing = this.getMediaList(
                                                        this.mediaModalIndex,
                                                        this.mediaModalKey,
                                                    );
                                                    const selection = {};
                                                    for (const url of existing) {
                                                        selection[url] = true;
                                                    }
                                                    this.mediaModalSelection = selection;
                                                } else {
                                                    this.mediaModalSelection = {};
                                                }

                                                this.mediaModalOpen = true;
                                            },

                                            closeMediaModal() {
                                                this.mediaModalOpen = false;
                                                this.mediaModalSearch = '';
                                            },

                                            modalMatches(opt, query) {
                                                const q = String(query || '')
                                                    .trim()
                                                    .toLowerCase();
                                                if (q === '') return true;
                                                const hay = [
                                                    String(opt.label || ''),
                                                    String(opt.original_name || ''),
                                                    String(opt.mime_type || ''),
                                                    String(opt.value || ''),
                                                ]
                                                    .join(' ')
                                                    .toLowerCase();
                                                return hay.includes(q);
                                            },

                                            modalFilteredOptions() {
                                                const query = this.mediaModalSearch;
                                                return this.mediaOptions.filter(
                                                    (opt) => opt && this.modalMatches(opt, query),
                                                );
                                            },

                                            modalToggleSelection(value) {
                                                const key = String(value || '').trim();
                                                if (!key) return;
                                                const next = { ...this.mediaModalSelection };
                                                next[key] = !next[key];
                                                if (!next[key]) {
                                                    delete next[key];
                                                }
                                                this.mediaModalSelection = next;
                                            },

                                            modalIsSelected(value) {
                                                const key = String(value || '').trim();
                                                if (!key) return false;
                                                return !!this.mediaModalSelection[key];
                                            },

                                            modalSelectionCount() {
                                                return Object.keys(this.mediaModalSelection).length;
                                            },

                                            modalClearSelection() {
                                                this.mediaModalSelection = {};
                                            },

                                            modalSelectSingle(value) {
                                                if (this.mediaModalIndex === null || this.mediaModalKey === '') {
                                                    return;
                                                }
                                                this.setProp(this.mediaModalIndex, this.mediaModalKey, value);
                                                this.closeMediaModal();
                                            },

                                            modalApplyMulti() {
                                                if (this.mediaModalIndex === null || this.mediaModalKey === '') {
                                                    return;
                                                }
                                                const selected = [];
                                                for (const opt of this.mediaOptions) {
                                                    if (!opt || !opt.value) continue;
                                                    if (this.mediaModalSelection[String(opt.value)]) {
                                                        selected.push(String(opt.value));
                                                    }
                                                }
                                                this.setMediaList(this.mediaModalIndex, this.mediaModalKey, selected);
                                                this.closeMediaModal();
                                            },

                                            titleFor(type) {
                                                const def = this.defByType(type);
                                                return def && def.name ? def.name : type || 'Block';
                                            },

                                            addBlock() {
                                                if (!this.addType) return;
                                                const def = this.defByType(this.addType);
                                                const defaults = def && def.defaults ? def.defaults : {};
                                                const variant = this.defaultVariantFor(this.addType);
                                                this.blocks.push(
                                                    this.decorateBlock({
                                                        type: this.addType,
                                                        ...(variant ? { variant } : {}),
                                                        props: this.deepClone(defaults),
                                                    }),
                                                );
                                                this.addType = '';
                                            },

                                            duplicateBlock(index) {
                                                const block = this.blocks[index];
                                                if (!block) return;
                                                const clone = this.deepClone(block);
                                                clone._key = this.uid();
                                                this.blocks.splice(index + 1, 0, clone);
                                            },

                                            remove(index) {
                                                this.blocks.splice(index, 1);
                                            },

                                            move(index, dir) {
                                                const next = index + dir;
                                                if (next < 0 || next >= this.blocks.length) return;
                                                const [item] = this.blocks.splice(index, 1);
                                                this.blocks.splice(next, 0, item);
                                            },

                                            dragStart(index, event) {
                                                this.dragIndex = index;
                                                if (event && event.dataTransfer) {
                                                    event.dataTransfer.effectAllowed = 'move';
                                                    event.dataTransfer.setData('text/plain', 'move');

                                                    const row = event.target?.closest('.tp-metabox');
                                                    if (row && event.dataTransfer.setDragImage) {
                                                        const rect = row.getBoundingClientRect();
                                                        const ghost = row.cloneNode(true);
                                                        ghost.style.position = 'fixed';
                                                        ghost.style.top = '-1000px';
                                                        ghost.style.left = '-1000px';
                                                        ghost.style.width = `${rect.width}px`;
                                                        ghost.style.pointerEvents = 'none';
                                                        ghost.style.opacity = '0.85';
                                                        document.body.appendChild(ghost);
                                                        event.dataTransfer.setDragImage(ghost, 24, 16);
                                                        setTimeout(() => ghost.remove(), 0);
                                                    }
                                                }
                                            },

                                            dragEnd() {
                                                this.dragIndex = null;
                                                this.dragOverIndex = null;
                                            },

                                            dragOver(index) {
                                                this.dragOverIndex = index;
                                            },

                                            dragLeave(index) {
                                                if (this.dragOverIndex === index) {
                                                    this.dragOverIndex = null;
                                                }
                                            },

                                            dropOn(index) {
                                                if (this.dragIndex === null || this.dragIndex === undefined) return;
                                                this.moveTo(this.dragIndex, index);
                                                this.dragIndex = null;
                                                this.dragOverIndex = null;
                                            },

                                            moveTo(index, target) {
                                                if (!Number.isFinite(target)) return;
                                                if (target < 0 || target >= this.blocks.length) return;
                                                if (index === target) return;
                                                const item = this.blocks.splice(index, 1)[0];
                                                this.blocks.splice(target, 0, item);
                                            },

                                            toggleCollapse(index) {
                                                const block = this.blocks[index];
                                                if (!block) return;
                                                block._collapsed = !block._collapsed;
                                            },

                                            expandAll() {
                                                this.blocks.forEach((block) => {
                                                    block._collapsed = false;
                                                });
                                            },

                                            collapseAll() {
                                                this.blocks.forEach((block) => {
                                                    block._collapsed = true;
                                                });
                                            },

                                            getPropRaw(index, path) {
                                                const block = this.blocks[index];
                                                if (!block || !block.props || typeof block.props !== 'object')
                                                    return null;

                                                const parts = String(path).split('.');
                                                let cur = block.props;

                                                for (const p of parts) {
                                                    if (
                                                        !cur ||
                                                        typeof cur !== 'object' ||
                                                        !Object.prototype.hasOwnProperty.call(cur, p)
                                                    ) {
                                                        cur = null;
                                                        break;
                                                    }
                                                    cur = cur[p];
                                                }

                                                if (cur !== null && cur !== undefined) {
                                                    return cur;
                                                }

                                                if (Object.prototype.hasOwnProperty.call(block.props, path)) {
                                                    return block.props[path];
                                                }

                                                return null;
                                            },

                                            getProp(index, path) {
                                                const raw = this.getPropRaw(index, path);
                                                if (raw === null || raw === undefined) return '';
                                                if (typeof raw === 'object') return '';
                                                return String(raw);
                                            },

                                            setProp(index, path, value) {
                                                const block = this.blocks[index];
                                                if (!block) return;
                                                if (!block.props || typeof block.props !== 'object') block.props = {};

                                                const parts = String(path).split('.');
                                                let cur = block.props;

                                                for (let i = 0; i < parts.length; i++) {
                                                    const p = parts[i];

                                                    if (i === parts.length - 1) {
                                                        cur[p] = value;
                                                        return;
                                                    }

                                                    if (!cur[p] || typeof cur[p] !== 'object') cur[p] = {};
                                                    cur = cur[p];
                                                }
                                            },

                                            propsJson(index) {
                                                const block = this.blocks[index];
                                                if (!block) return '{}';
                                                return JSON.stringify(block.props || {}, null, 2);
                                            },

                                            setPropsJson(index, value) {
                                                try {
                                                    const parsed = JSON.parse(value || '{}');
                                                    const block = this.blocks[index];
                                                    if (!block) return;
                                                    block.props = parsed && typeof parsed === 'object' ? parsed : {};
                                                    this.jsonInvalid = false;
                                                } catch {
                                                    this.jsonInvalid = true;
                                                }
                                            },

                                            safeParseBlocks(raw) {
                                                try {
                                                    const parsed = JSON.parse(raw || '[]');
                                                    if (!Array.isArray(parsed)) {
                                                        return { ok: false, blocks: [] };
                                                    }
                                                    return { ok: true, blocks: parsed };
                                                } catch {
                                                    return { ok: false, blocks: [] };
                                                }
                                            },

                                            applyAdvancedJson() {
                                                const parsed = this.safeParseBlocks(this.advancedJson);
                                                this.jsonInvalid = !parsed.ok;
                                                if (!parsed.ok) return;
                                                this.blocks = parsed.blocks.map((b) => this.decorateBlock(b));
                                            },

                                            sync() {
                                                const safe = this.blocks.map((b) => ({
                                                    type: b.type,
                                                    version: b.version,
                                                    ...(b.variant && typeof b.variant === 'string'
                                                        ? { variant: b.variant.trim() }
                                                        : {}),
                                                    props: b.props,
                                                }));
                                                this.advancedJson = JSON.stringify(safe, null, 2);
                                                if (this.$refs.hidden) {
                                                    this.$refs.hidden.value = this.advancedJson;
                                                }
                                            },
                                        }));
                                    });
                                </script>
                            </div>
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
