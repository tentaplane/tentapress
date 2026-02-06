<div class="tp-panel space-y-3">
    <div class="flex items-center justify-between gap-3">
        <div>
            <p class="text-sm font-semibold">Unsplash</p>
            <p class="text-xs text-black/60">Photos with required attribution</p>
        </div>
        <label class="inline-flex items-center gap-2 text-sm">
            <input
                type="checkbox"
                name="stock_unsplash_enabled"
                value="1"
                class="tp-checkbox"
                @checked(old('stock_unsplash_enabled', $stockUnsplashEnabled) === '1') />
            Enable source
        </label>
    </div>

    <div class="tp-field">
        <label class="tp-label">Access key</label>
        <input
            type="password"
            name="stock_unsplash_key"
            class="tp-input"
            value="{{ old('stock_unsplash_key', $stockUnsplashKey) }}" />
        <div class="tp-help mt-1">
            Create an Unsplash app and paste your Access Key.
            <br /><a class="tp-button-link" href="https://unsplash.com/documentation#creating-a-developer-account" target="_blank" rel="noopener">Unsplash documentation</a>
        </div>
    </div>
</div>
