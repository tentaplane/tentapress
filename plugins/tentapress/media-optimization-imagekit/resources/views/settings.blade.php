<section class="space-y-3 rounded-xl border border-black/10 bg-white p-4">
    <div>
        <h2 class="text-sm font-semibold text-[#1d2327]">ImageKit</h2>
        <p class="text-xs text-black/60">
            Configure the ImageKit URL endpoint used for optimized images.
        </p>
    </div>

    <label class="inline-flex items-center gap-2 text-sm">
        <input
            type="checkbox"
            name="optimization_imagekit_enabled"
            value="1"
            class="tp-checkbox"
            @checked(old('optimization_imagekit_enabled', $optimizationImageKitEnabled ?? '0') === '1') />
        Enable ImageKit optimization
    </label>

    <label class="block text-sm">
        <span class="font-semibold text-[#1d2327]">URL endpoint</span>
        <input
            type="text"
            name="optimization_imagekit_endpoint"
            class="tp-input mt-1"
            placeholder="https://ik.imagekit.io/your_id"
            value="{{ old('optimization_imagekit_endpoint', $optimizationImageKitEndpoint ?? '') }}" />
        <span class="tp-help">Use the ImageKit URL endpoint from your dashboard.</span>
    </label>
</section>
