@php
    $normalizer = app(\TentaPress\Forms\Services\FormConfigNormalizer::class);
    $signer = app(\TentaPress\Forms\Services\FormPayloadSigner::class);

    $rawFormKey = trim((string) ($props['form_key'] ?? 'signup'));
    if ($rawFormKey === '') {
        $rawFormKey = 'signup';
    }

    $routeFormKey = strtolower(trim((string) preg_replace('/[^A-Za-z0-9._-]/', '-', $rawFormKey), '-'));
    if ($routeFormKey === '') {
        $routeFormKey = 'signup';
    }

    $title = (string) ($props['title'] ?? '');
    $description = (string) ($props['description'] ?? '');
    $submitLabel = (string) ($props['submit_label'] ?? 'Submit');
    $privacyNotice = (string) ($props['privacy_notice'] ?? '');

    $provider = $normalizer->normalizeProvider($props['provider'] ?? 'mailchimp');
    $fields = $normalizer->normalizeFields($props['fields'] ?? []);
    $providerConfig = $normalizer->normalizeProviderConfig(is_array($props) ? $props : []);

    $payloadToken = $signer->sign([
        'provider' => $provider,
        'fields' => $fields,
        'provider_config' => $providerConfig,
        'success_message' => (string) ($props['success_message'] ?? ''),
        'error_message' => (string) ($props['error_message'] ?? ''),
        'redirect_url' => (string) ($props['redirect_url'] ?? ''),
    ]);

    $statusKey = 'tp_forms.status.'.(string) preg_replace('/[^a-z0-9_-]/', '', $routeFormKey);
    $status = session($statusKey);
@endphp

<section id="tp-form-{{ $routeFormKey }}" class="py-12">
    <div class="mx-auto max-w-3xl space-y-5 rounded-xl border border-black/10 bg-white p-8">
        @if (is_array($status) && ($status['type'] ?? '') === 'success' && trim((string) ($status['message'] ?? '')) !== '')
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                {{ (string) ($status['message'] ?? '') }}
            </div>
        @endif

        @if (is_array($status) && ($status['type'] ?? '') === 'error' && trim((string) ($status['message'] ?? '')) !== '')
            <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                {{ (string) ($status['message'] ?? '') }}
            </div>
        @endif

        @if ($title !== '')
            <h2 class="text-2xl font-semibold">{{ $title }}</h2>
        @endif

        @if ($description !== '')
            <p class="text-black/70">{{ $description }}</p>
        @endif

        <form action="{{ route('tp.forms.submit', ['formKey' => $routeFormKey]) }}" method="post" class="space-y-4">
            @csrf
            <input type="hidden" name="_tp_payload" value="{{ $payloadToken }}" />
            <input type="hidden" name="_tp_started_at" value="{{ now()->timestamp }}" />
            <input type="hidden" name="_tp_return_url" value="{{ url()->full() }}#tp-form-{{ $routeFormKey }}" />

            <div class="absolute -left-[10000px] top-auto h-px w-px overflow-hidden" aria-hidden="true">
                <label for="tp-hp-{{ $routeFormKey }}">Company</label>
                <input type="text" id="tp-hp-{{ $routeFormKey }}" name="_tp_hp" tabindex="-1" autocomplete="off" />
            </div>

            <div class="grid gap-4">
                @foreach ($fields as $field)
                    @php
                        $key = $field['key'];
                        $id = $routeFormKey.'-'.$key;
                        $required = $field['required'];
                        $value = old($key, $field['default']);
                    @endphp

                    @if ($field['type'] === 'hidden')
                        <input type="hidden" name="{{ $key }}" value="{{ is_scalar($value) ? (string) $value : '' }}" />
                        @continue
                    @endif

                    @if ($field['type'] === 'checkbox')
                        <label class="inline-flex items-center gap-2 text-sm text-black/80" for="{{ $id }}">
                            <input
                                id="{{ $id }}"
                                type="checkbox"
                                name="{{ $key }}"
                                value="1"
                                @checked(in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true))
                                class="rounded border border-black/20"
                                @required($required) />
                            <span>{{ $field['placeholder'] !== '' ? $field['placeholder'] : $field['label'] }}</span>
                        </label>
                        @error($key)
                            <p class="text-xs text-red-700">{{ $message }}</p>
                        @enderror
                        @continue
                    @endif

                    <label class="block text-sm font-medium text-black/80" for="{{ $id }}">
                        {{ $field['label'] }}
                    </label>

                    @if ($field['type'] === 'textarea')
                        <textarea
                            id="{{ $id }}"
                            name="{{ $key }}"
                            rows="4"
                            class="w-full rounded border border-black/10 px-4 py-2 text-sm"
                            placeholder="{{ $field['placeholder'] }}"
                            @required($required)>{{ is_scalar($value) ? (string) $value : '' }}</textarea>
                    @elseif ($field['type'] === 'select')
                        <select
                            id="{{ $id }}"
                            name="{{ $key }}"
                            class="w-full rounded border border-black/10 px-4 py-2 text-sm"
                            @required($required)>
                            @if (! $required)
                                <option value="">Select…</option>
                            @endif
                            @foreach ($field['options'] as $optionValue => $optionLabel)
                                <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>
                                    {{ $optionLabel }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input
                            id="{{ $id }}"
                            type="{{ $field['type'] === 'email' ? 'email' : 'text' }}"
                            name="{{ $key }}"
                            value="{{ is_scalar($value) ? (string) $value : '' }}"
                            class="w-full rounded border border-black/10 px-4 py-2 text-sm"
                            placeholder="{{ $field['placeholder'] }}"
                            @required($required) />
                    @endif

                    @error($key)
                        <p class="text-xs text-red-700">{{ $message }}</p>
                    @enderror
                @endforeach
            </div>

            <button type="submit" class="rounded bg-black px-4 py-2 text-sm font-semibold text-white">
                {{ $submitLabel }}
            </button>
        </form>

        @if ($privacyNotice !== '')
            <p class="text-xs text-black/60">{{ $privacyNotice }}</p>
        @endif
    </div>
</section>
