# Architecture: Rich Text Editor Plugin

**Research Date:** 2026-02-03
**Project:** TentaPress Rich Text Block Plugin
**Confidence:** HIGH

## Executive Summary

A rich text editor plugin for TentaPress should follow the existing markdown editor pattern: a standalone plugin that registers a new block type with the `BlockRegistry`, provides an admin editor component via JavaScript, and renders HTML on the frontend via Blade views. The architecture mirrors TentaPress's plugin-first design where blocks are JSON data stored in pages/posts, with the editor handling the transformation between user input and structured content.

## Recommended Architecture

### Component Boundaries

```
┌─────────────────────────────────────────────────────────────┐
│                    Rich Text Block Plugin                    │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌─────────────────────┐        ┌──────────────────────┐   │
│  │  ServiceProvider    │───────▶│   BlockRegistry      │   │
│  │  (Registration)     │        │   (Core System)      │   │
│  └─────────────────────┘        └──────────────────────┘   │
│           │                                                  │
│           │ registers block definition                       │
│           ▼                                                  │
│  ┌─────────────────────────────────────────────────────┐   │
│  │         Block Definition (PHP)                       │   │
│  │  - type: "blocks/richtext"                          │   │
│  │  - fields: [{key: "content", type: "richtext"}]    │   │
│  │  - defaults, example data                           │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                               │
│  ┌──────────────────────┐      ┌──────────────────────┐    │
│  │  Editor Component    │      │  Renderer View       │    │
│  │  (JavaScript/Alpine) │      │  (Blade Template)    │    │
│  │                      │      │                      │    │
│  │  - Mount Tiptap      │      │  - Sanitize HTML     │    │
│  │  - Sync to textarea  │      │  - Apply styles      │    │
│  │  - Handle toolbar    │      │  - Render content    │    │
│  └──────────────────────┘      └──────────────────────┘    │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

### Data Flow

**Admin Edit Flow:**

```
1. Page Edit View Loaded
   ↓
2. BlocksEditor Alpine component initialized (editor.blade.php)
   ↓
3. User adds "Rich Text" block → Alpine creates block object:
   {
     type: "blocks/richtext",
     props: { content: "" },
     _key: "block-uuid-1234"
   }
   ↓
4. Alpine renders field template for type="richtext" (in editor.blade.php)
   ↓
5. Rich text editor JS detects [data-richtext-editor] element via MutationObserver
   ↓
6. Tiptap editor instance created and mounted to element
   ↓
7. User types → Tiptap onChange → sync HTML to hidden textarea
   ↓
8. Textarea input event → Alpine setProp(index, "content", htmlString)
   ↓
9. Alpine updates blocks array and hidden blocks_json textarea
   ↓
10. User saves → POST to UpdateController with blocks_json
   ↓
11. Controller decodes JSON → validates → saves to TpPage.blocks (JSON column)
```

**Frontend Render Flow:**

```
1. PageController loads published TpPage
   ↓
2. PageRenderer.render() calls BlockRenderer for blocks array
   ↓
3. For each block with type="blocks/richtext":
   ↓
4. BlockRenderer looks up BlockDefinition in registry
   ↓
5. Resolves Blade view: "blocks.richtext"
   - First checks theme override: tp-theme::blocks.richtext
   - Falls back to plugin: tentapress-blocks::blocks.richtext
   ↓
6. Renders view with props: ['content' => '<p>HTML content...</p>']
   ↓
7. Blade template:
   - Sanitizes HTML (strip <script>, dangerous attributes)
   - Applies content width/alignment classes
   - Wraps in semantic markup
   ↓
8. Returns safe HTML string
   ↓
