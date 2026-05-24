@extends('layouts.app')

@section('title', __('Inquiries'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Inquiries') }}</h1>
        <a href="{{ route('inquiries.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-xl font-semibold transition-colors shadow-md">
            {{ __('New Inquiry') }}
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-md p-4">
        <form method="GET" action="{{ route('inquiries.index') }}" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by inquiry number or type...') }}" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
            </div>
            <div class="w-full md:w-48">
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-white">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>{{ __('Processing') }}</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                </select>
            </div>
            <div class="w-full md:w-48">
                <select name="sort" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-white">
                    <option value="latest" {{ request('sort', 'latest') == 'latest' ? 'selected' : '' }}>{{ __('Latest First') }}</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>{{ __('Oldest First') }}</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-6 py-2 rounded-xl font-semibold transition-colors shadow-md">
                    {{ __('Apply') }}
                </button>
                @if(request()->hasAny(['search', 'status', 'sort']) && (request('search') || request('status') || request('sort') != 'latest'))
                    <a href="{{ route('inquiries.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl font-semibold transition-colors flex items-center justify-center">
                        {{ __('Clear') }}
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-green-700 text-white">
                    <tr>
                        <th class="px-6 py-4 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-sm font-semibold">#</th>
                        <th class="px-6 py-4 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-sm font-semibold">{{ __('Type') }}</th>
                        <th class="px-6 py-4 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-sm font-semibold">{{ __('Status') }}</th>
                        <th class="px-6 py-4 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-sm font-semibold">{{ __('Request Date') }}</th>
                        <th class="px-6 py-4 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-sm font-semibold">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($inquiries as $inquiry)
                        <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="window.location='{{ route('inquiries.show', $inquiry->id) }}'">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $inquiry->id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $inquiry->type->name ?? '-' }}</td>
                            <td class="px-6 py-4"><x-status-badge :status="$inquiry->status" /></td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $inquiry->created_at->format('Y/m/d H:i') }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('inquiries.show', $inquiry->id) }}" class="text-green-600 hover:text-green-800 font-semibold text-sm">
                                    {{ __('View Details') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">{{ __('No inquiries found') }}</td>
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
