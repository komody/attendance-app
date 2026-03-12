<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetFortifyAdminConfig
{
    /**
     * Handle an incoming request.
     * /admin/* のリクエスト時に Fortify の設定を admin 用に切り替える
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        config([
            'fortify.guard' => 'admin',
            'fortify.home' => '/admin/attendance/list',
        ]);

        return $next($request);
    }
}
