<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') === 'Laravel' ? 'AdminHR' : config('app.name', 'AdminHR') }} — Enterprise Portal</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.scss', 'resources/js/app.js'])

        <style>
            :root {
                --bg-canvas: #070a12;
                --card-bg: rgba(13, 19, 33, 0.65);
                --card-border: rgba(255, 255, 255, 0.09);
                --glow-primary: #3b82f6;
                --glow-accent: #6366f1;
                --glow-cyan: #06b6d4;
            }

            body {
                background-color: var(--bg-canvas);
                color: #f1f5f9;
                font-family: 'Plus Jakarta Sans', 'Figtree', system-ui, -apple-system, sans-serif;
                overflow-x: hidden;
                min-height: 100vh;
            }

            /* Animated ambient background */
            .ambient-wrapper {
                position: fixed;
                inset: 0;
                overflow: hidden;
                z-index: 0;
                pointer-events: none;
                background: radial-gradient(circle at 50% 50%, #0d1527 0%, #060911 100%);
            }

            .ambient-orb {
                position: absolute;
                border-radius: 50%;
                filter: blur(100px);
                will-change: transform;
                opacity: 0.42;
            }

            .orb-1 {
                top: 10%;
                left: 15%;
                width: 520px;
                height: 520px;
                background: radial-gradient(circle, var(--glow-primary) 0%, rgba(59, 130, 246, 0) 70%);
                animation: floatOrb1 20s ease-in-out infinite alternate;
            }

            .orb-2 {
                bottom: 12%;
                right: 15%;
                width: 580px;
                height: 580px;
                background: radial-gradient(circle, var(--glow-accent) 0%, rgba(99, 102, 241, 0) 70%);
                animation: floatOrb2 24s ease-in-out infinite alternate;
            }

            .orb-3 {
                top: 45%;
                left: 55%;
                width: 440px;
                height: 440px;
                background: radial-gradient(circle, var(--glow-cyan) 0%, rgba(6, 182, 212, 0) 70%);
                animation: floatOrb3 28s ease-in-out infinite alternate;
            }

            .ambient-grid {
                position: absolute;
                inset: 0;
                background-image: 
                    linear-gradient(rgba(255, 255, 255, 0.035) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255, 255, 255, 0.035) 1px, transparent 1px);
                background-size: 56px 56px;
                mask-image: radial-gradient(ellipse 75% 65% at 50% 50%, #000 25%, transparent 80%);
                -webkit-mask-image: radial-gradient(ellipse 75% 65% at 50% 50%, #000 25%, transparent 80%);
                animation: gridDrift 25s linear infinite;
            }

            @keyframes floatOrb1 {
                0% { transform: translate(0, 0) scale(1); }
                50% { transform: translate(140px, 90px) scale(1.12); }
                100% { transform: translate(-70px, 150px) scale(0.96); }
            }

            @keyframes floatOrb2 {
                0% { transform: translate(0, 0) scale(1); }
                50% { transform: translate(-150px, -70px) scale(1.08); }
                100% { transform: translate(90px, -110px) scale(0.92); }
            }

            @keyframes floatOrb3 {
                0% { transform: translate(0, 0) scale(0.95); }
                50% { transform: translate(-90px, 120px) scale(1.15); }
                100% { transform: translate(110px, -60px) scale(1.02); }
            }

            @keyframes gridDrift {
                0% { transform: translateY(0); }
                100% { transform: translateY(56px); }
            }

            /* Content Layer */
            .page-layout {
                position: relative;
                z-index: 1;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
            }

            /* Header */
            .corporate-header {
                backdrop-filter: blur(12px);
                border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            }

            .brand-lockup {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                text-decoration: none;
                color: #ffffff;
            }

            .brand-name {
                font-size: 1.25rem;
                font-weight: 700;
                letter-spacing: -0.02em;
            }

            .brand-name span {
                color: #38bdf8;
                font-weight: 800;
            }

            /* Glassmorphic Hero Card */
            .corporate-card {
                background: var(--card-bg);
                backdrop-filter: blur(28px) saturate(180%);
                -webkit-backdrop-filter: blur(28px) saturate(180%);
                border: 1px solid var(--card-border);
                border-radius: 24px;
                padding: 3.5rem 3rem;
                box-shadow: 
                    0 30px 80px -15px rgba(0, 0, 0, 0.65),
                    0 0 50px -15px rgba(59, 130, 246, 0.15);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .status-pill {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.4rem 0.95rem;
                border-radius: 9999px;
                font-size: 0.78rem;
                font-weight: 600;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                background: rgba(14, 165, 233, 0.08);
                color: #38bdf8;
                border: 1px solid rgba(56, 189, 248, 0.22);
            }

            .status-pulse {
                width: 7px;
                height: 7px;
                border-radius: 50%;
                background-color: #38bdf8;
                box-shadow: 0 0 10px #38bdf8;
                animation: pulseGlow 2s infinite ease-in-out;
            }

            @keyframes pulseGlow {
                0%, 100% { opacity: 1; transform: scale(1); }
                50% { opacity: 0.4; transform: scale(0.85); }
            }

            .hero-heading {
                font-size: clamp(2.25rem, 4.5vw, 3.25rem);
                font-weight: 800;
                letter-spacing: -0.03em;
                line-height: 1.15;
                color: #ffffff;
            }

            .hero-heading .text-gradient {
                background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #818cf8 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            .hero-tagline {
                font-size: 1.05rem;
                color: #94a3b8;
                letter-spacing: -0.01em;
            }

            /* Buttons */
            .btn-corporate-primary {
                background: linear-gradient(135deg, #2563eb, #1d4ed8);
                color: #ffffff;
                font-weight: 600;
                padding: 0.75rem 1.75rem;
                border-radius: 12px;
                border: 1px solid rgba(255, 255, 255, 0.15);
                box-shadow: 0 4px 18px rgba(37, 99, 235, 0.35);
                transition: all 0.2s ease;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                text-decoration: none;
            }

            .btn-corporate-primary:hover {
                background: linear-gradient(135deg, #1d4ed8, #1e40af);
                color: #ffffff;
                transform: translateY(-1px);
                box-shadow: 0 8px 24px rgba(37, 99, 235, 0.48);
            }

            .btn-corporate-ghost {
                background: rgba(255, 255, 255, 0.04);
                color: #e2e8f0;
                font-weight: 600;
                padding: 0.75rem 1.75rem;
                border-radius: 12px;
                border: 1px solid rgba(255, 255, 255, 0.12);
                transition: all 0.2s ease;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                text-decoration: none;
            }

            .btn-corporate-ghost:hover {
                background: rgba(255, 255, 255, 0.08);
                color: #ffffff;
                border-color: rgba(255, 255, 255, 0.22);
                transform: translateY(-1px);
            }

            .meta-divider {
                width: 100%;
                height: 1px;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.08), transparent);
                margin: 2rem 0 1.5rem 0;
            }

            .meta-tags {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 1.5rem;
                font-size: 0.82rem;
                color: #64748b;
            }

            .meta-tags span {
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
            }

            /* Footer */
            .corporate-footer {
                border-top: 1px solid rgba(255, 255, 255, 0.04);
                color: #64748b;
                font-size: 0.8rem;
                letter-spacing: 0.02em;
            }

            @media (max-width: 576px) {
                .corporate-card {
                    padding: 2.5rem 1.75rem;
                }
                .meta-tags {
                    flex-direction: column;
                    gap: 0.6rem;
                }
            }
        </style>
    </head>
    <body class="antialiased">
        <!-- Ambient animated backdrop -->
        <div class="ambient-wrapper" aria-hidden="true">
            <div class="ambient-orb orb-1"></div>
            <div class="ambient-orb orb-2"></div>
            <div class="ambient-orb orb-3"></div>
            <div class="ambient-grid"></div>
        </div>

        <!-- Foreground layout -->
        <div class="page-layout">
            <!-- Navigation Header -->
            <header class="corporate-header py-3">
                <div class="container d-flex justify-content-between align-items-center">
                    <a href="{{ url('/') }}" class="brand-lockup">
                        <svg width="34" height="34" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="38" height="38" rx="10" fill="url(#adminhrGrad)" />
                            <path d="M19 8L28 12.5V19.5C28 24.8 24.2 29.7 19 31C13.8 29.7 10 24.8 10 19.5V12.5L19 8Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                            <path d="M15 19.5L18 22.5L23 16.5" stroke="#38bdf8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                            <defs>
                                <linearGradient id="adminhrGrad" x1="0" y1="0" x2="38" y2="38" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#0f172a"/>
                                    <stop offset="0.6" stop-color="#1e293b"/>
                                    <stop offset="1" stop-color="#1e3a8a"/>
                                </linearGradient>
                            </defs>
                        </svg>
                        <span class="brand-name">Admin<span>HR</span></span>
                    </a>

                    @if (Route::has('login'))
                        <nav class="d-flex align-items-center gap-2">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="btn-corporate-primary btn-sm px-3 py-2">
                                    <span>Dashboard</span>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="btn-corporate-ghost btn-sm px-3 py-2">
                                    Log in
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="btn-corporate-primary btn-sm px-3 py-2">
                                        Register
                                    </a>
                                @endif
                            @endauth
                        </nav>
                    @endif
                </div>
            </header>

            <!-- Hero Section -->
            <main class="flex-grow-1 d-flex align-items-center justify-content-center py-5">
                <div class="container d-flex justify-content-center">
                    <div class="corporate-card text-center w-100" style="max-width: 560px;">
                        <div class="mb-4">
                            <div class="status-pill">
                                <span class="status-pulse"></span>
                                Enterprise Portal
                            </div>
                        </div>

                        <h1 class="hero-heading mb-3">
                            Admin<span class="text-gradient">HR</span>
                        </h1>

                        <p class="hero-tagline mb-4">
                            Workforce Management &amp; Financial Ledger Operations
                        </p>

                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="btn-corporate-primary">
                                    <span>Access Dashboard</span>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="btn-corporate-primary">
                                    <i class="bi bi-box-arrow-in-right"></i>
                                    <span>Sign In</span>
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="btn-corporate-ghost">
                                        <span>Create Account</span>
                                    </a>
                                @endif
                            @endauth
                        </div>

                        <div class="meta-divider"></div>

                        <div class="meta-tags">
                            <span><i class="bi bi-shield-check text-primary"></i> Multi-tier Authorization</span>
                            <span><i class="bi bi-journal-text text-info"></i> Audited Records</span>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="corporate-footer py-3 text-center">
                <div class="container">
                    &copy; {{ date('Y') }} AdminHR Operations. All rights reserved.
                </div>
            </footer>
        </div>
    </body>
</html>
