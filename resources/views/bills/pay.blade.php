@extends('layouts.app')

@section('title', __('Bill Payment'))

@section('content')
<div class="max-w-lg mx-auto space-y-6" x-data="{ showModal: true }">
    <div class="flex items-center gap-4">
        <a href="{{ route('bills.index') }}" class="text-amber-600 hover:text-amber-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Bill Payment') }} #{{ $bill->id }}</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <div class="bg-amber-600 text-white px-6 py-4">
            <p class="font-bold text-lg">{{ __('Bill Details') }}</p>
        </div>

        <div class="p-6 space-y-4">
            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                <span class="text-gray-500">{{ __('Bill Type') }}</span>
                <span class="font-semibold text-gray-900">{{ $bill->bill_type }}</span>
            </div>
            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                <span class="text-gray-500">{{ __('Amount Due') }}</span>
                <span class="font-bold text-2xl text-amber-600">{{ number_format($bill->amount, 0) }} {{ __('SYP') }}</span>
            </div>
            <div class="flex justify-between items-center py-3">
                <span class="text-gray-500">{{ __('Due Date') }}</span>
                <span class="font-semibold text-gray-900">{{ $bill->due_date ? $bill->due_date->format('Y/m/d') : '-' }}</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-md overflow-hidden" x-show="showModal">
        <div class="bg-blue-700 text-white px-6 py-4 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="font-bold">{{ __('Attach Payment Receipt for Verification') }}</span>
        </div>

        <div class="p-6">
            <form method="POST" action="{{ route('bills.process', $bill->id) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div>
                    <label for="paid_amount" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Paid Amount (SYP)') }}</label>
                    <input type="number" id="paid_amount" name="paid_amount" required min="1"
                           value="{{ old('paid_amount', $bill->amount) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                </div>

                <div>
                    <label for="payment_receipt" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Payment Receipt Image (Required)') }}</label>
                    <input type="file" id="payment_receipt" name="payment_receipt" required accept="image/*,.pdf"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-white">
                </div>

                <div>
                    <label for="payment_details" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Other Details (Notes, Account Number, etc.)') }}</label>
                    <textarea id="payment_details" name="payment_details" rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                              placeholder="{{ __('Enter any notes here...') }}">{{ old('payment_details') }}</textarea>
                </div>

                <div class="flex gap-4 pt-2">
                    <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition-colors shadow-lg">
                        {{ __('Confirm & Send Receipt') }}
                    </button>
                    <a href="{{ route('bills.index') }}" class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition-colors font-semibold">
                        {{ __('Cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
