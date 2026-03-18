@foreach ($provider['fields'] as $field)
    @php($inputId = 'provider_'.$provider['key'].'_'.$field['key'])
    <div class="tp-field">
        <label for="{{ $inputId }}" class="tp-label">{{ $field['label'] }}</label>
        <input
            id="{{ $inputId }}"
            name="{{ $inputId }}"
            class="tp-input"
            value="{{ old($inputId, $field['value']) }}"
            placeholder="{{ $field['placeholder'] }}" />
        @if ($field['help'] !== '')
            <div class="tp-help">{{ $field['help'] }}</div>
        @endif
    </div>
@endforeach
