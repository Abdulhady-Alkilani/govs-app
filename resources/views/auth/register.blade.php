<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Register') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-900 via-blue-800 to-blue-950 flex items-center justify-center px-4 py-8">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center bg-amber-500 rounded-2xl p-4 mb-4">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white">{{ __('Government Services Portal') }}</h1>
            <p class="text-blue-200 mt-2">{{ __('Create new account.') }}</p>
        </div>

        <div class="bg-white rounded-2xl shadow-2xl p-8">
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 mb-6 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.submit') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="national_id" class="block text-sm font-semibold text-gray-700 mb-1">{{ __('National ID') }}</label>
                    <input type="text" id="national_id" name="national_id"
                           value="{{ old('national_id') }}" maxlength="11" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           placeholder="{{ __('Enter national ID (11 digits)') }}">
                </div>

                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">{{ __('Full Name') }}</label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name') }}" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           placeholder="{{ __('Enter your full name') }}">
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">{{ __('Email') }}</label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email') }}" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           placeholder="{{ __('Enter your email') }}">
                </div>

                <div>
                    <label for="phone" class="block text-sm font-semibold text-gray-700 mb-1">{{ __('Phone') }}</label>
                    <input type="tel" id="phone" name="phone"
                           value="{{ old('phone') }}" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           placeholder="{{ __('Enter your phone number') }}">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">{{ __('Password') }}</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           placeholder="{{ __('Enter your password') }}">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">{{ __('Confirm Password') }}</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           placeholder="{{ __('Re-enter your password') }}">
                </div>

                <button type="submit"
                        class="w-full bg-blue-900 hover:bg-blue-950 text-white font-bold py-3 rounded-xl transition-colors shadow-lg mt-2">
                    {{ __('Register') }}
                </button>
            </form>

            <div class="mt-6 text-center flex items-center justify-center gap-4">
                <p class="text-gray-600 text-sm">{{ __('Already have an account?') }}
                    <a href="{{ route('login') }}" class="text-blue-900 font-semibold hover:underline">{{ __('Login') }}</a>
                </p>
                <a href="{{ route('lang.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="text-sm text-blue-600 hover:underline font-medium">
                    {{ app()->getLocale() === 'ar' ? 'EN' : 'عربي' }}
                </a>
            </div>
        </div>
    </div>

</body>
</html>
