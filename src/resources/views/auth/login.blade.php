<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/header.css') }}">
</head>

<body>
    @include('layouts.header', ['headerType' => 'login-page'])

    <main class="auth-login">
        <div class="auth-login-container">
            <h2 class="auth-login-title">ログイン</h2>
            @if(session('errors'))
            <div class="auth-login-errors">
                <ul>
                    @foreach(session('errors') as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <form method="POST" action="{{ route('login') }}" class="auth-login-form" novalidate>
                @csrf
                <div class="auth-login-field">
                    <label for="email" class="auth-login-label">メールアドレス</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="auth-login-input @error('email') is-invalid @enderror">
                    @error('email')
                    <span class="auth-login-error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="auth-login-field">
                    <label for="password" class="auth-login-label">パスワード</label>
                    <input type="password" id="password" name="password" class="auth-login-input @error('password') is-invalid @enderror">
                    @error('password')
                    <span class="auth-login-error">{{ $message }}</span>
                    @enderror
                </div>
                <button type="submit" class="auth-login-submit-btn">ログインする</button>
            </form>
            <div class="auth-login-register-link">
                <a href="{{ route('register') }}" class="auth-login-register-link-text">会員登録はこちら</a>
            </div>
        </div>
    </main>
</body>

</html>
