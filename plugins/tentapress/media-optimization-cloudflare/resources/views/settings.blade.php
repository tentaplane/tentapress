<section class="space-y-3 rounded-xl border border-black/10 bg-white p-4">
    <div>
        <h2 class="text-sm font-semibold text-[#1d2327]">Cloudflare Images</h2>
        <p class="text-xs text-black/60">
            Configure Cloudflare Image Resizing defaults. Learn more in the
            <a class="tp-button-link" href="https://developers.cloudflare.com/images/transform-images/" target="_blank" rel="noopener">
                Cloudflare API docs
            </a>.
        </p>
    </div>

    <label class="inline-flex items-center gap-2 text-sm">
        <input
            type="checkbox"
            name="optimization_cloudflare_enabled"
            value="1"
            class="tp-checkbox"
            @checked(old('optimization_cloudflare_enabled', $optimizationCloudflareEnabled ?? '0') === '1') />
        Enable Cloudflare optimization
    </label>

    <div class="grid gap-3 sm:grid-cols-2">
        <label class="block text-sm">
            <span class="font-semibold text-[#1d2327]">Zone host (optional)</span>
            <input
                type="text"
                name="optimization_cloudflare_host"
                class="tp-input mt-1"
                placeholder="media.example.com"
                value="{{ old('optimization_cloudflare_host', $optimizationCloudflareHost ?? '') }}" />
            <span class="tp-help">Leave blank to use the media URL host.</span>
        </label>

        <label class="block text-sm">
            <span class="font-semibold text-[#1d2327]">URL mode</span>
            <select name="optimization_cloudflare_mode" class="tp-select mt-1">
                @php($mode = old('optimization_cloudflare_mode', $optimizationCloudflareMode ?? 'auto'))
                <option value="auto" @selected($mode === 'auto')>Auto</option>
                <option value="path" @selected($mode === 'path')>Path</option>
                <option value="absolute" @selected($mode === 'absolute')>Absolute</option>
            </select>
            <span class="tp-help">Auto chooses path for same host, absolute otherwise.</span>
        </label>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <label class="block text-sm">
            <span class="font-semibold text-[#1d2327]">Default format</span>
            <input
                type="text"
                name="optimization_cloudflare_default_format"
                class="tp-input mt-1"
                placeholder="auto"
                value="{{ old('optimization_cloudflare_default_format', $optimizationCloudflareDefaultFormat ?? 'auto') }}" />
        </label>

        <label class="block text-sm">
            <span class="font-semibold text-[#1d2327]">Default quality</span>
            <input
                type="text"
                name="optimization_cloudflare_default_quality"
                class="tp-input mt-1"
                placeholder="80"
                value="{{ old('optimization_cloudflare_default_quality', $optimizationCloudflareDefaultQuality ?? '80') }}" />
        </label>

        <label class="block text-sm">
            <span class="font-semibold text-[#1d2327]">Default fit</span>
            <input
                type="text"
                name="optimization_cloudflare_default_fit"
                class="tp-input mt-1"
                placeholder="scale-down"
                value="{{ old('optimization_cloudflare_default_fit', $optimizationCloudflareDefaultFit ?? 'scale-down') }}" />
        </label>

        <label class="block text-sm">
            <span class="font-semibold text-[#1d2327]">Default DPR</span>
            <input
                type="text"
                name="optimization_cloudflare_default_dpr"
                class="tp-input mt-1"
                placeholder="1"
                value="{{ old('optimization_cloudflare_default_dpr', $optimizationCloudflareDefaultDpr ?? '1') }}" />
        </label>
    </div>
</section>
