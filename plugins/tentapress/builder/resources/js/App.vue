<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useBuilderEditorStore } from './stores/editor';
import type { BuilderConfig, BuilderPreviewDocument } from './types';

const props = defineProps<{ config: BuilderConfig }>();

const store = useBuilderEditorStore();

const dragIndex = ref<number | null>(null);
const previewLoading = ref(false);
const previewError = ref('');
const previewHost = ref<HTMLDivElement | null>(null);
const previewRevision = ref('');
let previewShadowRoot: ShadowRoot | null = null;
let previewTimer: number | null = null;

const hasSelection = computed(() => store.selectedIndex >= 0 && store.selectedIndex < store.blocks.length);
const previewMode = computed<'fragment' | 'iframe'>(() =>
    props.config.previewMode === 'iframe' ? 'iframe' : 'fragment',
);
const selectedPresentation = computed(() => {
    if (!hasSelection.value) {
        return {
            container: 'default',
            align: 'left',
            background: 'none',
            spacing: { top: 'none', bottom: 'none' },
        };
    }

    return store.presentation(store.selectedIndex);
});
const spacingTop = computed(() => String((selectedPresentation.value.spacing as { top?: string }).top ?? 'none'));
const spacingBottom = computed(() => String((selectedPresentation.value.spacing as { bottom?: string }).bottom ?? 'none'));

const resourceLabel = computed(() => (props.config.resource === 'pages' ? 'page' : 'post'));

function blockSummary(index: number): string {
    const block = store.blocks[index];
    if (!block) {
        return '';
    }

    for (const value of Object.values(block.props)) {
        if (typeof value === 'string' && value.trim() !== '') {
            return value.length > 90 ? `${value.slice(0, 90)}...` : value;
        }
    }

    return 'No content yet';
}

function onDragStart(index: number, event: DragEvent): void {
    dragIndex.value = index;
    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
    }
}

function onDrop(index: number): void {
    if (dragIndex.value === null) {
        return;
    }

    store.move(dragIndex.value, index);
    dragIndex.value = null;
}

function submitForm(): void {
    const form = document.getElementById(props.config.resource === 'pages' ? 'page-form' : 'post-form') as HTMLFormElement | null;
    form?.requestSubmit();
}

function inputValue(event: Event): string {
    const target = event.target;
    if (target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement || target instanceof HTMLSelectElement) {
        return String(target.value ?? '');
    }

    return '';
}

function checkboxValue(event: Event): boolean {
    const target = event.target;
    if (target instanceof HTMLInputElement) {
        return !!target.checked;
    }

    return false;
}

function updateField(key: string, event: Event): void {
    if (!hasSelection.value) {
        return;
    }

    store.setBlockProp(store.selectedIndex, key, inputValue(event));
}

function updateToggleField(key: string, event: Event): void {
    if (!hasSelection.value) {
        return;
    }

    store.setBlockProp(store.selectedIndex, key, checkboxValue(event));
}

function updatePresentation(key: 'container' | 'align' | 'background', event: Event): void {
    if (!hasSelection.value) {
        return;
    }

    store.setPresentation(store.selectedIndex, key, inputValue(event));
}

function updatePresentationSpacing(key: 'top' | 'bottom', event: Event): void {
    if (!hasSelection.value) {
        return;
    }

    store.setPresentationSpacing(store.selectedIndex, key, inputValue(event));
}

function getMetaValue(name: string, fallback = ''): string {
    const form = document.getElementById(props.config.resource === 'pages' ? 'page-form' : 'post-form') as HTMLFormElement | null;
    const input = form?.querySelector(`[name="${name}"]`) as HTMLInputElement | HTMLSelectElement | null;

    if (!input) {
        return fallback;
    }

    return String(input.value ?? fallback);
}

function resolveStyleHref(href: string): string {
    try {
        return new URL(href, window.location.origin).toString();
    } catch {
        return href;
    }
}

