<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// الصفحة الرئيسية للموقع (تهبط بالزائر إلى صفحة تسجيل الدخول إذا لم يكن مسجلاً)
Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| مسارات الزوار (Guest Routes)
|--------------------------------------------------------------------------
| هذه المسارات متاحة فقط لمن لم يسجل دخوله (لا يمكن للمسجل رؤية صفحة الدخول مجدداً)
*/
Route::middleware('guest')->group(function () {
    // تسجيل الدخول
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    // إنشاء حساب جديد
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

/*
|--------------------------------------------------------------------------
| مسارات المواطن المسجل (Authenticated Routes)
|--------------------------------------------------------------------------
| جميع هذه المسارات محمية بـ auth، ولا يمكن الوصول إليها إلا بعد تسجيل الدخول
*/
Route::middleware('auth')->group(function () {
    
    // تسجيل الخروج
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // لوحة تحكم المواطن (الرئيسية)
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // ==========================================
    // مسارات نظام الشكاوى (Complaints)
    // ==========================================
    Route::prefix('complaints')->name('complaints.')->group(function () {
        Route::get('/', [ComplaintController::class, 'index'])->name('index');           // عرض كل الشكاوى
        Route::get('/create', [ComplaintController::class, 'create'])->name('create');   // صفحة تقديم الشكوى
        Route::post('/', [ComplaintController::class, 'store'])->name('store');          // حفظ الشكوى الجديدة
        Route::get('/{id}', [ComplaintController::class, 'show'])->name('show');         // عرض تفاصيل شكوى محددة
    });

    // ==========================================
    // مسارات نظام الاستعلامات (Inquiries)
    // ==========================================
    Route::prefix('inquiries')->name('inquiries.')->group(function () {
        Route::get('/', [InquiryController::class, 'index'])->name('index');             // عرض كل الاستعلامات
        Route::get('/create', [InquiryController::class, 'create'])->name('create');     // صفحة تقديم طلب الاستعلام
        Route::post('/', [InquiryController::class, 'store'])->name('store');            // حفظ طلب الاستعلام
        Route::get('/{id}', [InquiryController::class, 'show'])->name('show');           // عرض تفاصيل استعلام ونتيجته
    });

    // ==========================================
    // مسارات نظام الفواتير والدفع (Bills)
    // ==========================================
    Route::prefix('bills')->name('bills.')->group(function () {
        Route::get('/', [BillController::class, 'index'])->name('index');                        // عرض الفواتير
        Route::get('/{id}/pay', [BillController::class, 'showPaymentForm'])->name('pay');        // صفحة تسديد الفاتورة (محاكاة شام كاش)
        Route::post('/{id}/pay', [BillController::class, 'processPayment'])->name('process');    // معالجة الدفع
    });

    // ==========================================
    // مسارات نظام الإشعارات (Notifications)
    // ==========================================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');                // عرض جميع الإشعارات
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');  // تحديد إشعار واحد كمقروء
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read_all'); // تحديد الكل كمقروء
    });

});