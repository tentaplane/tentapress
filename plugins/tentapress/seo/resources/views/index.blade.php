@extends('tentapress-admin::layouts.shell')

@section('title', 'SEO')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">SEO</h1>
            <p class="tp-description">Manage page SEO and defaults.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('tp.seo.settings') }}" class="tp-button-secondary">Settings</a>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__title">Pages</div>

        @if (empty($pages))
            <div class="tp-metabox__body tp-muted text-sm">No pages found.</div>
        @else
            <div class="tp-table-wrap">
                <table class="tp-table">
                    <thead class="tp-table__thead">
                        <tr>
                            <th class="tp-table__th">ID</th>
                            <th class="tp-table__th">Title</th>
                            <th class="tp-table__th">Slug</th>
                            <th class="tp-table__th">Custom SEO</th>
                            <th class="tp-table__th text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="tp-table__tbody">
                        @foreach ($pages as $p)
                            @php
                                $id = (int) $p->id;
                                $title = (string) ($p->title ?? '');
                                $slug = (string) ($p->slug ?? '');
                                $custom = !empty($hasSeo[$id]);
                            @endphp

                            <tr class="tp-table__row">
                                <td class="tp-table__td">{{ $id }}</td>
                                <td class="tp-table__td">
                                    <div class="font-semibold">
                                        <a
                                            class="tp-button-link"
                                            href="{{ route('tp.seo.pages.edit', ['page' => $id]) }}">
                                            {{ $title !== '' ? $title : '(Untitled)' }}
                                        </a>
                                    </div>
                                </td>
                                <td class="tp-table__td">/{{ $slug }}</td>
                                <td class="tp-table__td">
                                    @if ($custom)
                                        <span class="tp-notice-success mb-0 inline-block px-3 py-1">Yes</span>
                                    @else
                                        <span class="tp-muted">No</span>
                                    @endif
                                </td>
                                <td class="tp-table__td">
                                    <div class="flex justify-end gap-3 text-xs text-slate-600">
                                        <a
                                            class="tp-button-link hover:text-slate-900"
                                            href="{{ route('tp.seo.pages.edit', ['page' => $id]) }}">
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
