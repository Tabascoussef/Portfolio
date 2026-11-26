@foreach ($fields as $field => $type)
    @php
        $key = $prefix ? $prefix.'.'.$field : $field;
        $inputName = $field;
        if ($prefix) {
            $segments = explode('.', $key);
            $inputName = array_shift($segments).'['.implode('][', $segments).']';
        }
        $id = str_replace('.', '-', $key);
        $value = old($key, $values[$field] ?? '');
        $multiline = in_array($type, ['textarea', 'lines', 'urls'], true);
        if (is_array($value)) {
            $value = implode("\n", array_filter($value, 'is_string'));
        }
        $value = is_scalar($value) ? (string) $value : '';
        $error = $errors->first($key) ?: $errors->first($key.'.*');
        $hint = match ($type) {
            'lines', 'urls' => __('admin.line_help'),
            'slug' => __('admin.slug_help'),
            'domain' => __('admin.domain_help'),
            default => null,
        };
    @endphp
    <div @class(['field', 'wide' => $multiline])>
        <label for="{{ $id }}">{{ __('admin.field.'.$field) }}</label>
        @if ($multiline)
            <textarea id="{{ $id }}" name="{{ $inputName }}" rows="{{ $type === 'textarea' ? 4 : 3 }}"
                @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @elseif ($hint) aria-describedby="{{ $id }}-hint" @endif>{{ $value }}</textarea>
        @else
            <input id="{{ $id }}" name="{{ $inputName }}" value="{{ $value }}"
                type="{{ match ($type) { 'email', 'url' => $type, 'position', 'year' => 'number', default => 'text' } }}"
                @if ($type === 'position') min="0" max="1000000" required @endif
                @if ($type === 'slug') pattern="[a-z0-9]+(?:-[a-z0-9]+)*" required @endif
                @if ($type === 'domain') inputmode="url" @endif
                @if ($type === 'timezone') placeholder="Asia/Tashkent" @endif
                @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @elseif ($hint) aria-describedby="{{ $id }}-hint" @endif>
        @endif
        @if ($hint)<small id="{{ $id }}-hint">{{ $hint }}</small>@endif
        @if ($error)<small id="{{ $id }}-error" class="error">{{ $error }}</small>@endif
    </div>
@endforeach

