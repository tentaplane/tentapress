<section class="space-y-3 rounded-xl border border-black/10 bg-white p-4">
    <div>
        <h2 class="text-sm font-semibold text-[#1d2327]">Imgix</h2>
        <p class="text-xs text-black/60">
            Configure the Imgix source domain used for optimized URLs.
        </p>
    </div>

    <label class="inline-flex items-center gap-2 text-sm">
        <input
            type="checkbox"
            name="optimization_imgix_enabled"
            value="1"
            class="tp-checkbox"
            @checked(old('optimization_imgix_enabled', $optimizationImgixEnabled ?? '0') === '1') />
        Enable Imgix optimization
    </label>

    <label class="block text-sm">
        <span class="font-semibold text-[#1d2327]">Imgix domain</span>
        <input
            type="text"
            name="optimization_imgix_host"
            class="tp-input mt-1"
            placeholder="example.imgix.net"
            value="{{ old('optimization_imgix_host', $optimizationImgixHost ?? '') }}" />
        <span class="tp-help">Use the domain configured for your Imgix source.</span>
    </label>
</section>
