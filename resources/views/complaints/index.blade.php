@extends('layouts.app')

@section('title', 'الشكاوى')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">الشكاوى</h1>
        <a href="{{ route('complaints.create') }}" class="bg-blue-900 hover:bg-blue-950 text-white px-6 py-2.5 rounded-xl font-semibold transition-colors shadow-md">
            تقديم شكوى جديدة
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-blue-900 text-white">
                    <tr>
                        <th class="px-6 py-4 text-right text-sm font-semibold">#</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold">النوع</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold">الحالة</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold">تاريخ التقديم</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($complaints as $complaint)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $complaint->id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $complaint->type->name ?? '-' }}</td>
                            <td class="px-6 py-4"><x-status-badge :status="$complaint->status" /></td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $complaint->created_at->format('Y/m/d H:i') }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('complaints.show', $complaint->id) }}" class="text-blue-600 hover:text-blue-800 font-semibold text-sm">
                                    عرض التفاصيل
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">لا توجد شكاوى بعد</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($complaints->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $complaints->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
