<?php

namespace App\Providers;

use App\Models\Bill;
use App\Models\Complaint;
use App\Models\Inquiry;
use App\Observers\ComplaintObserver;
use App\Observers\InquiryObserver;
use App\Observers\SystemLogObserver;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.tailwind');

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['ar', 'en'])
                ->labels([
                    'ar' => 'العربية',
                    'en' => 'English',
                ])
                ->displayLocale(app()->getLocale())
                ->visible(outsidePanels: false)
                ->circular();
        });

        Complaint::observe(ComplaintObserver::class);
        Inquiry::observe(InquiryObserver::class);
        Complaint::observe(SystemLogObserver::class);
        Inquiry::observe(SystemLogObserver::class);
        Bill::observe(SystemLogObserver::class);
    }
}
