@php
    $formKey = trim((string) ($props['form_key'] ?? 'signup'));
    if ($formKey === '') {
        $formKey = 'signup';
    }

    $title = (string) ($props['title'] ?? '');
    $description = (string) ($props['description'] ?? '');
    $submitLabel = (string) ($props['submit_label'] ?? 'Submit');
    $privacyNotice = (string) ($props['privacy_notice'] ?? '');

    $rawFields = $props['fields'] ?? [];
    if (is_string($rawFields)) {
        $decodedFields = json_decode($rawFields, true);
        $rawFields = is_array($decodedFields) ? $decodedFields : [];
    }

    $allowedTypes = ['email', 'text', 'textarea', 'checkbox', 'select', 'hidden'];
    $fields = [];

    foreach ((array) $rawFields as $field) {
        if (! is_array($field)) {
            continue;
        }

        $key = trim((string) ($field['key'] ?? ''));
        $label = trim((string) ($field['label'] ?? ''));
        $type = strtolower(trim((string) ($field['type'] ?? 'text')));

        if ($key === '' || $label === '') {
            continue;
        }

        if (! in_array($type, $allowedTypes, true)) {
            $type = 'text';
        }

        $fields[] = [
            'key' => $key,
            'label' => $label,
            'type' => $type,
            'required' => in_array((string) ($field['required'] ?? '0'), ['1', 'true', 'yes', 'on'], true),
            'placeholder' => (string) ($field['placeholder'] ?? ''),
            'default' => (string) ($field['default'] ?? ''),
            'options' => (string) ($field['options'] ?? ''),
        ];
    }
@endphp

<section id="tp-form-{{ $formKey }}" class="py-12">
    <div class="mx-auto max-w-3xl space-y-5 rounded-xl border border-black/10 bg-white p-8">
        @if ($title !== '')
            <h2 class="text-2xl font-semibold">{{ $title }}</h2>
        @endif

        @if ($description !== '')
            <p class="text-black/70">{{ $description }}</p>
        @endif

        <form action="#" method="post" class="space-y-4">
            <div class="grid gap-4">
                @foreach ($fields as $field)
                    @php
                        $isRequired = $field['required'];
                        $requiredAttr = $isRequired ? 'required' : null;
                    @endphp

                    @if ($field['type'] === 'hidden')
                        <input type="hidden" name="{{ $field['key'] }}" value="{{ $field['default'] }}" />
                        @continue
                    @endif

                    <label class="block text-sm font-medium text-black/80" for="{{ $formKey }}-{{ $field['key'] }}">
                        {{ $field['label'] }}
                    </label>

                    @if ($field['type'] === 'textarea')
                        <textarea
                            id="{{ $formKey }}-{{ $field['key'] }}"
                            name="{{ $field['key'] }}"
                            rows="4"
                            {{ $requiredAttr }}
                            class="w-full rounded border border-black/10 px-4 py-2 text-sm"
                            placeholder="{{ $field['placeholder'] }}">{{ $field['default'] }}</textarea>
                    @elseif ($field['type'] === 'select')
                        @php
                            $rawOptions = preg_split('/\r?\n/', trim($field['options'])) ?: [];
                            $options = [];

                            foreach ($rawOptions as $line) {
                                $line = trim($line);
                                if ($line === '') {
                                    continue;
                                }

                                $parts = array_map('trim', explode('|', $line, 2));
                                $value = (string) ($parts[0] ?? '');
                                $label = (string) ($parts[1] ?? $value);

                                if ($value === '') {
                                    continue;
                                }

                                $options[] = ['value' => $value, 'label' => $label];
                            }
                        @endphp

                        <select
                            id="{{ $formKey }}-{{ $field['key'] }}"
                            name="{{ $field['key'] }}"
                            {{ $requiredAttr }}
                            class="w-full rounded border border-black/10 px-4 py-2 text-sm">
                            @foreach ($options as $option)
                                <option value="{{ $option['value'] }}" @selected($field['default'] === $option['value'])>
                                    {{ $option['label'] }}
                                </option>
                            @endforeach
                        </select>
                    @elseif ($field['type'] === 'checkbox')
                        <label class="inline-flex items-center gap-2 text-sm text-black/80">
                            <input
                                id="{{ $formKey }}-{{ $field['key'] }}"
                                type="checkbox"
                                name="{{ $field['key'] }}"
                                value="1"
                                @checked(in_array(strtolower($field['default']), ['1', 'true', 'yes', 'on'], true))
                                class="rounded border border-black/20"
                                {{ $requiredAttr }} />
                            <span>{{ $field['placeholder'] !== '' ? $field['placeholder'] : $field['label'] }}</span>
                        </label>
                    @else
                        <input
                            id="{{ $formKey }}-{{ $field['key'] }}"
                            type="{{ $field['type'] === 'email' ? 'email' : 'text' }}"
                            name="{{ $field['key'] }}"
                            value="{{ $field['default'] }}"
                            {{ $requiredAttr }}
                            class="w-full rounded border border-black/10 px-4 py-2 text-sm"
                            placeholder="{{ $field['placeholder'] }}" />
                    @endif
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
