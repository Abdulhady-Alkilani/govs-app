@extends('layouts.app')

@section('title', 'الرئيسية')

@section('content')
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">مرحباً، {{ auth()->user()->name }}</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl shadow-md p-6 border-r-4 border-blue-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">الشكاوى</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $complaintsCount }}</p>
                </div>
                <div class="bg-blue-100 rounded-xl p-3">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6 border-r-4 border-green-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">الاستعلامات</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $inquiriesCount }}</p>
                </div>
                <div class="bg-green-100 rounded-xl p-3">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6 border-r-4 border-amber-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">الفواتير غير المدفوعة</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $unpaidBillsCount }}</p>
                </div>
                <div class="bg-amber-100 rounded-xl p-3">
                    <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
            <div class="bg-blue-900 text-white px-6 py-4 flex items-center justify-between">
                <h2 class="font-bold text-lg">آخر الشكاوى</h2>
                <a href="{{ route('complaints.index') }}" class="text-amber-400 hover:underline text-sm">عرض الكل</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600">النوع</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600">الحالة</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600">التاريخ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentComplaints as $complaint)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $complaint->type->name ?? '-' }}</td>
                                <td class="px-4 py-3"><x-status-badge :status="$complaint->status" /></td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $complaint->created_at->format('Y/m/d') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-gray-500">لا توجد شكاوى</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
            <div class="bg-amber-600 text-white px-6 py-4 flex items-center justify-between">
                <h2 class="font-bold text-lg">آخر الفواتير</h2>
                <a href="{{ route('bills.index') }}" class="text-white hover:underline text-sm">عرض الكل</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600">النوع</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600">المبلغ</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentBills as $bill)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $bill->bill_type ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($bill->amount, 0) }} ل.س</td>
                                <td class="px-4 py-3"><x-status-badge :status="$bill->status" /></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-gray-500">لا توجد فواتير</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-md p-6">
        <h2 class="font-bold text-lg text-gray-900 mb-4">إجراءات سريعة</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('complaints.create') }}" class="flex items-center gap-3 bg-blue-50 hover:bg-blue-100 rounded-xl p-4 transition-colors">
                <div class="bg-blue-600 rounded-lg p-2">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <span class="font-semibold text-blue-900">تقديم شكوى</span>
            </a>
            <a href="{{ route('inquiries.create') }}" class="flex items-center gap-3 bg-green-50 hover:bg-green-100 rounded-xl p-4 transition-colors">
                <div class="bg-green-600 rounded-lg p-2">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01"/>
                    </svg>
                </div>
                <span class="font-semibold text-green-900">طلب استعلام</span>
            </a>
            <a href="{{ route('bills.index') }}" class="flex items-center gap-3 bg-amber-50 hover:bg-amber-100 rounded-xl p-4 transition-colors">
                <div class="bg-amber-600 rounded-lg p-2">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="font-semibold text-amber-900">دفع الفواتير</span>
            </a>
        </div>
    </div>
</div>
@endsection
