@props([
    'disabled'      => false,
    'label'         => '',
    'id'            => '',
    'placeholder'   => '',
    'value'         => '',
    'name'          => '',
    'type'          => 'text',
    'mode'          => '1',
    'required'      => false
])

@if($mode == '1')
    @if($label != '')
    <label for="{{$id}}" class="form-label">
        {{$label}}

        @if($required)
            <span class="text-danger required_indicator">*</span>
        @endif
    </label>
    @endif

    <input @disabled($disabled) name="{{$name}}" type="{{$type}}" placeholder="{{$placeholder}}" id="{{$id}}" value="{{$value}}" {{ $attributes->merge(['class' => 'form-control']) }}/>

@else

    <div class="form-floating mb-3">
        <input type="{{$type}}" name="{{$name}}" placeholder="{{$placeholder}}" id="{{$id}}" value="{{$value}}" @disabled($disabled) {{ $attributes->merge(['class' => 'form-control']) }} />
        
        <label for="{{$id}}">
            {{$label}}

            @if($required)
                <span class="text-danger required_indicator">*</span>
            @endif
        </label>
    </div>

@endif
