@extends('layouts.app')

@section('title', __('Request Inquiry'))

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('inquiries.index') }}" class="text-green-600 hover:text-green-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ __('New Inquiry') }}</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-md p-8">
        <form method="POST" action="{{ route('inquiries.store') }}" enctype="multipart/form-data" class="space-y-6" x-data="inquiryForm()">
            @csrf

            <div>
                <label for="type_id" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Inquiry Type') }}</label>
                <select id="type_id" name="type_id" required x-model="type_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white">
                    <option value="">{{ __('Choose inquiry type') }}</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <template x-if="config.fields.length > 0">
                <div class="space-y-4 bg-blue-50 p-5 rounded-xl border border-blue-100">
                    <label class="block text-sm font-bold text-blue-800 border-b border-blue-200 pb-2">{{ __('Required Data for this Inquiry') }}</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <template x-for="field in config.fields">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2" x-text="field"></label>
                                <input type="text" :name="'custom_fields[' + field + ']'" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white">
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <div class="space-y-4 bg-gray-50 p-5 rounded-xl border border-gray-100">
                <label class="block text-sm font-bold text-gray-800 border-b pb-2">{{ __('Required Attachments and Documents') }}</label>

                <template x-for="(fileLabel, index) in config.files" :key="index">
                    <div class="mt-3">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            <span x-text="fileLabel"></span> <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="attachments[]" required accept="image/*,.pdf"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white">
                    </div>
                </template>

                <template x-for="(item, index) in attachments" :key="index">
                    <div class="flex items-end gap-3 mt-3" x-show="index > 0">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">{{ __('Additional Attachment') }}</label>
                            <input type="file" name="attachments[]" accept="image/*,.pdf"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white">
                        </div>
                        <button type="button" @click="attachments.splice(index, 1)" title="Delete" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl hover:bg-red-100 transition-colors shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </template>

                <button type="button" @click="attachments.push(Date.now())" class="mt-4 inline-flex items-center gap-2 text-sm text-green-700 font-bold bg-green-50 hover:bg-green-100 px-4 py-2 rounded-lg transition-colors border border-green-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Add Another Attachment') }}
                </button>
            </div>

            <div>
                <label for="details" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Additional Notes (Optional)') }}</label>
                <textarea id="details" name="details" rows="4"
                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">{{ old('details') }}</textarea>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl transition-colors shadow-lg">
                    {{ __('Submit Request') }}
                </button>
                <a href="{{ route('inquiries.index') }}" class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition-colors font-semibold">
                    {{ __('Cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

<script>
const inquiryTypesMap = @json($types->pluck('name', 'id'));

document.addEventListener('alpine:init', () => {
    Alpine.data('inquiryForm', () => ({
        type_id: '{{ old('type_id') }}',
        attachments: [1],
        get config() {
            const id = this.type_id;
            if (!id) return { files: [], fields: [] };

            const typeName = inquiryTypesMap[id] || '';

            if (!typeName || typeName === '{{ __("Choose inquiry type") }}') {
                return { files: [], fields: [] };
            }
            if (typeName.includes('بيان عائلي') || typeName.includes('Family')) {
                return { files: ['صورة الهوية الشخصية', 'صورة عن دفتر العائلة'], fields: ['رقم القيد', 'الخانة'] };
            }
            if (typeName.includes('لا حكم عليه') || typeName.includes('No conviction')) {
                return { files: ['صورة الهوية الشخصية', 'صورة شخصية حديثة'], fields: ['الاسم الرباعي', 'اسم الأم'] };
            }
            if (typeName.includes('جواز سفر') || typeName.includes('Passport')) {
                return { files: ['صورة الهوية الشخصية', 'موافقة شعبة التجنيد (للذكور)', 'صور شخصية عدد 4'], fields: ['المهنة المراد كتابتها', 'رقم الهاتف'] };
            }
            if (typeName.includes('مخالفات') || typeName.includes('Violations')) {
                return { files: ['صورة الهوية الشخصية', 'صورة رخصة القيادة'], fields: ['رقم المركبة', 'نوع المركبة'] };
            }
            if (typeName.includes('مركبة') || typeName.includes('Vehicle')) {
                return { files: ['صورة الهوية الشخصية', 'عقد الشراء'], fields: ['رقم الهيكل', 'رقم المحرك'] };
            }
            if (typeName.includes('ميلاد') || typeName.includes('Birth')) {
                return { files: ['صورة الهوية الشخصية', 'شهادة المشفى'], fields: ['اسم المولود', 'تاريخ الولادة'] };
            }

            return { files: ['صورة الهوية الشخصية (أساسي)'], fields: [] };
        }
    }));
});
</script>
