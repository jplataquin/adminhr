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
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
        @vite(['resources/css/app.scss', 'resources/js/app.js'])

        <script type="text/javascript">
            window.$base_url = '{{ url('') }}';
        </script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-vh-100 bg-body-tertiary">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-body shadow-sm mb-4">
                    <div class="container py-3">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                <div class="container">
                    {{ $slot }}
                </div>
            </main>
        </div>

        <div class="drawer_modal_background"></div>
        <div class="drawer_modal bg-body-tertiary">
            <div class="drawer_modal_header bg-body border-bottom">
                <div class="p-2 d-inline-block">
                    <h5 class="drawer_modal_title h5 mb-0"></h5>
                </div>
                <div class="p-2 float-end">
                    <button class="btn btn-link text-decoration-none p-0" onclick="$drawerModal.close()">
                        <i class="bi bi-x-lg h4"></i>
                    </button>
                </div>
            </div>
            <div class="drawer_modal_body p-3">
            </div>
        </div>
    </body>
</html>
