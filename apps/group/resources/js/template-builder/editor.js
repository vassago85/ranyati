/**
 * TipTap editor factory for the template builder.
 *
 * Three modes:
 *   - 'heading'   single-line, text + tokens only, no linebreaks
 *   - 'plain'     paragraphs + hard breaks + tokens
 *                 serialises to text with `\n\n` paragraph gaps so the
 *                 renderer never sees HTML it didn't ask for.
 *   - 'rich'      paragraphs + bold/italic/underline + lists + tokens,
 *                 serialises to HTML the renderer sanitises again
 *                 server-side via RichText::sanitize().
 *
 * Placeholder chips: `{{profile.full_name}}` round-trips as a Token node
 * so what you type stays legible on screen. When the admin toggles
 * "Show sample values", the chip label swaps to the resolved value but
 * what we save is always the original token.
 */
import { Editor, Extension, Node, mergeAttributes } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import Suggestion from '@tiptap/suggestion';
import { PluginKey } from '@tiptap/pm/state';

const TOKEN_PATTERN = /\{\{\s*([A-Za-z0-9_.]+)\s*\}\}/g;

/** TipTap node for a `{{path}}` placeholder chip. */
const Token = Node.create({
    name: 'token',
    group: 'inline',
    inline: true,
    atom: true,
    selectable: true,
    draggable: false,

    addOptions() {
        return { getSampleValue: null, isSampleMode: () => false };
    },

    addAttributes() {
        return {
            path: {
                default: '',
                parseHTML: (el) => el.getAttribute('data-path') || el.textContent.replace(/^\{\{\s*|\s*\}\}$/g, ''),
                renderHTML: (attrs) => ({ 'data-path': attrs.path }),
            },
        };
    },

    parseHTML() {
        return [{ tag: 'span[data-token]' }];
    },

    renderHTML({ node, HTMLAttributes }) {
        return [
            'span',
            mergeAttributes(HTMLAttributes, { 'data-token': '1', 'data-path': node.attrs.path, class: 'tb-token' }),
            `{{${node.attrs.path}}}`,
        ];
    },

    renderText({ node }) {
        return `{{${node.attrs.path}}}`;
    },

    addNodeView() {
        return ({ node, editor }) => {
            const dom = document.createElement('span');
            dom.className = 'tb-token';
            dom.dataset.token = '1';
            dom.dataset.path = node.attrs.path;
            dom.contentEditable = 'false';

            const update = () => {
                const opts = editor.extensionManager.extensions.find((e) => e.name === 'token')?.options || {};
                if (opts.isSampleMode && opts.isSampleMode()) {
                    const value = opts.getSampleValue ? opts.getSampleValue(node.attrs.path) : '';
                    dom.textContent = value || `{{${node.attrs.path}}}`;
                    dom.classList.toggle('is-sample', !!value);
                } else {
                    dom.textContent = `{{${node.attrs.path}}}`;
                    dom.classList.remove('is-sample');
                }
            };
            update();

            // Re-render chip labels whenever the sample toggle changes.
            const handler = () => update();
            window.addEventListener('tb:sample-mode', handler);

            return {
                dom,
                update() {
                    update();
                    return true;
                },
                destroy() {
                    window.removeEventListener('tb:sample-mode', handler);
                },
            };
        };
    },
});

/**
 * `{{` opens the token suggestion popup. The caller passes the catalogue
 * (group => path => label) and we build a simple keyboard-navigable list.
 */
