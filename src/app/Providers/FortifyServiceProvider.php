<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Requests\LoginRequest;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use App\Notifications\VerifyEmail;

class FortifyServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureFortify();
        $this->configureRegisterResponse();
        $this->configureRateLimiters();
        $this->configureLoginResponse();
        $this->configureAuthenticateUsing();
    }

    private function configureFortify(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::registerView(fn () => view('auth.register'));
        Fortify::loginView(fn () => view('auth.login'));
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
                    $user = $request->user();

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

    private function configureAuthenticateUsing(): void
    {
        Fortify::authenticateUsing(function (Request $request) {
            $loginRequest = new LoginRequest();
            $validator = \Illuminate\Support\Facades\Validator::make(
                $request->all(),
                $loginRequest->rules(),
                $loginRequest->messages()
            );

            if ($validator->fails()) {
                throw \Illuminate\Validation\ValidationException::withMessages($validator->errors()->toArray());
            }

            $user = \App\Models\User::where('email', $request->email)->first();

            if ($user && \Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
                return $user;
            }

            session()->flash('errors', collect(['ログイン情報が登録されていません']));
            return null;
        });
    }
}
