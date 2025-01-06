<div class="form-group">
    @if($label ?? null)
        <label for="{{ $id ??  $name }}">
            {{ $label }}
            @if($required ?? null)
                <span class="text-danger"
                      data-toggle="tooltip"
                      data-placement="top"
                      title="{{trans('text.champ_obligatoire')}}">*</span>
            @endif
        </label>
    @endif
    <select
        id="{{ $id ?? $name }}"
        name="{{ $name }}"
        {{$attributes->merge(['class' => 'form-control'])}}
    >
        {{ $slot }}
    </select>
    {{--    this is a test --}}
</div>
