<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会員登録 - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/header.css') }}">
</head>

<body>
    @include('layouts.header', ['headerType' => 'login-page'])

    <main class="auth-register">
        <div class="auth-register-container">
            <h2 class="auth-register-title">会員登録</h2>
            <form method="POST" action="{{ route('register') }}" class="auth-register-form" novalidate>
                @csrf
                <div class="auth-register-field">
                    <label for="name" class="auth-register-label">ユーザー名</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" class="auth-register-input @error('name') is-invalid @enderror">
                    @error('name')
                    <span class="auth-register-error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="auth-register-field">
                    <label for="email" class="auth-register-label">メールアドレス</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="auth-register-input @error('email') is-invalid @enderror">
                    @error('email')
                    <span class="auth-register-error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="auth-register-field">
                    <label for="password" class="auth-register-label">パスワード</label>
                    <input type="password" id="password" name="password" class="auth-register-input @error('password') is-invalid @enderror">
                    @error('password')
                    <span class="auth-register-error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="auth-register-field">
                    <label for="password_confirmation" class="auth-register-label">確認用パスワード</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="auth-register-input">
                </div>
                <button type="submit" class="auth-register-submit-btn">登録する</button>
            </form>
            <div class="auth-register-login-link">
                <a href="{{ route('login') }}" class="auth-register-login-link-text">ログインはこちら</a>
            </div>
        </div>
    </main>
</body>

</html>
