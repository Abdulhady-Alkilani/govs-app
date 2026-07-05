@extends('layouts.app')

@section('title', __('Complaint Details'))

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('complaints.index') }}" class="text-blue-600 hover:text-blue-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Complaint Details') }} #{{ $complaint->id }}</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <div class="bg-blue-900 text-white px-6 py-4 flex items-center justify-between">
            <span class="font-bold">{{ __('Complaint') }} #{{ $complaint->id }}</span>
            <x-status-badge :status="$complaint->status" />
        </div>

        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-gray-500 mb-1">{{ __('Complaint Type') }}</p>
                    <p class="font-semibold text-gray-900">{{ $complaint->type->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">{{ __('Submission Date') }}</p>
                    <p class="font-semibold text-gray-900">{{ $complaint->created_at->format('Y/m/d H:i') }}</p>
                </div>
            </div>

            <div>
                <p class="text-sm text-gray-500 mb-2">{{ __('Complaint Description') }}</p>
                <div class="bg-gray-50 rounded-xl p-4 text-gray-800 leading-relaxed whitespace-pre-line">{{ $complaint->description }}</div>
            </div>

            {{-- ===== الميزة 1: عرض تحليل الذكاء الاصطناعي للمواطن ===== --}}
            @if($complaint->ai_summary || $complaint->ai_priority)
                <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl p-5 border border-indigo-100">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <h3 class="text-sm font-bold text-indigo-800">{{ __('AI Analysis') }}</h3>
                    </div>

                    @if($complaint->ai_priority)
                        <div class="mb-3">
                            <span class="text-xs text-gray-500 font-medium">{{ __('Priority') }}:</span>
                            @php
                                $priorityConfig = match($complaint->ai_priority) {
                                    'high' => ['label' => __('High'), 'emoji' => '🔴', 'class' => 'bg-red-100 text-red-800 border-red-200'],
                                    'medium' => ['label' => __('Medium'), 'emoji' => '🟡', 'class' => 'bg-yellow-100 text-yellow-800 border-yellow-200'],
                                    'low' => ['label' => __('Low'), 'emoji' => '⚪', 'class' => 'bg-gray-100 text-gray-800 border-gray-200'],
                                    default => ['label' => $complaint->ai_priority, 'emoji' => '⚪', 'class' => 'bg-gray-100 text-gray-800 border-gray-200'],
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold border {{ $priorityConfig['class'] }}">
                                {{ $priorityConfig['emoji'] }} {{ $priorityConfig['label'] }}
                            </span>
                        </div>
                    @endif

                    @if($complaint->ai_summary)
                        <div>
                            <span class="text-xs text-gray-500 font-medium">{{ __('Summary') }}:</span>
                            <p class="text-sm text-indigo-900 mt-1 leading-relaxed">{{ $complaint->ai_summary }}</p>
                        </div>
                    @endif
                </div>
            @endif

            @if($complaint->internal_notes)
                <div>
                    <p class="text-sm text-gray-500 mb-2">{{ __('Admin Reply') }}</p>
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-gray-800 leading-relaxed whitespace-pre-line">
                        {{ $complaint->internal_notes }}
                    </div>
                </div>
            @endif

            @if($complaint->attachments && $complaint->attachments->count() > 0)
                <div>
                    <p class="text-sm text-gray-500 mb-3">{{ __('Attachments') }}</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        @foreach($complaint->attachments as $attachment)
                            @php
                                $isImage = in_array(strtolower(pathinfo($attachment->file_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            @endphp
                            @if($isImage)
                                <div class="border border-gray-200 rounded-xl overflow-hidden relative">
                                    <img src="{{ Storage::url($attachment->file_path) }}" alt="{{ __('Attachment') }}" class="w-full h-32 object-cover">
                                    {{-- ===== الميزة 6: عرض حالة التحقق من المرفق ===== --}}
                                    @if($attachment->is_ai_verified === true)
                                        <div class="absolute top-2 right-2 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-lg shadow-md flex items-center gap-1" title="{{ __('AI Verified Document') }}">
                                            ✅ {{ __('Verified') }}
                                        </div>
                                    @elseif($attachment->is_ai_verified === false)
                                        <div class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-lg shadow-md flex items-center gap-1" title="{{ __('Not a valid document') }}">
                                            ❌ {{ __('Unverified') }}
                                        </div>
                                    @endif
                                </div>
                            @else
                                <a href="{{ Storage::url($attachment->file_path) }}" target="_blank"
                                   class="flex items-center gap-2 border border-gray-200 rounded-xl p-3 hover:bg-gray-50 transition-colors">
                                    <svg class="w-6 h-6 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="text-sm text-blue-600 font-semibold">{{ __('Download File') }}</span>
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
        {{ __('Back to Complaints') }}
    </a>
</div>
@endsection