function tokenSuggestion({ catalogue }) {
    return {
        char: '{{',
        allowSpaces: false,
        startOfLine: false,

        items: ({ query }) => {
            const q = query.toLowerCase();
            const results = [];
            for (const [group, items] of Object.entries(catalogue || {})) {
                for (const [path, label] of Object.entries(items)) {
                    if (!q || path.toLowerCase().includes(q) || String(label).toLowerCase().includes(q)) {
                        results.push({ group, path, label });
                        if (results.length >= 60) break;
                    }
                }
                if (results.length >= 60) break;
            }
            return results;
        },

        render: () => {
            let popup, selected = 0, currentItems = [], currentProps;

            const highlight = () => {
                [...popup.querySelectorAll('.tb-suggestion-item')].forEach((el, i) => {
                    el.classList.toggle('is-selected', i === selected);
                });
            };

            const commit = (index) => {
                const item = currentItems[index];
                if (!item || !currentProps) return;
                currentProps.command({ path: item.path });
            };

            const render = (props) => {
                currentProps = props;
                currentItems = props.items;
                selected = 0;
                if (!popup) return;
                if (currentItems.length === 0) {
                    popup.innerHTML = '<div class="tb-suggestion-item"><em style="color:rgba(255,255,255,.4);">No matches</em></div>';
                    return;
                }
                const groups = new Map();
                currentItems.forEach((it) => {
                    if (!groups.has(it.group)) groups.set(it.group, []);
                    groups.get(it.group).push(it);
                });
                let html = '';
                let index = 0;
                for (const [group, items] of groups) {
                    html += `<div class="tb-suggestion-group">${escapeHtml(group)}</div>`;
                    for (const item of items) {
                        html += `<div class="tb-suggestion-item" data-index="${index}">`
                            + `<span class="tb-path">${escapeHtml(item.path)}</span>`
                            + `<span class="tb-label">${escapeHtml(String(item.label))}</span>`
                            + `</div>`;
                        index++;
                    }
                }
                popup.innerHTML = html;
                [...popup.querySelectorAll('.tb-suggestion-item')].forEach((el) => {
                    el.addEventListener('mousedown', (ev) => {
                        ev.preventDefault();
                        commit(Number(el.dataset.index));
                    });
                });
                highlight();
            };

            return {
                onStart(props) {
                    popup = document.createElement('div');
                    popup.className = 'tb-suggestion';
                    document.body.appendChild(popup);
                    const rect = props.clientRect?.();
                    if (rect) {
                        popup.style.left = `${rect.left}px`;
                        popup.style.top = `${rect.bottom + 4}px`;
                    }
                    render(props);
                },
                onUpdate(props) {
                    render(props);
                    const rect = props.clientRect?.();
                    if (rect && popup) {
                        popup.style.left = `${rect.left}px`;
                        popup.style.top = `${rect.bottom + 4}px`;
                    }
                },
                onKeyDown(props) {
                    if (!popup) return false;
                    if (props.event.key === 'ArrowDown') {
                        selected = (selected + 1) % Math.max(1, currentItems.length);
                        highlight();
                        return true;
                    }
                    if (props.event.key === 'ArrowUp') {
                        selected = (selected - 1 + currentItems.length) % Math.max(1, currentItems.length);
                        highlight();
                        return true;
                    }
                    if (props.event.key === 'Enter' || props.event.key === 'Tab') {
                        commit(selected);
                        return true;
                    }
                    if (props.event.key === 'Escape') {
                        popup.remove();
                        popup = null;
                        return true;
                    }
                    return false;
                },
                onExit() {
                    popup?.remove();
                    popup = null;
                },
            };
        },
    };
}

function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

/**
 * Wrap @tiptap/suggestion (a ProseMirror plugin factory, not an
 * Extension) so TipTap can install it. Typing `{{` opens the picker.
 */
function tokenSuggestionExtension(catalogue) {
    return Extension.create({
        name: 'tokenSuggestion',
        addProseMirrorPlugins() {
            return [
                Suggestion({
                    editor: this.editor,
                    pluginKey: new PluginKey('tbTokenSuggestion'),
                    ...tokenSuggestion({ catalogue }),
                    command: ({ editor, range, props }) => {
                        editor
                            .chain()
                            .focus()
                            .deleteRange(range)
                            .insertContent({ type: 'token', attrs: { path: props.path } })
                            .insertContent(' ')
                            .run();
                    },
                }),
            ];
        },
    });
}

/**
 * Parse a text or HTML string with embedded `{{path}}` tokens into a
 * TipTap ProseMirror document. Tokens become Token nodes.
 */
export function placeholdersToJSON(input, mode) {
    if (mode === 'rich') {
        // Turn stored `{{path}}` tokens into <span data-token> so TipTap
        // can parse them as Token nodes. On save they go back to {{path}}.
        const html = String(input || '').replace(TOKEN_PATTERN, (_m, path) =>
            `<span data-token="1" data-path="${path}">{{${path}}}</span>`,
        );
        return { html, isHtml: true };
    }
    const str = String(input || '');
    const paragraphs = mode === 'heading' ? [str] : str.split(/\r?\n\r?\n/);
    const docContent = paragraphs.map((para) => {
        const inlines = [];
        let last = 0;
        TOKEN_PATTERN.lastIndex = 0;
        let m;
        while ((m = TOKEN_PATTERN.exec(para))) {
            if (m.index > last) inlines.push({ type: 'text', text: para.slice(last, m.index) });
            inlines.push({ type: 'token', attrs: { path: m[1] } });
            last = m.index + m[0].length;
        }
        if (last < para.length) {
            // Convert single newlines in a plain paragraph into hardBreak nodes.
            const tail = para.slice(last);
            const parts = tail.split(/\r?\n/);
            parts.forEach((t, i) => {
                if (t) inlines.push({ type: 'text', text: t });
                if (i < parts.length - 1) inlines.push({ type: 'hardBreak' });
            });
        }
        if (mode === 'heading') return { type: 'paragraph', content: inlines };
        // Insert hardBreaks for interior single-newlines that fell on the start
        // of the paragraph (edge case handled above). Empty paragraphs are
        // allowed in prosemirror.
        return { type: 'paragraph', content: inlines };
    });

    return { type: 'doc', content: docContent.length ? docContent : [{ type: 'paragraph' }] };
}

