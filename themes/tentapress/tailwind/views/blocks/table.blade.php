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
    <section class="py-16 sm:py-20">
        <div class="mx-auto max-w-7xl space-y-5 px-6">
            @if ($title !== '')
                <h2 class="font-display text-3xl font-semibold text-surface-900 sm:text-4xl">
                    {{ $title }}
                </h2>
            @endif
            <div class="overflow-hidden rounded-[2.5rem] border border-black/[0.08] bg-white">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-black/[0.08] bg-surface-50 text-xs font-semibold uppercase tracking-[0.15em] text-surface-500">
                        <tr>
                            @foreach ($headers as $head)
                                <th class="px-6 py-3.5">{{ trim((string) $head) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100">
                        @foreach ($rows as $row)
                            <tr class="{{ $striped && $loop->odd ? 'bg-surface-50/50' : '' }}">
                                @foreach ($headers as $i => $head)
                                    <td class="px-6 py-3.5 text-surface-700">
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
