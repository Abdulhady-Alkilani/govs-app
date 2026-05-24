<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\AiController;

Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::get('/lang/{locale}', [LanguageController::class, 'switch'])->name('lang.switch');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/logout', [AuthController::class, 'logout']);

    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
    });

    Route::prefix('complaints')->name('complaints.')->group(function () {
        Route::get('/', [ComplaintController::class, 'index'])->name('index');
        Route::get('/create', [ComplaintController::class, 'create'])->name('create');
        Route::post('/', [ComplaintController::class, 'store'])->name('store');
        Route::get('/{id}', [ComplaintController::class, 'show'])->name('show');
    });

    Route::prefix('inquiries')->name('inquiries.')->group(function () {
        Route::get('/', [InquiryController::class, 'index'])->name('index');
        Route::get('/create', [InquiryController::class, 'create'])->name('create');
        Route::post('/', [InquiryController::class, 'store'])->name('store');
        Route::get('/{id}', [InquiryController::class, 'show'])->name('show');
    });

    Route::prefix('bills')->name('bills.')->group(function () {
        Route::get('/', [BillController::class, 'index'])->name('index');
        Route::get('/create', [BillController::class, 'create'])->name('create');
        Route::post('/', [BillController::class, 'store'])->name('store');
        Route::get('/{id}/pay', [BillController::class, 'showPaymentForm'])->name('pay');
        Route::post('/{id}/pay', [BillController::class, 'processPayment'])->name('process');
        Route::get('/{id}/show', [BillController::class, 'show'])->name('show');
    });

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read_all');
    });

    // AI Routes
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::post('/generate-reply', [AiController::class, 'generateReply'])->name('generate-reply');
        Route::post('/enhance-text', [AiController::class, 'enhanceText'])->name('enhance-text');
    });

});
