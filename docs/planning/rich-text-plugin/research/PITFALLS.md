# Rich Text Editor Implementation Pitfalls

**Domain:** Rich Text Block Plugin for TentaPress CMS
**Researched:** 2026-02-03
**Confidence:** HIGH (verified with official documentation and recent sources)

This document catalogs common mistakes and critical errors teams make when building rich text editor implementations, with specific focus on Alpine.js integration, JSON schema design, XSS prevention, and the TentaPress architecture.

---

## Critical Pitfalls

Mistakes that cause rewrites, security breaches, or major architectural problems.

### Pitfall 1: Inadequate XSS Sanitization

**What goes wrong:** Rich text editors are particularly vulnerable to XSS attacks because user-generated HTML is rendered for other users. Recent CVEs (2023-2026) show that even established editors like Froala and react-draft-wysiwyg have XSS vulnerabilities via image embeds, iframes, and link parameters.

**Why it happens:**
- Developers trust user input or assume the editor library handles sanitization
- Sanitization only happens client-side, not server-side
- Custom HTML features (embeds, images) bypass sanitization rules
- Never trust user input, especially beyond plaintext

**Consequences:**
- Malicious scripts execute in victim browsers
- Session hijacking, data theft, privilege escalation
- Compliance violations (GDPR, SOC 2)
- Reputation damage

**Prevention:**
1. **Server-side sanitization is mandatory** - Never trust client-side sanitization alone
2. **Use proven libraries** - Implement DOMPurify for HTML sanitization on render
3. **Strict Content Security Policy** - CSP headers prevent external script execution
4. **Strip dangerous HTML** - When converting JSON to HTML, explicitly allow only safe tags
5. **Validate on storage AND render** - Sanitize when storing JSON and when rendering to Blade templates

**Detection:**
- Security scanner flags `{!! $html !!}` without sanitization
- Code review finds direct JSON-to-HTML conversion without filtering
- Penetration testing reveals script injection via image alt text, link href, or embed src

**Phase to address:** Phase 1 (Foundation) - Security must be baked in from the start

**TentaPress-specific notes:**
- The markdown block uses `'html_input' => 'strip'` and `'allow_unsafe_links' => false` - apply similar principles
- Blade's `{!! !!}` syntax bypasses escaping, use only with sanitized content
- JSON schema should define allowed HTML tags for each node type
- Test with payloads: `<script>alert('xss')</script>`, `<img src=x onerror=alert('xss')>`, `javascript:` links