function ensurePreviewShadowRoot(): ShadowRoot | null {
    if (!previewHost.value) {
        return null;
    }

    if (!previewShadowRoot) {
        previewShadowRoot = previewHost.value.attachShadow({ mode: 'open' });
    }

    return previewShadowRoot;
}

function applyPreviewSelection(): void {
    if (previewMode.value !== 'fragment' || !previewShadowRoot) {
        return;
    }

    const allMarkers = Array.from(previewShadowRoot.querySelectorAll('[data-tp-builder-block-index]'));
    for (const marker of allMarkers) {
        if (!(marker instanceof HTMLElement)) {
            continue;
        }

        marker.removeAttribute('data-tp-builder-selected');
    }

    if (!hasSelection.value) {
        return;
    }

    const selected = previewShadowRoot.querySelector(
        `[data-tp-builder-block-index="${store.selectedIndex}"]`,
    );
    if (selected instanceof HTMLElement) {
        selected.setAttribute('data-tp-builder-selected', '1');
    }
}

function onPreviewClick(event: MouseEvent): void {
    const target = event.target;
    if (!(target instanceof HTMLElement)) {
        return;
    }

    const anchor = target.closest('a');
    if (anchor instanceof HTMLAnchorElement) {
        event.preventDefault();
    }

    const marker = target.closest('[data-tp-builder-block-index]');
    if (!(marker instanceof HTMLElement)) {
        return;
    }

    event.preventDefault();

    const rawIndex = marker.getAttribute('data-tp-builder-block-index') ?? '';
    const index = Number.parseInt(rawIndex, 10);
    if (Number.isNaN(index)) {
        return;
    }

    store.select(index);
}

function applyPreviewDocument(payload: BuilderPreviewDocument): void {
    const shadowRoot = ensurePreviewShadowRoot();
    if (!shadowRoot) {
        return;
    }

    shadowRoot.innerHTML = '';

    const uiStyle = document.createElement('style');
    uiStyle.textContent = `
        :host { display: block; min-height: 100%; color: inherit; }
        .tp-builder-preview-document { min-height: 100%; background: #fff; color: #0f172a; }
        .tp-builder-preview-document [data-tp-builder-block-index] {
            cursor: pointer;
            border-radius: 0.5rem;
            transition: box-shadow 120ms ease-in-out;
        }
        .tp-builder-preview-document [data-tp-builder-block-index][data-tp-builder-selected="1"] {
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.65);
        }
    `;
    shadowRoot.appendChild(uiStyle);

    for (const styleDef of payload.styles ?? []) {
        const href = typeof styleDef?.href === 'string' ? styleDef.href.trim() : '';
        if (href === '') {
            continue;
        }

        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = resolveStyleHref(href);
        link.media = typeof styleDef?.media === 'string' && styleDef.media.trim() !== '' ? styleDef.media : 'all';
        shadowRoot.appendChild(link);
    }

    for (const cssText of payload.inline_styles ?? []) {
        if (typeof cssText !== 'string' || cssText.trim() === '') {
            continue;
        }

        const style = document.createElement('style');
        style.textContent = cssText;
        shadowRoot.appendChild(style);
    }

    const documentRoot = document.createElement('div');
    documentRoot.className = `tp-builder-preview-document ${payload.body_class ?? ''}`.trim();
    documentRoot.innerHTML = payload.body_html ?? '';
    documentRoot.addEventListener('click', onPreviewClick);
    shadowRoot.appendChild(documentRoot);

    applyPreviewSelection();
}

