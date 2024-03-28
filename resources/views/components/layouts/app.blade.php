<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    @filamentStyles
    @vite('resources/assets/css/tailwind.css')

    <title>{{ isset($title) ? "VATSIM UK | $title" : 'VATSIM UK' }}</title>
</head>
<body>
    <div class="flex w-screen h-screen items-center justify-center text-center bg-gray-100">
        <div class="flex min-h-full w-full md:w-2/3 flex-col justify-center py-12 px-6 lg:px-8">
            <div class="flex justify-center mb-2">
                <a href="{{ route('site.home') }}">
                    <img width="200" src="{{ secure_asset('images/branding/vatsimuk_blackblue.png') }}" unselectable="on">
                </a>
            </div>
            {{ $slot }}
        </div>
    </div>
    @filamentScripts
    @livewire('notifications')
</body>
</html>
