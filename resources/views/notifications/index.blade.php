@extends('layouts.app')

@section('title', __('Notifications'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Notifications') }}</h1>
        <form method="POST" action="{{ route('notifications.read_all') }}">
            @csrf
            <button type="submit" class="bg-blue-900 hover:bg-blue-950 text-white px-6 py-2.5 rounded-xl font-semibold transition-colors shadow-md text-sm">
                {{ __('Mark All as Read') }}
            </button>
        </form>
    </div>

    <div class="space-y-3">
        @forelse($notifications as $notification)
            <div class="bg-white rounded-2xl shadow-md overflow-hidden {{ $notification->is_read ? '' : (app()->getLocale() === 'ar' ? 'border-r-4' : 'border-l-4') . ' border-blue-500 bg-blue-50' }}">
                <div class="p-6 flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4 flex-1">
                        <div class="shrink-0 mt-1">
                            @if($notification->is_read)
                                <div class="w-3 h-3 rounded-full bg-gray-300"></div>
                            @else
                                <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-gray-900">{{ $notification->title ?? __('Notification') }}</p>
                            <p class="text-gray-600 mt-1">{{ $notification->message }}</p>
                            <p class="text-xs text-gray-400 mt-2">{{ $notification->created_at->format('Y/m/d H:i') }}</p>
                        </div>
                    </div>

                    @if(!$notification->is_read)
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                            @csrf
                            <button type="submit" class="text-blue-600 hover:text-blue-800 text-sm font-semibold whitespace-nowrap bg-blue-100 hover:bg-blue-200 px-4 py-2 rounded-lg transition-colors">
                                {{ $notification->action_url ? __('View Details / Process') : __('Mark as Read') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl shadow-md p-12 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <p class="text-gray-500 text-lg">{{ __('No notifications') }}</p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
