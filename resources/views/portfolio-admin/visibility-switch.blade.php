<label class="visibility-control">
    <input type="hidden" name="{{ $visibilityName ?? 'is_active' }}" value="0">
    <input type="checkbox" role="switch" class="visibility-switch" name="{{ $visibilityName ?? 'is_active' }}" value="1" aria-label="{{ $label }}"
        @checked($active) @if ($submitOnChange) onchange="this.form.requestSubmit()" @endif>
    <span class="visibility-state" aria-hidden="true">
        <span class="visibility-on">{{ __('admin.active') }}</span>
        <span class="visibility-off">{{ __('admin.inactive') }}</span>
    </span>
</label>
