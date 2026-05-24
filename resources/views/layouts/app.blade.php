<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('Sezerians gov'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 font-sans min-h-screen flex flex-col">

    <nav class="bg-blue-900 text-white shadow-lg" x-data="{ open: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-10 w-auto object-contain">
                    <a href="{{ route('home') }}" class="text-xl font-bold tracking-wide">{{ __('Sezerians gov') }}</a>
                </div>

                @auth
                <div class="hidden md:flex items-center gap-6">
                    <a href="{{ route('home') }}" class="transition-colors {{ request()->routeIs('home') ? 'text-amber-400 font-bold' : 'hover:text-amber-400' }}">{{ __('Home') }}</a>
                    <a href="{{ route('complaints.index') }}" class="transition-colors {{ request()->routeIs('complaints.*') ? 'text-amber-400 font-bold' : 'hover:text-amber-400' }}">{{ __('Complaints') }}</a>
                    <a href="{{ route('inquiries.index') }}" class="transition-colors {{ request()->routeIs('inquiries.*') ? 'text-amber-400 font-bold' : 'hover:text-amber-400' }}">{{ __('Inquiries') }}</a>
                    <a href="{{ route('bills.index') }}" class="transition-colors {{ request()->routeIs('bills.*') ? 'text-amber-400 font-bold' : 'hover:text-amber-400' }}">{{ __('Bills') }}</a>

                    <a href="{{ route('notifications.index') }}" class="relative transition-colors {{ request()->routeIs('notifications.*') ? 'text-amber-400 font-bold' : 'hover:text-amber-400' }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @php
                            $unreadCount = auth()->user()->customNotifications()->where('is_read', false)->count();
                        @endphp
                        @if($unreadCount > 0)
                            <span class="absolute -top-2 {{ app()->getLocale() === 'ar' ? '-left-2' : '-right-2' }} bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ $unreadCount }}</span>
                        @endif
                    </a>

                    <a href="{{ route('profile.edit') }}" class="transition-colors {{ request()->routeIs('profile.*') ? 'text-amber-400 font-bold' : 'text-amber-400 hover:text-amber-300 font-medium' }}">{{ auth()->user()->name }}</a>

                    <a href="{{ route('lang.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="text-sm bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-lg transition-colors">
                        {{ app()->getLocale() === 'ar' ? 'EN' : 'عربي' }}
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg text-sm transition-colors">{{ __('Logout') }}</button>
                    </form>
                </div>

                <div class="md:hidden flex items-center gap-3">
                    <a href="{{ route('lang.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="text-sm bg-white/10 hover:bg-white/20 px-2 py-1 rounded-lg transition-colors">
                        {{ app()->getLocale() === 'ar' ? 'EN' : 'عربي' }}
                    </a>
                    <a href="{{ route('notifications.index') }}" class="relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @if($unreadCount > 0)
                            <span class="absolute -top-2 {{ app()->getLocale() === 'ar' ? '-left-2' : '-right-2' }} bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ $unreadCount }}</span>
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
                    <a href="{{ route('lang.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="text-sm bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-lg transition-colors">
                        {{ app()->getLocale() === 'ar' ? 'EN' : 'عربي' }}
                    </a>
                    <a href="{{ route('login') }}" class="hover:text-amber-400 transition-colors">{{ __('Login') }}</a>
                    <a href="{{ route('register') }}" class="bg-amber-500 hover:bg-amber-600 px-4 py-2 rounded-lg text-sm transition-colors">{{ __('Register') }}</a>
                </div>
                @endguest
            </div>
        </div>

        @auth
        <div x-show="open" x-transition class="md:hidden bg-blue-800 px-4 pb-4 space-y-2">
            <a href="{{ route('home') }}" class="block py-2 hover:text-amber-400">{{ __('Home') }}</a>
            <a href="{{ route('complaints.index') }}" class="block py-2 hover:text-amber-400">{{ __('Complaints') }}</a>
            <a href="{{ route('inquiries.index') }}" class="block py-2 hover:text-amber-400">{{ __('Inquiries') }}</a>
            <a href="{{ route('bills.index') }}" class="block py-2 hover:text-amber-400">{{ __('Bills') }}</a>
            <a href="{{ route('profile.edit') }}" class="block py-2 text-amber-400 hover:text-amber-300 font-medium">{{ auth()->user()->name }}</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg text-sm transition-colors">{{ __('Logout') }}</button>
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

    @if($errors->any())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative" role="alert">
                <strong class="font-bold">{{ __('Alert! Please review the following errors:') }}</strong>
                <ul class="list-disc list-inside mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <footer class="bg-blue-900 text-white py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p>&copy; {{ date('Y') }} {{ __('Sezerians gov') }} - {{ __('All Rights Reserved') }}</p>
        </div>
    </footer>

</body>
</html>
