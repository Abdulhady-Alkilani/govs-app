@extends('layouts.app')

@section('title', __('Submit Complaint'))

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('complaints.index') }}" class="text-blue-600 hover:text-blue-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ __('New Complaint') }}</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-md p-8">
        <form method="POST" action="{{ route('complaints.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div>
                <label for="type_id" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Complaint Type') }}</label>
                <select id="type_id" name="type_id" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-white">
                    <option value="">{{ __('Choose complaint type') }}</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" {{ old('type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- ===== الميزة 5: مصحح النصوص الذكي بالذكاء الاصطناعي ===== --}}
            <div x-data="{
                description: '{{ old('description', '') }}',
                isEnhancing: false,
                enhanced: false,
                errorMsg: '',
                async enhanceText() {
                    if (!this.description || this.description.trim().length < 10) {
                        this.errorMsg = '{{ __('يرجى كتابة نص الشكوى أولاً (10 أحرف على الأقل)') }}';
                        return;
                    }
                    this.isEnhancing = true;
                    this.errorMsg = '';
                    this.enhanced = false;
                    try {
                        const response = await fetch('{{ route('ai.enhance-text') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ text: this.description }),
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.description = data.enhanced_text;
                            this.enhanced = true;
                            setTimeout(() => this.enhanced = false, 3000);
                        } else {
                            this.errorMsg = data.message || '{{ __('حدث خطأ أثناء تحسين النص') }}';
                        }
                    } catch (err) {
                        this.errorMsg = '{{ __('حدث خطأ في الاتصال. يرجى المحاولة مرة أخرى.') }}';
                    } finally {
                        this.isEnhancing = false;
                    }
                }
            }">
                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Description') }}</label>
                <textarea id="description" name="description" rows="6" required
                          x-model="description"
                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition resize-y"
                          :class="{ 'border-green-400 ring-2 ring-green-200': enhanced }"
                          placeholder="{{ __('Write a detailed description of your complaint...') }}"></textarea>

                {{-- زر تحسين الصياغة --}}
                <div class="flex items-center gap-3 mt-3">
                    <button type="button"
                            x-on:click="enhanceText()"
                            :disabled="isEnhancing"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed text-sm">
                        {{-- أيقونة التحميل أثناء المعالجة --}}
                        <template x-if="isEnhancing">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <template x-if="!isEnhancing">
                            <span>✨</span>
                        </template>
                        <span x-text="isEnhancing ? '{{ __('جاري التحسين...') }}' : '{{ __('حسّن الصياغة بالذكاء الاصطناعي') }}'"></span>
                    </button>

                    {{-- رسالة النجاح --}}
                    <span x-show="enhanced" x-transition class="text-green-600 text-sm font-medium">
                        ✅ {{ __('تم تحسين النص بنجاح!') }}
                    </span>
                </div>

                {{-- رسالة الخطأ --}}
                <p x-show="errorMsg" x-text="errorMsg" x-transition class="text-red-500 text-sm mt-2"></p>
            </div>

            <div>
                <label for="attachments" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Attachments') }}</label>
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition-colors">
                    <svg class="w-10 h-10 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <input type="file" id="attachments" name="attachments[]" multiple accept="image/*,.pdf"
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-900 file:text-white file:font-semibold hover:file:bg-blue-950 file:transition-colors">
                    <p class="text-xs text-gray-500 mt-2">{{ __('You can upload images or PDF files (max 10MB)') }}</p>
                </div>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-blue-900 hover:bg-blue-950 text-white font-bold py-3 rounded-xl transition-colors shadow-lg">
                    {{ __('Submit') }}
                </button>
                <a href="{{ route('complaints.index') }}" class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition-colors font-semibold">
                    {{ __('Cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
