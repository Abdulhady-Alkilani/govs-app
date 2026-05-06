@extends('layouts.app')

@section('title', 'الفواتير')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">الفواتير</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-amber-600 text-white">
                    <tr>
                        <th class="px-6 py-4 text-right text-sm font-semibold">#</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold">نوع الفاتورة</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold">المبلغ</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold">الحالة</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold">تاريخ الاستحقاق</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold">تاريخ الدفع</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold">رقم المعاملة</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($bills as $bill)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $bill->id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $bill->bill_type }}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ number_format($bill->amount, 0) }} ل.س</td>
                            <td class="px-6 py-4"><x-status-badge :status="$bill->status" /></td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $bill->due_date ? $bill->due_date->format('Y/m/d') : '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $bill->paid_at ? $bill->paid_at->format('Y/m/d H:i') : '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $bill->transaction_id ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @if($bill->status === 'unpaid')
                                    <a href="{{ route('bills.pay', $bill->id) }}" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-1.5 rounded-lg text-sm font-semibold transition-colors">
                                        دفع
                                    </a>
                                @else
                                    <span class="text-gray-400 text-sm">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">لا توجد فواتير</td>
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
