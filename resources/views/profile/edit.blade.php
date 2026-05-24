@extends('layouts.app')

@section('title', __('Profile'))

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('home') }}" class="text-amber-600 hover:text-amber-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Manage Profile') }}</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-md p-8">
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <h2 class="text-lg font-bold text-blue-900 border-b pb-2">{{ __('Personal Information') }}</h2>

                <div>
                    <label for="national_id" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('National ID') }}</label>
                    <input type="text" id="national_id" value="{{ $user->national_id }}" readonly
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-100 text-gray-500 cursor-not-allowed outline-none">
                    <p class="text-xs text-gray-500 mt-1">{{ __('National ID is fixed and cannot be changed.') }}</p>
                </div>

                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Full Name') }}</label>
                    <input type="text" id="name" name="name" required value="{{ old('name', $user->name) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition">
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Email') }}</label>
                    <input type="email" id="email" name="email" required value="{{ old('email', $user->email) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition">
                </div>
            </div>

            <div class="space-y-4 pt-4">
                <h2 class="text-lg font-bold text-blue-900 border-b pb-2">{{ __('Change Password') }}</h2>
                <p class="text-sm text-gray-500 mb-4">{{ __('Leave fields empty if you don\'t want to change your password.') }}</p>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('New Password') }}</label>
                    <input type="password" id="password" name="password"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Confirm New Password') }}</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition">
                </div>
            </div>

            <div class="flex gap-4 pt-6">
                <button type="submit" class="flex-1 bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 rounded-xl transition-colors shadow-lg">
                    {{ __('Save Changes') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
