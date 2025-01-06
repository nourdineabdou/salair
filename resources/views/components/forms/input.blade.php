<div class="form-group">
    @if($label ?? null)
        <label for="{{ $id ?? $name }}">
            {{ $label }}
            @if($required ?? null)
                <span class="text-danger"
                      data-toggle="tooltip"
                      data-placement="top"
                      title="{{ trans('text.champ_obligatoire') }}">*</span>
            @endif
        </label>
    @endif
    <input
        {{$attributes->merge(['class'=>'form-control'])}}
        autocomplete="off"
        type="{{ $type ?? 'text' }}"
        name="{{ $name }}"
        id="{{ $id ?? $name }}"
        placeholder="{{ $placeholder ?? '' }}"
        value="{{ old($name, $value ?? '') }}"
    >
</div>