async function refreshPreview(): Promise<void> {
    if (!props.config.snapshotEndpoint) {
        return;
    }

    previewError.value = '';
    previewLoading.value = true;

    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

        const response = await fetch(props.config.snapshotEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({
                resource: props.config.resource,
                title: getMetaValue('title', ''),
                slug: getMetaValue('slug', ''),
                layout: getMetaValue('layout', 'default'),
                blocks: JSON.parse(store.serializeBlocks()),
            }),
        });

        if (!response.ok) {
            throw new Error('Preview request failed.');
        }

        const snapshot = (await response.json()) as { document_url?: string; preview_url?: string };

        if (previewMode.value === 'iframe') {
            if (!snapshot.preview_url) {
                throw new Error('Preview URL missing.');
            }

            store.setPreviewUrl(snapshot.preview_url);
            previewRevision.value = '';

            return;
        }

        if (!snapshot.document_url) {
            throw new Error('Preview document URL missing.');
        }

        const documentResponse = await fetch(snapshot.document_url, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!documentResponse.ok) {
            throw new Error('Preview document request failed.');
        }

        const payload = (await documentResponse.json()) as BuilderPreviewDocument;
        if (typeof payload.revision !== 'string' || payload.revision.trim() === '') {
            throw new Error('Preview document revision missing.');
        }

        if (payload.revision === previewRevision.value) {
            return;
        }

        previewRevision.value = payload.revision;
        applyPreviewDocument(payload);
    } catch (error) {
        previewError.value = 'Preview unavailable. Save and refresh to retry.';
    } finally {
        previewLoading.value = false;
    }
}

function schedulePreview(): void {
    if (previewTimer !== null) {
        window.clearTimeout(previewTimer);
    }

    previewTimer = window.setTimeout(() => {
        refreshPreview();
    }, 350);
}

function onKeydown(event: KeyboardEvent): void {
    const key = event.key.toLowerCase();
    if ((event.metaKey || event.ctrlKey) && key === 's') {
        event.preventDefault();
        submitForm();
        return;
    }

    if (!hasSelection.value) {
        return;
    }

    if ((event.metaKey || event.ctrlKey) && key === 'd') {
        event.preventDefault();
        store.duplicate(store.selectedIndex);
        return;
    }

    if (key === 'backspace' || key === 'delete') {
        const target = event.target as HTMLElement | null;
        if (target && (target.closest('input') || target.closest('textarea') || target.isContentEditable)) {
            return;
        }

        event.preventDefault();
        store.remove(store.selectedIndex);
    }
}

onMounted(() => {
    store.init(props.config);
    window.addEventListener('keydown', onKeydown);
    const form = document.getElementById(props.config.resource === 'pages' ? 'page-form' : 'post-form') as HTMLFormElement | null;
    form?.addEventListener('submit', () => {
        store.clearDraft();
    });
    schedulePreview();
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKeydown);
    if (previewTimer !== null) {
        window.clearTimeout(previewTimer);
    }
    previewShadowRoot = null;
});

watch(
    () => store.serializeBlocks(),
    () => {
        store.persistDraft();
        schedulePreview();
    },
);

watch(
    () => store.selectedIndex,
    () => {
        applyPreviewSelection();
    },
);
</script>

