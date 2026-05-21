@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1'])

@php
$alignmentClasses = match ($align) {
    'left' => 'dropdown-menu-start',
    'top' => 'dropup',
    default => 'dropdown-menu-end',
};
@endphp

<div class="dropdown" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div x-show="open"
            class="dropdown-menu shadow {{ $alignmentClasses }} show"
            style="display: none; position: absolute; inset: 0px 0px auto auto; margin: 0px; transform: translate(0px, 40px);"
            @click="open = false">
        <div class="{{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
