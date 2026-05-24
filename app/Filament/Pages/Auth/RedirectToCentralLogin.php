<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;

class RedirectToCentralLogin extends BaseLogin
{
    public function mount(): void
    {
        redirect('/login')->send();
        exit;
    }
}
