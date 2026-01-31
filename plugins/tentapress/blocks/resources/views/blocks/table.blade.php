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
            if ($row === false) continue;
            $rows[] = $row;
        }
    }

    if ($rows !== []) {
        $headers = array_shift($rows);
    }
@endphp

@if ($headers !== [])
    <section class="py-10">
        <div class="mx-auto max-w-5xl space-y-4 px-6">
            @if ($title !== '')
                <h2 class="text-xl font-semibold">{{ $title }}</h2>
            @endif
            <div class="overflow-hidden rounded-xl border border-black/10 bg-white">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.08em] text-black/60">
                        <tr>
                            @foreach ($headers as $head)
                                <th class="px-4 py-3 font-semibold">{{ trim((string) $head) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr class="{{ $striped && $loop->odd ? 'bg-slate-50' : '' }}">
                                @foreach ($headers as $i => $head)
                                    <td class="px-4 py-3">
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
