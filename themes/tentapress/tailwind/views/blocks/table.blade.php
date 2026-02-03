@php
    $title = (string) ($props['title'] ?? '');
    $raw = (string) ($props['data'] ?? '');
    $striped = filter_var($props['striped'] ?? true, FILTER_VALIDATE_BOOL);

    $rows = [];
    $headers = [];

    if (trim($raw) !== '') {
        $lines = preg_split('/\r?\n/', trim($raw)) ?: [];
        foreach ($lines as $line) {
            $row = str_getcsv($line);
            if ($row === false) {
                continue;
            }
            $rows[] = $row;
        }
    }

    if ($rows !== []) {
        $headers = array_shift($rows);
    }
@endphp

@if ($headers !== [])
    <section class="py-12 sm:py-16">
        <div class="mx-auto max-w-6xl space-y-4 px-6">
            @if ($title !== '')
                <h2 class="font-display text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">
                    {{ $title }}
                </h2>
            @endif
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                        <tr>
                            @foreach ($headers as $head)
                                <th class="px-5 py-3">{{ trim((string) $head) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr class="{{ $striped && $loop->odd ? 'bg-slate-50/60' : '' }}">
                                @foreach ($headers as $i => $head)
                                    <td class="px-5 py-3 text-slate-600">
                                        {{ isset($row[$i]) ? trim((string) $row[$i]) : '' }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endif
