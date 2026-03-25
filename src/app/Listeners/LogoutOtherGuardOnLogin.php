<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;

class LogoutOtherGuardOnLogin
{
    /**
     * 後からログインしたガードだけを有効にし、もう一方のガードをログアウトする
     */
    public function handle(Login $event): void
    {
        if ($event->guard === 'web') {
            Auth::guard('admin')->logout();
            return;
        }

        if ($event->guard === 'admin') {
            Auth::guard('web')->logout();
        }
    }
}
