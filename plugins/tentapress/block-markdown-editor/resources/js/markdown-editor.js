import Editor from '@toast-ui/editor';
import '@toast-ui/editor/dist/toastui-editor.css';

const editors = new Map();

const initEditor = (group) => {
    if (!(group instanceof HTMLElement)) {
        return;
    }

    if (group.dataset.markdownInitialized === '1') {
        return;
    }

    const key = group.dataset.markdownKey || '';
    if (!key) {
        return;
    }

    const textarea = group.querySelector('[data-markdown-textarea]');
    if (!textarea) {
        return;
    }

    if (editors.has(key)) {
        group.dataset.markdownInitialized = '1';
        return;
    }

    const mount = document.createElement('div');
    mount.className = 'tp-markdown-editor';
    textarea.classList.add('hidden');
    textarea.insertAdjacentElement('afterend', mount);

    const height = group.dataset.markdownHeight || '300px';

    const editor = new Editor({
        el: mount,
        height,
        initialValue: textarea.value || '',
        initialEditType: 'wysiwyg',
        previewStyle: 'tab',
        usageStatistics: false,
        toolbarItems: [
            ['heading', 'bold', 'italic', 'strike'],
            ['hr', 'quote'],
            ['ul', 'ol', 'task', 'indent', 'outdent'],
            ['table', 'link'],
            ['code', 'codeblock'],
        ],
    });

    const syncFromEditor = () => {
        const value = editor.getMarkdown();
        if (textarea.value !== value) {
            textarea.value = value;
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
        }
    };

    editor.on('change', syncFromEditor);
    editor.on('blur', syncFromEditor);

    editors.set(key, { editor, textarea });
    group.dataset.markdownInitialized = '1';
};

const initAll = (root = document) => {
    root.querySelectorAll('[data-markdown-editor]').forEach(initEditor);
};

const handleMutations = (mutations) => {
    mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
            if (!(node instanceof HTMLElement)) {
                return;
            }

            if (node.matches('[data-markdown-editor]')) {
                initEditor(node);
            }

            if (node.querySelector) {
                initAll(node);
            }
        });
    });
};

const observer = new MutationObserver(handleMutations);

window.tpMarkdownSync = (key, value) => {
    const entry = editors.get(key);
    if (!entry) {
        return;
    }

    const { editor } = entry;
    if (editor && editor.getMarkdown() !== value) {
        editor.setMarkdown(value || '');
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initAll(document));
} else {
    initAll(document);
}

observer.observe(document.body, { childList: true, subtree: true });
