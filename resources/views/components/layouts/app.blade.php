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
<div class="flex w-screen h-screen items-center justify-center text-center bg-gray-100 overflow-y-hidden">
    <div class="flex flex-col h-screen justify-center lg:px-8 md:w-2/3 px-6 py-12 w-full">
            <div class="flex justify-center mb-2">
            <a href="{{ route('site.home') }}">
                <img width="200" src="{{ asset('images/branding/vatsimuk_blackblue.png') }}" unselectable="on">
            </a>
        </div>
        <div class="flex justify-center overflow-hidden">
        {{ $slot }}
        </div>
    </div>
    @filamentScripts
    @livewire('notifications')
</body>
</html>
