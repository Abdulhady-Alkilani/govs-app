@extends('layouts.app')

@section('title', __('New Bill'))

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('bills.index') }}" class="text-amber-600 hover:text-amber-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ __('New Bill') }}</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-md p-8">
        <form method="POST" action="{{ route('bills.store') }}" class="space-y-6">
            @csrf

            <div>
                <label for="bill_type" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Bill Type / Fine') }}</label>
                <select id="bill_type" name="bill_type" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white">
                    <option value="">{{ __('Choose type...') }}</option>
                    <option value="water" {{ old('bill_type') == 'water' ? 'selected' : '' }}>{{ __('Water Bill') }}</option>
                    <option value="electricity" {{ old('bill_type') == 'electricity' ? 'selected' : '' }}>{{ __('Electricity Bill') }}</option>
                    <option value="telecom" {{ old('bill_type') == 'telecom' ? 'selected' : '' }}>{{ __('Telecom Bill') }}</option>
                    <option value="property_tax" {{ old('bill_type') == 'property_tax' ? 'selected' : '' }}>{{ __('Property Tax') }}</option>
                    <option value="traffic_fine" {{ old('bill_type') == 'traffic_fine' ? 'selected' : '' }}>{{ __('Traffic Fine') }}</option>
                    <option value="late_fine" {{ old('bill_type') == 'late_fine' ? 'selected' : '' }}>{{ __('Late Fine') }}</option>
                    <option value="other" {{ old('bill_type') == 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                </select>
            </div>

            <div>
                <label for="amount" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Amount (SYP)') }}</label>
                <input type="number" id="amount" name="amount" min="1" step="1" required placeholder="{{ __('Example: 50000') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white" value="{{ old('amount') }}">
            </div>

            <div class="flex gap-4 mt-8">
                <button type="submit" class="flex-1 bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 rounded-xl transition-colors shadow-lg">
                    {{ __('Submit Bill') }}
                </button>
                <a href="{{ route('bills.index') }}" class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition-colors font-semibold">
                    {{ __('Cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