9. All block HTML concatenated and passed to layout
```

### Integration Points

#### With TentaPress Blocks System

**BlockRegistry (Core System)**
- Location: `plugins/tentapress/blocks/src/Registry/BlockRegistry.php`
- Interface: `register(BlockDefinition $definition)`
- Usage: Plugin ServiceProvider calls `$registry->register()` in `boot()` method
- Confidence: HIGH (verified in codebase)

**BlockRenderer (Core System)**
- Location: `plugins/tentapress/blocks/src/Render/BlockRenderer.php`
- Interface: `render(array $block): string`
- Lookup: Matches `$block['type']` to registered BlockDefinition
- View resolution: Theme override → Plugin fallback → empty string
- Confidence: HIGH (verified in codebase)

**Block Editor UI (admin-shell/blocks)**
- Location: `plugins/tentapress/blocks/resources/views/editor.blade.php`
- Field type handling: Template `x-if="field.type === 'richtext'"` (already exists!)
- Pattern: Matches existing `markdown` and `media` field type patterns
- Confidence: HIGH (richtext field type template already present at line 343)

#### With Media Library

**Media Picker Integration**
- The block editor already has a media modal system for image/file selection
- Rich text editor should integrate via:
  - Toolbar button "Insert Image" opens `openMediaModal(index, 'content', 'single')`
  - Modal callback inserts `<img src="{{url}}">` at cursor position in Tiptap
  - Reuses existing `mediaOptions` and `mediaIndexUrl` passed to editor
- Implementation: Add custom Tiptap image extension with media modal trigger
- Confidence: MEDIUM (pattern exists, requires custom Tiptap extension)

#### With Asset Pipeline

**Vite Integration**
- Location: Plugin's `resources/js/richtext-editor.js`
- Load pattern: `@vite(['plugins/tentapress/block-richtext/resources/js/richtext-editor.js'])` in `@push('head')` once block type is detected
- Mirrors: `block-markdown-editor` plugin pattern (verified at line 64 of editor.blade.php)
- Dependencies: Tiptap packages installed via npm/bun
- Confidence: HIGH (exact pattern exists for markdown editor)

### Storage Layer

**JSON Structure in Database:**

```json
{
  "type": "blocks/richtext",
  "version": 1,
  "props": {
    "content": "<p>Rich text <strong>HTML</strong> content here...</p>",
    "width": "normal",
    "alignment": "left",
    "background": "white"
  }
}
```

**Database Schema:**
- No changes required to existing schema
- `TpPage.blocks` and `TpPost.blocks` columns already accept JSON arrays
- Cast to array via Eloquent: `protected $casts = ['blocks' => 'array']`
- Confidence: HIGH (verified in TpPage model)

**Content Format:**
- Store as sanitized HTML string (not Tiptap's JSON format)
- Rationale:
  - Simpler to render on frontend (no JSON parsing)
  - Portable across editor versions
  - Theme can style with CSS classes
  - Matches WordPress post_content pattern
- Trade-off: Cannot round-trip back to Tiptap JSON without loss
- Alternative: Store Tiptap JSON, convert to HTML in renderer (more complex)
- Confidence: MEDIUM (design decision based on simplicity vs. flexibility)

## Recommended Build Order

### Phase 1: Minimal Block Registration (Foundation)
**Goal:** Register block type, render static content

1. Create plugin directory structure:
   ```
   plugins/tentapress/block-richtext/
   ├── composer.json
   ├── tentapress.json
   └── src/BlockRichTextServiceProvider.php
   ```

2. Register ServiceProvider with BlockRegistry:
   - Define BlockDefinition with type `"blocks/richtext"`
   - Single field: `{key: "content", type: "textarea"}` (plain textarea for now)
   - Set defaults, example data

3. Create frontend Blade view:
   - `resources/views/blocks/richtext.blade.php`
   - Render `$props['content']` as plain text (no HTML yet)
   - Add width/alignment/background options (copy from content block)

4. Test: Add block in admin, save, verify it renders on frontend

**Exit Criteria:** Can create richtext block, save plain text, see it on page

### Phase 2: Admin Editor Component (Interactivity)
**Goal:** Replace textarea with Tiptap WYSIWYG editor

1. Install Tiptap dependencies:
   ```bash
   cd plugins/tentapress/block-richtext
   npm install @tiptap/core @tiptap/pm @tiptap/starter-kit
   ```

2. Create `resources/js/richtext-editor.js`:
   - MutationObserver pattern (copy from markdown-editor.js)
   - Initialize Tiptap editor with StarterKit
   - Basic toolbar: bold, italic, heading, lists, links
   - Sync editor content to hidden textarea on change/blur
   - Store editors in Map by unique key

3. Update ServiceProvider:
   - Change field type from `"textarea"` to `"richtext"`
   - Add height option to field definition

4. Update Blade view to render HTML:
   - Change from `e($content)` to `{!! $sanitized !!}`
   - Add HTML sanitization (strip `<script>`, dangerous attrs)
   - Consider using `Illuminate\Support\Str::stripTags()` allowlist

5. Test: Edit block, bold text, save, verify styled content on frontend

**Exit Criteria:** Can format text in admin, see formatted output on page

### Phase 3: Image Integration (Media Library)
**Goal:** Insert images from media library into rich text

1. Create custom Tiptap image extension:
   - Override default image node with custom insert command
   - Add toolbar button "Insert Image"
   - Button calls Alpine method to open media modal

2. Wire media modal to Tiptap:
   - Alpine component exposes `insertImageAtCursor(url)` method
   - Media modal callback passes selected image URL to this method
   - Method calls Tiptap `chain().focus().setImage({src: url}).run()`

3. Update image rendering in Blade:
   - Ensure images have responsive classes
   - Add lazy loading attribute
   - Validate image URLs are from allowed domains

4. Test: Insert image, see it in editor, save, verify on frontend

**Exit Criteria:** Can insert media library images into rich text content

### Phase 4: Advanced Features (Polish)
**Goal:** Link management, code blocks, undo/redo

1. Extend Tiptap configuration:
   - Add Link extension with prompt dialog
   - Add Code and CodeBlock extensions
   - Add History extension for undo/redo
   - Add placeholder text option

2. Toolbar enhancements:
   - Group buttons logically
   - Add keyboard shortcuts hints
   - Add active state indicators

3. Content validation:
   - Max length option in field definition
   - Strip dangerous HTML tags on save (server-side)
   - Validate content structure

4. Theme integration:
   - Document CSS classes for prose styling
   - Provide example theme overrides
   - Test with multiple themes

**Exit Criteria:** Production-ready rich text editor with full feature set

## Architectural Patterns to Follow

### Pattern 1: Plugin Self-Registration
**What:** Plugin registers its block type directly with BlockRegistry in ServiceProvider.boot()
**When:** Always, for any new block type
**Example:**
```php
public function boot(): void
{
    $registry = $this->app->make(BlockRegistry::class);
    $registry->register(new BlockDefinition(
        type: 'blocks/richtext',
        name: 'Rich Text',
        // ...
    ));
}
```
**Why:** Decouples plugin from core, enables/disables cleanly
**Source:** Verified in `block-markdown-editor/src/BlockMarkdownEditorServiceProvider.php`

### Pattern 2: MutationObserver for Dynamic Mounting
**What:** JavaScript uses MutationObserver to detect new editor elements and initialize them
**When:** Editor instances need to initialize after Alpine renders DOM
**Example:**
```javascript
const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
            if (node.matches('[data-richtext-editor]')) {
                initEditor(node);
            }
        });
    });
});
observer.observe(document.body, { childList: true, subtree: true });
```
**Why:** Alpine renders block fields dynamically; editor must mount when DOM appears
**Source:** Verified in `block-markdown-editor/resources/js/markdown-editor.js`

### Pattern 3: Hidden Textarea as State Bridge
**What:** Rich text editor syncs HTML content to a hidden textarea that Alpine watches
**When:** Third-party editor needs to communicate with Alpine component
**Example:**
```html
<textarea
    data-richtext-textarea
    class="hidden"
    x-model="blocks[index].props.content"
    @input="setProp(index, 'content', $event.target.value)">
