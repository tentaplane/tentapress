<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useBuilderEditorStore } from './stores/editor';
import type { BlockField, BuilderConfig, BuilderPreviewDocument, MediaOption, PatternDefinition } from './types';

const props = defineProps<{ config: BuilderConfig }>();

const store = useBuilderEditorStore();

const dragIndex = ref<number | null>(null);
const previewLoading = ref(false);
const previewError = ref('');
const previewFrame = ref<HTMLIFrameElement | null>(null);
const previewRevision = ref('');
const previewViewport = ref<'desktop' | 'tablet' | 'mobile'>('desktop');
const blockLibraryOpen = ref(false);
const leftPanelWidth = ref(360);
const rightPanelWidth = ref(250);
const isResizing = ref<'left' | 'right' | null>(null);
const resizeStartX = ref(0);
const resizeStartWidth = ref(0);
let previewTimer: number | null = null;
const mediaModalOpen = ref(false);
const mediaModalSearch = ref('');
const mediaModalMode = ref<'single' | 'multi'>('single');
const mediaModalFieldKey = ref('');
const mediaModalSelection = ref<Record<string, boolean>>({});

const LAYOUT_STORAGE_KEY = 'tp.builder.layout.v1';
const LEFT_MIN_WIDTH = 320;
const LEFT_MAX_WIDTH = 520;
const RIGHT_MIN_WIDTH = 220;
const RIGHT_MAX_WIDTH = 420;

const hasSelection = computed(() => store.selectedIndex >= 0 && store.selectedIndex < store.blocks.length);
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
const mediaOptions = computed<MediaOption[]>(() => (Array.isArray(props.config.mediaOptions) ? props.config.mediaOptions : []));

const layoutStyle = computed<Record<string, string>>(() => ({
    '--tp-builder-left': `${leftPanelWidth.value}px`,
    '--tp-builder-right': `${rightPanelWidth.value}px`,
}));

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

function clampWidth(value: number, min: number, max: number): number {
    return Math.max(min, Math.min(max, value));
}

function loadLayoutPreference(): void {
    const raw = window.localStorage.getItem(LAYOUT_STORAGE_KEY);
    if (!raw) {
        return;
    }

    try {
        const parsed = JSON.parse(raw) as { left?: number; right?: number };
        if (typeof parsed.left === 'number') {
            leftPanelWidth.value = clampWidth(parsed.left, LEFT_MIN_WIDTH, LEFT_MAX_WIDTH);
        }

        if (typeof parsed.right === 'number') {
            rightPanelWidth.value = clampWidth(parsed.right, RIGHT_MIN_WIDTH, RIGHT_MAX_WIDTH);
        }
    } catch {
        return;
    }
}

function storeLayoutPreference(): void {
    window.localStorage.setItem(
        LAYOUT_STORAGE_KEY,
        JSON.stringify({
            left: leftPanelWidth.value,
            right: rightPanelWidth.value,
        }),
    );
}

const filteredMediaOptions = computed<MediaOption[]>(() => {
    const query = mediaModalSearch.value.trim().toLowerCase();
    if (query === '') {
        return mediaOptions.value;
    }

    return mediaOptions.value.filter((option) => {
        const label = String(option.label ?? option.original_name ?? '').toLowerCase();
        const value = String(option.value ?? '').toLowerCase();
        return label.includes(query) || value.includes(query);
    });
});

function onResizeMove(event: PointerEvent): void {
    if (isResizing.value === null) {
        return;
    }

    const delta = event.clientX - resizeStartX.value;
    if (isResizing.value === 'left') {
        leftPanelWidth.value = clampWidth(resizeStartWidth.value + delta, LEFT_MIN_WIDTH, LEFT_MAX_WIDTH);
        return;
    }

    rightPanelWidth.value = clampWidth(resizeStartWidth.value - delta, RIGHT_MIN_WIDTH, RIGHT_MAX_WIDTH);
}

function onResizeEnd(): void {
    if (isResizing.value === null) {
        return;
    }

    isResizing.value = null;
    window.removeEventListener('pointermove', onResizeMove);
    window.removeEventListener('pointerup', onResizeEnd);
    storeLayoutPreference();
}

