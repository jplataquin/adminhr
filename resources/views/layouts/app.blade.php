<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>



            @php
                $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
            @endphp
            <script type="module" src="/build/{{ $manifest['resources/js/app.js']['file'] }}"></script>
            <link rel="stylesheet" href="/build/{{ $manifest['resources/css/app.css']['file'] }}">

            <script type="text/javascript">
                
                window.$base_url = '{{ url('') }}';
                
            
            </script>
    </head>
    <body class="font-sans antialiased dark:bg-gray-900">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <div class="drawer_modal_background"></div>
        <div class="drawer_modal bg-gray-100 dark:bg-gray-900">
            <div class="drawer_modal_header bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
                <div class="p-2 float-left">
                    <h5 class="drawer_modal_title text-xl font-semibold dark:text-white"></h5>
                </div>
                <div class="p-2 float-right">
                    <button class="text-xl font-semibold dark:text-white" onclick="$drawerModal.close()" class="">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            <div class="drawer_modal_body p-3">
            </div>
        </div>
    </body>
</html>
