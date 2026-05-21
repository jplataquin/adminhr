@props([
    'disabled'      => false,
    'label'         => '',
    'id'            => '',
    'placeholder'   => '',
    'rows'          => '3',
    'name'          => '',
    'required'      => false
])

@if($label != '')
<label for="{{$id}}" class="form-label">
    {{$label}}

    @if($required)
        <span class="text-danger required_indicator">*</span>
    @endif
</label>
@endif

<textarea @disabled($disabled) name="{{$name}}" placeholder="{{$placeholder}}" id="{{$id}}" rows="{{$rows}}" {{ $attributes->merge(['class' => "form-control"]) }}>{{ $slot }}</textarea>
