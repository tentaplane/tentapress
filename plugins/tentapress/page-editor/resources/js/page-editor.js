import { Editor, Extension } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import BubbleMenu from '@tiptap/extension-bubble-menu';
import Suggestion from '@tiptap/suggestion';

window.tpPageEditor = function tpPageEditor(config) {
    return {
        json: config?.initialJson || '{"type":"page","content":[]}',
        editor: null,
        dirty: false,
        init() {
            const container = this.$refs.editor;
            const surface = this.$refs.surface;
            const placeholder = container?.querySelector('.tp-page-editor__placeholder');
            const storageKey = container?.dataset?.storageKey || null;
            const slashMenu = this.$refs.slashMenu;
            const bubbleMenu = this.$refs.bubbleMenu;

            const restore = storageKey ? window.localStorage.getItem(storageKey) : null;
            if (restore) {
                this.json = restore;
            }

            this.initToolbar(bubbleMenu);

            const slashCommand = Extension.create({
                name: 'slashCommand',
                addOptions() {
                    return {
                        suggestion: {
                            char: '/',
                            startOfLine: false,
                            command: ({ editor, range, props }) => {
                                editor.chain().focus().deleteRange(range).run();
                                props.command(editor);
                            },
                            items: ({ query }) => {
                                const items = [
                                    {
                                        title: 'Paragraph',
                                        description: 'Start writing with plain text',
                                        command: (editor) => editor.chain().focus().setNode('paragraph').run(),
                                    },
                                    {
                                        title: 'Heading 1',
                                        description: 'Big section heading',
                                        command: (editor) => editor.chain().focus().setNode('heading', { level: 1 }).run(),
                                    },
                                    {
                                        title: 'Heading 2',
                                        description: 'Medium section heading',
                                        command: (editor) => editor.chain().focus().setNode('heading', { level: 2 }).run(),
                                    },
                                    {
                                        title: 'Heading 3',
                                        description: 'Small section heading',
                                        command: (editor) => editor.chain().focus().setNode('heading', { level: 3 }).run(),
                                    },
                                    {
                                        title: 'Blockquote',
                                        description: 'Emphasized quote block',
                                        command: (editor) => editor.chain().focus().toggleBlockquote().run(),
                                    },
                                ];

                                if (!query) {
                                    return items;
                                }

                                return items.filter((item) => item.title.toLowerCase().includes(query.toLowerCase()));
                            },
                            render: () => {
                                let popup;
                                let list;

                                const onSelect = (item) => {
                                    if (!item) {
                                        return;
                                    }
                                    item.command?.(this.editor);
                                };

                                return {
                                    onStart: (props) => {
                                        popup = slashMenu;
                                        if (!popup) {
                                            return;
                                        }

                                        popup.innerHTML = '';
                                        list = document.createElement('div');
                                        list.className = 'tp-page-editor__slash-list';
                                        popup.appendChild(list);

                                        this.updateSlashList(list, props.items, onSelect);
                                        popup.style.display = 'block';
                                        this.positionSlashMenu(popup, props.clientRect);
                                    },
                                    onUpdate: (props) => {
                                        if (!popup || !list) {
                                            return;
                                        }
                                        this.updateSlashList(list, props.items, onSelect);
                                        this.positionSlashMenu(popup, props.clientRect);
                                    },
                                    onKeyDown: (props) => {
                                        if (props.event.key === 'Escape') {
                                            if (popup) {
                                                popup.style.display = 'none';
                                            }
                                            return true;
                                        }
                                        return false;
                                    },
                                    onExit: () => {
                                        if (popup) {
                                            popup.style.display = 'none';
                                        }
                                    },
                                };
                            },
                        },
                    };
                },
                addProseMirrorPlugins() {
                    return [Suggestion(this.options.suggestion)];
                },
            });

            const extensions = [
                StarterKit.configure({
                    heading: { levels: [1, 2, 3] },
                    bulletList: false,
                    orderedList: false,
                    listItem: false,
                    codeBlock: false,
                    blockquote: true,
                }),
                Link.configure({
                    openOnClick: false,
                    protocols: ['http', 'https', 'mailto'],
                }),
                Placeholder.configure({
                    placeholder: 'Start writing. Type / for commands.',
                    emptyNodeClass: 'is-empty',
                }),
                slashCommand,
            ];

            if (bubbleMenu instanceof HTMLElement) {
                extensions.push(
                    BubbleMenu.configure({
                        element: bubbleMenu,
                        tippyOptions: { duration: 150 },
                    })
                );
            }

            this.editor = new Editor({
                element: surface,
                extensions,
                content: this.toTiptapDoc(this.safeJsonParse(this.json)),
                editorProps: {
                    transformPastedHTML: (html) => {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const text = doc?.body?.textContent || '';
                        return text;
                    },
                },
                onUpdate: ({ editor }) => {
                    this.json = JSON.stringify(this.fromTiptapDoc(editor.getJSON()));
                    this.dirty = true;
                    if (storageKey) {
                        window.localStorage.setItem(storageKey, this.json);
                    }
                },
            });

            this.togglePlaceholder(placeholder, surface);

            window.addEventListener('beforeunload', (event) => {
                if (!this.dirty) {
                    return;
                }
                event.preventDefault();
                event.returnValue = '';
            });
        },
        destroy() {
            if (this.editor) {
                this.editor.destroy();
                this.editor = null;
            }
        },
        safeJsonParse(value) {
            try {
                return JSON.parse(value);
            } catch (error) {
                return { type: 'page', content: [] };
            }
        },
        toTiptapDoc(doc) {
            if (!doc || typeof doc !== 'object') {
                return { type: 'doc', content: [] };
            }

            if (doc.type === 'page') {
                return { type: 'doc', content: Array.isArray(doc.content) ? doc.content : [] };
            }

            return doc;
        },
        fromTiptapDoc(doc) {
            if (!doc || typeof doc !== 'object') {
                return { type: 'page', content: [] };
            }

            if (doc.type === 'doc') {
                return { type: 'page', content: Array.isArray(doc.content) ? doc.content : [] };
            }

            return doc;
        },
        togglePlaceholder(placeholder, surface) {
            if (!placeholder || !surface) {
                return;
            }

            const text = surface.textContent || '';
            placeholder.style.display = text.trim() === '' ? 'block' : 'none';
        },
        initToolbar(toolbar) {
            if (!toolbar) {
                return;
            }

            toolbar.addEventListener('click', (event) => {
                const button = event.target.closest('[data-action]');
                if (!button || !this.editor) {
                    return;
                }

                const action = button.dataset.action;
                switch (action) {
                    case 'bold':
                        this.editor.chain().focus().toggleBold().run();
                        break;
                    case 'italic':
                        this.editor.chain().focus().toggleItalic().run();
                        break;
                    case 'link': {
                        const current = this.editor.getAttributes('link').href || '';
                        const href = window.prompt('Enter URL', current);
                        if (href === null) {
                            return;
                        }
                        if (href.trim() === '') {
                            this.editor.chain().focus().unsetLink().run();
                            return;
                        }
                        this.editor.chain().focus().setLink({ href: href.trim() }).run();
                        break;
                    }
                    default:
                        break;
                }
            });
        },
        updateSlashList(container, items, onSelect) {
            container.innerHTML = '';
            if (!items || items.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'tp-page-editor__slash-empty';
                empty.textContent = 'No results';
                container.appendChild(empty);
                return;
            }

            items.forEach((item) => {
                const row = document.createElement('button');
                row.type = 'button';
                row.className = 'tp-page-editor__slash-item';
                row.innerHTML = `<span class="tp-page-editor__slash-title">${item.title}</span><span class="tp-page-editor__slash-desc">${item.description}</span>`;
                row.addEventListener('click', () => onSelect(item));
                container.appendChild(row);
            });
        },
        positionSlashMenu(menu, clientRect) {
            if (!menu || !clientRect) {
                return;
            }
            const rect = clientRect();
            if (!rect) {
                return;
            }
            menu.style.left = `${rect.left}px`;
            menu.style.top = `${rect.bottom + 8}px`;
        },
    };
};
