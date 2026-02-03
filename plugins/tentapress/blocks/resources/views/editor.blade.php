@php
    $blocksEditorMode = (bool) ($blocksEditorMode ?? false);
    $blockDefinitions = is_array($blockDefinitions ?? null) ? $blockDefinitions : [];
    $mediaOptions = is_array($mediaOptions ?? null) ? $mediaOptions : [];
    $mediaIndexUrl = $mediaIndexUrl ?? (\Illuminate\Support\Facades\Route::has('tp.media.index') ? route('tp.media.index') : '');
    $blocksJson = $blocksJson ?? '[]';
    $initialBlocksJson = old('blocks_json', $blocksJson);
    $initialBlocksJson = is_string($initialBlocksJson) ? $initialBlocksJson : '[]';
    $editorTitle = is_string($editorTitle ?? null) ? (string) $editorTitle : 'Blocks';
    $hasMarkdown = false;
    foreach ($blockDefinitions as $definition) {
        if (! is_array($definition)) {
            continue;
        }

        $fields = is_array($definition['fields'] ?? null) ? $definition['fields'] : [];
        foreach ($fields as $field) {
            if (is_array($field) && ($field['type'] ?? null) === 'markdown') {
                $hasMarkdown = true;
                break 2;
            }
        }
    }

    usort($blockDefinitions, static function (array $a, array $b): int {
        $aType = is_string($a['type'] ?? null) ? (string) $a['type'] : '';
        $bType = is_string($b['type'] ?? null) ? (string) $b['type'] : '';
        $aFirstParty = $aType !== '' && str_starts_with($aType, 'blocks/');
        $bFirstParty = $bType !== '' && str_starts_with($bType, 'blocks/');

        if ($aFirstParty !== $bFirstParty) {
            return $aFirstParty ? -1 : 1;
        }

        $aName = is_string($a['name'] ?? null) ? trim((string) $a['name']) : '';
        $bName = is_string($b['name'] ?? null) ? trim((string) $b['name']) : '';
        $aKey = $aName !== '' ? $aName : $aType;
        $bKey = $bName !== '' ? $bName : $bType;
        $cmp = strcasecmp($aKey, $bKey);

        if ($cmp !== 0) {
            return $cmp;
        }

        return strcasecmp($aType, $bType);
    });
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
    <label class="tp-label">{{ $editorTitle }}</label>

    @if ($hasMarkdown)
        @once
            @push('head')
                @vite(['plugins/tentapress/block-markdown-editor/resources/js/markdown-editor.js'])
            @endpush
        @endonce
    @endif

@if (isset($header))
    {{ $header }}
