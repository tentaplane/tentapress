<div class="tp-panel space-y-3">
    <div class="flex items-center justify-between gap-3">
        <div>
            <p class="text-sm font-semibold">Pexels</p>
            <p class="text-xs text-black/60">Photos, videos</p>
        </div>
        <label class="inline-flex items-center gap-2 text-sm">
            <input
                type="checkbox"
                name="stock_pexels_enabled"
                value="1"
                class="tp-checkbox"
                @checked(old('stock_pexels_enabled', $stockPexelsEnabled) === '1') />
            Enabled
        </label>
    </div>

    <div class="tp-field">
        <label class="tp-label">API key</label>
        <input
            type="password"
            name="stock_pexels_key"
            class="tp-input"
            value="{{ old('stock_pexels_key', $stockPexelsKey) }}" />
        <div class="tp-help">
            <a class="tp-button-link" href="https://www.pexels.com/api/key/" target="_blank" rel="noopener">Your Pexels API Key</a>
        </div>
    </div>

    <label class="inline-flex items-center gap-2 text-sm">
        <input
            type="checkbox"
            name="stock_pexels_video_enabled"
            value="1"
            class="tp-checkbox"
            @checked(old('stock_pexels_video_enabled', $stockPexelsVideoEnabled) === '1') />
        Enable video results
    </label>
</div>
