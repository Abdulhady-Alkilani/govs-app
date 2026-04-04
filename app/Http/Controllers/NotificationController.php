<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->customNotifications()->latest()->paginate(15);
        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $notification = Auth::user()->customNotifications()->findOrFail($id);
        
        $notification->update([
            'is_read' => true
        ]);

        return back()->with('success', 'تم تحديد الإشعار كمقروء.');
    }

    public function markAllAsRead()
    {
        Auth::user()->customNotifications()->where('is_read', false)->update([
            'is_read' => true
        ]);

        return back()->with('success', 'تم تحديد جميع الإشعارات كمقروءة.');
    }
}