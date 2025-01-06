<button {{ $attributes->merge(['class' => 'btn btn-success']) }} >
        <span class="icon text-white">
            <i class="main-icon fa fa-save"></i>
{{--            class="la la-check-square-o"--}}
            <span class="spinner-border spinner-border-sm" style="display:none" role="status"
                  aria-hidden="true"></span>
                <i class="answers-well-saved text-white fa fa-check" style="display:none"
                   aria-hidden="true"></i>
        </span>
    <span class="text">{{ $slot }}</span>
</button>

