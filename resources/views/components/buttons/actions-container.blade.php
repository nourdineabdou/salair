@php
    $classes = 'btn-group float-right'
@endphp


<div {{ $attributes->merge(['class' => $classes]) }} >
    @foreach($actions as $action)
        <x-buttons.actions-btn
            class="{{$action['class']}}"
            title="{{$action['title']}}"
            href="{{$action['href']}}"
            onClick="{{ (array_key_exists('onClick',$action) ?  $action['onClick'] :'') }}"
            target="{{ (array_key_exists('target',$action) ?  $action['target'] :'') }}"
        >
            @switch($action["icon"])
                @case('show')
                <x-icons.show/>
                @break
                @case('edit')
                <x-icons.edit/>
                @break
                @case('delete')
                <x-icons.delete/>
                @break
                @default
                <i class=" fa {{ $action["icon"] }}"></i>
                @break
            @endswitch
        </x-buttons.actions-btn>
    @endforeach
</div>


