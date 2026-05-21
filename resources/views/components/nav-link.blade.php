@props(['active'])

@php
$classes = ($active ?? false)
            ? 'nav-link active fw-bold'
            : 'nav-link';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
