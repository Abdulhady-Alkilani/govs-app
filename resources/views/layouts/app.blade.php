<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'الخدمات الحكومية')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 font-sans min-h-screen flex flex-col">

    <nav class="bg-blue-900 text-white shadow-lg" x-data="{ open: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-3">
                    <div class="bg-amber-500 rounded-lg p-2">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <a href="{{ route('home') }}" class="text-xl font-bold tracking-wide">الخدمات الحكومية</a>
                </div>

                @auth
                <div class="hidden md:flex items-center gap-6">
                    <a href="{{ route('home') }}" class="hover:text-amber-400 transition-colors">الرئيسية</a>
                    <a href="{{ route('complaints.index') }}" class="hover:text-amber-400 transition-colors">الشكاوى</a>
                    <a href="{{ route('inquiries.index') }}" class="hover:text-amber-400 transition-colors">الاستعلامات</a>
                    <a href="{{ route('bills.index') }}" class="hover:text-amber-400 transition-colors">الفواتير</a>

                    <a href="{{ route('notifications.index') }}" class="relative hover:text-amber-400 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @php
                            $unreadCount = auth()->user()->customNotifications()->where('is_read', false)->count();
                        @endphp
                        @if($unreadCount > 0)
                            <span class="absolute -top-2 -left-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ $unreadCount }}</span>
                        @endif
                    </a>

                    <span class="text-amber-400 font-medium">{{ auth()->user()->name }}</span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg text-sm transition-colors">تسجيل الخروج</button>
                    </form>
                </div>

                <div class="md:hidden flex items-center gap-3">
                    <a href="{{ route('notifications.index') }}" class="relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @if($unreadCount > 0)
                            <span class="absolute -top-2 -left-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ $unreadCount }}</span>
                        @endif
                    </a>
                    <button @click="open = !open" class="text-white focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
                @endauth

                @guest
                <div class="flex items-center gap-4">
                    <a href="{{ route('login') }}" class="hover:text-amber-400 transition-colors">تسجيل الدخول</a>
                    <a href="{{ route('register') }}" class="bg-amber-500 hover:bg-amber-600 px-4 py-2 rounded-lg text-sm transition-colors">إنشاء حساب</a>
                </div>
                @endguest
            </div>
        </div>

        @auth
        <div x-show="open" x-transition class="md:hidden bg-blue-800 px-4 pb-4 space-y-2">
            <a href="{{ route('home') }}" class="block py-2 hover:text-amber-400">الرئيسية</a>
            <a href="{{ route('complaints.index') }}" class="block py-2 hover:text-amber-400">الشكاوى</a>
            <a href="{{ route('inquiries.index') }}" class="block py-2 hover:text-amber-400">الاستعلامات</a>
            <a href="{{ route('bills.index') }}" class="block py-2 hover:text-amber-400">الفواتير</a>
            <span class="block py-2 text-amber-400">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg text-sm transition-colors">تسجيل الخروج</button>
            </form>
        </div>
        @endauth
    </nav>

    @if(session('success'))
        <x-alert type="success" :message="session('success')" />
    @endif

    @if(session('error'))
        <x-alert type="error" :message="session('error')" />
    @endif

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <footer class="bg-blue-900 text-white py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p>&copy; {{ date('Y') }} الخدمات الحكومية - جميع الحقوق محفوظة</p>
        </div>
    </footer>

</body>
</html>
