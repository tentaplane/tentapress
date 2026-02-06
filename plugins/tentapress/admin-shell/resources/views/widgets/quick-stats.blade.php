@if (count($stats) > 0)
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        @foreach ($stats as $stat)
            @if ($stat['route'])
                <a href="{{ route($stat['route']) }}" class="tp-metabox transition hover:border-[#2271b1]/30">
                    <div class="tp-metabox__body py-4 text-center">
                        <div class="text-3xl font-bold text-[#2271b1]">{{ number_format($stat['value']) }}</div>
                        <div class="mt-1 text-sm text-black/60">{{ $stat['label'] }}</div>
                    </div>
                </a>
            @else
                <div class="tp-metabox">
                    <div class="tp-metabox__body py-4 text-center">
                        <div class="text-3xl font-bold text-[#2271b1]">{{ number_format($stat['value']) }}</div>
                        <div class="mt-1 text-sm text-black/60">{{ $stat['label'] }}</div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@else
    <div class="tp-metabox">
        <div class="tp-metabox__body py-6 text-center text-black/50">
            <p>Enable content plugins to see dashboard stats.</p>
        </div>
    </div>
@endif
