@extends('tentapress-admin::layouts.shell')

@section('title', 'Edit Menu')

@section('content')
    @php
        $itemsArray = [];
        foreach ($items as $item) {
            $itemsArray[] = [
                'id' => (int) ($item->id ?? 0),
                'title' => (string) ($item->title ?? ''),
                'url' => (string) ($item->url ?? ''),
                'target' => $item->target !== null ? (string) $item->target : '',
                'parent_id' => $item->parent_id !== null ? (int) $item->parent_id : null,
                'sort_order' => (int) ($item->sort_order ?? 0),
            ];
        }

        $pagesArray = [];
        foreach ($pages as $pageItem) {
            $pagesArray[] = [
                'id' => (int) ($pageItem->id ?? 0),
                'title' => (string) ($pageItem->title ?? ''),
                'slug' => (string) ($pageItem->slug ?? ''),
            ];
        }

        $postsArray = [];
        foreach ($posts as $postItem) {
            $postsArray[] = [
                'id' => (int) ($postItem->id ?? 0),
                'title' => (string) ($postItem->title ?? ''),
                'slug' => (string) ($postItem->slug ?? ''),
            ];
        }

        $menusArray = [];
        foreach ($menus as $menuOption) {
            $menusArray[] = [
                'id' => (int) ($menuOption->id ?? 0),
                'name' => (string) ($menuOption->name ?? ''),
            ];
        }

        $locationsArray = is_array($locations ?? null) ? $locations : [];
        $assignments = is_array($locationAssignments ?? null) ? $locationAssignments : [];
        $linkedLocationCount = count(array_filter($assignments, static fn ($menuId): bool => (int) $menuId === (int) $menu->id));
    @endphp

    <div class="tp-editor space-y-6">
        <div class="tp-page-header">
            <div>
                <h1 class="tp-page-title">Edit Menu</h1>
                <p class="tp-description">
                    <span class="font-semibold">{{ $menu->name }}</span>
                    <span class="tp-muted">({{ $menu->slug }})</span>
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('tp.menus.update', ['menu' => $menu->id]) }}" id="menu-form" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
                <div class="space-y-6 lg:col-span-3">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="rounded-xl border border-black/10 bg-white px-4 py-3">
                            <div class="tp-muted text-xs uppercase tracking-wide">Menu items</div>
                            <div class="mt-2 text-xl font-semibold text-[#1d2327]">{{ count($itemsArray) }}</div>
                        </div>
                        <div class="rounded-xl border border-black/10 bg-white px-4 py-3">
                            <div class="tp-muted text-xs uppercase tracking-wide">Theme locations</div>
                            <div class="mt-2 text-xl font-semibold text-[#1d2327]">{{ count($locationsArray) }}</div>
                        </div>
                        <div class="rounded-xl border border-black/10 bg-white px-4 py-3">
                            <div class="tp-muted text-xs uppercase tracking-wide">Assigned</div>
                            <div class="mt-2 text-xl font-semibold text-[#1d2327]">{{ $linkedLocationCount }}</div>
                        </div>
                    </div>

                    <div class="tp-metabox">
                        <div class="tp-metabox__title">Menu details</div>
                        <div class="tp-metabox__body grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <div class="tp-field">
                                <label class="tp-label">Name</label>
                                <input name="name" class="tp-input" value="{{ old('name', $menu->name) }}" required />
                            </div>
                            <div class="tp-field">
                                <label class="tp-label">Menu key</label>
                                <input name="slug" class="tp-input" value="{{ old('slug', $menu->slug) }}" pattern="[a-z0-9-]+" />
                                <div class="tp-help">Lowercase letters, numbers, and dashes only.</div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="tp-metabox"
                        x-data="tpMenuEditor({
                            initialItems: @js($itemsArray),
                            pages: @js($pagesArray),
                            posts: @js($postsArray),
                            blogBase: @js($blogBase),
                        })"
                        x-init="init()">
                        <div class="tp-metabox__title">Menu items</div>
                        <div class="tp-metabox__body space-y-5">
                            <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                                <div class="space-y-3 rounded-xl border border-black/10 bg-gradient-to-b from-white to-black/[0.02] p-4">
                                    <div class="space-y-1">
                                        <div class="text-sm font-semibold text-[#1d2327]">Custom link</div>
                                        <div class="tp-muted text-xs">Add any URL, including external destinations.</div>
                                    </div>
                                    <div class="space-y-2">
                                        <input class="tp-input" placeholder="Title" x-model="addTitle" />
                                        <input class="tp-input" placeholder="/path or https://..." x-model="addUrl" />
                                        <select class="tp-select" x-model="addTarget">
                                            <option value="">Same tab</option>
                                            <option value="_blank">New tab</option>
                                        </select>
                                        <button type="button" class="tp-button-secondary" @click="addCustom()">Add link</button>
                                    </div>
                                </div>

                                <div class="space-y-3 rounded-xl border border-black/10 bg-gradient-to-b from-white to-black/[0.02] p-4">
                                    <div class="space-y-1">
                                        <div class="text-sm font-semibold text-[#1d2327]">Add page</div>
                                        <div class="tp-muted text-xs">Link to a published page.</div>
                                    </div>
                                    @if (count($pagesArray) === 0)
                                        <div class="tp-muted rounded border border-dashed border-black/15 bg-white p-2 text-xs">No published pages available.</div>
                                    @else
                                        <div class="space-y-2">
                                            <select class="tp-select" x-model="selectedPageId">
                                                <option value="">Select a page...</option>
                                                <template x-for="page in pages" :key="page.id">
                                                    <option :value="page.id" x-text="page.title || 'Page #' + page.id"></option>
                                                </template>
                                            </select>
                                            <button type="button" class="tp-button-secondary" @click="addFromPage()">Add page</button>
                                        </div>
                                    @endif
                                </div>

                                <div class="space-y-3 rounded-xl border border-black/10 bg-gradient-to-b from-white to-black/[0.02] p-4">
                                    <div class="space-y-1">
                                        <div class="text-sm font-semibold text-[#1d2327]">Add post</div>
                                        <div class="tp-muted text-xs">Link to a published post.</div>
                                    </div>
                                    @if (count($postsArray) === 0)
                                        <div class="tp-muted rounded border border-dashed border-black/15 bg-white p-2 text-xs">No published posts available.</div>
                                    @else
                                        <div class="space-y-2">
                                            <select class="tp-select" x-model="selectedPostId">
                                                <option value="">Select a post...</option>
                                                <template x-for="post in posts" :key="post.id">
                                                    <option :value="post.id" x-text="post.title || 'Post #' + post.id"></option>
                                                </template>
                                            </select>
                                            <button type="button" class="tp-button-secondary" @click="addFromPost()">Add post</button>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <template x-if="items.length === 0">
                                <div class="tp-muted rounded border border-dashed border-black/15 bg-white p-4 text-sm">
                                    No menu items yet. Add a custom link, page, or post above.
                                </div>
                            </template>

                            <div class="space-y-3" x-show="items.length > 0" x-cloak>
                                <template x-for="(item, index) in items" :key="item._key">
                                    <div class="rounded-xl border border-black/10 bg-white p-4" :class="depthFor(item) > 0 ? 'border-sky-200 bg-sky-50/40' : ''">
                                        <input type="hidden" :name="`items[${index}][id]`" :value="item.id || ''" />

                                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                            <div class="flex items-center gap-2">
                                                <span class="rounded-full bg-black/5 px-2 py-0.5 text-xs font-semibold text-black/70" x-text="`#${index + 1}`"></span>
                                                <span class="rounded-full border border-black/10 bg-white px-2 py-0.5 text-xs text-black/70" x-text="itemType(item)"></span>
                                                <span class="tp-muted text-xs" x-show="depthFor(item) > 0" x-text="`Nested level ${depthFor(item)}`"></span>
                                            </div>
                                            <div class="tp-muted text-xs" x-show="parentLabel(item)" x-text="`Parent: ${parentLabel(item)}`"></div>
                                        </div>

                                        <div class="grid grid-cols-1 gap-3 xl:grid-cols-12">
                                            <div class="xl:col-span-3">
                                                <label class="tp-label">Title</label>
                                                <input class="tp-input" :name="`items[${index}][title]`" x-model="item.title" />
                                            </div>

                                            <div class="xl:col-span-5">
                                                <label class="tp-label">URL</label>
                                                <input class="tp-input" :name="`items[${index}][url]`" x-model="item.url" />
                                            </div>

                                            <div class="xl:col-span-2">
                                                <label class="tp-label">Target</label>
                                                <select class="tp-select" :name="`items[${index}][target]`" x-model="item.target">
                                                    <option value="">Same tab</option>
                                                    <option value="_blank">New tab</option>
                                                    <option value="_self">Same tab (_self)</option>
                                                </select>
                                            </div>

                                            <div class="xl:col-span-2">
                                                <label class="tp-label">Parent</label>
                                                <select class="tp-select" :name="`items[${index}][parent_id]`" x-model="item.parent_id">
                                                    <option value="">— None —</option>
                                                    <template x-for="parent in parentOptions(item.id)" :key="parent.id">
                                                        <option :value="parent.id" x-text="parent.title"></option>
                                                    </template>
                                                </select>
                                            </div>

                                            <div class="xl:col-span-2">
                                                <label class="tp-label">Order</label>
                                                <input
                                                    class="tp-input"
                                                    type="number"
                                                    :name="`items[${index}][sort_order]`"
                                                    x-model.number="item.sort_order" />
                                            </div>

                                            <div class="flex items-end gap-2 xl:col-span-10 xl:justify-end">
                                                <button type="button" class="tp-button-link" :disabled="index === 0" @click="move(index, -1)">
                                                    Move up
                                                </button>
                                                <button
                                                    type="button"
                                                    class="tp-button-link"
                                                    :disabled="index === items.length - 1"
                                                    @click="move(index, 1)">
                                                    Move down
                                                </button>
                                                <span class="text-slate-300">|</span>
                                                <button type="button" class="tp-button-link text-red-600 hover:text-red-700" @click="remove(index)">
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6 lg:sticky lg:top-6 lg:self-start">
                    <div class="tp-metabox">
                        <div class="tp-metabox__title">Actions</div>
                        <div class="tp-metabox__body space-y-2 text-sm">
                            <button type="submit" class="tp-button-primary w-full justify-center">Save Menu</button>
                            <a href="{{ route('tp.menus.index') }}" class="tp-button-secondary w-full justify-center">Back to menus</a>
                            <button type="submit" form="delete-menu-form" class="tp-button-danger w-full justify-center">Delete</button>
                            <div class="tp-muted text-xs">Location changes apply to the active theme.</div>
                        </div>
                    </div>

                    @if (count($locationsArray) > 0)
                        <div class="tp-metabox">
                            <div class="tp-metabox__title">Theme locations</div>
                            <div class="tp-metabox__body space-y-3 text-sm">
                                @foreach ($locationsArray as $loc)
                                    @php
                                        $key = isset($loc['key']) ? (string) $loc['key'] : '';
                                        $label = isset($loc['label']) ? (string) $loc['label'] : $key;
                                        $current = $assignments[$key] ?? null;
                                    @endphp

                                    @if ($key !== '')
                                        <div class="rounded border border-black/10 bg-white p-2.5">
                                            <div class="space-y-1">
                                                <label class="tp-label">{{ $label }}</label>
                                                <select name="locations[{{ $key }}]" class="tp-select">
                                                    <option value="">None</option>
                                                    @foreach ($menusArray as $menuOption)
                                                        <option value="{{ $menuOption['id'] }}" @selected((int) $current === (int) $menuOption['id'])>
                                                            {{ $menuOption['name'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="tp-code text-[11px]">{{ $key }}</div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if (count($locationsWithMenus) > 0)
                        <div class="tp-metabox">
                            <div class="tp-metabox__title">Assignments</div>
                            <div class="tp-metabox__body space-y-2 text-sm">
                                @foreach ($locationsWithMenus as $loc)
                                    @php
                                        $label = (string) ($loc['label'] ?? $loc['key'] ?? 'Location');
                                        $menuName = isset($loc['menu_name']) ? (string) $loc['menu_name'] : '';
                                    @endphp

                                    <div class="flex items-center justify-between gap-3 rounded border border-black/10 bg-white px-3 py-2">
                                        <div class="font-semibold">{{ $label }}</div>
                                        <div class="tp-muted text-xs">{{ $menuName !== '' ? $menuName : 'Not assigned' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </form>

        <form
            id="delete-menu-form"
            method="POST"
            action="{{ route('tp.menus.destroy', ['menu' => $menu->id]) }}"
            class="hidden"
            data-confirm="Delete this menu? This action cannot be undone.">
            @csrf
            @method('DELETE')
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('tpMenuEditor', (opts) => ({
                items: [],
                pages: Array.isArray(opts.pages) ? opts.pages : [],
                posts: Array.isArray(opts.posts) ? opts.posts : [],
                blogBase: typeof opts.blogBase === 'string' && opts.blogBase ? opts.blogBase : 'blog',

                addTitle: '',
                addUrl: '',
                addTarget: '',

                selectedPageId: '',
                selectedPostId: '',

                init() {
                    const initial = Array.isArray(opts.initialItems) ? opts.initialItems : [];
                    this.items = initial.map((item) => this.decorate(item));
                    this.sortItems();
                    this.syncSortOrders();
                },

                decorate(item) {
                    const out = item && typeof item === 'object' ? { ...item } : {};
                    out.id = Number.isFinite(parseInt(out.id)) ? parseInt(out.id) : null;
                    out.title = typeof out.title === 'string' ? out.title : '';
                    out.url = typeof out.url === 'string' ? out.url : '';
                    out.target = typeof out.target === 'string' ? out.target : '';
                    out.parent_id = Number.isFinite(parseInt(out.parent_id)) ? parseInt(out.parent_id) : '';
                    out.sort_order = Number.isFinite(parseInt(out.sort_order)) ? parseInt(out.sort_order) : 0;
                    out._key = out._key || `mi_${Math.random().toString(36).slice(2, 10)}_${Date.now()}`;
                    return out;
                },

                sortItems() {
                    this.items.sort((a, b) => {
                        const ao = Number.isFinite(parseInt(a.sort_order)) ? parseInt(a.sort_order) : 0;
                        const bo = Number.isFinite(parseInt(b.sort_order)) ? parseInt(b.sort_order) : 0;
                        if (ao !== bo) return ao - bo;
                        const at = (a.title || '').toLowerCase();
                        const bt = (b.title || '').toLowerCase();
                        return at.localeCompare(bt);
                    });
                },

                syncSortOrders() {
                    this.items = this.items.map((item, index) => ({
                        ...item,
                        sort_order: index * 10,
                    }));
                },

                move(index, delta) {
                    const next = index + delta;
                    if (next < 0 || next >= this.items.length) return;
                    const copy = [...this.items];
                    const [item] = copy.splice(index, 1);
                    copy.splice(next, 0, item);
                    this.items = copy;
                    this.syncSortOrders();
                },

                remove(index) {
                    const copy = [...this.items];
                    copy.splice(index, 1);
                    this.items = copy;
                    this.syncSortOrders();
                },

                addItem(item) {
                    this.items = [...this.items, this.decorate(item)];
                    this.syncSortOrders();
                },

                addCustom() {
                    const title = String(this.addTitle || '').trim();
                    const url = String(this.addUrl || '').trim();
                    if (!title && !url) return;
                    this.addItem({
                        id: null,
                        title: title || url,
                        url: url || '#',
                        target: this.addTarget || '',
                        parent_id: '',
                        sort_order: this.items.length * 10,
                    });
                    this.addTitle = '';
                    this.addUrl = '';
                    this.addTarget = '';
                },

                addFromPage() {
                    const id = parseInt(this.selectedPageId);
                    if (!Number.isFinite(id)) return;
                    const page = this.pages.find((p) => parseInt(p.id) === id);
                    if (!page) return;
                    const slug = String(page.slug || '').trim();
                    const url = slug ? `/${slug}` : '/';
                    this.addItem({
                        id: null,
                        title: page.title || url,
                        url,
                        target: '',
                        parent_id: '',
                        sort_order: this.items.length * 10,
                    });
                    this.selectedPageId = '';
                },

                addFromPost() {
                    const id = parseInt(this.selectedPostId);
                    if (!Number.isFinite(id)) return;
                    const post = this.posts.find((p) => parseInt(p.id) === id);
                    if (!post) return;
                    const slug = String(post.slug || '').trim();
                    if (!slug) return;
                    const base = String(this.blogBase || 'blog').replace(/^\/+|\/+$/g, '') || 'blog';
                    const url = `/${base}/${slug}`;
                    this.addItem({
                        id: null,
                        title: post.title || url,
                        url,
                        target: '',
                        parent_id: '',
                        sort_order: this.items.length * 10,
                    });
                    this.selectedPostId = '';
                },

                parentOptions(currentId) {
                    const id = Number.isFinite(parseInt(currentId)) ? parseInt(currentId) : null;
                    return this.items
                        .filter((item) => Number.isFinite(parseInt(item.id)) && parseInt(item.id) > 0)
                        .filter((item) => (id === null ? true : parseInt(item.id) !== id))
                        .map((item) => ({ id: parseInt(item.id), title: item.title || `Item #${item.id}` }));
                },

                parentLabel(item) {
                    if (!item || !Number.isFinite(parseInt(item.parent_id))) {
                        return '';
                    }

                    const parentId = parseInt(item.parent_id);
                    const parent = this.items.find((candidate) => parseInt(candidate.id) === parentId);
                    return parent ? parent.title || `Item #${parentId}` : '';
                },

                depthFor(item) {
                    const visited = new Set();
                    let depth = 0;
                    let parentId = Number.isFinite(parseInt(item.parent_id)) ? parseInt(item.parent_id) : null;

                    while (parentId !== null && depth < 6) {
                        if (visited.has(parentId)) {
                            break;
                        }

                        visited.add(parentId);

                        const parent = this.items.find((candidate) => parseInt(candidate.id) === parentId);
                        if (!parent) {
                            break;
                        }

                        depth += 1;
                        parentId = Number.isFinite(parseInt(parent.parent_id)) ? parseInt(parent.parent_id) : null;
                    }

                    return depth;
                },

                itemType(item) {
                    const url = String(item?.url || '').trim();
                    if (url === '') {
                        return 'Link';
                    }

                    if (/^https?:\/\//i.test(url)) {
                        return 'External';
                    }

                    const base = String(this.blogBase || 'blog').replace(/^\/+|\/+$/g, '');
                    if (base !== '' && (url === `/${base}` || url.startsWith(`/${base}/`))) {
                        return 'Post';
                    }

                    return 'Page';
                },
            }));
        });
    </script>
@endpush
