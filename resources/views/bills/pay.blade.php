@extends('layouts.app')

@section('title', 'دفع الفاتورة')

@section('content')
<div class="max-w-lg mx-auto space-y-6" x-data="{ showModal: true }">
    <div class="flex items-center gap-4">
        <a href="{{ route('bills.index') }}" class="text-amber-600 hover:text-amber-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">دفع الفاتورة #{{ $bill->id }}</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <div class="bg-amber-600 text-white px-6 py-4">
            <p class="font-bold text-lg">تفاصيل الفاتورة</p>
        </div>

        <div class="p-6 space-y-4">
            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                <span class="text-gray-500">نوع الفاتورة</span>
                <span class="font-semibold text-gray-900">{{ $bill->bill_type }}</span>
            </div>
            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                <span class="text-gray-500">المبلغ المطلوب</span>
                <span class="font-bold text-2xl text-amber-600">{{ number_format($bill->amount, 0) }} ل.س</span>
            </div>
            <div class="flex justify-between items-center py-3">
                <span class="text-gray-500">تاريخ الاستحقاق</span>
                <span class="font-semibold text-gray-900">{{ $bill->due_date ? $bill->due_date->format('Y/m/d') : '-' }}</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-md overflow-hidden" x-show="showModal">
        <div class="bg-green-700 text-white px-6 py-4 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <span class="font-bold">الدفع عبر شام كاش</span>
        </div>

        <div class="p-6">
            <form method="POST" action="{{ route('bills.process', $bill->id) }}" class="space-y-5" x-data="{ phone: '', code: '' }">
                @csrf

                <div>
                    <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">رقم الهاتف</label>
                    <input type="tel" id="phone" name="phone" x-model="phone" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition"
                           placeholder="مثال: 0991234567">
                </div>

                <div>
                    <label for="code" class="block text-sm font-semibold text-gray-700 mb-2">رمز التحقق</label>
                    <input type="text" id="code" name="code" x-model="code" required maxlength="6"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition text-center text-lg tracking-widest"
                           placeholder="أدخل رمز التحقق">
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
                    <p>هذه عملية محاكاة للدفع. لن يتم خصم أي مبلغ حقيقي.</p>
                </div>

                <div class="flex gap-4">
                    <button type="submit"
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl transition-colors shadow-lg">
                        تأكيد الدفع
                    </button>
                    <a href="{{ route('bills.index') }}" class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition-colors font-semibold">
                        إلغاء
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
