import EditorJS from '@editorjs/editorjs';
import Header from '@editorjs/header';
import List from '@editorjs/list';
import Quote from '@editorjs/quote';
import Paragraph from '@editorjs/paragraph';
import InlineCode from '@editorjs/inline-code';
import Delimiter from '@editorjs/delimiter';

const imageToolIcon =
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"></rect><circle cx="8.5" cy="10" r="1.5"></circle><path d="M21 16l-5-5-4 4-2-2-5 5"></path></svg>';
const embedToolIcon =
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 9L4 12l4 3"></path><path d="M16 9l4 3-4 3"></path><path d="M10 19l4-14"></path></svg>';
const checklistToolIcon =
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l2 2 4-4"></path><rect x="3" y="4" width="18" height="16" rx="2"></rect></svg>';
const codeToolIcon =
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 9l-4 3 4 3"></path><path d="M16 9l4 3-4 3"></path><path d="M14 5l-4 14"></path></svg>';
const calloutToolIcon =
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l8 6v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9l8-6z"></path><path d="M12 10v4"></path><circle cx="12" cy="17" r="1"></circle></svg>';

const resolveEmbed = (url) => {
    if (!url) {
        return null;
    }
    const trimmed = url.trim();
    const youtubeMatch =
        trimmed.match(
            /(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([^#&?/]+)/,
        );
    if (youtubeMatch && youtubeMatch[1]) {
        return {
            service: 'youtube',
            embed: `https://www.youtube.com/embed/${youtubeMatch[1]}`,
        };
    }

    const vimeoMatch = trimmed.match(/(?:https?:\/\/)?(?:www\.)?vimeo\.com\/(\d+)/);
    if (vimeoMatch && vimeoMatch[1]) {
        return {
            service: 'vimeo',
            embed: `https://player.vimeo.com/video/${vimeoMatch[1]}`,
        };
    }

    return null;
};

class SimpleEmbedTool {
    constructor({ data }) {
        this.data = {
            source: typeof data?.source === 'string' ? data.source : '',
            service: typeof data?.service === 'string' ? data.service : '',
            embed: typeof data?.embed === 'string' ? data.embed : '',
            caption: typeof data?.caption === 'string' ? data.caption : '',
        };
        this.wrapper = null;
        this.error = '';
    }

    static get toolbox() {
        return {
            title: 'Embed',
            icon: embedToolIcon,
        };
    }

    static get sanitize() {
        return {
            source: true,
            service: true,
            embed: true,
            caption: {
                br: true,
                b: true,
                strong: true,
                i: true,
                em: true,
                u: true,
                s: true,
                a: {
                    href: true,
                    rel: true,
                    target: true,
                },
            },
        };
    }

    render() {
        this.wrapper = document.createElement('div');
        this.wrapper.classList.add('tp-page-editor__embed');
        this.renderContent();
        return this.wrapper;
    }

    renderContent() {
        if (!this.wrapper) {
            return;
        }
        this.wrapper.innerHTML = '';

        if (!this.data.embed) {
            const input = document.createElement('input');
            input.type = 'url';
            input.placeholder = 'Paste a YouTube or Vimeo URL';
            input.className = 'tp-page-editor__embed-input';
            input.value = this.data.source || '';

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'tp-page-editor__embed-button';
            button.textContent = 'Embed';

            const error = document.createElement('div');
            error.className = 'tp-page-editor__embed-error';
            error.textContent = this.error;

            button.addEventListener('click', () => {
                const next = resolveEmbed(input.value);
                if (!next) {
                    this.error = 'Enter a valid YouTube or Vimeo URL.';
                    this.renderContent();
                    return;
                }
                this.error = '';
                this.data.source = input.value.trim();
                this.data.service = next.service;
                this.data.embed = next.embed;
                this.renderContent();
            });

            this.wrapper.appendChild(input);
            this.wrapper.appendChild(button);
            if (this.error) {
                this.wrapper.appendChild(error);
            }
            return;
        }

        const frame = document.createElement('div');
        frame.className = 'tp-page-editor__embed-frame';
        const iframe = document.createElement('iframe');
        iframe.src = this.data.embed;
        iframe.loading = 'lazy';
        iframe.allowFullscreen = true;
        frame.appendChild(iframe);

        const caption = document.createElement('div');
        caption.className = 'tp-page-editor__embed-caption';
        caption.contentEditable = 'true';
        caption.dataset.placeholder = 'Add a caption';
        caption.innerHTML = this.data.caption || '';
        caption.addEventListener('input', () => {
            this.data.caption = caption.innerHTML || '';
        });

        const actions = document.createElement('div');
        actions.className = 'tp-page-editor__embed-actions';

        const replaceBtn = document.createElement('button');
        replaceBtn.type = 'button';
        replaceBtn.textContent = 'Replace';
        replaceBtn.className = 'tp-page-editor__embed-link';
        replaceBtn.addEventListener('click', () => {
            this.data.embed = '';
            this.data.service = '';
            this.renderContent();
        });

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.textContent = 'Remove';
        removeBtn.className = 'tp-page-editor__embed-link';
        removeBtn.addEventListener('click', () => {
            this.data.embed = '';
            this.data.service = '';
            this.data.source = '';
            this.data.caption = '';
            this.renderContent();
        });

        actions.appendChild(replaceBtn);
        actions.appendChild(removeBtn);

        this.wrapper.appendChild(frame);
        this.wrapper.appendChild(caption);
        this.wrapper.appendChild(actions);
    }

    save() {
        return {
            source: this.data.source,
            service: this.data.service,
            embed: this.data.embed,
            caption: this.data.caption,
        };
    }
}

class ChecklistTool {
    constructor({ data }) {
        this.data = {
            items: Array.isArray(data?.items)
                ? data.items
                      .filter((item) => item && typeof item === 'object')
                      .map((item) => ({
                          text: typeof item.text === 'string' ? item.text : '',
                          checked: !!item.checked,
                      }))
                : [{ text: '', checked: false }],
        };
        if (this.data.items.length === 0) {
            this.data.items = [{ text: '', checked: false }];
        }
        this.wrapper = null;
    }

    static get toolbox() {
        return {
            title: 'Checklist',
            icon: checklistToolIcon,
        };
    }

    static get sanitize() {
        return {
            items: {
                text: {
                    br: true,
                    b: true,
                    strong: true,
                    i: true,
                    em: true,
                    u: true,
                    s: true,
                    a: {
                        href: true,
                        rel: true,
                        target: true,
                    },
                },
                checked: true,
            },
        };
    }

    render() {
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'tp-page-editor__checklist';
        this.renderContent();
        return this.wrapper;
    }

    renderContent() {
        if (!this.wrapper) {
            return;
        }
        this.wrapper.innerHTML = '';

        this.data.items.forEach((item, index) => {
            const row = document.createElement('div');
            row.className = 'tp-page-editor__checklist-row';

            const input = document.createElement('input');
            input.type = 'checkbox';
            input.checked = !!item.checked;
            input.addEventListener('change', () => {
                this.data.items[index].checked = input.checked;
            });

            const text = document.createElement('div');
            text.className = 'tp-page-editor__checklist-text';
            text.contentEditable = 'true';
            text.dataset.placeholder = 'Checklist item';
            text.innerHTML = item.text || '';
            text.addEventListener('input', () => {
                this.data.items[index].text = text.innerHTML || '';
            });

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'tp-page-editor__checklist-remove';
            removeBtn.textContent = '−';
            removeBtn.addEventListener('click', () => {
                this.data.items.splice(index, 1);
                if (this.data.items.length === 0) {
                    this.data.items.push({ text: '', checked: false });
                }
                this.renderContent();
            });

            row.appendChild(input);
            row.appendChild(text);
            row.appendChild(removeBtn);
            this.wrapper.appendChild(row);
        });

        const addBtn = document.createElement('button');
        addBtn.type = 'button';
        addBtn.className = 'tp-page-editor__checklist-add';
        addBtn.textContent = '+ Add item';
        addBtn.addEventListener('click', () => {
            this.data.items.push({ text: '', checked: false });
            this.renderContent();
        });
        this.wrapper.appendChild(addBtn);
    }

    save() {
        return {
            items: this.data.items
                .map((item) => ({
                    text: String(item.text || '').trim(),
                    checked: !!item.checked,
                }))
                .filter((item) => item.text !== ''),
        };
    }
}

class CodeBlockTool {
    constructor({ data }) {
        this.data = {
            code: typeof data?.code === 'string' ? data.code : '',
            language: typeof data?.language === 'string' ? data.language : '',
        };
        this.wrapper = null;
    }

    static get toolbox() {
        return {
            title: 'Code',
            icon: codeToolIcon,
        };
    }

    render() {
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'tp-page-editor__code';

        const language = document.createElement('input');
        language.type = 'text';
        language.className = 'tp-page-editor__code-language';
        language.placeholder = 'Language (optional)';
        language.value = this.data.language || '';
        language.addEventListener('input', () => {
            this.data.language = language.value;
        });

        const code = document.createElement('textarea');
        code.className = 'tp-page-editor__code-input';
        code.placeholder = 'Paste or type code';
        code.value = this.data.code || '';
        code.addEventListener('input', () => {
            this.data.code = code.value;
        });

        this.wrapper.appendChild(language);
        this.wrapper.appendChild(code);
        return this.wrapper;
    }

    save() {
        return {
            code: this.data.code || '',
            language: this.data.language || '',
        };
    }
}

class CalloutTool {
    constructor({ data }) {
        this.data = {
            type: typeof data?.type === 'string' ? data.type : 'info',
            text: typeof data?.text === 'string' ? data.text : '',
        };
        this.wrapper = null;
    }

    static get toolbox() {
        return {
            title: 'Callout',
            icon: calloutToolIcon,
        };
    }

    static get sanitize() {
        return {
            type: true,
            text: {
                br: true,
                b: true,
                strong: true,
                i: true,
                em: true,
                u: true,
                s: true,
                a: {
                    href: true,
                    rel: true,
                    target: true,
                },
            },
        };
    }

    render() {
        this.wrapper = document.createElement('div');
        this.wrapper.className = `tp-page-editor__callout tp-page-editor__callout--${this.safeType(this.data.type)}`;

        const top = document.createElement('div');
        top.className = 'tp-page-editor__callout-top';

        const select = document.createElement('select');
        select.className = 'tp-page-editor__callout-select';
        ['info', 'warning', 'success'].forEach((type) => {
            const option = document.createElement('option');
            option.value = type;
            option.textContent = type[0].toUpperCase() + type.slice(1);
            option.selected = this.safeType(this.data.type) === type;
            select.appendChild(option);
        });
        select.addEventListener('change', () => {
            this.data.type = this.safeType(select.value);
            this.wrapper.className = `tp-page-editor__callout tp-page-editor__callout--${this.data.type}`;
        });

        const text = document.createElement('div');
        text.className = 'tp-page-editor__callout-text';
        text.contentEditable = 'true';
        text.dataset.placeholder = 'Add callout text';
        text.innerHTML = this.data.text || '';
        text.addEventListener('input', () => {
            this.data.text = text.innerHTML || '';
        });

        top.appendChild(select);
        this.wrapper.appendChild(top);
        this.wrapper.appendChild(text);
        return this.wrapper;
    }

    safeType(type) {
        return ['info', 'warning', 'success'].includes(type) ? type : 'info';
    }

    save() {
        return {
            type: this.safeType(this.data.type),
            text: this.data.text || '',
        };
    }
}

class MediaImageTool {
    constructor({ data, config }) {
        this.config = config || {};
        const mediaId = Number.isInteger(data?.media_id)
            ? data.media_id
            : Number.isFinite(Number(data?.media_id))
              ? Number(data?.media_id)
              : null;
        this.data = {
            media_id: mediaId && mediaId > 0 ? mediaId : null,
            url: typeof data?.url === 'string' ? data.url : '',
            alt: typeof data?.alt === 'string' ? data.alt : '',
            caption: typeof data?.caption === 'string' ? data.caption : '',
        };
        this.wrapper = null;
        this.captionInput = null;
    }

    static get toolbox() {
        return {
            title: 'Image',
            icon: imageToolIcon,
        };
    }

    static get sanitize() {
        return {
            media_id: true,
            url: true,
            alt: true,
            caption: {
                br: true,
                b: true,
                strong: true,
                i: true,
                em: true,
                u: true,
                s: true,
                a: {
                    href: true,
                    rel: true,
                    target: true,
                },
            },
        };
    }

    render() {
        this.wrapper = document.createElement('div');
        this.wrapper.classList.add('tp-page-editor__image');
        this.renderContent();
        return this.wrapper;
    }

    renderContent() {
        if (!this.wrapper) {
            return;
        }
        this.wrapper.innerHTML = '';

        if (!this.data.url) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'tp-page-editor__image-button';
            button.textContent = 'Select image';
            button.addEventListener('click', () => this.pickImage());
            this.wrapper.appendChild(button);
            return;
        }

        const figure = document.createElement('figure');
        figure.className = 'tp-page-editor__image-figure';
        const img = document.createElement('img');
        img.src = this.data.url;
        img.alt = this.data.alt || '';
        img.className = 'tp-page-editor__image-img';
        figure.appendChild(img);

        const caption = document.createElement('figcaption');
        caption.className = 'tp-page-editor__image-caption';
        caption.contentEditable = 'true';
        caption.dataset.placeholder = 'Add a caption';
        caption.innerHTML = this.data.caption || '';
        caption.addEventListener('input', () => {
            this.data.caption = caption.innerHTML || '';
        });
        figure.appendChild(caption);

        const actions = document.createElement('div');
        actions.className = 'tp-page-editor__image-actions';

        const replaceBtn = document.createElement('button');
        replaceBtn.type = 'button';
        replaceBtn.textContent = 'Replace';
        replaceBtn.className = 'tp-page-editor__image-link';
        replaceBtn.addEventListener('click', () => this.pickImage());

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.textContent = 'Remove';
        removeBtn.className = 'tp-page-editor__image-link';
        removeBtn.addEventListener('click', () => {
            this.data.media_id = null;
            this.data.url = '';
            this.data.alt = '';
            this.data.caption = '';
            this.renderContent();
        });

        actions.appendChild(replaceBtn);
        actions.appendChild(removeBtn);

        this.wrapper.appendChild(figure);
        this.wrapper.appendChild(actions);
    }

    async pickImage() {
        if (typeof this.config.onPick !== 'function') {
            return;
        }
        const media = await this.config.onPick();
        if (!media || typeof media.url !== 'string' || media.url.trim() === '') {
            return;
        }
        this.data.url = media.url;
        this.data.media_id = Number.isInteger(media.id) && media.id > 0 ? media.id : null;
        this.data.alt = typeof media.alt === 'string' ? media.alt : typeof media.label === 'string' ? media.label : '';
        this.renderContent();
    }

    save() {
        return {
            media_id: this.data.media_id,
            url: this.data.url,
            alt: this.data.alt,
            caption: this.data.caption,
        };
    }
}

window.tpPageEditor = function tpPageEditor(config) {
    return {
        json: config?.initialJson || '{"time":0,"blocks":[],"version":"2.28.0"}',
        mediaOptions: Array.isArray(config?.mediaOptions) ? config.mediaOptions : [],
        mediaIndexUrl: typeof config?.mediaIndexUrl === 'string' ? config.mediaIndexUrl : '',
        mediaModalOpen: false,
        mediaModalSearch: '',
        mediaModalResolve: null,
        editor: null,
        dirty: false,
        storageKey: null,
        baselineSnapshot: '',
        beforeUnloadHandler: null,
        formSubmitHandler: null,
        init() {
            const surface = this.$refs.surface;
            const storageKey = this.$refs.editor?.dataset?.storageKey || null;
            this.storageKey = storageKey;
            if (!surface) {
                return;
            }
            if (surface.dataset.editorReady === '1') {
                return;
            }
            surface.dataset.editorReady = '1';
            surface.innerHTML = '';

            const restore = storageKey ? window.localStorage.getItem(storageKey) : null;
            if (restore) {
                this.json = restore;
            }

            const data = this.safeJsonParse(this.json);
            this.json = JSON.stringify(data);
            this.baselineSnapshot = this.snapshotFromDoc(data);

            const openMediaPicker = () =>
                new Promise((resolve) => {
                    this.mediaModalResolve = resolve;
                    this.mediaModalSearch = '';
                    this.mediaModalOpen = true;
                });

            this.editor = new EditorJS({
                holder: surface,
                data,
                autofocus: true,
                defaultBlock: 'paragraph',
                minHeight: 200,
                placeholder: 'Type to write, use the menu to add blocks',
                inlineToolbar: ['bold', 'italic', 'link', 'inlineCode'],
                onChange: async () => {
                    const output = await this.editor.save();
                    this.json = JSON.stringify(output);
                    const snapshot = this.snapshotFromDoc(output);
                    this.dirty = snapshot !== this.baselineSnapshot;
                    if (this.storageKey) {
                        if (this.dirty) {
                            window.localStorage.setItem(this.storageKey, this.json);
                        } else {
                            window.localStorage.removeItem(this.storageKey);
                        }
                    }
                },
                tools: {
                    paragraph: {
                        class: Paragraph,
                        inlineToolbar: ['bold', 'italic', 'link', 'inlineCode'],
                    },
                    header: {
                        class: Header,
                        inlineToolbar: ['bold', 'italic', 'link', 'inlineCode'],
                        config: {
                            levels: [1, 2, 3],
                            defaultLevel: 2,
                        },
                    },
                    list: {
                        class: List,
                        inlineToolbar: ['bold', 'italic', 'link', 'inlineCode'],
                    },
                    quote: {
                        class: Quote,
                        inlineToolbar: ['bold', 'italic', 'link', 'inlineCode'],
                        config: {
                            quotePlaceholder: 'Write a quote',
                            captionPlaceholder: 'Quote author',
                        },
                    },
                    inlineCode: {
                        class: InlineCode,
                        shortcut: 'CMD+SHIFT+M',
                    },
                    delimiter: {
                        class: Delimiter,
                    },
                    image: {
                        class: MediaImageTool,
                        config: {
                            onPick: openMediaPicker,
                        },
                    },
                    embed: {
                        class: SimpleEmbedTool,
                    },
                    checklist: {
                        class: ChecklistTool,
                    },
                    code: {
                        class: CodeBlockTool,
                    },
                    callout: {
                        class: CalloutTool,
                    },
                },
            });

            this.beforeUnloadHandler = (event) => {
                if (!this.dirty) {
                    return;
                }
                event.preventDefault();
                event.returnValue = '';
            };
            window.addEventListener('beforeunload', this.beforeUnloadHandler);

            const form = this.$el.closest('form');
            if (form) {
                this.formSubmitHandler = () => {
                    this.dirty = false;
                    if (this.storageKey) {
                        window.localStorage.removeItem(this.storageKey);
                    }
                };
                form.addEventListener('submit', this.formSubmitHandler);
            }
        },
        destroy() {
            if (this.editor) {
                this.editor.destroy();
                this.editor = null;
            }
            if (this.beforeUnloadHandler) {
                window.removeEventListener('beforeunload', this.beforeUnloadHandler);
                this.beforeUnloadHandler = null;
            }
            const form = this.$el?.closest('form');
            if (form && this.formSubmitHandler) {
                form.removeEventListener('submit', this.formSubmitHandler);
                this.formSubmitHandler = null;
            }
            if (this.$refs?.surface) {
                this.$refs.surface.dataset.editorReady = '0';
            }
        },
        filteredMediaOptions() {
            const query = this.mediaModalSearch.trim().toLowerCase();
            if (!query) {
                return this.mediaOptions;
            }
            return this.mediaOptions.filter((opt) => {
                const label = String(opt?.label || '').toLowerCase();
                const value = String(opt?.value || '').toLowerCase();
                return label.includes(query) || value.includes(query);
            });
        },
        chooseMedia(option) {
            if (this.mediaModalResolve) {
                this.mediaModalResolve({
                    id: Number.isInteger(option?.id) ? option.id : null,
                    url: String(option?.value || ''),
                    alt: String(option?.label || ''),
                });
            }
            this.mediaModalResolve = null;
            this.mediaModalOpen = false;
        },
        closeMediaModal() {
            if (this.mediaModalResolve) {
                this.mediaModalResolve(null);
            }
            this.mediaModalResolve = null;
            this.mediaModalOpen = false;
        },
        safeJsonParse(value) {
            try {
                const parsed = JSON.parse(value);
                return parsed && typeof parsed === 'object' ? parsed : { time: 0, blocks: [], version: '2.28.0' };
            } catch (error) {
                return { time: 0, blocks: [], version: '2.28.0' };
            }
        },
        snapshotFromDoc(doc) {
            const safe = doc && typeof doc === 'object' ? doc : { blocks: [] };
            const blocks = Array.isArray(safe.blocks) ? safe.blocks : [];
            const normalizedBlocks = blocks
                .map((block) => this.normalizeBlock(block))
                .filter((block) => block !== null);

            return JSON.stringify({ blocks: normalizedBlocks });
        },
        normalizeBlock(block) {
            if (!block || typeof block !== 'object') {
                return null;
            }
            const type = String(block.type || '');
            const data = block.data && typeof block.data === 'object' ? block.data : {};

            if (type === 'paragraph') {
                const text = String(data.text || '').replace(/<br\s*\/?>/gi, '').trim();
                if (text === '') return null;
                return { type, data: { text: String(data.text || '') } };
            }
            if (type === 'header') {
                const text = String(data.text || '').trim();
                if (text === '') return null;
                return { type, data: { text: String(data.text || ''), level: Number(data.level || 2) } };
            }
            if (type === 'quote') {
                const text = String(data.text || '').trim();
                const caption = String(data.caption || '').trim();
                if (text === '' && caption === '') return null;
                return { type, data: { text: String(data.text || ''), caption: String(data.caption || '') } };
            }
            if (type === 'list') {
                const items = Array.isArray(data.items) ? data.items : [];
                const hasContent = items.some((item) => {
                    if (typeof item === 'string') return item.trim() !== '';
                    if (item && typeof item === 'object') return String(item.content || '').trim() !== '';
                    return false;
                });
                if (!hasContent) return null;
                return { type, data: { style: String(data.style || 'unordered'), items } };
            }
            if (type === 'checklist') {
                const items = Array.isArray(data.items) ? data.items : [];
                const cleaned = items
                    .filter((item) => item && typeof item === 'object')
                    .map((item) => ({ text: String(item.text || ''), checked: !!item.checked }))
                    .filter((item) => item.text.trim() !== '');
                if (cleaned.length === 0) return null;
                return { type, data: { items: cleaned } };
            }
            if (type === 'code') {
                const code = String(data.code || '');
                if (code.trim() === '') return null;
                return { type, data: { code, language: String(data.language || '') } };
            }
            if (type === 'callout') {
                const text = String(data.text || '');
                if (text.trim() === '') return null;
                return { type, data: { type: String(data.type || 'info'), text } };
            }
            if (type === 'embed') {
                const embed = String(data.embed || '');
                if (embed.trim() === '') return null;
                return {
                    type,
                    data: {
                        service: String(data.service || ''),
                        source: String(data.source || ''),
                        embed,
                        caption: String(data.caption || ''),
                    },
                };
            }
            if (type === 'image') {
                const mediaId = Number.isInteger(data.media_id)
                    ? data.media_id
                    : Number.isFinite(Number(data.media_id))
                      ? Number(data.media_id)
                      : null;
                const url = String(data.url || '');
                if (url.trim() === '') return null;
                return {
                    type,
                    data: {
                        ...(mediaId && mediaId > 0 ? { media_id: mediaId } : {}),
                        url,
                        alt: String(data.alt || ''),
                        caption: String(data.caption || ''),
                    },
                };
            }

            return { type, data };
        },
    };
};
