import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { BUILTIN_PATTERNS } from '../patterns';
import type { BlockDefinition, BuilderBlock, BuilderConfig, PatternDefinition } from '../types';

const PRESENTATION_DEFAULT = {
    container: 'default',
    align: 'left',
    background: 'none',
    spacing: {
        top: 'none',
        bottom: 'none',
    },
};

function deepClone<T>(value: T): T {
    return JSON.parse(JSON.stringify(value)) as T;
}

function uid(): string {
    return `b_${Math.random().toString(16).slice(2)}${Date.now().toString(16)}`;
}

export const useBuilderEditorStore = defineStore('builder-editor', () => {
    const config = ref<BuilderConfig | null>(null);
    const blocks = ref<BuilderBlock[]>([]);
    const selectedIndex = ref<number>(-1);
    const search = ref('');
    const dirty = ref(false);

    const history = ref<string[]>([]);
    const historyIndex = ref(-1);

    const definitions = computed(() => config.value?.definitions ?? []);

    const selectedBlock = computed(() => {
        if (selectedIndex.value < 0 || selectedIndex.value >= blocks.value.length) {
            return null;
        }

        return blocks.value[selectedIndex.value] ?? null;
    });

    const selectedDefinition = computed(() => {
        const block = selectedBlock.value;
        if (!block) {
            return null;
        }

        return definitionFor(block.type);
    });

    const filteredDefinitions = computed(() => {
        const term = search.value.trim().toLowerCase();

        if (term === '') {
            return definitions.value;
        }

        return definitions.value.filter((definition) => {
            const haystack = `${definition.name} ${definition.type} ${definition.description}`.toLowerCase();
            return haystack.includes(term);
        });
    });

    const patterns = computed<PatternDefinition[]>(() => BUILTIN_PATTERNS);

    function parseInitialBlocks(raw: string): BuilderBlock[] {
        let parsed: unknown = [];

        try {
            parsed = JSON.parse(raw);
        } catch {
            parsed = [];
        }

        if (!Array.isArray(parsed)) {
            return [];
        }

        return parsed
            .map((item) => normalizeBlock(item))
            .filter((item): item is BuilderBlock => item !== null);
    }

    function normalizeBlock(input: unknown): BuilderBlock | null {
        if (!input || typeof input !== 'object') {
            return null;
        }

        const source = input as Record<string, unknown>;
        const type = String(source.type ?? '').trim();

        if (type === '') {
            return null;
        }

        const definition = definitionFor(type);
        const version = Number.isFinite(Number(source.version))
            ? Number(source.version)
            : Number(definition?.version ?? 1);

        const props = source.props && typeof source.props === 'object'
            ? deepClone(source.props as Record<string, unknown>)
            : {};

        if (definition?.defaults && typeof definition.defaults === 'object') {
            for (const [key, value] of Object.entries(definition.defaults)) {
                if (!(key in props)) {
                    props[key] = deepClone(value);
                }
            }
        }

        return {
            type,
            version: version > 0 ? version : 1,
            variant: typeof source.variant === 'string' && source.variant.trim() !== '' ? source.variant.trim() : undefined,
            props,
            _key: uid(),
        };
    }

    function serializeBlocks(): string {
        const canonical = blocks.value.map((block) => ({
            type: block.type,
            version: block.version,
            ...(block.variant ? { variant: block.variant } : {}),
            props: deepClone(block.props),
        }));

        return JSON.stringify(canonical, null, 2);
    }

    function syncHidden(): void {
        if (!config.value) {
            return;
        }

        const element = document.getElementById(config.value.hiddenFieldId);
        if (element instanceof HTMLTextAreaElement) {
            element.value = serializeBlocks();
        }
    }

    function persistDraft(): void {
        if (!config.value) {
            return;
        }

        const payload = serializeBlocks();
        window.localStorage.setItem(config.value.storageKey, payload);
        syncHidden();
        dirty.value = true;
    }

    function pushHistory(): void {
        const snapshot = serializeBlocks();

        if (historyIndex.value >= 0 && history.value[historyIndex.value] === snapshot) {
            return;
        }

        const next = history.value.slice(0, historyIndex.value + 1);
        next.push(snapshot);

        history.value = next;
        historyIndex.value = next.length - 1;
    }

    function restoreSnapshot(snapshot: string): void {
        blocks.value = parseInitialBlocks(snapshot);

        if (selectedIndex.value >= blocks.value.length) {
            selectedIndex.value = blocks.value.length > 0 ? blocks.value.length - 1 : -1;
        }

        syncHidden();
    }

    function definitionFor(type: string): BlockDefinition | null {
        return definitions.value.find((definition) => definition.type === type) ?? null;
    }

    function blockForType(type: string): BuilderBlock {
        const definition = definitionFor(type);
        const exampleProps = definition?.example?.props && typeof definition.example.props === 'object'
            ? deepClone(definition.example.props)
            : {};

        if (definition?.defaults && typeof definition.defaults === 'object') {
            for (const [key, value] of Object.entries(definition.defaults)) {
                if (!(key in exampleProps)) {
                    exampleProps[key] = deepClone(value);
                }
            }
        }

        return {
            type,
            version: Number(definition?.version ?? 1),
            props: exampleProps,
            _key: uid(),
        };
    }

    function updateBlock(index: number, updater: (block: BuilderBlock) => void): void {
        const block = blocks.value[index];
        if (!block) {
            return;
        }

        updater(block);
        pushHistory();
        persistDraft();
    }

    function init(next: BuilderConfig): void {
        config.value = next;
        const restored = window.localStorage.getItem(next.storageKey);
        const initial = restored && restored.trim() !== '' ? restored : next.initialJson;

        blocks.value = parseInitialBlocks(initial);
        selectedIndex.value = blocks.value.length > 0 ? 0 : -1;

        syncHidden();
        pushHistory();
        dirty.value = false;
    }

    function select(index: number): void {
        if (index < 0 || index >= blocks.value.length) {
            return;
        }

        selectedIndex.value = index;
    }

    function addBlock(type: string): void {
        const block = blockForType(type);
        blocks.value.push(block);
        selectedIndex.value = blocks.value.length - 1;
        pushHistory();
        persistDraft();
    }

    function insertPattern(pattern: PatternDefinition): void {
        const injected = pattern.blocks
            .map((block) => normalizeBlock(block))
            .filter((block): block is BuilderBlock => block !== null)
            .map((block) => ({ ...block, _key: uid() }));

        if (injected.length === 0) {
            return;
        }

        blocks.value.push(...injected);
        selectedIndex.value = blocks.value.length - injected.length;
        pushHistory();
        persistDraft();
    }

    function duplicate(index: number): void {
        const block = blocks.value[index];
        if (!block) {
            return;
        }

        const copy = deepClone(block);
        copy._key = uid();
        blocks.value.splice(index + 1, 0, copy);
        selectedIndex.value = index + 1;
        pushHistory();
        persistDraft();
    }

    function remove(index: number): void {
        if (index < 0 || index >= blocks.value.length) {
            return;
        }

        blocks.value.splice(index, 1);
        if (blocks.value.length === 0) {
            selectedIndex.value = -1;
        } else if (selectedIndex.value >= blocks.value.length) {
            selectedIndex.value = blocks.value.length - 1;
        }

        pushHistory();
        persistDraft();
    }

    function move(index: number, target: number): void {
        if (index < 0 || target < 0 || index >= blocks.value.length || target >= blocks.value.length || index === target) {
            return;
        }

        const [item] = blocks.value.splice(index, 1);
        blocks.value.splice(target, 0, item);
        selectedIndex.value = target;

        pushHistory();
        persistDraft();
    }

    function replaceBlockWithMany(index: number, incoming: unknown[]): void {
        if (index < 0 || index >= blocks.value.length || !Array.isArray(incoming)) {
            return;
        }

        const replacements = incoming
            .map((block) => normalizeBlock(block))
            .filter((block): block is BuilderBlock => block !== null)
            .map((block) => ({ ...block, _key: uid() }));

        if (replacements.length === 0) {
            return;
        }

        blocks.value.splice(index, 1, ...replacements);
        selectedIndex.value = index;
        pushHistory();
        persistDraft();
    }

    function setBlockProp(index: number, key: string, value: unknown): void {
        updateBlock(index, (block) => {
            block.props[key] = value;
        });
    }

    function setPresentation(index: number, key: 'container' | 'align' | 'background', value: string): void {
        updateBlock(index, (block) => {
            const current = block.props.presentation && typeof block.props.presentation === 'object'
                ? (block.props.presentation as Record<string, unknown>)
                : deepClone(PRESENTATION_DEFAULT);

            current[key] = value;
            block.props.presentation = current;
        });
    }

    function setPresentationSpacing(index: number, key: 'top' | 'bottom', value: string): void {
        updateBlock(index, (block) => {
            const current = block.props.presentation && typeof block.props.presentation === 'object'
                ? (block.props.presentation as Record<string, unknown>)
                : deepClone(PRESENTATION_DEFAULT);

            const spacing = current.spacing && typeof current.spacing === 'object'
                ? (current.spacing as Record<string, unknown>)
                : { top: 'none', bottom: 'none' };

            spacing[key] = value;
            current.spacing = spacing;
            block.props.presentation = current;
        });
    }

    function presentation(index: number): Record<string, unknown> {
        const block = blocks.value[index];
        if (!block || !block.props.presentation || typeof block.props.presentation !== 'object') {
            return deepClone(PRESENTATION_DEFAULT);
        }

        const current = deepClone(block.props.presentation as Record<string, unknown>);

        if (!current.spacing || typeof current.spacing !== 'object') {
            current.spacing = { top: 'none', bottom: 'none' };
        }

        return {
            ...deepClone(PRESENTATION_DEFAULT),
            ...current,
            spacing: {
                ...(deepClone(PRESENTATION_DEFAULT).spacing as Record<string, unknown>),
                ...(current.spacing as Record<string, unknown>),
            },
        };
    }

    function undo(): void {
        if (historyIndex.value <= 0) {
            return;
        }

        historyIndex.value -= 1;
        const snapshot = history.value[historyIndex.value] ?? '[]';
        restoreSnapshot(snapshot);
        persistDraft();
    }

    function redo(): void {
        if (historyIndex.value >= history.value.length - 1) {
            return;
        }

        historyIndex.value += 1;
        const snapshot = history.value[historyIndex.value] ?? '[]';
        restoreSnapshot(snapshot);
        persistDraft();
    }

    function clearDraft(): void {
        if (!config.value) {
            return;
        }

        window.localStorage.removeItem(config.value.storageKey);
        dirty.value = false;
    }

    return {
        blocks,
        selectedIndex,
        selectedBlock,
        selectedDefinition,
        filteredDefinitions,
        patterns,
        search,
        dirty,
        historyIndex,
        history,
        init,
        definitionFor,
        serializeBlocks,
        select,
        addBlock,
        insertPattern,
        duplicate,
        remove,
        move,
        replaceBlockWithMany,
        setBlockProp,
        setPresentation,
        setPresentationSpacing,
        presentation,
        undo,
        redo,
        clearDraft,
        persistDraft,
        syncHidden,
        config,
    };
});
