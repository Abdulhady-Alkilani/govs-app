<?php

namespace App\Providers;

use App\Models\Bill;
use App\Models\Complaint;
use App\Models\Inquiry;
use App\Observers\ComplaintObserver;
use App\Observers\InquiryObserver;
use App\Observers\SystemLogObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Complaint::observe(ComplaintObserver::class);
        Inquiry::observe(InquiryObserver::class);
        Complaint::observe(SystemLogObserver::class);
        Inquiry::observe(SystemLogObserver::class);
        Bill::observe(SystemLogObserver::class);
    }
}
