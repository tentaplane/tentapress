@extends('tentapress-admin::layouts.shell')

@section('title', 'System information')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">System information</h1>
            <p class="tp-description">Technical details that can help when troubleshooting your site.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('tp.system-info.diagnostics') }}" class="tp-button-secondary">Download report</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        <div class="tp-metabox">
            <div class="tp-metabox__title">TentaPress</div>
            <div class="tp-metabox__body space-y-3">
                <div>
                    <div class="tp-muted text-xs font-semibold uppercase">Active theme</div>
                    <div class="tp-code mt-1">{{ $report['tentapress']['active_theme'] ?? '—' }}</div>
                </div>

                <div class="tp-divider"></div>

                <div>
                    <div class="tp-muted text-xs font-semibold uppercase">Enabled plugins</div>
                    @if (count($report['tentapress']['enabled_plugins'] ?? []) === 0)
                        <div class="tp-muted mt-2 text-sm">No plugins are currently enabled.</div>
                    @else
                        <div class="tp-table-wrap mt-2">
                            <table class="tp-table">
                                <thead class="tp-table__thead">
                                    <tr>
                                        <th class="tp-table__th">Plugin</th>
                                        <th class="tp-table__th">Version</th>
                                    </tr>
                                </thead>
                                <tbody class="tp-table__tbody">
                                    @foreach (($report['tentapress']['enabled_plugins'] ?? []) as $p)
                                        <tr class="tp-table__row">
                                            <td class="tp-table__td">
                                                <div class="font-semibold">{{ $p['id'] }}</div>
                                                <div class="tp-muted text-xs">{{ $p['path'] }}</div>
                                            </td>
                                            <td class="tp-table__td tp-code">{{ $p['version'] ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="tp-metabox">
            <div class="tp-metabox__title">Laravel</div>
            <div class="tp-metabox__body space-y-3">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">Version</div>
                        <div class="tp-code mt-1">{{ $report['laravel']['laravel_version'] ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">Environment</div>
                        <div class="tp-code mt-1">{{ $report['laravel']['app_env'] ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">Debug mode</div>
                        <div class="tp-code mt-1">
                            {{ !empty($report['laravel']['app_debug']) ? 'true' : 'false' }}
                        </div>
                    </div>
                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">App URL</div>
                        <div class="tp-code mt-1 break-all">{{ $report['laravel']['app_url'] ?? '—' }}</div>
                    </div>
                </div>

                <div class="tp-divider"></div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">Database</div>
                        <div class="tp-code mt-1">{{ $report['runtime']['database']['default'] ?? '—' }}</div>
                        <div class="tp-muted text-xs">
                            Driver: {{ $report['runtime']['database']['driver'] ?? '—' }}
                        </div>
                    </div>
                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">Cache</div>
                        <div class="tp-code mt-1">{{ $report['runtime']['cache']['default'] ?? '—' }}</div>
                        <div class="tp-muted text-xs">
                            Session: {{ $report['runtime']['session']['driver'] ?? '—' }}
                        </div>
                    </div>
                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">Queue</div>
                        <div class="tp-code mt-1">{{ $report['runtime']['queue']['default'] ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tp-metabox">
            <div class="tp-metabox__title">PHP</div>
            <div class="tp-metabox__body space-y-4">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">Version</div>
                        <div class="tp-code mt-1">{{ $report['php']['php_version'] ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">SAPI</div>
                        <div class="tp-code mt-1">{{ $report['php']['sapi'] ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">Memory limit</div>
                        <div class="tp-code mt-1">{{ $report['php']['memory_limit'] ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">Max execution</div>
                        <div class="tp-code mt-1">{{ $report['php']['max_execution_time'] ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">Max upload size</div>
                        <div class="tp-code mt-1">{{ $report['php']['upload_max_filesize'] ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="tp-muted text-xs font-semibold uppercase">Max post size</div>
                        <div class="tp-code mt-1">{{ $report['php']['post_max_size'] ?? '—' }}</div>
                    </div>
                </div>

                <div class="tp-divider"></div>

                <div>
                    <div class="tp-muted text-xs font-semibold uppercase">Key extensions</div>
                    <div class="tp-table-wrap mt-2">
                        <table class="tp-table">
                            <thead class="tp-table__thead">
                                <tr>
                                    <th class="tp-table__th">Extension</th>
                                    <th class="tp-table__th">Available</th>
                                </tr>
                            </thead>
                            <tbody class="tp-table__tbody">
                                @foreach (($report['php']['extensions'] ?? []) as $ext)
                                    <tr class="tp-table__row">
                                        <td class="tp-table__td tp-code">{{ $ext['key'] }}</td>
                                        <td class="tp-table__td">
                                            @if (!empty($ext['loaded']))
                                                <span class="tp-notice-success mb-0 inline-block px-2 py-1 text-xs">
                                                    Yes
                                                </span>
                                            @else
                                                <span class="tp-notice-error mb-0 inline-block px-2 py-1 text-xs">
                                                    No
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tp-metabox">
            <div class="tp-metabox__title">Paths and storage</div>
            <div class="tp-metabox__body space-y-3">
                <div>
                    <div class="tp-muted text-xs font-semibold uppercase">Storage</div>
                    <div class="tp-code mt-1 break-all">{{ $report['storage']['storage_path'] ?? '—' }}</div>
                    <div class="tp-muted mt-1 text-xs">
                        Writable: {{ !empty($report['storage']['storage_writable']) ? 'Yes' : 'No' }}
                    </div>
                </div>

                <div class="tp-divider"></div>

                <div>
                    <div class="tp-muted text-xs font-semibold uppercase">Bootstrap cache</div>
                    <div class="tp-code mt-1 break-all">{{ $report['storage']['bootstrap_cache_path'] ?? '—' }}</div>
                    <div class="tp-muted mt-1 text-xs">
                        Writable: {{ !empty($report['storage']['bootstrap_cache_writable']) ? 'Yes' : 'No' }}
                    </div>
                </div>

                <div class="tp-divider"></div>

                <div>
                    <div class="tp-muted text-xs font-semibold uppercase">Base path</div>
                    <div class="tp-code mt-1 break-all">{{ $report['paths']['base_path'] ?? '—' }}</div>
                </div>

                <div>
                    <div class="tp-muted text-xs font-semibold uppercase">Public path</div>
                    <div class="tp-code mt-1 break-all">{{ $report['paths']['public_path'] ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
