@extends('layouts.app')

@section('title', __('Bill Details'))

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('bills.index') }}" class="text-blue-600 hover:text-blue-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Financial Claim Details') }} #{{ $bill->id }}</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <div class="bg-blue-700 text-white px-6 py-4 flex items-center justify-between">
            <span class="font-bold">{{ __('Bill #') }}{{ $bill->id }}</span>
            <x-status-badge :status="$bill->status" />
        </div>

        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-gray-500 mb-1">{{ __('Bill Type / Fine') }}</p>
                    <p class="font-semibold text-gray-900">
                        @php
                            $typeName = match ($bill->bill_type) {
                                'water' => __('Water Bill'),
                                'electricity' => __('Electricity Bill'),
                                'telecom' => __('Telecom Bill'),
                                'property_tax' => __('Property Tax'),
                                'traffic_fine' => __('Traffic Fine'),
                                'late_fine' => __('Late Fine'),
                                'other' => __('Other'),
                                default => $bill->bill_type,
                            };
                        @endphp
                        {{ $typeName }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">{{ __('Amount Due') }}</p>
                    <p class="font-bold text-xl text-amber-600">{{ number_format($bill->amount, 0) }} {{ __('SYP') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">{{ __('Due Date') }}</p>
                    <p class="font-semibold text-gray-900">{{ $bill->due_date ? $bill->due_date->format('Y/m/d') : '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">{{ __('Issue Date') }}</p>
                    <p class="font-semibold text-gray-900">{{ $bill->created_at->format('Y/m/d H:i') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">{{ __('System Transaction Number') }}</p>
                    <p class="font-semibold text-gray-900">{{ $bill->transaction_id ?? '-' }}</p>
                </div>
            </div>

            @if($bill->payment_details)
            <div class="mt-6">
                <p class="text-sm text-gray-500 mb-2">{{ __('Admin Reply / Details') }}</p>
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-gray-800 leading-relaxed whitespace-pre-line">
                    {{ $bill->payment_details }}
                </div>
            </div>
            @endif

            @if($bill->status == 'paid')
            <hr class="border-gray-100 mt-6">
            <h3 class="font-bold text-gray-900 text-lg">{{ __('Payment Details') }}</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">
                <div>
                    <p class="text-sm text-gray-500 mb-1">{{ __('Paid Amount') }}</p>
                    <p class="font-bold text-xl text-green-600">{{ number_format($bill->paid_amount ?? $bill->amount, 0) }} {{ __('SYP') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">{{ __('Payment Date') }}</p>
                    <p class="font-semibold text-gray-900">{{ $bill->paid_at ? $bill->paid_at->format('Y/m/d H:i') : '-' }}</p>
                </div>

                @if($bill->payment_receipt_path)
                <div class="col-span-1 sm:col-span-2">
                    <p class="text-sm text-gray-500 mb-2">{{ __('Attached Receipt Image') }}</p>
                    <div class="mt-2">
                        <a href="{{ Storage::url($bill->payment_receipt_path) }}" target="_blank"
                           class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 hover:bg-blue-100 px-4 py-3 rounded-xl transition-colors font-semibold border border-blue-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            {{ __('View Full Size Receipt') }}
                        </a>
                    </div>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
