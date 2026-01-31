@extends('tentapress-admin::layouts.shell')

@section('title', 'SEO Settings')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">SEO Settings</h1>
            <p class="tp-description">Set global defaults for title, description and robots.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('tp.seo.index') }}" class="tp-button-secondary">Back</a>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__body">
            <form method="POST" action="{{ route('tp.seo.settings.update') }}" class="space-y-5">
                @csrf

                <div class="tp-field">
                    <label class="tp-label">Title template</label>
                    <input
                        name="title_template"
                        class="tp-input"
                        value="{{ old('title_template', $titleTemplate) }}"
                        required />
                </div>

                <div class="tp-field">
                    <label class="tp-label">Default description</label>
                    <textarea name="default_description" class="tp-textarea" rows="4">
{{ old('default_description', $defaultDescription) }}
                    </textarea>
                </div>

                <div class="tp-field">
                    <label class="tp-label">Default robots</label>
                    <input
                        name="default_robots"
                        class="tp-input"
                        value="{{ old('default_robots', $defaultRobots) }}"
                        required />
                    <div class="tp-help">
                        Example:
                        <code class="tp-code">index,follow</code>
                        or
                        <code class="tp-code">noindex,nofollow</code>
                        .
                    </div>
                </div>

                <div class="tp-field">
                    <label class="tp-label">Canonical base URL (optional)</label>
                    <input
                        name="canonical_base"
                        class="tp-input"
                        value="{{ old('canonical_base', $canonicalBase) }}" />
                    <div class="tp-help">
                        If set, canonical becomes
                        <code class="tp-code">base/slug</code>
                        unless overridden per page.
                    </div>
                </div>

                <div class="tp-field">
                    <label class="tp-label">Blog title (optional)</label>
                    <input name="blog_title" class="tp-input" value="{{ old('blog_title', $blogTitle) }}" />
                    <div class="tp-help">Used for the blog index page title when set.</div>
                </div>

                <div class="tp-field">
                    <label class="tp-label">Blog description (optional)</label>
                    <textarea name="blog_description" class="tp-textarea" rows="4">
{{ old('blog_description', $blogDescription) }}
                    </textarea>
                    <div class="tp-help">Used for the blog index page description when set.</div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="tp-button-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection
