<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'AdminHR') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.scss', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-vh-100 d-flex flex-column bg-body-tertiary">
            <header class="container py-4">
                <div class="d-flex justify-content-between align-items-center">
                    <x-application-logo style="height: 3rem; width: auto;" class="text-primary" />
                    
                    @if (Route::has('login'))
                        <nav class="nav">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="nav-link text-white">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="nav-link text-white">Log in</a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="nav-link text-white">Register</a>
                                @endif
                            @endauth
                        </nav>
                    @endif
                </div>
            </header>

            <main class="flex-grow-1 d-flex align-items-center">
                <div class="container text-center py-5">
                    <h1 class="display-3 fw-bold mb-4">AdminHR</h1>
                    <p class="lead mb-5 text-secondary">
                        Comprehensive management for Employees and Organizational Ledgers.
                    </p>
                    
                    <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn btn-primary btn-lg px-4 gap-3">Go to Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-4 gap-3">Sign In</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-lg px-4">Register</a>
                            @endif
                        @endauth
                    </div>
                </div>
            </main>

            <footer class="container py-4 text-center text-secondary small">
                Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
            </footer>
        </div>
    </body>
</html>
