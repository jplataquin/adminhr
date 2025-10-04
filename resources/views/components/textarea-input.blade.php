@props([
    'disabled'      => false,
    'label'         => '',
    'id'            => '',
    'placeholder'   => '',
    'rows'          => '',
    'name'          => '',
    'required'      => false
])

@if($label != '')
<label class="text-sm font-medium dark:text-white block mb-2">
    {{$label}}

    @if($required)
        <span class="text-red-500 text-sm">*</span>
    @endif

</label>
@endif

<textarea @disabled($disabled) name="{{$name}}" placeholder="{{$placeholder}}" id="{{$id}}" rows="{{$rows}}" {{ $attributes->merge(['class' => "border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full p-2.5"]) }}>{{ $slot }}</textarea>