function startResize(side: 'left' | 'right', event: PointerEvent): void {
    if (window.matchMedia('(max-width: 1279px)').matches) {
        return;
    }

    event.preventDefault();
    isResizing.value = side;
    resizeStartX.value = event.clientX;
    resizeStartWidth.value = side === 'left' ? leftPanelWidth.value : rightPanelWidth.value;
    window.addEventListener('pointermove', onResizeMove);
    window.addEventListener('pointerup', onResizeEnd);
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

function repeaterColumns(field: BlockField): BlockField[] {
    if (!Array.isArray(field.columns)) {
        return [];
    }

    return field.columns.filter(
        (column) => typeof column?.key === 'string' && column.key.trim() !== '',
    );
}

function normalizeRepeaterRow(field: BlockField, row: unknown): Record<string, unknown> {
    const columns = repeaterColumns(field);
    const source = row && typeof row === 'object' ? (row as Record<string, unknown>) : {};
    const normalized: Record<string, unknown> = {};

    for (const column of columns) {
        const key = String(column.key ?? '').trim();
        if (key === '') {
            continue;
        }

        const value = source[key];
        if (column.type === 'toggle') {
            normalized[key] = value === true || value === '1' || value === 1 || String(value ?? '').toLowerCase() === 'true';
            continue;
        }

        normalized[key] = value === null || value === undefined ? '' : String(value);
    }

    return normalized;
}

function parseRepeaterLines(field: BlockField, value: string): Array<Record<string, unknown>> {
    const rows: Array<Record<string, unknown>> = [];
    const columns = repeaterColumns(field);
    const lines = value.split(/\r?\n/);

    for (const line of lines) {
        const trimmed = String(line).trim();
        if (trimmed === '') {
            continue;
        }

        const parts = trimmed.split('|').map((part) => part.trim());
        const row: Record<string, unknown> = {};
        columns.forEach((column, index) => {
            const key = String(column.key ?? '').trim();
            if (key === '') {
                return;
            }

            row[key] = parts[index] ?? '';
        });
        rows.push(normalizeRepeaterRow(field, row));
    }

    return rows;
}

function repeaterRows(fieldKey: string, field: BlockField): Array<Record<string, unknown>> {
    if (!hasSelection.value || !store.selectedBlock) {
        return [];
    }

    const raw = store.selectedBlock.props[fieldKey];
    if (Array.isArray(raw)) {
        return raw.map((row) => normalizeRepeaterRow(field, row));
    }

    if (typeof raw === 'string') {
        const trimmed = raw.trim();
        if (trimmed === '') {
            return [];
        }

        try {
            const parsed = JSON.parse(trimmed);
            if (Array.isArray(parsed)) {
                return parsed.map((row) => normalizeRepeaterRow(field, row));
            }
        } catch {
            return parseRepeaterLines(field, trimmed);
        }

        return parseRepeaterLines(field, trimmed);
    }

    return [];
}

function setRepeaterRows(fieldKey: string, rows: Array<Record<string, unknown>>): void {
    if (!hasSelection.value) {
        return;
    }

    store.setBlockProp(store.selectedIndex, fieldKey, rows);
}

function addRepeaterRow(fieldKey: string, field: BlockField): void {
    const rows = repeaterRows(fieldKey, field);
    rows.push(normalizeRepeaterRow(field, {}));
    setRepeaterRows(fieldKey, rows);
}

function removeRepeaterRow(fieldKey: string, field: BlockField, rowIndex: number): void {
    const rows = repeaterRows(fieldKey, field);
    if (rowIndex < 0 || rowIndex >= rows.length) {
        return;
    }

    rows.splice(rowIndex, 1);
    setRepeaterRows(fieldKey, rows);
}

function duplicateRepeaterRow(fieldKey: string, field: BlockField, rowIndex: number): void {
    const rows = repeaterRows(fieldKey, field);
    if (rowIndex < 0 || rowIndex >= rows.length) {
        return;
    }

    rows.splice(rowIndex + 1, 0, normalizeRepeaterRow(field, rows[rowIndex]));
    setRepeaterRows(fieldKey, rows);
}

function moveRepeaterRow(fieldKey: string, field: BlockField, rowIndex: number, delta: number): void {
    const rows = repeaterRows(fieldKey, field);
    const targetIndex = rowIndex + delta;
    if (rowIndex < 0 || rowIndex >= rows.length || targetIndex < 0 || targetIndex >= rows.length) {
        return;
    }

    const [row] = rows.splice(rowIndex, 1);
    rows.splice(targetIndex, 0, row);
    setRepeaterRows(fieldKey, rows);
}

function updateRepeaterColumn(fieldKey: string, field: BlockField, rowIndex: number, column: BlockField, event: Event): void {
    const rows = repeaterRows(fieldKey, field);
    if (rowIndex < 0 || rowIndex >= rows.length) {
        return;
    }

    const key = String(column.key ?? '').trim();
    if (key === '') {
        return;
    }

    const next = normalizeRepeaterRow(field, rows[rowIndex]);
    next[key] = column.type === 'toggle' ? checkboxValue(event) : inputValue(event);
    rows[rowIndex] = next;
    setRepeaterRows(fieldKey, rows);
}

function repeaterColumnTextValue(row: Record<string, unknown>, column: BlockField): string {
    const key = String(column.key ?? '').trim();
    if (key === '') {
        return '';
    }

    const value = row[key];
    return value === null || value === undefined ? '' : String(value);
}

function repeaterColumnToggleValue(row: Record<string, unknown>, column: BlockField): boolean {
    const key = String(column.key ?? '').trim();
    if (key === '') {
        return false;
    }

    const value = row[key];
    return value === true || value === '1' || value === 1 || String(value ?? '').toLowerCase() === 'true';
}

function repeaterRowSummary(row: Record<string, unknown>, field: BlockField, rowIndex: number): string {
    const pieces: string[] = [];
    for (const column of repeaterColumns(field)) {
        const value = repeaterColumnTextValue(row, column).trim();
        if (value !== '') {
            pieces.push(value);
        }
        if (pieces.length >= 2) {
            break;
        }
    }

    if (pieces.length === 0) {
        return `Row ${rowIndex + 1}`;
    }

    return pieces.join(' - ');
}

function mediaOption(value: unknown): MediaOption | null {
    const key = String(value ?? '').trim();
    if (key === '') {
        return null;
    }

    const found = mediaOptions.value.find((option) => String(option.value ?? '').trim() === key);
    if (found) {
        return found;
    }

    return {
        id: 0,
        value: key,
        label: key.split('/').pop() || key,
    };
}

function mediaLabel(value: unknown): string {
    const option = mediaOption(value);
    if (!option) {
        return '';
    }

    return option.label || option.original_name || option.value || '';
}

function isMediaImage(value: unknown): boolean {
    const option = mediaOption(value);
    if (!option) {
        return false;
    }

    if (option.is_image !== undefined) {
        return !!option.is_image;
    }

    return /\.(png|jpe?g|gif|webp|svg)$/i.test(String(option.value ?? ''));
}

function mediaFieldValue(fieldKey: string): string {
    if (!hasSelection.value || !store.selectedBlock) {
        return '';
    }

    return String(store.selectedBlock.props[fieldKey] ?? '');
}

function clearMediaField(fieldKey: string): void {
    if (!hasSelection.value) {
        return;
    }

    store.setBlockProp(store.selectedIndex, fieldKey, '');
}

function mediaListValue(fieldKey: string): string[] {
    if (!hasSelection.value || !store.selectedBlock) {
        return [];
    }

    const raw = store.selectedBlock.props[fieldKey];
    if (Array.isArray(raw)) {
        return raw.map((item) => String(item ?? '').trim()).filter((item) => item !== '');
    }

    if (typeof raw === 'string') {
        return raw
            .split(/[\n,]/)
            .map((item) => item.trim())
            .filter((item) => item !== '');
    }

    return [];
}

function setMediaListValue(fieldKey: string, values: string[]): void {
    if (!hasSelection.value) {
        return;
    }

    const normalized = values.map((item) => String(item ?? '').trim()).filter((item) => item !== '');
    store.setBlockProp(store.selectedIndex, fieldKey, normalized);
}

function removeMediaListItem(fieldKey: string, index: number): void {
    const next = mediaListValue(fieldKey);
    if (index < 0 || index >= next.length) {
        return;
    }

    next.splice(index, 1);
    setMediaListValue(fieldKey, next);
}

function openMediaModal(fieldKey: string, mode: 'single' | 'multi' = 'single'): void {
    if (!hasSelection.value) {
        return;
    }

    mediaModalFieldKey.value = fieldKey;
    mediaModalMode.value = mode;
    mediaModalSearch.value = '';
    if (mode === 'multi') {
        mediaModalSelection.value = Object.fromEntries(mediaListValue(fieldKey).map((value) => [value, true]));
    } else {
        mediaModalSelection.value = {};
    }

    mediaModalOpen.value = true;
}

function closeMediaModal(): void {
    mediaModalOpen.value = false;
    mediaModalSearch.value = '';
}

function modalToggleSelection(value: string): void {
    const key = String(value ?? '').trim();
    if (key === '') {
        return;
    }

    const next = { ...mediaModalSelection.value };
    if (next[key]) {
        delete next[key];
    } else {
        next[key] = true;
    }
    mediaModalSelection.value = next;
}

function modalIsSelected(value: string): boolean {
    return !!mediaModalSelection.value[String(value ?? '').trim()];
}

function modalSelectSingle(value: string): void {
    if (!hasSelection.value || mediaModalFieldKey.value.trim() === '') {
        return;
    }

    store.setBlockProp(store.selectedIndex, mediaModalFieldKey.value.trim(), value);
    closeMediaModal();
}

function modalApplyMulti(): void {
    if (!hasSelection.value || mediaModalFieldKey.value.trim() === '') {
        return;
    }

    const selected = mediaOptions.value
        .map((option) => String(option.value ?? '').trim())
        .filter((value) => value !== '' && !!mediaModalSelection.value[value]);
    setMediaListValue(mediaModalFieldKey.value.trim(), selected);
    closeMediaModal();
}

function insertBlockFromLibrary(type: string): void {
    store.addBlock(type);
    blockLibraryOpen.value = false;
}

function insertPatternFromLibrary(pattern: PatternDefinition): void {
    store.insertPattern(pattern);
    blockLibraryOpen.value = false;
}

function onDocumentClick(event: MouseEvent): void {
    const target = event.target;
    if (!(target instanceof HTMLElement)) {
        return;
    }

    if (!target.closest('[data-tp-builder-library]')) {
        blockLibraryOpen.value = false;
    }
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

function applyPreviewSelection(): void {
    const frameDocument = previewFrame.value?.contentDocument ?? null;
    if (!frameDocument) {
        return;
    }

    const allMarkers = Array.from(frameDocument.querySelectorAll('[data-tp-builder-block-index]'));
    for (const marker of allMarkers) {
        if (!(marker instanceof HTMLElement)) {
            continue;
        }

        marker.removeAttribute('data-tp-builder-selected');
    }

    if (!hasSelection.value) {
        return;
    }

    const selected = frameDocument.querySelector(
        `[data-tp-builder-block-index="${store.selectedIndex}"]`,
    );
    if (selected instanceof HTMLElement) {
        selected.setAttribute('data-tp-builder-selected', '1');
    }
}

function onPreviewClick(event: Event): void {
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
    if (!previewFrame.value) {
        return;
    }

    const styleLinks = (payload.styles ?? [])
        .map((styleDef) => {
            const href = typeof styleDef?.href === 'string' ? styleDef.href.trim() : '';
            if (href === '') {
                return '';
            }

            const media = typeof styleDef?.media === 'string' && styleDef.media.trim() !== '' ? styleDef.media : 'all';
            return `<link rel="stylesheet" href="${resolveStyleHref(href)}" media="${media}">`;
        })
        .join('');
    const inlineStyles = (payload.inline_styles ?? [])
        .filter((cssText) => typeof cssText === 'string' && cssText.trim() !== '')
        .map((cssText) => `<style>${cssText}</style>`)
        .join('');
    const uiStyle = `<style>
        html, body { margin: 0; min-height: 100%; }
        body { overflow-x: hidden; }
        .tp-builder-preview-document { min-height: 100%; background: #fff; color: #0f172a; overflow-x: hidden; box-sizing: border-box; }
        .tp-builder-preview-document * { box-sizing: border-box; }
        .tp-builder-preview-document img, .tp-builder-preview-document video, .tp-builder-preview-document svg { max-width: 100%; }
        .tp-builder-preview-document .w-screen,
        .tp-builder-preview-document [class*="w-screen"] { width: 100% !important; max-width: 100% !important; }
        .tp-builder-preview-document [data-tp-builder-block-index] {
            cursor: pointer;
            border-radius: 0.5rem;
            transition: box-shadow 120ms ease-in-out;
        }
        .tp-builder-preview-document [data-tp-builder-block-index][data-tp-builder-selected="1"] {
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.65);
        }
    </style>`;
    const bodyClass = String(payload.body_class ?? '').trim();
    const bodyHtml = String(payload.body_html ?? '');

    previewFrame.value.srcdoc = `<!doctype html>
<html lang="${String(payload.lang ?? 'en')}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        ${styleLinks}
        ${inlineStyles}
        ${uiStyle}
    </head>
    <body class="${bodyClass}">
        <div class="tp-builder-preview-document">${bodyHtml}</div>
    </body>
</html>`;
    previewFrame.value.onload = () => {
        const frameDocument = previewFrame.value?.contentDocument;
        if (!frameDocument) {
            return;
        }

        frameDocument.addEventListener('click', onPreviewClick);
        applyPreviewSelection();
    };
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

        const snapshot = (await response.json()) as { document_url?: string };

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
    loadLayoutPreference();
    window.addEventListener('keydown', onKeydown);
    document.addEventListener('click', onDocumentClick);
    const form = document.getElementById(props.config.resource === 'pages' ? 'page-form' : 'post-form') as HTMLFormElement | null;
    form?.addEventListener('submit', () => {
        store.clearDraft();
    });
    schedulePreview();
});

onBeforeUnmount(() => {
    onResizeEnd();
    window.removeEventListener('keydown', onKeydown);
    document.removeEventListener('click', onDocumentClick);
    if (previewTimer !== null) {
        window.clearTimeout(previewTimer);
    }
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
    <div class="tp-builder" :style="layoutStyle">
        <aside class="tp-builder__panel tp-builder__panel--inspector">
            <div class="tp-builder__panel-title">Block configuration</div>

            <template v-if="hasSelection && store.selectedBlock && store.selectedDefinition">
                <div class="tp-builder__selected-meta">
                    <div class="tp-builder__selected-title">{{ store.selectedDefinition.name || store.selectedBlock.type }}</div>
                    <div class="tp-builder__selected-type">{{ store.selectedBlock.type }}</div>
                </div>

                <div class="tp-builder__selected-actions">
                    <button
                        type="button"
                        class="tp-builder__icon-button"
                        title="Duplicate block"
                        aria-label="Duplicate block"
                        @click="store.duplicate(store.selectedIndex)">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        class="tp-builder__icon-button tp-builder__icon-button--danger"
                        title="Delete block"
                        aria-label="Delete block"
                        @click="store.remove(store.selectedIndex)">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                    </button>
                </div>

                <div class="tp-builder__field-group">
                    <div
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
                            v-else-if="['textarea', 'richtext', 'markdown', 'embed', 'nested-blocks'].includes(field.type)"
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

                        <div v-else-if="field.type === 'media'" class="tp-builder__media-field">
                            <button
                                type="button"
                                class="tp-button-secondary"
                                @click="openMediaModal(field.key, 'single')">
                                {{ mediaFieldValue(field.key) === '' ? 'Select media' : 'Replace media' }}
                            </button>

                            <div class="tp-builder__media-actions">
                                <button
                                    type="button"
                                    class="tp-builder__icon-button tp-builder__icon-button--danger"
                                    :disabled="mediaFieldValue(field.key) === ''"
                                    title="Clear media"
                                    aria-label="Clear media"
                                    @click="clearMediaField(field.key)">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>

                            <div
                                v-if="mediaFieldValue(field.key) !== ''"
                                class="tp-builder__media-preview">
                                <img
                                    v-if="isMediaImage(mediaFieldValue(field.key))"
                                    :src="mediaFieldValue(field.key)"
                                    :alt="mediaLabel(mediaFieldValue(field.key))" />
                                <a
                                    :href="mediaFieldValue(field.key)"
                                    target="_blank"
                                    rel="noreferrer"
                                    class="tp-builder__media-link">
                                    {{ mediaLabel(mediaFieldValue(field.key)) || mediaFieldValue(field.key) }}
                                </a>
                            </div>
                        </div>

                        <div v-else-if="field.type === 'media-list'" class="tp-builder__media-list-field">
                            <button
                                type="button"
                                class="tp-button-secondary"
                                @click="openMediaModal(field.key, 'multi')">
                                Manage media list
                            </button>

                            <div
                                v-if="mediaListValue(field.key).length === 0"
                                class="tp-builder__empty tp-builder__empty--inline">
                                No media selected.
                            </div>
                            <div v-else class="tp-builder__media-list">
                                <article
                                    v-for="(item, mediaIndex) in mediaListValue(field.key)"
                                    :key="`${field.key}:${item}:${mediaIndex}`"
                                    class="tp-builder__media-list-item">
                                    <img
                                        v-if="isMediaImage(item)"
                                        :src="item"
                                        :alt="mediaLabel(item)"
                                        class="tp-builder__media-thumb" />
                                    <div class="tp-builder__media-item-meta">
                                        <div class="tp-builder__media-item-title">{{ mediaLabel(item) || item }}</div>
                                        <a :href="item" target="_blank" rel="noreferrer" class="tp-builder__media-link">{{ item }}</a>
                                    </div>
                                    <button
                                        type="button"
                                        class="tp-builder__icon-button tp-builder__icon-button--danger"
                                        title="Remove media item"
                                        aria-label="Remove media item"
                                        @click="removeMediaListItem(field.key, mediaIndex)">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </article>
                            </div>
                        </div>

                        <div v-else-if="field.type === 'repeater'" class="tp-builder__repeater-field">
                            <div class="tp-builder__repeater-toolbar">
                                <button
                                    type="button"
                                    class="tp-builder__icon-button"
                                    title="Add row"
                                    aria-label="Add row"
                                    @click="addRepeaterRow(field.key, field)">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                                    </svg>
                                </button>
                            </div>

                            <div
                                v-if="repeaterRows(field.key, field).length === 0"
                                class="tp-builder__empty tp-builder__empty--inline">
                                No rows yet.
                            </div>

                            <article
                                v-for="(row, rowIndex) in repeaterRows(field.key, field)"
                                :key="`${field.key}:${rowIndex}`"
                                class="tp-builder__repeater-row">
                                <header class="tp-builder__repeater-row-header">
                                    <div class="tp-builder__repeater-row-title">{{ repeaterRowSummary(row, field, rowIndex) }}</div>
                                    <div class="tp-builder__repeater-row-actions">
                                        <button
                                            type="button"
                                            class="tp-builder__icon-button"
                                            :disabled="rowIndex === 0"
                                            title="Move row up"
                                            aria-label="Move row up"
                                            @click="moveRepeaterRow(field.key, field, rowIndex, -1)">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18" />
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            class="tp-builder__icon-button"
                                            :disabled="rowIndex === repeaterRows(field.key, field).length - 1"
                                            title="Move row down"
                                            aria-label="Move row down"
                                            @click="moveRepeaterRow(field.key, field, rowIndex, 1)">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            class="tp-builder__icon-button"
                                            title="Duplicate row"
                                            aria-label="Duplicate row"
                                            @click="duplicateRepeaterRow(field.key, field, rowIndex)">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            class="tp-builder__icon-button tp-builder__icon-button--danger"
                                            title="Remove row"
                                            aria-label="Remove row"
                                            @click="removeRepeaterRow(field.key, field, rowIndex)">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </header>

                                <div class="tp-builder__repeater-grid">
                                    <label
                                        v-for="column in repeaterColumns(field)"
                                        :key="`${field.key}:${rowIndex}:${column.key}`"
                                        class="tp-builder__field">
                                        <span class="tp-builder__label">{{ column.label }}</span>

                                        <input
                                            v-if="['text', 'url', 'color', 'number', 'range'].includes(column.type)"
                                            class="tp-input"
                                            :type="column.type === 'range' ? 'range' : column.type === 'number' ? 'number' : column.type === 'color' ? 'color' : column.type === 'url' ? 'url' : 'text'"
                                            :value="repeaterColumnTextValue(row, column)"
                                            @input="updateRepeaterColumn(field.key, field, rowIndex, column, $event)" />

                                        <textarea
                                            v-else-if="['textarea', 'richtext', 'markdown', 'embed'].includes(column.type)"
                                            class="tp-textarea"
                                            rows="3"
                                            :value="repeaterColumnTextValue(row, column)"
                                            @input="updateRepeaterColumn(field.key, field, rowIndex, column, $event)" />

                                        <select
                                            v-else-if="column.type === 'select'"
                                            class="tp-select"
                                            :value="repeaterColumnTextValue(row, column)"
                                            @change="updateRepeaterColumn(field.key, field, rowIndex, column, $event)">
                                            <option
                                                v-for="option in column.options || []"
                                                :key="typeof option === 'string' ? option : option.value"
                                                :value="typeof option === 'string' ? option : option.value">
                                                {{ typeof option === 'string' ? option : option.label }}
                                            </option>
                                        </select>

                                        <label v-else-if="column.type === 'toggle'" class="tp-builder__inline-toggle">
                                            <input
                                                type="checkbox"
                                                :checked="repeaterColumnToggleValue(row, column)"
                                                @change="updateRepeaterColumn(field.key, field, rowIndex, column, $event)" />
                                            <span>Enabled</span>
                                        </label>

                                        <input
                                            v-else
                                            class="tp-input"
                                            type="text"
                                            :value="repeaterColumnTextValue(row, column)"
                                            @input="updateRepeaterColumn(field.key, field, rowIndex, column, $event)" />
                                    </label>
                                </div>
                            </article>
                        </div>

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
                    </div>
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

        <div class="tp-builder__resizer" aria-hidden="true" @pointerdown="startResize('left', $event)"></div>

        <section class="tp-builder__canvas tp-builder__canvas--preview">
            <div class="tp-builder__canvas-toolbar">
                <div class="tp-builder__toolbar-left">
                    <div class="tp-builder__canvas-title">Live {{ resourceLabel }} preview</div>
                    <div class="tp-builder__library-dropdown" data-tp-builder-library>
                        <button
                            type="button"
                            class="tp-button-secondary tp-builder__library-trigger"
                            @click.stop="blockLibraryOpen = !blockLibraryOpen">
                            Add block
                        </button>
                        <div v-if="blockLibraryOpen" class="tp-builder__library-menu">
                            <input v-model="store.search" type="search" class="tp-builder__search" placeholder="Search blocks..." />
                            <div class="tp-builder__list">
                                <button
                                    v-for="definition in store.filteredDefinitions"
                                    :key="definition.type"
                                    type="button"
                                    class="tp-builder__library-item"
                                    @click="insertBlockFromLibrary(definition.type)">
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
                                    @click="insertPatternFromLibrary(pattern)">
                                    <span class="tp-builder__library-title">{{ pattern.name }}</span>
                                    <span class="tp-builder__library-meta">{{ pattern.description }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tp-builder__toolbar-groups">
                    <div class="tp-builder__viewport-switch" role="group" aria-label="Preview viewport">
                        <button
                            type="button"
                            class="tp-builder__icon-button"
                            :class="{ 'is-active': previewViewport === 'mobile' }"
                            title="Mobile viewport"
                            aria-label="Mobile viewport"
                            @click="previewViewport = 'mobile'">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 2.25h3A2.25 2.25 0 0 1 15.75 4.5v15A2.25 2.25 0 0 1 13.5 21.75h-3a2.25 2.25 0 0 1-2.25-2.25v-15A2.25 2.25 0 0 1 10.5 2.25Zm0 14.25h3" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            class="tp-builder__icon-button"
                            :class="{ 'is-active': previewViewport === 'tablet' }"
                            title="Tablet viewport"
                            aria-label="Tablet viewport"
                            @click="previewViewport = 'tablet'">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3.75h10.5c.621 0 1.125.504 1.125 1.125v14.25c0 .621-.504 1.125-1.125 1.125H6.75a1.125 1.125 0 0 1-1.125-1.125V4.875c0-.621.504-1.125 1.125-1.125Z" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            class="tp-builder__icon-button"
                            :class="{ 'is-active': previewViewport === 'desktop' }"
                            title="Desktop viewport"
                            aria-label="Desktop viewport"
                            @click="previewViewport = 'desktop'">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25A2.25 2.25 0 0 1 6 3h12a2.25 2.25 0 0 1 2.25 2.25v9A2.25 2.25 0 0 1 18 16.5H6a2.25 2.25 0 0 1-2.25-2.25v-9Zm6.75 15h3m-4.5 0h6" />
                            </svg>
                        </button>
                    </div>

                    <div class="tp-builder__toolbar-actions">
                    <button
                        type="button"
                        class="tp-builder__icon-button"
                        :disabled="store.historyIndex <= 0"
                        title="Undo"
                        aria-label="Undo"
                        @click="store.undo()">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        class="tp-builder__icon-button"
                        :disabled="store.historyIndex >= store.history.length - 1"
                        title="Redo"
                        aria-label="Redo"
                        @click="store.redo()">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        class="tp-builder__icon-button"
                        title="Save"
                        aria-label="Save"
                        @click="submitForm()">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3H5.25A2.25 2.25 0 0 0 3 5.25v13.5A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V9M9 3v6h6V3M9 3h6m0 0 6 6" />
                        </svg>
                    </button>
                </div>
                </div>
            </div>

            <div class="tp-builder__preview-state" v-if="previewLoading">Updating preview...</div>
            <div class="tp-builder__preview-state tp-builder__preview-state--error" v-if="previewError">{{ previewError }}</div>
            <div class="tp-builder__preview-stage">
                <div class="tp-builder__preview-viewport" :class="`is-${previewViewport}`">
                    <iframe
                        ref="previewFrame"
                        class="tp-builder__preview tp-builder__preview--center"
                        role="region"
                        title="Builder preview"></iframe>
                </div>
            </div>
        </section>

        <div class="tp-builder__resizer" aria-hidden="true" @pointerdown="startResize('right', $event)"></div>

        <aside class="tp-builder__panel tp-builder__panel--library">
            <div class="tp-builder__panel-title">Page structure</div>
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
                            <button
                                type="button"
                                class="tp-builder__icon-button"
                                title="Duplicate block"
                                aria-label="Duplicate block"
                                @click.stop="store.duplicate(index)">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                                </svg>
                            </button>
                            <button
                                type="button"
                                class="tp-builder__icon-button tp-builder__icon-button--danger"
                                title="Delete block"
                                aria-label="Delete block"
                                @click.stop="store.remove(index)">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </div>
                    </header>
                    <p class="tp-builder__card-summary">{{ blockSummary(index) }}</p>
                </article>
            </div>
        </aside>

        <div v-if="mediaModalOpen" class="tp-builder__media-modal-backdrop" @click.self="closeMediaModal">
            <div class="tp-builder__media-modal" role="dialog" aria-modal="true" aria-label="Media library">
                <header class="tp-builder__media-modal-header">
                    <h3 class="tp-builder__media-modal-title">Media library</h3>
                    <button type="button" class="tp-button-secondary" @click="closeMediaModal">Close</button>
                </header>

                <input v-model="mediaModalSearch" type="search" class="tp-builder__search" placeholder="Search media..." />

                <div class="tp-builder__media-modal-list">
                    <button
                        v-for="option in filteredMediaOptions"
                        :key="option.value"
                        type="button"
                        class="tp-builder__media-modal-item"
                        :class="{ 'is-selected': modalIsSelected(option.value) }"
                        @click="mediaModalMode === 'multi' ? modalToggleSelection(option.value) : modalSelectSingle(option.value)">
                        <span class="tp-builder__media-modal-label">{{ option.label || option.original_name || option.value }}</span>
                        <span class="tp-builder__media-modal-value">{{ option.value }}</span>
                    </button>
                    <div v-if="filteredMediaOptions.length === 0" class="tp-builder__empty tp-builder__empty--inline">
                        No media matched your search.
                    </div>
                </div>

                <footer v-if="mediaModalMode === 'multi'" class="tp-builder__media-modal-footer">
                    <button type="button" class="tp-button-secondary" @click="mediaModalSelection = {}">Clear selection</button>
                    <button type="button" class="tp-button-primary" @click="modalApplyMulti()">Apply selection</button>
                </footer>
            </div>
        </div>
    </div>
</template>
