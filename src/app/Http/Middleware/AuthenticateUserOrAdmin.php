<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateUserOrAdmin
{
    /**
     * 一般ユーザーまたは管理者のいずれかで認証されている必要がある
     * 一般ユーザーの場合はメール認証済みも必須
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('admin')->check()) {
            return $next($request);
        }

        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            if ($user && !is_null($user->email_verified_at)) {
                return $next($request);
            }
            return redirect()->route('verification.notice');
        }

        return redirect()->route('login');
    }
}