</textarea>
<div data-richtext-mount></div>
```
**Why:** Decouples editor implementation from Alpine; textarea is standard form element
**Source:** Pattern observed in markdown editor, standard practice in Laravel+Livewire

### Pattern 4: Theme Override View Resolution
**What:** BlockRenderer checks theme views first, falls back to plugin views
**When:** Rendering any block on frontend
**Example:**
```php
// 1. Try theme override
if ($this->views->exists('tp-theme::blocks.richtext')) {
    return $this->views->make('tp-theme::blocks.richtext', $data);
}
// 2. Fallback to plugin
if ($this->views->exists('tentapress-blocks::blocks.richtext')) {
    return $this->views->make('tentapress-blocks::blocks.richtext', $data);
}
```
**Why:** Allows themes to customize block appearance without modifying plugin
**Source:** Verified in `blocks/src/Render/BlockRenderer.php` lines 49-66

### Pattern 5: JSON Column with Eloquent Array Cast
**What:** Blocks stored as JSON in database, cast to array by Eloquent
**When:** Any model storing structured block data
**Example:**
```php
// Migration
$table->json('blocks')->nullable();

// Model
protected $casts = ['blocks' => 'array'];

// Usage
$page->blocks = [
    ['type' => 'blocks/richtext', 'props' => ['content' => '...']]
];
```
**Why:** Native JSON support in SQLite/MySQL, automatic encoding/decoding
**Source:** Verified in TpPage model and migration

## Anti-Patterns to Avoid

### Anti-Pattern 1: Direct HTML in User Input
**What goes wrong:** User can inject `<script>` tags or malicious HTML
**Why it happens:** Temptation to use `{!! $content !!}` without sanitization
**Consequences:** XSS vulnerability, security breach
**Prevention:**
- Always sanitize HTML before rendering: `Str::of($html)->stripTags('<p><br><strong><em><ul><ol><li><a><h2><h3>')`
- Or use a library like `HTML Purifier` for robust sanitization
- Validate on both client (Tiptap config) and server (Blade render)
**Detection:** Security audit finds unescaped user content in views

### Anti-Pattern 2: Tightly Coupling to Tiptap JSON Format
**What goes wrong:** Storing Tiptap's internal JSON structure in database
**Why it happens:** Easier to round-trip content between editor sessions
**Consequences:**
- Breaking changes when Tiptap updates its schema
- Cannot switch editors without data migration
- Frontend renderer needs JSON parser
**Prevention:**
- Store sanitized HTML string, not Tiptap JSON
- Editor converts HTML → Tiptap on mount, Tiptap → HTML on save
- Accept loss of perfect round-tripping for portability
**Alternative:** If round-tripping is critical, store both HTML (for render) and JSON (for editing) in separate props

### Anti-Pattern 3: Loading Editor JS for All Blocks
**What goes wrong:** Rich text editor JS bundle loads on every page with block editor
**Why it happens:** Adding script to admin layout instead of conditional push
**Consequences:** Slow admin page loads, wasted bandwidth for pages without richtext blocks
**Prevention:**
- Check if any block has `type: "richtext"` field in PHP
- Only `@push('head')` the Vite script if condition is true
- Use `@once` directive to prevent duplicate includes
**Example:**
```php
@php
$hasRichText = false;
foreach ($blockDefinitions as $def) {
    if ($def->fields has type richtext) $hasRichText = true;
}
@endphp
@if ($hasRichText)
    @once
        @push('head')
            @vite(['plugins/tentapress/block-richtext/resources/js/richtext-editor.js'])
        @endpush
    @endonce
