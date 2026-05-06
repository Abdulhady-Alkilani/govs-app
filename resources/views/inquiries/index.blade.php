@extends('layouts.app')

@section('title', 'الاستعلامات')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">الاستعلامات</h1>
        <a href="{{ route('inquiries.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-xl font-semibold transition-colors shadow-md">
            طلب استعلام جديد
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-green-700 text-white">
                    <tr>
                        <th class="px-6 py-4 text-right text-sm font-semibold">#</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold">النوع</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold">الحالة</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold">تاريخ الطلب</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($inquiries as $inquiry)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $inquiry->id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $inquiry->type->name ?? '-' }}</td>
                            <td class="px-6 py-4"><x-status-badge :status="$inquiry->status" /></td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $inquiry->created_at->format('Y/m/d H:i') }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('inquiries.show', $inquiry->id) }}" class="text-green-600 hover:text-green-800 font-semibold text-sm">
                                    عرض التفاصيل
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">لا توجد استعلامات بعد</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($inquiries->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $inquiries->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
