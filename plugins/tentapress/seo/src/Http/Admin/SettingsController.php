<?php

declare(strict_types=1);

namespace TentaPress\Seo\Http\Admin;

use Illuminate\Http\Request;
use TentaPress\Seo\Services\SeoSettings;

final class SettingsController
{
    public function __invoke(Request $request, SeoSettings $settings)
    {
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'title_template' => ['required', 'string', 'max:255'],
                'default_description' => ['nullable', 'string', 'max:2000'],
                'default_robots' => ['required', 'string', 'max:255'],
                'canonical_base' => ['nullable', 'string', 'max:255'],
                'blog_title' => ['nullable', 'string', 'max:255'],
                'blog_description' => ['nullable', 'string', 'max:2000'],
            ]);

            $settings->set('seo.title_template', (string) $data['title_template']);
            $settings->set('seo.default_description', (string) ($data['default_description'] ?? ''));
            $settings->set('seo.default_robots', (string) $data['default_robots']);
            $settings->set('seo.canonical_base', rtrim((string) ($data['canonical_base'] ?? ''), '/'));
            $settings->set('seo.blog_title', (string) ($data['blog_title'] ?? ''));
            $settings->set('seo.blog_description', (string) ($data['blog_description'] ?? ''));

            return to_route('tp.seo.settings')
                ->with('tp_notice_success', 'SEO settings saved.');
        }

        return view('tentapress-seo::settings', [
            'titleTemplate' => $settings->titleTemplate(),
            'defaultDescription' => $settings->defaultDescription(),
            'defaultRobots' => $settings->defaultRobots(),
            'canonicalBase' => $settings->canonicalBase(),
            'blogTitle' => $settings->blogTitle(),
            'blogDescription' => $settings->blogDescription(),
        ]);
    }
}