@endif
```
**Source:** Exact pattern verified for markdown editor in `editor.blade.php` lines 10-23

### Anti-Pattern 4: Forgetting Keyboard Users
**What goes wrong:** Toolbar buttons have no keyboard shortcuts, focus traps
**Why it happens:** WYSIWYG editors are mouse-first by default
**Consequences:** Inaccessible to keyboard-only users, fails WCAG standards
**Prevention:**
- Configure Tiptap keyboard shortcuts: `Ctrl+B` for bold, etc.
- Ensure focus management works with Tab key
- Add `aria-label` attributes to toolbar buttons
- Test with screen reader
**Source:** General accessibility best practice, not specific to TentaPress

## Component Specifications

### BlockRichTextServiceProvider

**Responsibility:** Register plugin with Laravel, register block with BlockRegistry

**Dependencies:**
- `Illuminate\Support\ServiceProvider`
- `TentaPress\Blocks\Registry\BlockRegistry`
- `TentaPress\Blocks\Registry\BlockDefinition`

**Key Methods:**
```php
public function boot(): void
{
    // Guard clause: check if blocks plugin is available
    if (!$this->app->bound(BlockRegistry::class)) return;

    // Register block definition
    $registry = $this->app->make(BlockRegistry::class);
    $registry->register(new BlockDefinition(
        type: 'blocks/richtext',
        name: 'Rich Text',
        description: 'A rich text block with formatting toolbar.',
        version: 1,
        fields: [
            [
                'key' => 'content',
                'label' => 'Content',
                'type' => 'richtext',
                'height' => '320px',
                'help' => 'Formatted text with bold, italic, links, etc.',
            ],
            // ... width, alignment, background fields
        ],
        defaults: ['content' => '', 'width' => 'normal', ...],
        view: 'blocks.richtext',
    ));

    // Register views
    $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-blocks');
}
```

**Testing Strategy:**
- Unit test: Verify BlockDefinition created with correct properties
- Integration test: Boot plugin, assert `BlockRegistry->get('blocks/richtext')` returns definition

### Tiptap Editor Component (JavaScript)

**Responsibility:** Mount Tiptap editor, sync content to Alpine component

**Dependencies:**
- `@tiptap/core`
- `@tiptap/pm`
- `@tiptap/starter-kit`

**Key Functions:**
```javascript
const editors = new Map();

function initEditor(group) {
    const key = group.dataset.richtextKey;
    const textarea = group.querySelector('[data-richtext-textarea]');
    const mount = document.createElement('div');

    const editor = new Editor({
        element: mount,
        extensions: [StarterKit],
        content: textarea.value,
        onUpdate: ({ editor }) => {
            const html = editor.getHTML();
            textarea.value = html;
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
        },
    });

    editors.set(key, { editor, textarea });
}

// MutationObserver to detect new editors
const observer = new MutationObserver((mutations) => {
    // ... scan for [data-richtext-editor] elements
});
```

**Data Attributes:**
- `data-richtext-editor`: Marks container for editor initialization
- `data-richtext-key`: Unique identifier for editor instance
- `data-richtext-height`: CSS height for editor (e.g., "320px")
- `data-richtext-textarea`: Hidden textarea that holds HTML content

**Events:**
- `input`: Dispatched on textarea when content changes, picked up by Alpine

### Blade Render View

**Responsibility:** Render sanitized HTML content with styling

**Location:** `plugins/tentapress/block-richtext/resources/views/blocks/richtext.blade.php`

**Template Structure:**
```blade
@php
    $content = (string) ($props['content'] ?? '');
    $width = (string) ($props['width'] ?? 'normal');
    $alignment = (string) ($props['alignment'] ?? 'left');
    $background = (string) ($props['background'] ?? 'white');

    // Sanitize HTML
    $sanitized = Illuminate\Support\Str::of($content)
        ->stripTags('<p><br><strong><em><ul><ol><li><a><h2><h3><h4><blockquote><code><pre>')
        ->toString();

    // Apply classes
    $widthClass = match ($width) {
        'narrow' => 'max-w-3xl',
        'wide' => 'max-w-6xl',
        default => 'max-w-5xl',
    };
    // ... alignment, background classes
@endphp

<section class="py-10">
    <div class="mx-auto px-6 {{ $widthClass }}">
        <div class="rounded-xl {{ $panelClass }} {{ $panelPadding }} {{ $alignClass }}">
            @if ($sanitized !== '')
                <div class="prose prose-slate max-w-none">
                    {!! $sanitized !!}
                </div>
            @else
                <div class="text-black/50">No content.</div>
            @endif
        </div>
    </div>
