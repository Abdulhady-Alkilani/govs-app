@extends('layouts.app')

@section('title', 'تفاصيل الاستعلام')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('inquiries.index') }}" class="text-green-600 hover:text-green-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">تفاصيل الاستعلام #{{ $inquiry->id }}</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <div class="bg-green-700 text-white px-6 py-4 flex items-center justify-between">
            <span class="font-bold">الاستعلام #{{ $inquiry->id }}</span>
            <x-status-badge :status="$inquiry->status" />
        </div>

        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-gray-500 mb-1">نوع الاستعلام</p>
                    <p class="font-semibold text-gray-900">{{ $inquiry->type->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">تاريخ الطلب</p>
                    <p class="font-semibold text-gray-900">{{ $inquiry->created_at->format('Y/m/d H:i') }}</p>
                </div>
            </div>

            @if($inquiry->result_text)
                <div>
                    <p class="text-sm text-gray-500 mb-2">نتيجة الاستعلام</p>
                    <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-gray-800 leading-relaxed whitespace-pre-line">
                        {{ $inquiry->result_text }}
                    </div>
                </div>
            @endif

            @if($inquiry->result_file_path)
                <div>
                    <p class="text-sm text-gray-500 mb-2">ملف النتيجة</p>
                    <a href="{{ Storage::url($inquiry->result_file_path) }}" target="_blank"
                       class="inline-flex items-center gap-2 bg-green-100 hover:bg-green-200 text-green-800 px-4 py-2 rounded-xl transition-colors font-semibold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        تحميل الملف
                    </a>
                </div>
            @endif
        </div>
    </div>

    <a href="{{ route('inquiries.index') }}" class="inline-flex items-center gap-2 text-green-600 hover:text-green-800 font-semibold">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        العودة للاستعلامات
    </a>
</div>
@endsection
