<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('scripts')
    </head>
    <body class="font-sans antialiased bg-white text-gray-900">
        <div class="flex min-h-screen flex-col">
            <header class="flex items-center justify-between gap-2 border-b border-gray-100 px-4 py-4 sm:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="{{ asset('storage/images/logoInstitucional.png') }}" alt="Logo" class="h-10 w-auto sm:h-[50px]">
                </a>

                <div class="flex items-center gap-1 sm:gap-2">
                    <nav class="mr-1 hidden items-center gap-1 sm:flex">
                        <a href="{{ route('home') }}"
                           class="rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('home') ? 'text-hospitalblue' : 'text-gray-600 hover:text-hospitalblue' }}">
                            Verificar certificado
                        </a>
                        <a href="{{ route('catalog.index') }}"
                           class="rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('catalog.index') ? 'text-hospitalblue' : 'text-gray-600 hover:text-hospitalblue' }}">
                            Cursos
                        </a>
                    </nav>
                    <a href="{{ route('catalog.index') }}"
                       class="rounded-md px-2 py-2 text-sm font-medium text-gray-600 hover:text-hospitalblue sm:hidden">
                        Cursos
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}"
                           class="rounded-md bg-hospitalblue px-3 py-2 text-sm font-medium text-white hover:bg-hospitalblue-dark">
                            Ir al panel
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Iniciar sesión
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="hidden rounded-md px-3 py-2 text-sm font-medium text-hospitalblue hover:bg-gray-50 sm:inline-block">
                                Registrarme
                            </a>
                        @endif
                    @endauth
                </div>
            </header>

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="border-t border-gray-100 py-6 text-center text-xs text-hospitalgray">
                {{ config('app.name') }}
            </footer>
        </div>
    </body>
</html>
