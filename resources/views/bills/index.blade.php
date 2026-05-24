@extends('layouts.app')

@section('title', __('Bills'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Bills') }}</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-md p-4">
        <form method="GET" action="{{ route('bills.index') }}" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by bill number, type or transaction...') }}" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none transition">
            </div>
            <div class="w-full md:w-48">
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none transition bg-white">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>{{ __('Unpaid') }}</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>{{ __('Overdue') }}</option>
                </select>
            </div>
            <div class="w-full md:w-48">
                <select name="sort" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none transition bg-white">
                    <option value="latest" {{ request('sort', 'latest') == 'latest' ? 'selected' : '' }}>{{ __('Latest First') }}</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>{{ __('Oldest First') }}</option>
                    <option value="due_soon" {{ request('sort') == 'due_soon' ? 'selected' : '' }}>{{ __('Due Date (Nearest)') }}</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-2 rounded-xl font-semibold transition-colors shadow-md">
                    {{ __('Apply') }}
                </button>
                @if(request()->hasAny(['search', 'status', 'sort']) && (request('search') || request('status') || request('sort') != 'latest'))
                    <a href="{{ route('bills.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl font-semibold transition-colors flex items-center justify-center">
                        {{ __('Clear') }}
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-amber-600 text-white">
                    <tr>
                        <th class="px-6 py-4 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-sm font-semibold">#</th>
                        <th class="px-6 py-4 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-sm font-semibold">{{ __('Bill Type') }}</th>
                        <th class="px-6 py-4 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-sm font-semibold">{{ __('Amount') }}</th>
                        <th class="px-6 py-4 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-sm font-semibold">{{ __('Status') }}</th>
                        <th class="px-6 py-4 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-sm font-semibold">{{ __('Due Date') }}</th>
                        <th class="px-6 py-4 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-sm font-semibold">{{ __('Payment Date') }}</th>
                        <th class="px-6 py-4 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-sm font-semibold">{{ __('Transaction Number') }}</th>
                        <th class="px-6 py-4 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-sm font-semibold">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($bills as $bill)
                        <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="window.location='{{ $bill->status == 'unpaid' ? route('bills.pay', $bill->id) : route('bills.show', $bill->id) }}'">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $bill->id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">
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
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ number_format($bill->amount, 0) }} {{ __('SYP') }}</td>
                            <td class="px-6 py-4"><x-status-badge :status="$bill->status" /></td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $bill->due_date ? $bill->due_date->format('Y/m/d') : '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $bill->paid_at ? $bill->paid_at->format('Y/m/d H:i') : '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $bill->transaction_id ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @if($bill->status == 'unpaid')
                                    <a href="{{ route('bills.pay', $bill->id) }}" class="inline-flex items-center gap-2 bg-amber-50 text-amber-700 hover:bg-amber-100 px-4 py-2 rounded-xl transition-colors font-semibold text-sm">
                                        {{ __('Pay Bill') }}
                                    </a>
                                @else
                                    <a href="{{ route('bills.show', $bill->id) }}" class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 hover:bg-blue-100 px-4 py-2 rounded-xl transition-colors font-semibold text-sm">
                                        {{ __('View Details') }}
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">{{ __('No bills found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bills->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $bills->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
