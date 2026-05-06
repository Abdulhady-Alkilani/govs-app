@extends('layouts.app')

@section('title', 'تقديم شكوى')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('complaints.index') }}" class="text-blue-600 hover:text-blue-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">تقديم شكوى جديدة</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-md p-8">
        <form method="POST" action="{{ route('complaints.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div>
                <label for="type_id" class="block text-sm font-semibold text-gray-700 mb-2">نوع الشكوى</label>
                <select id="type_id" name="type_id" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-white">
                    <option value="">اختر نوع الشكوى</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" {{ old('type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">وصف الشكوى</label>
                <textarea id="description" name="description" rows="6" required
                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition resize-y"
                          placeholder="اكتب وصفاً تفصيلياً للشكوى...">{{ old('description') }}</textarea>
            </div>

            <div>
                <label for="attachments" class="block text-sm font-semibold text-gray-700 mb-2">المرفقات</label>
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition-colors">
                    <svg class="w-10 h-10 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <input type="file" id="attachments" name="attachments[]" multiple accept="image/*,.pdf"
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-900 file:text-white file:font-semibold hover:file:bg-blue-950 file:transition-colors">
                    <p class="text-xs text-gray-500 mt-2">يمكنك رفع صور أو ملفات PDF (الحد الأقصى 10MB)</p>
                </div>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-blue-900 hover:bg-blue-950 text-white font-bold py-3 rounded-xl transition-colors shadow-lg">
                    تقديم الشكوى
                </button>
                <a href="{{ route('complaints.index') }}" class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition-colors font-semibold">
                    إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
