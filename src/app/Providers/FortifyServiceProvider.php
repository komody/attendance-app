<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\LoginRequest;
use App\Models\Admin;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use App\Notifications\VerifyEmail;

class FortifyServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {
        Fortify::ignoreRoutes();
    }

    public function boot(): void
    {
        $this->configureFortify();
        $this->configureRegisterResponse();
        $this->configureRateLimiters();
        $this->configureLoginResponse();
        $this->configureLogoutResponse();
        $this->configureAuthenticateUsing();
        $this->configureRoutes();
    }

    private function configureFortify(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::registerView(fn () => view('auth.register'));
        Fortify::loginView(fn () => request()->is('admin*')
            ? view('admin.login')
            : view('auth.login'));
    }

    private function configureRegisterResponse(): void
    {
        $this->app->singleton(\Laravel\Fortify\Contracts\RegisterResponse::class, function () {
            return new class implements \Laravel\Fortify\Contracts\RegisterResponse {
                public function toResponse($request)
                {
                    return redirect()->route('verification.notice');
                }
            };
        });
    }

    private function configureRateLimiters(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }

    private function configureLoginResponse(): void
    {
        $this->app->singleton(\Laravel\Fortify\Contracts\LoginResponse::class, function () {
            return new class implements \Laravel\Fortify\Contracts\LoginResponse {
                public function toResponse($request)
                {
                    // 管理者ログイン時は admin ガードから取得（$request->user() は web ガードのため null）
                    $user = $request->is('admin*')
                        ? Auth::guard('admin')->user()
                        : $request->user();

                    // 管理者ログイン時（メール認証なし）
                    if ($user instanceof Admin) {
                        return redirect()->intended('/admin/attendance/list');
                    }

                    // メール認証未完了の場合（会員登録時のメール認証）
                    if (is_null($user->email_verified_at)) {
                        return redirect()->route('verification.notice');
                    }

                    // 初回ログイン時のメール認証未完了の場合
                    if (is_null($user->first_login_email_verified_at)) {
                        $user->notify(new VerifyEmail);
                        session()->put('first_login', true);
                        return redirect()->route('verification.notice');
                    }

                    // 通常のログイン成功時
                    return redirect()->intended(RouteServiceProvider::HOME);
                }
            };
        });
    }

    private function configureLogoutResponse(): void
    {
        $this->app->singleton(\Laravel\Fortify\Contracts\LogoutResponse::class, function () {
            return new class implements \Laravel\Fortify\Contracts\LogoutResponse {
                public function toResponse($request)
                {
                    if ($request->is('admin/logout') || strpos($request->path(), 'admin/') === 0) {
                        return redirect()->route('admin.login');
                    }

                    return redirect('/');
                }
            };
        });
    }

    private function configureAuthenticateUsing(): void
    {
        Fortify::authenticateUsing(function (Request $request) {
            $formRequest = $request->is('admin*')
                ? new AdminLoginRequest()
                : new LoginRequest();

            $validator = \Illuminate\Support\Facades\Validator::make(
                $request->all(),
                $formRequest->rules(),
                $formRequest->messages()
            );

            if ($validator->fails()) {
                throw \Illuminate\Validation\ValidationException::withMessages($validator->errors()->toArray());
            }

            // 管理者ログイン
            if ($request->is('admin*')) {
                $admin = Admin::where('email', $request->email)->first();

                if ($admin && \Illuminate\Support\Facades\Hash::check($request->password, $admin->password)) {
                    return $admin;
                }

                session()->flash('errors', collect(['ログイン情報が登録されていません']));
                return null;
            }

            // 一般ユーザーログイン
            $user = \App\Models\User::where('email', $request->email)->first();

            if ($user && \Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
                return $user;
            }

            session()->flash('errors', collect(['ログイン情報が登録されていません']));
            return null;
        });
    }

    private function configureRoutes(): void
    {
        $fortifyRoutesPath = base_path('vendor/laravel/fortify/routes/routes.php');

        // 一般ユーザー用ルート（/login, /logout など）
        Route::group([
            'namespace' => 'Laravel\Fortify\Http\Controllers',
            'middleware' => config('fortify.middleware', ['web']),
        ], function () use ($fortifyRoutesPath) {
            require $fortifyRoutesPath;
        });

        // 管理者用ルート（/admin/login, /admin/logout など）
        $originalGuard = config('fortify.guard');
        $originalHome = config('fortify.home');
        $originalFeatures = config('fortify.features');

        config([
            'fortify.guard' => 'admin',
            'fortify.home' => '/admin/attendance/list',
            'fortify.features' => [], // 登録・メール認証・パスワードリセットなし
        ]);

        Route::group([
            'namespace' => 'Laravel\Fortify\Http\Controllers',
            'prefix' => 'admin',
            'as' => 'admin.',
            'middleware' => array_merge(config('fortify.middleware', ['web']), ['fortify.admin']),
        ], function () use ($fortifyRoutesPath) {
            require $fortifyRoutesPath;
        });

        config([
            'fortify.guard' => $originalGuard,
            'fortify.home' => $originalHome,
            'fortify.features' => $originalFeatures,
        ]);
    }
}