**Sources:**
- [Cross-site Scripting (XSS) in froala/wysiwyg-editor](https://security.snyk.io/vuln/SNYK-PHP-FROALAWYSIWYGEDITOR-5918868)
- [Cross-site Scripting (XSS) in react-draft-wysiwyg](https://security.snyk.io/vuln/SNYK-JS-REACTDRAFTWYSIWYG-8515884)
- [How to Prevent XSS Attacks in React Rich Text Editor](https://www.syncfusion.com/blogs/post/react-rich-text-editor-xss-prevention)

---

### Pitfall 2: Alpine.js Integration Without Sync Strategy

**What goes wrong:** WYSIWYG editor libraries (Tiptap, Quill, TinyMCE) manage their own internal state, which conflicts with Alpine's reactive model. Developers report that `x-model` doesn't work correctly - characters get deleted, updates don't trigger Alpine watchers, and manual event listeners are required.

**Why it happens:**
- Editor libraries update DOM directly, bypassing Alpine's reactivity
- Alpine's `x-model` expects standard form inputs, not custom widgets
- Two-way binding creates infinite loops (Alpine updates editor, editor triggers Alpine)
- No clear "handoff" point between Alpine and the editor library

**Consequences:**
- Data loss during editing (characters deleted)
- Changes not persisted to hidden textarea/input
- Form submission sends stale data
- Undo/redo breaks when Alpine reactivity conflicts with editor history

**Prevention:**
1. **One-way data flow** - Editor is source of truth during editing, Alpine only reads on blur/change
2. **Hidden input pattern** - Use hidden textarea for persistence, sync from editor on `change` and `blur` events
3. **Manual event dispatch** - Editor must dispatch `input` event so Alpine watchers fire
4. **Initialization guard** - Prevent re-initializing editor on Alpine re-renders
5. **Global sync helper** - Expose sync function (like `window.tpMarkdownSync()`) for Alpine to push updates when needed

**Detection:**
- Characters disappear while typing
- Console warnings about event loop
- Form submits empty or stale content
- Alpine DevTools shows stale data vs editor content

**Phase to address:** Phase 1 (Foundation) - Get the integration pattern right before building features

**TentaPress-specific notes:**
- Markdown editor already implements this pattern correctly:
  - Hidden textarea with `data-markdown-textarea`
  - Editor syncs to textarea on `change` and `blur` events
  - `window.tpMarkdownSync()` allows Alpine to push updates
  - `data-markdown-initialized` prevents double initialization
- Reuse this exact pattern for rich text editor
- Test with Alpine `x-model`, `@input` handlers, and form submission

**Code reference:**
```javascript
// From plugins/tentapress/block-markdown-editor/resources/js/markdown-editor.js
const syncFromEditor = () => {
    const value = editor.getMarkdown();
    if (textarea.value !== value) {
        textarea.value = value;
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
    }
};
editor.on('change', syncFromEditor);
editor.on('blur', syncFromEditor);
```

**Sources:**
- [Getting Trix editor and Alpine to work together](https://github.com/alpinejs/alpine/discussions/809)
- [Adding Quill Editor to a Livewire-Alpine.js App](https://dev.to/eduarguz/adding-quilljs-editor-to-livewire-alpinejs-app-389l)
- [AlpineEditor - ProseMirror for Alpine.JS](https://github.com/maxeckel/alpine-editor)

---

### Pitfall 3: ProseMirror Schema Mismatch and Migration Hell

**What goes wrong:** ProseMirror (which Tiptap is built on) strictly validates content against its schema. Content that doesn't match the schema is **silently discarded**. When you add/remove node types or change content rules, existing documents break or lose data.

**Why it happens:**
- Schema is defined in code, but documents are stored as JSON in database
- Adding new features (e.g., adding "callout" node type) invalidates old documents
- Misconfigured `content` or `group` properties cause validation failures
- No migration path when schema changes
- Developers assume schema is flexible like HTML

**Consequences:**
- **Data loss** - Content that doesn't fit new schema is thrown away
- **Breaking changes** - Updating plugin breaks all existing pages
- **Emergency rollbacks** - Production outage when documents won't load
- **No upgrade path** - Can't evolve editor features without breaking content

**Prevention:**
1. **Version your schema** - Store schema version in JSON: `{"version": 1, "type": "doc", ...}`
2. **Write migrations** - Transform old JSON to new schema when loading content
3. **Schema validation on load** - Use `enableContentCheck: true` and listen to `onContentError` events
4. **Backward compatible changes only** - Add optional nodes, never remove or change required nodes
5. **Test with old content** - Keep fixtures of JSON from previous schema versions
6. **Required extensions** - Always include `Document`, `Paragraph`, `Text` (Tiptap StarterKit provides these)

**Detection:**
- `onContentError` events fire when loading documents
- Console errors about invalid content or schema violations
- Empty editor when loading old documents
- Missing content after editor upgrade

**Phase to address:** Phase 1 (Foundation) - Design JSON schema and versioning strategy upfront

**TentaPress-specific notes:**
- Blocks are stored in `blocks` JSON column on TpPage/TpPost models
- Each block has `type` and `props` - rich text content goes in `props.content`
- Schema version should be in props: `{"type": "rich-text", "props": {"version": 1, "content": {...}}}`
- Migration runs on block load in Blade renderer, before converting to HTML
- Test schema changes with real page JSON fixtures

**Example JSON structure:**
```json
{
  "type": "rich-text",
  "props": {
    "version": 1,
    "content": {
      "type": "doc",
      "content": [
        {
          "type": "paragraph",
          "content": [
            {"type": "text", "text": "Hello world"}
          ]
        }
      ]
    }
  }
}
```

**Sources:**
- [ProseMirror Schema Documentation](https://tiptap.dev/docs/editor/core-concepts/schema)
- [Creating a Tiptap Extension: Best Practices and Common Pitfalls](https://medium.com/@Aribaskar-jb/creating-a-tiptap-extension-best-practices-and-common-pitfalls-67c93b5a10b9)
- [Tiptap best practices and tips](https://liveblocks.io/docs/guides/tiptap-best-practices-and-tips)

---

### Pitfall 4: ContentEditable Performance with Large Documents

**What goes wrong:** Browser `contenteditable` becomes extremely slow when documents have 1000+ DOM nodes or 100k+ characters. Typing lags by seconds, cursor jumps, and the page becomes unusable.

**Why it happens:**
- Browser re-calculates layout on every keystroke across entire contenteditable tree
- DOM mutations trigger expensive reflows
- Rich text editors render each formatting span as separate DOM node
- Performance degrades exponentially with DOM depth and node count

**Consequences:**
- Editor unusable for long-form content (blog posts, documentation)
- User complaints about lag and freezing
- Mobile devices hit limits faster than desktop
- Can't remove `contenteditable` attribute without breaking editor

**Prevention:**
1. **Use virtualization** - Only render visible portion of document (complex, may not be needed for v1)
2. **Optimize node views** - Keep custom NodeViews lightweight, avoid heavy computation in `addNodeView`
3. **Implement `ignoreMutation`** - Prevent unnecessary re-renders on DOM changes inside node views
4. **Pagination/chunking** - Split long documents into multiple blocks (TentaPress already does this)
5. **Test with large documents** - Include 10k+ word test documents in QA

**Detection:**
- Typing has visible lag (>100ms delay)
- DevTools Performance tab shows long layout/paint times
- Frame rate drops below 30fps during typing
- Browser reports "slow script" warnings

**Phase to address:** Phase 2-3 (Post-MVP) - Start with pagination limits, optimize if users hit limits

**TentaPress-specific notes:**
- TentaPress page builder uses **multiple blocks** instead of one giant editor - this naturally limits document size per block
- Consider setting soft limit (e.g., "Rich text blocks should be <5k words, use multiple blocks for longer content")
- Rich text is for landing pages, not 10k word blog posts (that's what markdown block is for)
- Monitor but don't over-engineer - may not be a real problem given use case

**Sources:**
- [Typing goes incredibly slow in a contentEditable div](https://bugzilla.mozilla.org/show_bug.cgi?id=635618)
- [Typing in a contenteditable with 100k characters is much slower](https://issues.chromium.org/issues/41237496)
- [Why ContentEditable is Terrible - Medium Engineering](https://medium.engineering/why-contenteditable-is-terrible-122d8a40e480)
- [ProseMirror performance issues with numerous tables](https://discuss.prosemirror.net/t/performance-issues-caused-by-numerous-tables/8037)

---

## Moderate Pitfalls

Mistakes that cause delays, technical debt, or user friction but are recoverable.

### Pitfall 5: Cursor Position Loss on Rerenders

**What goes wrong:** When Alpine or the editor library re-renders the DOM, cursor position resets to start of document or jumps to wrong location. This is especially problematic with framework integrations (React, Vue, Alpine).

**Why it happens:**
- DOM rerender destroys and recreates nodes, losing native browser selection
- Framework's virtual DOM replaces entire subtree
- No selection state saved before rerender
- Selection API not properly restored after DOM updates

**Prevention:**
1. **Avoid innerHTML replacement** - Prefer minimal DOM mutations over full rerenders
2. **Save and restore selection** - Use browser Selection/Range API before/after DOM changes
3. **Let editor manage its DOM** - Don't let Alpine touch the editor's contenteditable area
4. **Initialization-only pattern** - Alpine initializes editor once, then hands off control

**Detection:**
- Cursor jumps to start of document while typing
- Selection lost when clicking toolbar buttons
- Can't maintain selection across editor operations

**Phase to address:** Phase 1 (Foundation) - Prevent with proper integration pattern

**Sources:**
- [ContentEditable element and caret position jumps](https://github.com/facebook/react/issues/2047)
- [Getting the ContentEditable Caret Position in 2026](https://copyprogramming.com/howto/get-contenteditable-caret-position)
- [Finding The Cursor Position In ContentEditable](https://psymonryan.github.io/posts/Finding-The-Cursor-Position-In-ContentEditable/)

---

### Pitfall 6: Focus Management in Modals/Dialogs

**What goes wrong:** When rich text editor is inside a modal dialog (e.g., image settings dialog, link dialog), focus breaks. ESC key closes modal instead of exiting dropdown, toolbar doesn't respond, editor isn't selectable.

**Why it happens:**
- Modal libraries trap focus within dialog container
- Editor's floating toolbars/dropdowns render outside modal (in document.body)
- `focusin` events blocked by modal's event handlers
- ESC keypresses propagate from editor to modal

**Consequences:**
- Can't use editor inside modal dialogs
- Poor UX for inline editing features (link dialogs, image uploads)
- Keyboard shortcuts conflict (ESC, Tab, Enter)

**Prevention:**
1. **Configure modal focus trap** - Whitelist editor's auxiliary elements (dropdowns, popovers)
2. **Stop event propagation** - Prevent ESC from bubbling to modal
3. **Portal editor UI outside modal** - Render floating toolbars at document root
4. **Test modal integration early** - Don't discover this in Phase 3

**Detection:**
- Toolbar buttons don't respond to clicks in modal
- ESC key closes modal instead of closing dropdown
- Can't select text in editor when in modal

**Phase to address:** Phase 2 (if using modals for image/link insertion)

**TentaPress-specific notes:**
- Media library uses modal for file selection
- If rich text block needs media insertion, modal will be involved
- May need to use inline UI instead of modal for image insertion

**Example fix for Bootstrap/Tailwind modals:**
```javascript
document.addEventListener('focusin', (e) => {
  // Allow focus in editor's floating UI
  if (e.target.closest('.tiptap-popup, .tiptap-dropdown')) {
    e.stopImmediatePropagation();
  }
});
```

**Sources:**
- [Configure TinyMCE in modal windows](https://www.tiny.cloud/blog/tinymce-and-modal-windows/)
- [Rich Text Editor not working in Modal Dialogue](https://support.pega.com/question/rich-text-editor-not-working-modal-dialogue)
- [What is HTML focus, and how focus works for rich text editors](https://www.tiny.cloud/blog/text-editor-focus/)

---

### Pitfall 7: Paste Formatting Chaos

**What goes wrong:** Users copy/paste from Microsoft Word, Google Docs, or websites, and get broken HTML, invisible formatting, huge file sizes, or XSS payloads. The "Clean paste" feature doesn't work properly.

**Why it happens:**
- Word/Google Docs generate complex, nested HTML with proprietary styles
- Invisible `<span>` tags with font/color styles persist
- Images embedded as base64 bloat JSON size
- Malicious sites inject scripts via clipboard

**Consequences:**
- Document looks fine in editor but renders broken on frontend
- JSON storage size explodes (base64 images)
- Formatting inconsistent across pages
- XSS via crafted clipboard HTML

**Prevention:**
1. **Paste as plain text by default** - Ctrl+Shift+V strips all formatting
2. **Aggressive paste filtering** - Strip all styles, fonts, colors (keep only semantic tags)
3. **Block base64 images** - Require users to use media library for images
4. **Sanitize clipboard HTML** - Run through DOMPurify before inserting
5. **User education** - Show "Paste as plain text" hint in UI

**Detection:**
- Pasted content has inconsistent fonts/colors
- Inspect JSON and see `<span style="...">` everywhere
- JSON size is 100kb+ for simple content
- Pasted links contain `javascript:` URLs

**Phase to address:** Phase 1 (Foundation) - Configure editor paste rules from start

**TentaPress-specific notes:**
- Landing page content should have consistent styling (theme controls this)
- User shouldn't be able to paste custom fonts/colors
- Images MUST go through media library (for proper storage, optimization, alt text)

**Sources:**
- [Paste from Word does not remove all formatting when selecting "Clean"](https://github.com/froala/wysiwyg-editor/issues/3934)
- [Copying and pasting content into the WYSIWYG editor](https://support.igloosoftware.com/hc/en-us/articles/9481956809492-Copying-and-pasting-content-into-the-WYSIWYG-editor)
- [CKEditor 5 paste handling](https://froala.com/wysiwyg-editor/examples/plain-paste/)

---

### Pitfall 8: Undo/Redo Breaking on Form Operations

**What goes wrong:** Undo/redo stack gets corrupted when user pastes content, uses toolbar buttons, or switches between blocks. Multiple undos happen at once, or undo affects wrong content.

**Why it happens:**
- Default undo timer groups operations incorrectly (300ms default)
- Paste operations not treated as single undo step
- Multiple editor instances share undo stack
- Toolbar commands don't properly push to history

**Consequences:**
- User loses work due to unexpected undo behavior
- Can't undo last action reliably
- Undo in one block affects different block

**Prevention:**
1. **One undo stack per editor instance** - Use unique key for each block
2. **Configure undo timer** - Increase from 300ms to 500-1000ms for better grouping
3. **Test undo scenarios** - Paste, format, toolbar, type, undo x3
4. **Mark single operations** - Ensure paste is one undo step, not character-by-character

**Detection:**
- Undo removes more content than expected
- Undo affects wrong block
- Can't undo toolbar formatting changes

**Phase to address:** Phase 2 (Polish) - After basic editing works

**TentaPress-specific notes:**
- Each rich text block on page is separate editor instance
- Use unique key like `data-editor-key="{{ $blockId }}"` to isolate undo stacks
- Test: Add 3 rich text blocks, edit each, verify undo only affects active block

**Sources:**
- [Handling undo function in rich text editors](https://www.tiny.cloud/blog/undo-function-handling/)
- [Rich Text Editor undo/redo not working properly](https://github.com/OrchardCMS/OrchardCore/issues/6104)
- [Undo/Redo in RichText/Markdown editor breaks when pasting text](https://www.syncfusion.com/forums/173394/undo-redo-in-richtext-markdown-editor-breaks-when-pasting-text)

---

## Minor Pitfalls

Mistakes that cause annoyance but are fixable without major changes.

### Pitfall 9: Mobile/Touch Support as Afterthought

**What goes wrong:** Editor works great on desktop but on mobile: can't select text, toolbar off-screen, touch gestures don't work, soft keyboard overlaps editor.

**Why it happens:**
- Desktop-first development
- Touch events vs mouse events behave differently
- Mobile viewports smaller than expected
- Soft keyboard resizes viewport

**Prevention:**
1. **Test on real devices early** - iPhone, Android tablet
2. **Responsive toolbar** - Collapse to icon menu on mobile
3. **Touch-friendly hit targets** - 44px minimum for toolbar buttons
4. **Handle viewport resize** - Adjust editor height when keyboard appears

**Detection:**
- Can't tap to select text
- Toolbar buttons too small to tap
- Editor hidden behind keyboard

**Phase to address:** Phase 2 (Polish)

**Sources:**
- [Accessibility Best Practices for Rich Text Editors in Mobile Apps](https://medium.com/front-end-weekly/accessibility-best-practices-for-rich-text-editors-in-mobile-apps-884efabd22f7)
- [Accessible rich text editor](https://www.tiny.cloud/blog/accessible-rich-text-editor/)

---

### Pitfall 10: Accessibility - Keyboard Navigation and Screen Readers

**What goes wrong:** Users can't navigate toolbar with keyboard, screen readers don't announce formatting, keyboard focus gets trapped in editor.

**Why it happens:**
- Toolbar uses `<div>` with `onclick` instead of `<button>`
- No `aria-label` on icon-only buttons
- Custom formatting not announced to screen readers
- Can't Tab out of contenteditable

**Prevention:**
1. **Semantic HTML** - Use `<button>` for toolbar, proper heading levels
2. **ARIA labels** - Add `aria-label` to all icon buttons
3. **Keyboard shortcuts** - Document Ctrl+B, Ctrl+I, etc.
4. **Focus management** - Allow Tab to exit editor
5. **Test with screen reader** - NVDA (Windows), VoiceOver (Mac)

**Detection:**
- Can't Tab to toolbar buttons
- Screen reader doesn't announce "Bold applied"
- Keyboard users trapped in editor

**Phase to address:** Phase 2 (Polish) - After MVP, before launch

**Sources:**
- [10 tips for building accessible rich text editors](https://jkrsp.com/accessibility-for-rich-text-editors/)
- [Rich Text Editor Accessibility Guidelines](https://sakai.screenstepslive.com/s/sakai_help/a/198677-rich-text-editor-accessibility-guidelines)
- [Accessibility and Rich Text Editors](https://medium.com/@thehopefulautistic/accessibility-and-rich-text-editors-c5378dab0757)

---

### Pitfall 11: Keyboard Shortcut Conflicts with Browser

**What goes wrong:** Editor shortcuts conflict with browser shortcuts. Ctrl+S tries to save page instead of strikethrough, Ctrl+F opens browser find instead of editor search.

**Why it happens:**
- Browser shortcuts take precedence
- Developers don't prevent default on keydown
- Users expect editor shortcuts but get browser actions

**Prevention:**
1. **Avoid browser shortcuts** - Don't use Ctrl+S, Ctrl+F, Ctrl+P
2. **Prevent default** - Call `event.preventDefault()` on handled shortcuts
3. **Document shortcuts** - Show keyboard shortcuts in help tooltip
4. **Use standard shortcuts** - Ctrl+B (bold), Ctrl+I (italic) work everywhere

**Detection:**
- Ctrl+S triggers browser save dialog
- Shortcuts inconsistent across browsers

**Phase to address:** Phase 2 (Polish)

**Sources:**
- [Keyboard Shortcuts in Rich Text Editor](https://docs.devexpress.com/WindowsForms/6263/controls-and-libraries/rich-text-editor/keyboard-shortcuts)
- [Rich-text editor keyboard shortcuts](https://www.brightspot.com/documentation/brightspot-cms-user-guide/rich-text-editor-keyboard-shortcuts)

---

### Pitfall 12: Over-Engineering Before MVP

**What goes wrong:** Team spends weeks implementing tables, custom colors, file attachments, collaborative editing - features users don't need. Launch delayed by months.

**Why it happens:**
- "Let's just add one more feature"
- Copying feature sets from Notion/Google Docs
- Not validating what users actually need
- Technical debt from unused features

**Prevention:**
1. **Ship MVP first** - Headings, lists, bold/italic, links, images
2. **Measure usage** - See what users actually use before adding more
3. **Defer complexity** - Tables, colors, embeds can wait
4. **Say no** - "Great idea, let's add to v2 roadmap"

**Detection:**
- Roadmap keeps growing
- Launch date keeps slipping
- 50% of features unused after launch

**Phase to address:** Phase 0 (Planning) - Define MVP ruthlessly

**TentaPress-specific notes:**
- Project already scoped well - no colors, no tables, no nested blocks
- Out of scope items correctly deferred
- Stick to the plan!

---

## Phase-Specific Warnings

| Phase Topic | Likely Pitfall | Mitigation |
|-------------|---------------|------------|
| **Phase 1: Foundation** | XSS sanitization skipped | Implement server-side sanitization with DOMPurify from day 1 |
| **Phase 1: Foundation** | Alpine integration broken | Copy markdown editor's sync pattern exactly |
| **Phase 1: Foundation** | Schema changes break content | Version JSON schema, write migration helpers |
| **Phase 1: Editor Setup** | Tiptap SSR issues | Set `immediatelyRender: false` (TentaPress is server-rendered Laravel) |
| **Phase 1: Editor Setup** | Missing required extensions | Always include StarterKit (provides Doc, Paragraph, Text) |
| **Phase 2: Media Integration** | Focus breaks in media modal | Configure modal focus trap to allow editor dropdowns |
| **Phase 2: Paste Handling** | Word/Docs paste disaster | Configure aggressive paste filtering from start |
| **Phase 2: Polish** | Mobile unusable | Test on iPhone/Android weekly, not just desktop |
| **Phase 2: Polish** | Accessibility failure | Add ARIA labels, keyboard nav, screen reader testing |
| **Phase 3: Performance** | Large documents lag | Lazy-load optimization, pagination if needed |
| **All Phases** | Feature creep | Ruthlessly defend MVP scope |

---

## Research Gaps and Open Questions

These areas need validation during implementation:

1. **Tiptap + Alpine.js battle-tested integration** - AlpineEditor exists but not widely used. May discover integration issues.
   - **Confidence:** MEDIUM - Pattern from markdown editor should work, but Tiptap more complex than Toast UI

2. **JSON schema evolution strategy** - Need to define migration approach before first release.
   - **Confidence:** HIGH - Pattern well-established in CMSs, just need to implement

3. **Media library integration with Tiptap** - How to trigger media modal from editor and insert selected image.
   - **Confidence:** MEDIUM - Tiptap has image extension, need to customize "insert image" command

4. **Performance with 50+ blocks on one page** - Each block is separate editor instance.
   - **Confidence:** LOW - Need to test with real page builders, may be fine or may need lazy init

5. **Blade rendering optimization** - Converting JSON to HTML on every page load.
   - **Confidence:** MEDIUM - Can cache rendered HTML if performance issue, but probably fine

---

## Summary: Critical Success Factors

To avoid the most common and damaging pitfalls:

1. **Security first** - Server-side XSS sanitization from day 1
2. **Copy proven patterns** - Reuse markdown editor's Alpine integration exactly
3. **Version everything** - JSON schema versioning and migration from start
4. **Test early and often** - Mobile, accessibility, paste handling, undo/redo
5. **Defend MVP scope** - Ship basic features first, iterate based on usage
6. **Plan for schema changes** - Assume you'll need to evolve the JSON structure

The most expensive mistakes to fix later:
- Inadequate XSS sanitization (security breach)
- No schema versioning (data loss on updates)
- Wrong Alpine integration pattern (rewrite required)

Get these right in Phase 1, everything else is fixable.
