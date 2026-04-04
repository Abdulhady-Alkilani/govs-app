<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // جلب إحصائيات خاصة بالمواطن فقط
        $complaintsCount = $user->complaints()->count();
        $inquiriesCount = $user->inquiries()->count();
        $unpaidBillsCount = $user->bills()->where('status', 'unpaid')->count();
        
        // جلب آخر النشاطات
        $recentComplaints = $user->complaints()->with('type')->latest()->take(3)->get();
        $recentBills = $user->bills()->latest()->take(3)->get();

        return view('home.index', compact(
            'complaintsCount', 'inquiriesCount', 'unpaidBillsCount', 
            'recentComplaints', 'recentBills'
        ));
    }
}