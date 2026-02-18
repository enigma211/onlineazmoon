<!DOCTYPE html>
<html lang="fa" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $siteName }}</title>
        <meta name="description" content="{{ $siteDescription }}">
        <link rel="icon" href="{{ $siteFaviconUrl }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link href="{{ asset('fonts/vazirmatn.css') }}" rel="stylesheet" type="text/css" />
        <style>
            body { font-family: 'Vazirmatn', sans-serif; }
        </style>
    </head>
    <body class="bg-gray-50 flex items-center justify-center min-h-screen bg-[url('https://laravel.com/assets/img/welcome/background.svg')] bg-cover bg-center p-4">
        @php($enableRegistration = $siteSettings['enable_registration'] ?? true)
        <div class="w-full max-w-md bg-white/90 backdrop-blur-md rounded-2xl shadow-xl p-6 sm:p-8 text-center border border-gray-100">
            <div class="mb-6 sm:mb-8 flex justify-center">
                <img src="{{ $siteLogoUrl }}" alt="{{ $siteName }}" class="h-24 sm:h-32 md:h-40 w-auto object-contain drop-shadow-sm hover:scale-105 transition-transform duration-300">
            </div>
            
            <h1 class="text-2xl sm:text-3xl font-black text-gray-800 mb-2 sm:mb-3 leading-tight tracking-tight">
                {{ $siteName }}
            </h1>
            <h2 class="text-lg sm:text-xl font-bold text-gray-600 mb-8 sm:mb-10 leading-relaxed">
                {{ $siteDescription }}
            </h2>

            <div class="space-y-3 sm:space-y-4">
                <a href="{{ route('login') }}" class="block w-full py-3 sm:py-3.5 px-4 sm:px-6 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-bold rounded-xl shadow-lg shadow-red-500/30 transition-all duration-200 transform hover:-translate-y-1 focus:ring-4 focus:ring-red-500/20 text-sm sm:text-base">
                    ورود به سامانه
                </a>
                
                @if($enableRegistration)
                    <a href="{{ route('register') }}" class="block w-full py-3 sm:py-3.5 px-4 sm:px-6 bg-white hover:bg-gray-50 text-gray-800 font-bold rounded-xl border-2 border-gray-200 hover:border-gray-300 transition-all duration-200 transform hover:-translate-y-1 focus:ring-4 focus:ring-gray-200 text-sm sm:text-base">
                        ثبت نام داوطلبان
                    </a>
                @else
                    <div class="block w-full py-3 sm:py-3.5 px-4 sm:px-6 bg-gray-100 text-gray-500 font-bold rounded-xl border-2 border-gray-200 text-sm sm:text-base cursor-not-allowed">
                        ثبت نام در حال حاضر غیرفعال است
                    </div>
                @endif
            </div>

            <div class="mt-8 sm:mt-10 pt-4 sm:pt-6 border-t border-gray-100">
                <p class="text-xs text-gray-400 font-medium">
                    &copy; {{ date('Y') }} تمامی حقوق محفوظ است.
                </p>
            </div>
        </div>
    </body>
</html>
