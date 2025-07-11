<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0" style="background-color: #34373b;">
            <div class="flex items-center justify-center space-x-4">
                <a href="/">
                    <img src="{{ asset('images/bs-logo.png') }}" 
                         alt="Benim Şehrim Logosu" 
                         class="w-20 h-20 object-contain">
                </a>
                <a href="/">
                    <img src="{{ asset('images/kbb-logo.png') }}" 
                         alt="Konya BB Logosu" 
                         class="w-20 h-20 object-contain">
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 shadow-md overflow-hidden sm:rounded-lg" style="background-color: #2c2f33; color: #ffffff;">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