@endif


    <div class="{{ $blocksEditorMode ? 'grid gap-6 lg:grid-cols-[minmax(0,1fr)_280px]' : '' }}">
        <div class="{{ $blocksEditorMode ? 'space-y-4' : '' }}">
            <div
                class="{{ $blocksEditorMode ? 'space-y-6 rounded-2xl border border-slate-200 bg-slate-50/60 p-4 shadow-sm sm:p-5 lg:p-6' : 'tp-panel space-y-4' }}">
                <div
                    class="flex flex-col items-center justify-between gap-2 rounded-2xl border border-transparent pb-4 sm:flex-row sm:items-center"
                    :class="paletteDragType && addSectionOver ? 'border-dashed border-slate-300 bg-white/70 p-3' : ''"
                    @dragover.prevent="dragOverAddSection($event)"
                    @dragleave="dragLeaveAddSection($event)"
                    @drop="dropOnAddSection($event)">
                    <div class="flex flex-wrap items-center gap-2">
                        <select class="tp-select w-full sm:w-72" x-model="addType">
                            <option value="">Add a block…</option>
                            <template x-for="def in definitions" :key="def.type">
                                <option
                                    :value="def.type"
                                    x-text="def.name || def.type"></option>
                            </template>
                        </select>

                        <button
                            type="button"
                            class="{{ $blocksEditorMode ? 'tp-button-primary' : 'tp-button-secondary' }}"
                            @click="addBlock()">
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

                    <button
                        type="button"
                        class="tp-button-link"
                        @click="advanced = !advanced">
                        <span x-text="advanced ? 'Hide Advanced' : 'Advanced'"></span>
                    </button>
                </div>

                <div class="tp-notice-warning" x-show="jsonInvalid" x-cloak>
                    The blocks JSON is invalid. Fix it in Advanced mode.
                </div>

                <div class="space-y-3" x-ref="blocksList">
                    <template x-if="blocks.length === 0">
                        <div
                            class="{{ $blocksEditorMode ? 'rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-6 text-sm' : 'rounded-md border border-slate-200 bg-white p-4 text-sm' }}">
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
                        <div class="space-y-2">
                            <div
                                class="{{ $blocksEditorMode ? 'group rounded-2xl border border-slate-200 bg-white shadow-sm transition-shadow hover:border-slate-300 hover:shadow-md' : 'tp-metabox bg-zinc-50' }}"
                                :class="{
                        'opacity-60': dragIndex === index,
                        'ring-2 ring-black/10': dragOverIndex === index && dragIndex !== index,
                        'outline outline-2 outline-slate-300': selectedIndex === index,
                    }"
                                data-block-card
                                @if ($blocksEditorMode)
                                    @click="selectBlock(index)"
                                @endif
                                @dragover.prevent.stop="dragOver(index)"
                                @dragleave.stop="dragLeave(index, $event)"
                                @drop.stop="dropOn(index, $event)">
                                <div
                                    class="{{ $blocksEditorMode ? 'flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-4 py-3' : 'tp-metabox__title flex flex-wrap items-center justify-between gap-3' }}">
                                    <div class="flex min-w-0 items-center gap-3">
                                        @if ($blocksEditorMode)
                                            <div
                                                class="flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 text-xs font-semibold text-slate-500">
                                                <span x-text="index + 1"></span>
                                            </div>
                                            <button
                                                type="button"
                                                class="tp-button-link cursor-move text-slate-400"
                                                draggable="true"
                                                aria-label="Drag to reorder"
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
                                        @else
                                            <button
                                                type="button"
                                                class="tp-button-link cursor-move text-slate-400"
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
                                        @endif

                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                @if (! $blocksEditorMode)
                                                    <span class="tp-muted text-xs">
                                                        #
                                                        <span x-text="index + 1"></span>
                                                    </span>
                                                @endif

                                                <span
                                                    class="font-semibold"
                                                    x-text="titleFor(block.type)"></span>
                                                <span
                                                    class="{{ $blocksEditorMode ? 'rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-semibold tracking-[0.12em] text-slate-500 uppercase' : 'tp-muted text-xs font-normal' }}"
                                                    x-text="block.type"></span>
                                                <template x-if="block.version">
                                                    <span
                                                        class="{{ $blocksEditorMode ? 'rounded-full border border-slate-200 bg-slate-100 px-2 py-0.5 text-[10px] font-semibold tracking-[0.12em] text-slate-500 uppercase' : 'tp-muted text-xs font-normal' }}"
                                                        x-text="'v' + block.version"></span>
                                                </template>
                                                <template x-if="block.variant">
                                                    <span
                                                        class="{{ $blocksEditorMode ? 'rounded-full border border-slate-200 bg-slate-100 px-2 py-0.5 text-[10px] font-semibold tracking-[0.12em] text-slate-500 uppercase' : 'tp-muted text-xs font-normal' }}"
                                                        x-text="block.variant"></span>
                                                </template>
                                            </div>
                                            <template
                                                x-if="block._collapsed && summaryFor(block, index)">
                                                <div
                                                    class="mt-1 text-xs text-slate-500"
                                                    x-text="summaryFor(block, index)"></div>
                                            </template>
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
                                    class="{{ $blocksEditorMode ? 'space-y-4 px-5 pt-4 pb-5' : 'tp-metabox__body space-y-4' }}"
                                    x-show="!block._collapsed"
                                    x-cloak>
                                    <template x-if="variantsFor(block.type).length > 0">
                                        <div class="tp-field">
                                            <label class="tp-label">Variant</label>
                                            <select
                                                class="tp-select"
                                                :value="block.variant"
                                                @change="block.variant = String($event.target.value || '')">
                                                <template
                                                    x-for="variant in variantsFor(block.type)"
                                                    :key="variant.key">
                                                    <option
                                                        :value="String(variant.key)"
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

                                                    <template
                                                        x-if="field.type === 'markdown'">
                                                        <div
                                                            class="space-y-2"
                                                            data-markdown-editor
                                                            :data-markdown-key="block._key + ':' + field.key"
                                                            :data-markdown-height="
                                                                field.height
                                                                    ? field.height
                                                                    : field.rows
                                                                      ? field.rows * 22 + 'px'
                                                                      : ''
                                                            ">
                                                            <textarea
                                                                class="tp-textarea font-mono text-xs"
                                                                :rows="field.rows ? field.rows : 10"
                                                                :placeholder="field.placeholder || 'Write in Markdown…'"
                                                                :value="getProp(index, field.key)"
                                                                data-markdown-textarea
                                                                x-effect="markdownSync($el, block._key, field.key)"
                                                                @input="setProp(index, field.key, $event.target.value)"></textarea>
                                                        </div>
                                                    </template>

                                                    <template
                                                        x-if="field.type === 'richtext'">
                                                        <div class="space-y-2" data-richtext-group>
                                                            <div
                                                                class="flex flex-wrap items-center gap-2 text-xs">
                                                                <button
                                                                    type="button"
                                                                    class="tp-button-secondary px-2 py-1 text-xs"
                                                                    @click="richTextCommand($event, index, field.key, 'bold')">
                                                                    Bold
                                                                </button>
                                                                <button
                                                                    type="button"
                                                                    class="tp-button-secondary px-2 py-1 text-xs"
                                                                    @click="richTextCommand($event, index, field.key, 'italic')">
                                                                    Italic
                                                                </button>
                                                                <button
                                                                    type="button"
                                                                    class="tp-button-secondary px-2 py-1 text-xs"
                                                                    @click="richTextCommand($event, index, field.key, 'underline')">
                                                                    Underline
                                                                </button>
                                                                <button
                                                                    type="button"
                                                                    class="tp-button-secondary px-2 py-1 text-xs"
                                                                    @click="richTextPromptLink($event, index, field.key)">
                                                                    Link
                                                                </button>
                                                                <button
                                                                    type="button"
                                                                    class="tp-button-secondary px-2 py-1 text-xs"
                                                                    @click="richTextCommand($event, index, field.key, 'insertUnorderedList')">
                                                                    Bullets
                                                                </button>
                                                                <button
                                                                    type="button"
                                                                    class="tp-button-secondary px-2 py-1 text-xs"
                                                                    @click="richTextCommand($event, index, field.key, 'insertOrderedList')">
                                                                    Numbered
                                                                </button>
                                                                <button
                                                                    type="button"
                                                                    class="tp-button-secondary px-2 py-1 text-xs"
                                                                    @click="richTextCommand($event, index, field.key, 'removeFormat')">
                                                                    Clear
                                                                </button>
                                                            </div>
                                                            <div
                                                                class="tp-textarea min-h-[140px] leading-relaxed"
                                                                contenteditable="true"
                                                                data-richtext-editor
                                                                @focus="richTextFocus(index, field.key)"
                                                                @blur="richTextBlur(index, field.key, $event)"
                                                                @input="richTextInput(index, field.key, $event)"
                                                                x-effect="richTextSync($el, index, field.key)"></div>
                                                        </div>
                                                    </template>

                                                    <template
                                                        x-if="field.type === 'textarea'">
                                                        <textarea
                                                            class="tp-textarea"
                                                            :rows="field.rows ? field.rows : 4"
                                                            :placeholder="field.placeholder || ''"
                                                            :value="getProp(index, field.key)"
                                                            @input="setProp(index, field.key, $event.target.value)"></textarea>
                                                    </template>

                                                    <template
                                                        x-if="field.type === 'select'">
                                                        <select
                                                            class="tp-select"
                                                            @change="setProp(index, field.key, $event.target.value)">
                                                            <template
                                                                x-for="opt in selectOptions(field)"
                                                                :key="opt.value">
                                                                <option
                                                                    :value="opt.value"
                                                                    :selected="getProp(index, field.key) === opt.value"
                                                                    x-text="opt.label"></option>
                                                            </template>
                                                        </select>
                                                    </template>

                                                    <template
                                                        x-if="field.type === 'toggle'">
                                                        <label
                                                            class="flex items-center gap-2 text-sm">
                                                            <input
                                                                type="checkbox"
                                                                class="tp-checkbox"
                                                                :checked="!!getPropRaw(index, field.key)"
                                                                @change="setProp(index, field.key, $event.target.checked)" />
                                                            <span
                                                                x-text="field.toggle_label || 'Enabled'"></span>
                                                        </label>
                                                    </template>

                                                    <template
                                                        x-if="field.type === 'number'">
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

                                                    <template
                                                        x-if="field.type === 'range'">
                                                        <input
                                                            class="tp-input"
                                                            type="range"
                                                            :min="field.min !== undefined ? field.min : null"
                                                            :max="field.max !== undefined ? field.max : null"
                                                            :step="field.step !== undefined ? field.step : null"
                                                            :value="getProp(index, field.key)"
                                                            @input="setProp(index, field.key, $event.target.value)" />
                                                    </template>

                                                    <template
                                                        x-if="field.type === 'color'">
                                                        <input
                                                            class="tp-input h-10 p-1"
                                                            type="color"
                                                            :value="getProp(index, field.key)"
                                                            @input="setProp(index, field.key, $event.target.value)" />
                                                    </template>

                                                    <template
                                                        x-if="field.type === 'media'">
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
                                                                    Pick from media or paste
                                                                    a URL.
                                                                </div>
                                                            </div>

                                                            <div
                                                                class="tp-muted text-xs"
                                                                x-show="mediaOptions.length === 0">
                                                                Upload media first to select
                                                                it here.
                                                            </div>
                                                        </div>
                                                    </template>

                                                    <template
                                                        x-if="field.type === 'media-list'">
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
                                                                    One URL per line for
                                                                    advanced edits.
                                                                </div>
                                                            </div>

                                                            <div
                                                                class="tp-muted text-xs"
                                                                x-show="mediaOptions.length === 0">
                                                                Upload media first to select
                                                                it here.
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
                                                field.type !== 'color' &&
                                                field.type !== 'richtext' &&
                                                field.type !== 'markdown'
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
                                @if ($blocksEditorMode)
                                    <div class="flex items-center gap-3 px-5 py-3">
                                        <button
                                            type="button"
                                            class="group inline-flex items-center gap-2 text-xs font-semibold tracking-[0.2em] text-slate-400 uppercase hover:text-slate-600"
                                            @click="insertIndex = index; insertType = ''">
                                            <span
                                                class="flex h-6 w-6 items-center justify-center rounded-full border border-dashed border-slate-300 text-[14px] leading-none text-slate-400 transition group-hover:border-slate-400 group-hover:text-slate-600">
                                                +
                                            </span>
                                            Add block
                                        </button>
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                            x-show="insertIndex === index"
                                            x-cloak>
                                            <select
                                                class="tp-select w-full sm:w-64"
                                                x-model="insertType">
                                                <option value="">Select block…</option>
                                                <template
                                                    x-for="def in definitions"
                                                    :key="def.type">
                                                    <option
                                                        :value="def.type"
                                                        x-text="def.name || def.type"></option>
                                                </template>
                                            </select>
                                            <button
                                                type="button"
                                                class="tp-button-primary"
                                                @click="insertBlock(index)">
                                                Insert
                                            </button>
                                            <button
                                                type="button"
                                                class="tp-button-link text-slate-400"
                                                @click="insertIndex = null; insertType = ''">
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                @endif
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
                                <template
                                    x-for="opt in modalFilteredOptions()"
                                    :key="opt.value">
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
        </div>
        @if ($blocksEditorMode)
            <aside class="space-y-4 self-start lg:sticky lg:top-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div
                        class="text-xs font-semibold tracking-[0.2em] text-slate-400 uppercase">
                        Block library
                    </div>
                    <div class="mt-4 space-y-2">
                        <template x-for="def in definitions" :key="def.type">
                            <button
                                type="button"
                                class="flex w-full items-center justify-between gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-left text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                                :class="paletteDragType === def.type ? 'border-slate-300 bg-slate-50 text-slate-500' : ''"
                                draggable="true"
                                @dragstart="startPaletteDrag(def.type, $event)"
                                @dragend="endPaletteDrag()"
                                @click="addBlockType(def.type)">
                                <span
                                    class="truncate"
                                    x-text="def.name || def.type"></span>
                                <span
                                    class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-semibold tracking-[0.12em] text-slate-400 uppercase">
                                    Drag
                                </span>
                            </button>
                        </template>
                    </div>
                </div>
            </aside>
        @endif
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('tpBlocksEditor', (opts) => ({
                definitions: Array.isArray(opts.definitions) ? opts.definitions : [],
                mediaOptions: Array.isArray(opts.mediaOptions) ? opts.mediaOptions : [],
                mediaIndexUrl:
                    typeof opts.mediaIndexUrl === 'string' ? opts.mediaIndexUrl : '',
                addType: '',
                insertIndex: null,
                insertType: '',
                selectedIndex: null,
                paletteDragType: '',
                addSectionOver: false,
                richTextFocusKey: null,
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
                    this.selectedIndex = this.blocks.length ? 0 : null;

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

                uid() {
                    return (
                        'b_' + Math.random().toString(16).slice(2) + Date.now().toString(16)
                    );
                },

                defByType(type) {
                    type = String(type || '').trim();
                    if (!type) return null;
                    return this.definitions.find((d) => d && d.type === type) || null;
                },

                titleFor(type) {
                    const d = this.defByType(type);
                    return d && d.name ? d.name : type;
                },

                summaryFor(block, index) {
                    if (!block || !block.props || typeof block.props !== 'object')
                        return '';

                    const fields = this.fieldsFor(block.type);
                    if (!fields.length) return '';

                    const parts = [];
                    for (const field of fields) {
                        if (parts.length >= 2) break;
                        const raw = this.getPropRaw(index, field.key);
                        if (Array.isArray(raw)) {
                            if (raw.length > 0) {
                                parts.push(`${field.label}: ${raw.length} items`);
                            }
                            continue;
                        }
                        if (raw && typeof raw === 'object') {
                            continue;
                        }
                        let value =
                            raw === null || raw === undefined ? '' : String(raw).trim();
                        if (field.type === 'richtext') {
                            value = this.stripHtml(value);
                        }
                        if (field.type === 'markdown') {
                            value = this.stripMarkdown(value);
                        }
                        if (value !== '') {
                            parts.push(`${field.label}: ${value}`);
                        }
                    }

                    return parts.length ? parts.join(' | ') : '';
                },

                selectedBlock() {
                    if (!Number.isFinite(this.selectedIndex)) {
                        return null;
                    }
                    return this.blocks[this.selectedIndex] || null;
                },

                fieldsFor(type) {
                    const d = this.defByType(type);
                    const fields = d && Array.isArray(d.fields) ? d.fields : [];
                    // Only accept {key,label,type,help?}
                    return fields.filter((f) => f && f.key && f.label && f.type);
                },

                stripHtml(value) {
                    return String(value)
                        .replace(/<[^>]*>/g, ' ')
                        .replace(/\s+/g, ' ')
                        .trim();
                },

                stripMarkdown(value) {
                    return String(value)
                        .replace(/\[(.*?)\]\((.*?)\)/g, '$1')
                        .replace(/[`*_>#~=-]/g, ' ')
                        .replace(/\s+/g, ' ')
                        .trim();
                },

                richTextKey(index, key) {
                    return `${index}:${key}`;
                },

                richTextFocus(index, key) {
                    this.richTextFocusKey = this.richTextKey(index, key);
                },

                richTextBlur(index, key, event) {
                    this.richTextFocusKey = null;
                    this.richTextInput(index, key, event);
                },

                richTextInput(index, key, event) {
                    this.setProp(index, key, event.target.innerHTML);
                },

                richTextSync(el, index, key) {
                    if (this.richTextFocusKey === this.richTextKey(index, key)) {
                        return;
                    }

                    const value = this.getProp(index, key) || '';
                    if (el.innerHTML !== value) {
                        el.innerHTML = value;
                    }
                },

                richTextCommand(event, index, key, command, value = null) {
                    const group = event.currentTarget.closest('[data-richtext-group]');
                    if (!group) {
                        return;
                    }

                    const editor = group.querySelector('[data-richtext-editor]');
                    if (!editor) {
                        return;
                    }

                    editor.focus();
                    document.execCommand(command, false, value);
                    this.setProp(index, key, editor.innerHTML);
                },

                richTextPromptLink(event, index, key) {
                    const url = window.prompt('Enter URL');
                    if (!url) {
                        return;
                    }

                    this.richTextCommand(event, index, key, 'createLink', url);
                },

                markdownSync(el, blockKey, key) {
                    const index = this.indexForBlockKey(blockKey);
                    if (index < 0) {
                        return;
                    }

                    const value = this.getProp(index, key) || '';
                    if (el.value !== value) {
                        el.value = value;
                    }

                    if (typeof window.tpMarkdownSync === 'function') {
                        window.tpMarkdownSync(`${blockKey}:${key}`, value);
                    }
                },

                variantsFor(type) {
                    const d = this.defByType(type);
                    const variants = d && Array.isArray(d.variants) ? d.variants : [];
                    return variants.filter((v) => v && v.key);
                },

                defaultVariantFor(type) {
                    const d = this.defByType(type);
                    const variants = this.variantsFor(type);
                    if (d && d.default_variant) {
                        return String(d.default_variant);
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

                examplePropsFor(type) {
                    const d = this.defByType(type);

                    // Prefer explicit example.props
                    if (
                        d &&
                        d.example &&
                        typeof d.example === 'object' &&
                        d.example.props &&
                        typeof d.example.props === 'object'
                    ) {
                        return this.deepClone(d.example.props);
                    }

                    // Next: defaults
                    if (d && d.defaults && typeof d.defaults === 'object') {
                        return this.deepClone(d.defaults);
                    }

                    return {};
                },

                exampleBlock(type) {
                    const d = this.defByType(type);
                    const version = d && d.version ? parseInt(d.version) : 1;
                    const variant = this.defaultVariantFor(type);

                    return {
                        type,
                        version,
                        ...(variant ? { variant } : {}),
                        props: this.examplePropsFor(type),
                    };
                },

                deepClone(v) {
                    try {
                        return JSON.parse(JSON.stringify(v));
                    } catch (e) {
                        return v;
                    }
                },

                addBlock() {
                    const type = String(this.addType || '').trim();
                    if (!type) return;

                    this.blocks.push(this.decorateBlock(this.exampleBlock(type)));
                    this.addType = '';
                    this.insertIndex = null;
                    this.insertType = '';
                    this.selectedIndex = this.blocks.length - 1;
                },

                addBlockType(type) {
                    this.addType = type;
                    this.addBlock();
                },

                dragOverAddSection(event) {
                    if (!this.paletteDragType) {
                        return;
                    }
                    this.addSectionOver = true;
                },

                dragLeaveAddSection(event) {
                    if (
                        event &&
                        event.currentTarget &&
                        event.relatedTarget &&
                        event.currentTarget.contains(event.relatedTarget)
                    ) {
                        return;
                    }
                    this.addSectionOver = false;
                },

                dropOnAddSection(event) {
                    if (!this.paletteDragType) {
                        return;
                    }
                    this.insertBlockAt(this.blocks.length, this.paletteDragType);
                    this.paletteDragType = '';
                    this.addSectionOver = false;
                },

                startPaletteDrag(type, event) {
                    this.paletteDragType = String(type || '').trim();
                    if (event && event.dataTransfer) {
                        event.dataTransfer.effectAllowed = 'copy';
                        event.dataTransfer.setData(
                            'application/x-tentapress-block',
                            this.paletteDragType,
                        );
                        event.dataTransfer.setData('text/plain', this.paletteDragType);
                    }
                },

                endPaletteDrag() {
                    this.paletteDragType = '';
                    this.dragOverIndex = null;
                    this.addSectionOver = false;
                },

                draggedBlockType(event) {
                    if (!event || !event.dataTransfer) return '';
                    const byType = event.dataTransfer.getData(
                        'application/x-tentapress-block',
                    );
                    if (byType) return String(byType || '').trim();
                    const byText = event.dataTransfer.getData('text/plain');
                    return String(byText || '').trim();
                },

                insertBlockAt(index, type) {
                    const cleanType = String(type || '').trim();
                    if (!cleanType) return;

                    const nextIndex = Number.isFinite(index)
                        ? Math.min(Math.max(index, 0), this.blocks.length)
                        : this.blocks.length;

                    this.blocks.splice(
                        nextIndex,
                        0,
                        this.decorateBlock(this.exampleBlock(cleanType)),
                    );

                    this.selectedIndex = nextIndex;
                },

                selectBlock(index) {
                    if (!Number.isFinite(index)) return;
                    if (index < 0 || index >= this.blocks.length) return;
                    this.selectedIndex = index;
                },

                insertBlock(afterIndex) {
                    const type = String(this.insertType || '').trim();
                    if (!type) return;

                    const insertAt = Number.isFinite(afterIndex)
                        ? Math.min(afterIndex + 1, this.blocks.length)
                        : this.blocks.length;
                    const clampedIndex = Math.max(insertAt, 0);

                    this.blocks.splice(
                        clampedIndex,
                        0,
                        this.decorateBlock(this.exampleBlock(type)),
                    );

                    this.insertIndex = null;
                    this.insertType = '';
                    this.selectedIndex = clampedIndex;
                },

                duplicateBlock(index) {
                    if (index < 0 || index >= this.blocks.length) return;

                    const copy = this.deepClone(this.blocks[index]);
                    copy._key = this.uid();
                    copy._collapsed = false;

                    // Ensure canonical keys exist
                    copy.type = String(copy.type || '').trim();
                    if (!copy.type) copy.type = 'unknown';

                    if (!copy.version) {
                        const def = this.defByType(copy.type);
                        copy.version = def && def.version ? parseInt(def.version) : 1;
                    }

                    if (!copy.props || typeof copy.props !== 'object') copy.props = {};

                    this.blocks.splice(index + 1, 0, this.decorateBlock(copy));
                    this.selectedIndex = index + 1;
                },

                toggleCollapse(index) {
                    if (index < 0 || index >= this.blocks.length) return;
                    this.blocks[index]._collapsed = !this.blocks[index]._collapsed;
                },

                move(index, delta) {
                    const next = index + delta;
                    if (next < 0 || next >= this.blocks.length) return;

                    const tmp = this.blocks[index];
                    this.blocks[index] = this.blocks[next];
                    this.blocks[next] = tmp;

                    if (this.selectedIndex === index) {
                        this.selectedIndex = next;
                    } else if (this.selectedIndex === next) {
                        this.selectedIndex = index;
                    }
                },

                dragStart(index, event) {
                    this.dragIndex = index;
                    if (Number.isFinite(index)) {
                        this.selectedIndex = index;
                    }
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
                    if (this.dragOverIndex !== index) {
                        this.dragOverIndex = index;
                    }
                },

                dragLeave(index, event) {
                    return;
                },

                dropOn(index, event) {
                    const paletteType = this.draggedBlockType(event);
                    if (paletteType) {
                        this.insertBlockAt(index, paletteType);
                        this.paletteDragType = '';
                        this.dragOverIndex = null;
                        return;
                    }
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

                    if (!Number.isFinite(this.selectedIndex)) {
                        return;
                    }
                    if (this.selectedIndex === index) {
                        this.selectedIndex = target;
                        return;
                    }
                    if (
                        index < target &&
                        this.selectedIndex > index &&
                        this.selectedIndex <= target
                    ) {
                        this.selectedIndex -= 1;
                        return;
                    }
                    if (
                        index > target &&
                        this.selectedIndex < index &&
                        this.selectedIndex >= target
                    ) {
                        this.selectedIndex += 1;
                    }
                },

                remove(index) {
                    this.blocks.splice(index, 1);

                    if (!Number.isFinite(this.selectedIndex)) {
                        this.selectedIndex = this.blocks.length ? 0 : null;
                        return;
                    }

                    if (this.selectedIndex === index) {
                        this.selectedIndex = this.blocks.length
                            ? Math.min(index, this.blocks.length - 1)
                            : null;
                    } else if (this.selectedIndex > index) {
                        this.selectedIndex -= 1;
                    }
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
                    if (!block || !block.props) return null;

                    const parts = String(path).split('.');
                    let cur = block.props;

                    for (const p of parts) {
                        if (
                            !cur ||
                            typeof cur !== 'object' ||
                            !Object.prototype.hasOwnProperty.call(cur, p)
                        ) {
                            return null;
                        }
                        cur = cur[p];
                    }

                    return cur;
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
                    const props =
                        block && block.props && typeof block.props === 'object'
                            ? block.props
                            : {};
                    try {
                        return JSON.stringify(props, null, 2);
                    } catch (e) {
                        return '{}';
                    }
                },

                setPropsJson(index, text) {
                    try {
                        const parsed = JSON.parse(text);
                        if (parsed && typeof parsed === 'object') {
                            this.blocks[index].props = parsed;
                            this.jsonInvalid = false;
                            // Re-apply defaults after direct edit
                            const def = this.defByType(this.blocks[index].type);
                            if (def && def.defaults && typeof def.defaults === 'object') {
                                this.blocks[index].props = this.mergeDefaults(
                                    def.defaults,
                                    this.blocks[index].props,
                                );
                            }
                        }
                    } catch (e) {
                        this.jsonInvalid = true;
                    }
                },

                safeParseBlocks(text) {
                    try {
                        const v = JSON.parse(text);
                        if (!Array.isArray(v)) return { ok: false, blocks: [] };
                        return { ok: true, blocks: v };
                    } catch (e) {
                        return { ok: false, blocks: [] };
                    }
                },

                canonicalBlocks() {
                    return this.blocks
                        .map((b) => ({
                            type: String(b.type || '').trim(),
                            version: Number.isFinite(parseInt(b.version))
                                ? parseInt(b.version)
                                : 1,
                            ...(b.variant && typeof b.variant === 'string'
                                ? { variant: b.variant.trim() }
                                : {}),
                            props: b.props && typeof b.props === 'object' ? b.props : {},
                        }))
                        .filter((b) => b.type !== '');
                },

                sync() {
                    const json = JSON.stringify(this.canonicalBlocks(), null, 2);

                    this.$refs.hidden.value = json;
                    this.advancedJson = json;
                },

                applyAdvancedJson() {
                    const parsed = this.safeParseBlocks(this.advancedJson);
                    this.jsonInvalid = !parsed.ok;

                    if (!parsed.ok) return;

                    this.blocks = parsed.blocks.map((b) => this.decorateBlock(b));
                    if (!this.blocks.length) {
                        this.selectedIndex = null;
                    } else if (
                        !Number.isFinite(this.selectedIndex) ||
                        this.selectedIndex >= this.blocks.length
                    ) {
                        this.selectedIndex = 0;
                    }
                    this.sync();
                },

                indexForBlockKey(blockKey) {
                    if (!blockKey) {
                        return -1;
                    }

                    return this.blocks.findIndex((block) => block?._key === blockKey);
                },
            }));
        });
    </script>
</div>
