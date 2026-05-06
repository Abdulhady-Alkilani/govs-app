@extends('layouts.app')

@section('title', 'تفاصيل الشكوى')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('complaints.index') }}" class="text-blue-600 hover:text-blue-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">تفاصيل الشكوى #{{ $complaint->id }}</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <div class="bg-blue-900 text-white px-6 py-4 flex items-center justify-between">
            <span class="font-bold">الشكوى #{{ $complaint->id }}</span>
            <x-status-badge :status="$complaint->status" />
        </div>

        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-gray-500 mb-1">نوع الشكوى</p>
                    <p class="font-semibold text-gray-900">{{ $complaint->type->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">تاريخ التقديم</p>
                    <p class="font-semibold text-gray-900">{{ $complaint->created_at->format('Y/m/d H:i') }}</p>
                </div>
            </div>

            <div>
                <p class="text-sm text-gray-500 mb-2">وصف الشكوى</p>
                <div class="bg-gray-50 rounded-xl p-4 text-gray-800 leading-relaxed whitespace-pre-line">{{ $complaint->description }}</div>
            </div>

            @if($complaint->attachments && $complaint->attachments->count() > 0)
                <div>
                    <p class="text-sm text-gray-500 mb-3">المرفقات</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        @foreach($complaint->attachments as $attachment)
                            @php
                                $isImage = in_array(strtolower(pathinfo($attachment->file_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            @endphp
                            @if($isImage)
                                <div class="border border-gray-200 rounded-xl overflow-hidden">
                                    <img src="{{ Storage::url($attachment->file_path) }}" alt="مرفق" class="w-full h-32 object-cover">
                                </div>
                            @else
                                <a href="{{ Storage::url($attachment->file_path) }}" target="_blank"
                                   class="flex items-center gap-2 border border-gray-200 rounded-xl p-3 hover:bg-gray-50 transition-colors">
                                    <svg class="w-6 h-6 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="text-sm text-blue-600 font-semibold">تحميل الملف</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <a href="{{ route('complaints.index') }}" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-semibold">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        العودة للشكاوى
    </a>
</div>
@endsection
