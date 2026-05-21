<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.scss', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-vh-100 d-flex flex-column justify-content-center align-items-center bg-body-tertiary py-5">
            <div>
                <a href="/">
                    <x-application-logo style="width: 5rem; height: 5rem;" class="text-secondary" />
                </a>
            </div>

            <div class="w-100 mt-4 p-4 bg-body shadow-sm rounded" style="max-width: 400px;">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
