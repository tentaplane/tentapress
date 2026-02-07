@extends('tentapress-admin::layouts.shell')

@section('title', 'Create Menu')

@section('content')
    @php
        $locationCount = count($tpMenuLocations ?? []);
    @endphp

    <div class="tp-editor space-y-6">
        <div class="tp-page-header">
            <div>
                <h1 class="tp-page-title">Create Menu</h1>
                <p class="tp-description">Create a navigation menu for your site.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
            <div class="space-y-6 lg:col-span-3">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-black/10 bg-white px-4 py-3">
                        <div class="tp-muted text-xs uppercase tracking-wide">Ready to create</div>
                        <div class="mt-2 text-base font-semibold text-[#1d2327]">Menu details</div>
                    </div>
                    <div class="rounded-xl border border-black/10 bg-white px-4 py-3">
                        <div class="tp-muted text-xs uppercase tracking-wide">Theme locations</div>
                        <div class="mt-2 text-base font-semibold text-[#1d2327]">{{ $locationCount }} available</div>
                    </div>
                </div>

                <div class="tp-metabox">
                    <div class="tp-metabox__title">Menu details</div>
                    <div class="tp-metabox__body">
                        <form method="POST" action="{{ route('tp.menus.store') }}" class="space-y-4" id="menu-form">
                            @csrf

                            <div class="tp-field">
                                <label class="tp-label">Name</label>
                                <input name="name" class="tp-input" value="{{ old('name', $menu->name) }}" required />
                            </div>

                            <div class="tp-field">
                                <label class="tp-label">Menu key</label>
                                <input
                                    name="slug"
                                    class="tp-input"
                                    value="{{ old('slug', $menu->slug) }}"
                                    placeholder="auto-generated"
                                    pattern="[a-z0-9-]+" />
                                <div class="tp-help">Lowercase letters, numbers, and dashes only.</div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="space-y-6 lg:sticky lg:top-6 lg:self-start">
                <div class="tp-metabox">
                    <div class="tp-metabox__title">Actions</div>
                    <div class="tp-metabox__body space-y-2 text-sm">
                        <button type="submit" form="menu-form" class="tp-button-primary w-full justify-center">Create Menu</button>
                        <a href="{{ route('tp.menus.index') }}" class="tp-button-secondary w-full justify-center">Back to menus</a>
                    </div>
                </div>

                @if (!empty($tpMenuLocations))
                    <div class="tp-metabox">
                        <div class="tp-metabox__title">Theme locations</div>
                        <div class="tp-metabox__body space-y-2 text-sm">
                            @foreach ($tpMenuLocations as $loc)
                                @php
                                    $label = (string) ($loc['label'] ?? $loc['key'] ?? 'Location');
                                    $key = (string) ($loc['key'] ?? '');
                                @endphp

                                @if ($key !== '')
                                    <div class="rounded border border-black/10 bg-white px-3 py-2">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="font-semibold text-[#1d2327]">{{ $label }}</div>
                                            <div class="tp-code text-[11px]">{{ $key }}</div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            <div class="tp-muted pt-2 text-xs">You can assign menus to these locations after saving.</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
