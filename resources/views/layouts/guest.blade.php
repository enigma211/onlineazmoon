<!DOCTYPE html>
<html lang="fa" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>سامانه آزمون‌های دفتر مقررات ملی ساختمان</title>

        <link href="{{ asset('fonts/vazirmatn.css') }}" rel="stylesheet" type="text/css" />
        <style>
            body { font-family: 'Vazirmatn', sans-serif; }
        </style>
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-gray-50 flex items-center justify-center min-h-screen bg-[url('https://laravel.com/assets/img/welcome/background.svg')] bg-cover bg-center p-4">
        <div class="w-full max-w-md bg-white/90 backdrop-blur-md rounded-2xl shadow-xl p-6 sm:p-8 text-center border border-gray-100">
            <div class="mb-6 sm:mb-8 flex justify-center">
                <img src="{{ asset('images/logo.png') }}" alt="لوگو دفتر مقررات ملی ساختمان" class="h-24 sm:h-32 md:h-40 w-auto object-contain drop-shadow-sm">
            </div>
            
            <h1 class="text-2xl sm:text-3xl font-black text-gray-800 mb-2 sm:mb-3 leading-tight tracking-tight">
                سامانه آزمون‌های
            </h1>
            <h2 class="text-lg sm:text-xl font-bold text-gray-600 mb-6 sm:mb-8 leading-relaxed">
                دفتر مقررات ملی ساختمان
            </h2>

            {{ $slot }}
        </div>
        @livewireScripts
    </body>
</html>
