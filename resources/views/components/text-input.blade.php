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
    <label class="text-sm font-medium dark:text-white block mb-2">
        {{$label}}

        @if($required)
            <span class="text-red-500 text-sm required_indicator">*</span>
        @endif

    </label>
    @endif

    <input @disabled($disabled) name="{{$name}}" type="{{$type}}" placeholder="{{$placeholder}}" id="{{$id}}" value="{{$value}}" {{ $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full p-2.5']) }}/>

@else

    <div class="relative">
        <input type="{{$type}}" name="{{$name}}" placeholder="{{$placeholder}}" id="{{$id}}" value="{{$value}}" class="block rounded-t-lg px-2.5 pb-2.5 pt-5 w-full text-sm text-gray-900 bg-gray-50 dark:bg-gray-700 border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"/>
        
        <label for="{{$id}}" class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-75 top-4 z-10 origin-[0] start-2.5 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto">
            {{$label}}

            @if($required)
                <span class="text-red-500 text-sm required_indicator">*</span>
            @endif
        </label>
    </div>

@endif