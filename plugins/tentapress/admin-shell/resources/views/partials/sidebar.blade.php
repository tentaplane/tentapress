<aside
    class="fixed inset-y-0 left-0 z-40 h-screen w-64 -translate-x-full overflow-y-auto bg-[#1d2327] text-white transition-transform md:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : ''">
    <div class="flex h-14 items-center border-b border-white/10 px-4">
        <a href="{{ url('/admin') }}" class="flex items-center gap-2 text-sm hover:text-white/90">
            <span
                class="inline-flex h-6 w-6 items-center justify-center rounded bg-linear-to-br from-[#2b7bc7] to-[#1f6fb6] text-xs text-white shadow-sm">
                TP
            </span>
            <span class="text-base">TentaPress</span>
        </a>
    </div>

    <nav class="space-y-1 px-2 py-3">
        @foreach ($tpMenu as $item)
            @php
                $hasChildren = !empty($item['children']) && is_array($item['children']);
                $isActive = !empty($item['active']);
                $url = $item['url'] ?? null;
                $label = (string) ($item['label'] ?? 'Menu');
            @endphp

            @if (!$hasChildren)
                <a
                    href="{{ $url ?: '#' }}"
                    class="{{ $isActive ? 'bg-white/10 text-white' : 'text-white/75 hover:bg-white/5 hover:text-white' }} block rounded px-3 py-2 text-sm font-semibold transition">
                    {{ $label }}
                </a>
            @else
                <div x-data="{ open: {{ $isActive ? 'true' : 'false' }} }">
                    <div class="flex items-center gap-1">
                        <a
                            href="{{ $url ?: '#' }}"
                            class="{{ $isActive ? 'bg-white/10 text-white' : 'text-white/75 hover:bg-white/5 hover:text-white' }} block flex-1 rounded px-3 py-2 text-sm font-semibold transition">
                            {{ $label }}
                        </a>

                        <button
                            type="button"
                            @click="open = !open"
                            class="rounded px-2 py-2 text-white/60 transition hover:bg-white/5 hover:text-white"
                            aria-label="Show or hide {{ $label }}">
                            <span x-text="open ? '–' : '+'"></span>
                        </button>
                    </div>

                    <div x-show="open" x-cloak class="mt-1 space-y-1 pl-3">
                        @foreach ($item['children'] as $child)
                            @php
                                $childActive = !empty($child['active']);
                                $childUrl = $child['url'] ?? null;
                                $childLabel = (string) ($child['label'] ?? 'Item');
                            @endphp

                            <a
                                href="{{ $childUrl ?: '#' }}"
                                class="{{ $childActive ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }} block rounded px-3 py-2 text-sm transition">
                                {{ $childLabel }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </nav>
</aside>
