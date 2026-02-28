<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PT TTC') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased text-gray-900 bg-gray-100">

    {{-- HEADER --}}
    @if (Route::has('login'))
        <header class="fixed top-0 left-0 right-0 bg-white border-b z-50">
            <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

                <!-- KIRI: LOGO + NAMA -->
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/ss.png') }}"
                         class="h-9 w-auto"
                         alt="Logo PT TTC">

                    <span class="text-lg font-semibold text-gray-800">
                        PT TTC
                    </span>
                </div>

                <!-- KANAN: LOGIN / REGISTER -->
                <nav class="flex items-center gap-6 text-sm font-medium">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                           class="text-gray-700 hover:text-blue-600">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="text-gray-700 hover:text-blue-600">
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="text-gray-700 hover:text-blue-600">
                                Register
                            </a>
                        @endif
                    @endauth
                </nav>

            </div>
        </header>
    @endif

    {{-- CONTENT --}}
    <main class="pt-28">
        {{ $slot }}
    </main>

</body>
</html>