</section>
```

**Security Considerations:**
- HTML sanitization via `stripTags()` with allowlist of safe tags
- No `<script>`, `<iframe>`, or event attributes allowed
- Links should be validated for `javascript:` protocol
- Consider adding CSP headers for extra protection

**Styling:**
- Use Tailwind's `prose` class for typography defaults
- Allow theme to override with `tp-theme::blocks.richtext` view
- Document required CSS classes in plugin README

## Technology Decisions

### Why Tiptap over Alternatives

**Tiptap:**
- Pros: Modern, extensible, TypeScript, active development, ProseMirror-based
- Cons: Larger bundle size than simpler editors
- Fit: Excellent - matches TentaPress's modern stack (Alpine, Tailwind)

**Alternatives Considered:**

- **Quill**: Mature, smaller bundle, but less extensible
- **TinyMCE**: Feature-rich, but heavyweight, jQuery-style API
- **ContentEditable + execCommand**: Native browser, but buggy, deprecated API
- **Slate.js**: Low-level React-based, requires more custom work

**Recommendation:** Tiptap
**Rationale:** Best balance of modern API, extensibility, and active community
**Confidence:** HIGH

### Why HTML Storage over Tiptap JSON

**HTML String:**
- Pros: Portable, simple to render, theme-friendly, familiar to developers
- Cons: Loses semantic structure for editing, harder to migrate/transform

**Tiptap JSON:**
- Pros: Perfect round-tripping, structured data, enables advanced queries
- Cons: Tightly couples to Tiptap schema, requires JSON parser on frontend

**Recommendation:** HTML String
**Rationale:** Prioritize simplicity and theme flexibility for v1; can add JSON export later if needed
**Confidence:** MEDIUM (trade-off decision; JSON is valid alternative)

### Why Plugin Over Core Feature

**Plugin Approach:**
- Pros: Optional, can be disabled, doesn't bloat core, easier to iterate
- Cons: Requires separate installation, adds dependency

**Core Feature:**
- Pros: Always available, tighter integration
- Cons: Forces rich text on all users, harder to replace

**Recommendation:** Plugin
**Rationale:** Matches TentaPress philosophy ("everything is a plugin"); users may prefer Markdown
**Confidence:** HIGH (aligns with existing architecture)

## Open Questions

1. **Image Resizing:** Should editor support drag-to-resize images, or rely on fixed width classes?
   - Implication: Resizing requires storing width/height attributes in HTML
   - Recommendation: Start with fixed classes, add resizing in phase 4

2. **Link Validation:** Should editor validate external links are HTTPS, or allow any protocol?
   - Implication: Security vs. flexibility trade-off
   - Recommendation: Allow any protocol in editor, add validation option in field config

3. **Emoji Support:** Should editor include emoji picker, or rely on OS emoji keyboard?
   - Implication: Bundle size increase for emoji data
   - Recommendation: Skip built-in picker, document OS shortcuts

4. **Table Support:** Should editor support tables, or treat as separate block type?
   - Implication: Complex editing UX within rich text
   - Recommendation: Separate table block is cleaner; skip tables in rich text v1

5. **Max Content Length:** Should there be a configurable character/word limit per block?
   - Implication: Needs UI feedback, validation on save
   - Recommendation: Add as optional field config in phase 4

## Sources

**Codebase Analysis:**
- `/Users/Chris/Work/TentaPlane/tentapress/plugins/tentapress/blocks/src/Registry/BlockRegistry.php` - Block registration pattern
- `/Users/Chris/Work/TentaPlane/tentapress/plugins/tentapress/blocks/src/Render/BlockRenderer.php` - View resolution and rendering
- `/Users/Chris/Work/TentaPlane/tentapress/plugins/tentapress/blocks/resources/views/editor.blade.php` - Alpine editor component structure (1797 lines)
- `/Users/Chris/Work/TentaPlane/tentapress/plugins/tentapress/block-markdown-editor/` - Reference implementation for field type plugin

**External Research:**
- [AlpineEditor](https://maxeckel.github.io/alpine-editor/) - Prosemirror based WYSIWYG for Alpine.js
- [Tiptap PHP Documentation](https://tiptap.dev/docs/editor/getting-started/install/php) - Official Laravel integration guide
- [Tiptap Laravel Package](https://github.com/georgeboot/laravel-tiptap) - Community TALL stack implementation
- [Using Tiptap with Livewire](https://sudorealm.com/blog/using-tiptap-rich-text-editor-with-livewire) - Integration patterns
- [BlockNote & Strapi](https://strapi.io/integrations/blocknote) - Block-based rich text editor architecture
- [Umbraco RTE Blocks](https://docs.umbraco.com/umbraco-cms/fundamentals/backoffice/property-editors/built-in-umbraco-property-editors/rich-text-editor/rte-blocks) - CMS block integration patterns
- [dotCMS Block Editor](https://www.dotcms.com/docs/latest/block-editor) - Tiptap-based CMS implementation

**Confidence Assessment:**
- Block registration pattern: HIGH (verified in codebase)
- Editor component structure: HIGH (markdown editor provides exact template)
- Tiptap integration: MEDIUM (community resources, not TentaPress-specific)
- Security best practices: HIGH (standard Laravel/web security)
- Storage format decision: MEDIUM (trade-off decision with multiple valid approaches)