/**
 * Serialise an editor's document back to text (for heading/plain) or
 * HTML (for rich). Tokens become `{{path}}`.
 */
export function serialise(editor, mode) {
    if (mode === 'rich') {
        // Token chips must go back to literal {{path}} so LetterComposer
        // and Placeholders::replace keep working without a renderer change.
        return editor.getHTML().replace(
            /<span[^>]*data-path="([^"]+)"[^>]*>[\s\S]*?<\/span>/g,
            '{{$1}}',
        );
    }
    const doc = editor.state.doc;
    const parts = [];
    doc.forEach((node) => {
        parts.push(nodeToText(node));
    });
    const joined = parts.join('\n\n');
    return mode === 'heading' ? joined.replace(/\n+/g, ' ').trim() : joined;
}

function nodeToText(node) {
    let out = '';
    node.content.forEach((child) => {
        if (child.type.name === 'token') {
            out += `{{${child.attrs.path}}}`;
        } else if (child.type.name === 'hardBreak') {
            out += '\n';
        } else if (child.isText) {
            out += child.text;
        } else {
            out += nodeToText(child);
        }
    });
    return out;
}

/**
 * Build a TipTap editor for the given element and mode.
 *
 * @param {HTMLElement} element
 * @param {Object} opts
 * @param {'heading'|'plain'|'rich'} opts.mode
 * @param {string} opts.initial  initial text (plain/heading) or HTML (rich)
 * @param {Object} opts.catalogue  placeholder catalogue for the suggestion popup
 * @param {(path:string)=>string} opts.getSampleValue
 * @param {()=>boolean} opts.isSampleMode
 * @param {(value:string)=>void} opts.onChange  called after each edit with the serialised value
 * @param {()=>void} opts.onCommit  called on blur / 1.5s idle
 * @param {()=>void} opts.onEnterNewBlock  Enter at the end of a plain block exits to a new block
 * @param {()=>void} opts.onBackspaceEmpty Backspace on an empty block deletes it
 * @param {()=>void} opts.onSlashMenu       `/` on empty opens block menu
 */
export function createEditor(element, opts) {
    const extensions = [
        StarterKit.configure({
            // StarterKit now includes most marks and nodes. For 'heading'
            // mode we disable everything except paragraph + text; for
            // 'plain' mode we drop lists and marks.
            heading: false,
            codeBlock: false,
            blockquote: false,
            horizontalRule: false,
            bulletList: opts.mode === 'rich' ? {} : false,
            orderedList: opts.mode === 'rich' ? {} : false,
            listItem: opts.mode === 'rich' ? {} : false,
            strike: opts.mode === 'rich' ? {} : false,
            bold: opts.mode === 'rich' ? {} : false,
            italic: opts.mode === 'rich' ? {} : false,
            code: false,
            link: false,
        }),
        Token.configure({
            getSampleValue: opts.getSampleValue,
            isSampleMode: opts.isSampleMode,
        }),
        tokenSuggestionExtension(opts.catalogue),
    ];
    if (opts.mode === 'rich') {
        extensions.push(Underline);
    }

    const content = placeholdersToJSON(opts.initial || '', opts.mode);

    let idleTimer = null;
    const scheduleCommit = () => {
        clearTimeout(idleTimer);
        idleTimer = setTimeout(() => {
            opts.onCommit?.();
        }, 1500);
    };

    const editor = new Editor({
        element,
        content: content.isHtml ? content.html : content,
        extensions,
        editorProps: {
            attributes: {
                class: 'tb-editor-inner',
            },
            handleKeyDown(view, event) {
                // Enter at end of a plain-mode doc inserts a new block.
                if (opts.mode === 'plain' && event.key === 'Enter' && !event.shiftKey) {
                    const { state } = view;
                    const { $from } = state.selection;
                    const atEnd = $from.pos === state.doc.content.size - 1 || $from.pos === state.doc.content.size;
                    if (atEnd && !state.doc.textContent.trim().endsWith('')) {
                        opts.onEnterNewBlock?.();
                        return true;
                    }
                }
                if (event.key === 'Backspace') {
                    const { state } = view;
                    if (state.doc.textContent === '' && state.doc.childCount <= 1) {
                        opts.onBackspaceEmpty?.();
                        return true;
                    }
                }
                if (event.key === '/' && opts.mode === 'plain') {
                    const { state } = view;
                    if (state.doc.textContent === '') {
                        opts.onSlashMenu?.();
                        return true;
                    }
                }
                return false;
            },
        },
        onUpdate({ editor }) {
            opts.onChange?.(serialise(editor, opts.mode));
            scheduleCommit();
        },
        onBlur() {
            clearTimeout(idleTimer);
            opts.onCommit?.();
        },
    });

    return {
        editor,
        destroy() {
            clearTimeout(idleTimer);
            editor.destroy();
        },
        serialise: () => serialise(editor, opts.mode),
        focus: () => editor.commands.focus(),
    };
}
