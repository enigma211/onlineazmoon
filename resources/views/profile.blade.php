<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $siteDescription }}">
    <title>پروفایل کاربری - {{ $siteName }}</title>
    <link rel="icon" href="{{ $siteFaviconUrl }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('fonts/vazirmatn.css') }}" rel="stylesheet" type="text/css" />
    <style>
        body { font-family: 'Vazirmatn', sans-serif; }
    </style>
    @livewireStyles
</head>
<body class="bg-gray-50">
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 sm:py-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-3 sm:gap-4 w-full sm:w-auto">
                    <img src="{{ $siteLogoUrl }}" alt="{{ $siteName }}" class="h-12 sm:h-14 md:h-16 w-auto">
                    <div class="min-w-0 flex-1 sm:flex-none">
                        <h1 class="text-lg sm:text-xl font-bold text-gray-800 truncate">پروفایل کاربری</h1>
                        @if(filled($siteDescription))
                            <p class="text-xs sm:text-sm text-gray-600 hidden sm:block">{{ $siteDescription }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2 sm:gap-4 w-full sm:w-auto justify-between sm:justify-end">
                    <a href="{{ route('dashboard') }}" style="background-color: #6d28d9; border-color: #5b21b6; color: #ffffff;" class="px-3 sm:px-4 py-2 hover:bg-violet-800 text-white text-xs sm:text-sm font-semibold border rounded-lg transition-colors whitespace-nowrap shadow-sm">
                        بازگشت به داشبورد
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="px-3 sm:px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs sm:text-sm font-medium rounded-lg transition-colors whitespace-nowrap">
                            خروج
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-8">
            <div class="max-w-2xl">
                <livewire:profile.update-profile-information-form />
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-8">
            <div class="max-w-2xl">
                <livewire:profile.update-password-form />
            </div>
        </div>
    </main>

    @livewireScripts
</body>
</html>
