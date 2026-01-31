@extends('tentapress-admin::layouts.shell')

@section('title', 'Settings')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Settings</h1>
            <p class="tp-description">Basic site configuration.</p>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__body">
            <form method="POST" action="{{ route('tp.settings.update') }}" class="space-y-5">
                @csrf

                <div class="tp-field">
                    <label class="tp-label">Site title</label>
                    <input name="site_title" class="tp-input" value="{{ old('site_title', $siteTitle) }}" />
                </div>

                <div class="tp-field">
                    <label class="tp-label">Tagline</label>
                    <input name="tagline" class="tp-input" value="{{ old('tagline', $tagline) }}" />
                </div>

                <div class="tp-field">
                    <label class="tp-label">Home page</label>
                    <select name="home_page_id" class="tp-select">
                        <option value="">— Use default —</option>
                        @foreach ($pages as $p)
                            @php
                                $id = (int) $p->id;
                                $selected = (string) $id === (string) old('home_page_id', $homePageId);
                            @endphp

                            <option value="{{ $id }}" @selected($selected)>
                                {{ $p->title ?: '(Untitled)' }} — /{{ $p->slug }}
                            </option>
                        @endforeach
                    </select>
                    <div class="tp-help">Select a page to use as the site homepage.</div>
                </div>

                <div class="tp-field">
                    <label class="tp-label">Blog base (optional)</label>
                    <input name="blog_base" class="tp-input" value="{{ old('blog_base', $blogBase) }}" />
                    <div class="tp-help">URL slug for the blog index. Default is <code class="tp-code">blog</code>.</div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="tp-button-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection
