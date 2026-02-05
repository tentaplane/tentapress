<div class="tp-metabox">
    <div class="tp-metabox__body">
        <h2 class="text-base font-semibold">System</h2>
        <dl class="mt-3 space-y-2 text-sm">
            @foreach ($info as $item)
                <div class="flex justify-between gap-3">
                    <dt class="text-black/60">{{ $item['label'] }}</dt>
                    <dd class="font-mono text-black/80">{{ $item['value'] }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
</div>
