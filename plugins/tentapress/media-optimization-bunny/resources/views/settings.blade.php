<section class="space-y-3 rounded-xl border border-black/10 bg-white p-4">
    <div>
        <h2 class="text-sm font-semibold text-[#1d2327]">Bunny Optimizer</h2>
        <p class="text-xs text-black/60">
            Configure your Bunny Optimizer host for image transformations.
        </p>
    </div>

    <label class="inline-flex items-center gap-2 text-sm">
        <input
            type="checkbox"
            name="optimization_bunny_enabled"
            value="1"
            class="tp-checkbox"
            @checked(old('optimization_bunny_enabled', $optimizationBunnyEnabled ?? '0') === '1') />
        Enable Bunny optimization
    </label>

    <label class="block text-sm">
        <span class="font-semibold text-[#1d2327]">Optimizer host</span>
        <input
            type="text"
            name="optimization_bunny_host"
            class="tp-input mt-1"
            placeholder="example.b-cdn.net"
            value="{{ old('optimization_bunny_host', $optimizationBunnyHost ?? '') }}" />
        <span class="tp-help">Use your Bunny CDN pull zone domain.</span>
    </label>
</section>