<template>
    <div class="tp-builder">
        <aside class="tp-builder__panel tp-builder__panel--inspector">
            <div class="tp-builder__panel-title">Block configuration</div>

            <template v-if="hasSelection && store.selectedBlock && store.selectedDefinition">
                <div class="tp-builder__selected-meta">
                    <div class="tp-builder__selected-title">{{ store.selectedDefinition.name || store.selectedBlock.type }}</div>
                    <div class="tp-builder__selected-type">{{ store.selectedBlock.type }}</div>
                </div>

                <div class="tp-builder__selected-actions">
                    <button type="button" class="tp-button-secondary" @click="store.duplicate(store.selectedIndex)">Duplicate</button>
                    <button type="button" class="tp-button-secondary text-red-600" @click="store.remove(store.selectedIndex)">
                        Delete
                    </button>
                </div>

                <div class="tp-builder__field-group">
                    <label
                        v-for="field in store.selectedDefinition.fields"
                        :key="field.key"
                        class="tp-builder__field">
                        <span class="tp-builder__label">{{ field.label }}</span>

                        <input
                            v-if="['text', 'url', 'color', 'number', 'range'].includes(field.type)"
                            class="tp-input"
                            :type="field.type === 'range' ? 'range' : field.type === 'number' ? 'number' : field.type === 'color' ? 'color' : field.type === 'url' ? 'url' : 'text'"
                            :value="String(store.selectedBlock.props[field.key] ?? '')"
                            @input="updateField(field.key, $event)" />

                        <textarea
                            v-else-if="['textarea', 'richtext', 'markdown', 'embed', 'repeater', 'nested-blocks', 'media-list'].includes(field.type)"
                            class="tp-textarea"
                            rows="4"
                            :value="String(store.selectedBlock.props[field.key] ?? '')"
                            @input="updateField(field.key, $event)" />

                        <select
                            v-else-if="field.type === 'select'"
                            class="tp-select"
                            :value="String(store.selectedBlock.props[field.key] ?? '')"
                            @change="updateField(field.key, $event)">
                            <option
                                v-for="option in field.options || []"
                                :key="typeof option === 'string' ? option : option.value"
                                :value="typeof option === 'string' ? option : option.value">
                                {{ typeof option === 'string' ? option : option.label }}
                            </option>
                        </select>

                        <label v-else-if="field.type === 'toggle'" class="tp-builder__inline-toggle">
                            <input
                                type="checkbox"
                                :checked="Boolean(store.selectedBlock.props[field.key])"
                                @change="updateToggleField(field.key, $event)" />
                            <span>Enabled</span>
                        </label>

                        <input
                            v-else
                            class="tp-input"
                            type="text"
                            :value="String(store.selectedBlock.props[field.key] ?? '')"
                            @input="updateField(field.key, $event)" />

                        <small v-if="field.help" class="tp-builder__help">{{ field.help }}</small>
                    </label>
                </div>

                <div class="tp-builder__panel-title tp-builder__panel-title--spaced">Presentation</div>
                <div class="tp-builder__field-group">
                    <label class="tp-builder__field">
                        <span class="tp-builder__label">Container width</span>
                        <select
                            class="tp-select"
                            :value="String(selectedPresentation.container)"
                            @change="updatePresentation('container', $event)">
                            <option value="default">Default</option>
                            <option value="wide">Wide</option>
                            <option value="full">Full</option>
                        </select>
                    </label>

                    <label class="tp-builder__field">
                        <span class="tp-builder__label">Text alignment</span>
                        <select
                            class="tp-select"
                            :value="String(selectedPresentation.align)"
                            @change="updatePresentation('align', $event)">
                            <option value="left">Left</option>
                            <option value="center">Center</option>
                            <option value="right">Right</option>
                        </select>
                    </label>

                    <label class="tp-builder__field">
                        <span class="tp-builder__label">Background</span>
                        <select
                            class="tp-select"
                            :value="String(selectedPresentation.background)"
                            @change="updatePresentation('background', $event)">
                            <option value="none">None</option>
                            <option value="muted">Muted</option>
                            <option value="brand">Brand</option>
                        </select>
                    </label>

                    <label class="tp-builder__field">
                        <span class="tp-builder__label">Top spacing</span>
                        <select
                            class="tp-select"
                            :value="spacingTop"
                            @change="updatePresentationSpacing('top', $event)">
                            <option value="none">None</option>
                            <option value="xs">XS</option>
                            <option value="sm">SM</option>
                            <option value="md">MD</option>
                            <option value="lg">LG</option>
                            <option value="xl">XL</option>
                        </select>
                    </label>

                    <label class="tp-builder__field">
                        <span class="tp-builder__label">Bottom spacing</span>
                        <select
                            class="tp-select"
                            :value="spacingBottom"
                            @change="updatePresentationSpacing('bottom', $event)">
                            <option value="none">None</option>
                            <option value="xs">XS</option>
                            <option value="sm">SM</option>
                            <option value="md">MD</option>
                            <option value="lg">LG</option>
                            <option value="xl">XL</option>
                        </select>
                    </label>
                </div>
            </template>

            <div v-else class="tp-builder__empty tp-builder__empty--small">Select a block from the structure panel to configure it.</div>
        </aside>

        <section class="tp-builder__canvas tp-builder__canvas--preview">
            <div class="tp-builder__canvas-toolbar">
                <div class="tp-builder__canvas-title">Live {{ resourceLabel }} preview</div>
                <div class="tp-builder__toolbar-actions">
                    <button type="button" class="tp-button-secondary" :disabled="store.historyIndex <= 0" @click="store.undo()">
                        Undo
                    </button>
                    <button
                        type="button"
                        class="tp-button-secondary"
                        :disabled="store.historyIndex >= store.history.length - 1"
                        @click="store.redo()">
                        Redo
                    </button>
                    <button type="button" class="tp-button-primary" @click="submitForm()">Save</button>
                </div>
            </div>

            <div class="tp-builder__preview-state" v-if="previewLoading">Updating preview...</div>
            <div class="tp-builder__preview-state tp-builder__preview-state--error" v-if="previewError">{{ previewError }}</div>
            <template v-if="previewMode === 'iframe'">
                <iframe v-if="store.previewUrl" class="tp-builder__preview tp-builder__preview--center" :src="store.previewUrl" title="Builder preview"></iframe>
                <div v-else class="tp-builder__empty">
                    Preview is loading. Add blocks from the right panel to render sections into the page template.
                </div>
            </template>
            <div
                v-else
                ref="previewHost"
                class="tp-builder__preview tp-builder__preview--center"
                role="region"
                aria-label="Builder preview"></div>
        </section>

        <aside class="tp-builder__panel tp-builder__panel--library">
            <div class="tp-builder__panel-title">Block library</div>
            <input v-model="store.search" type="search" class="tp-builder__search" placeholder="Search blocks..." />
            <div class="tp-builder__list">
                <button
                    v-for="definition in store.filteredDefinitions"
                    :key="definition.type"
                    type="button"
                    class="tp-builder__library-item"
                    @click="store.addBlock(definition.type)">
                    <span class="tp-builder__library-title">{{ definition.name || definition.type }}</span>
                    <span class="tp-builder__library-meta">{{ definition.type }}</span>
                </button>
            </div>

            <div class="tp-builder__panel-title tp-builder__panel-title--spaced">Patterns</div>
            <div class="tp-builder__list">
                <button
                    v-for="pattern in store.patterns"
                    :key="pattern.id"
                    type="button"
                    class="tp-builder__library-item"
                    @click="store.insertPattern(pattern)">
                    <span class="tp-builder__library-title">{{ pattern.name }}</span>
                    <span class="tp-builder__library-meta">{{ pattern.description }}</span>
                </button>
            </div>

            <div class="tp-builder__panel-title tp-builder__panel-title--spaced">Page structure</div>
            <div v-if="store.blocks.length === 0" class="tp-builder__empty tp-builder__empty--small">
                Add a block to begin building this {{ resourceLabel }}.
            </div>
            <div class="tp-builder__canvas-list">
                <article
                    v-for="(block, index) in store.blocks"
                    :key="block._key"
                    class="tp-builder__card"
                    :class="{ 'is-selected': store.selectedIndex === index }"
                    draggable="true"
                    @click="store.select(index)"
                    @dragstart="onDragStart(index, $event)"
                    @dragover.prevent
                    @drop.prevent="onDrop(index)">
                    <header class="tp-builder__card-header">
                        <div>
                            <div class="tp-builder__card-title">{{ store.definitionFor(block.type)?.name || block.type }}</div>
                            <div class="tp-builder__card-meta">{{ block.type }}</div>
                        </div>
                        <div class="tp-builder__card-actions">
                            <button type="button" class="tp-button-link" @click.stop="store.duplicate(index)">Duplicate</button>
                            <button type="button" class="tp-button-link text-red-600" @click.stop="store.remove(index)">Delete</button>
                        </div>
                    </header>
                    <p class="tp-builder__card-summary">{{ blockSummary(index) }}</p>
                </article>
            </div>
        </aside>
    </div>
</template>
