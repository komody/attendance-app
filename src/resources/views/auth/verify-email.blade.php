<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>メール認証誘導画面 - {{ config('app.name') }}</title>
  <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
  <link rel="stylesheet" href="{{ asset('css/layouts/header.css') }}">
</head>

<body>
  @include('layouts.header', ['headerType' => 'login-page'])

  <main class="auth-verify-email">
    <div class="auth-verify-email-container">
      <div class="auth-verify-email-content">
        <p class="auth-verify-email-text">登録していただいたメールアドレスに認証メールを送付しました。</p>
        <p class="auth-verify-email-text">メール認証を完了してください。</p>
      </div>

      @if (session('message'))
      <p class="auth-verify-email-text" style="color: #0073cc; margin-bottom: 24px;">{{ session('message') }}</p>
      @endif

      <div class="auth-verify-email-actions">
        @if(config('app.env') === 'local')
        <a href="http://localhost:8025" class="auth-verify-email-button" target="_blank" rel="noopener">認証はこちらから</a>
        @endif
        <form method="POST" action="{{ route('verification.send') }}">
          @csrf
          <button type="submit" class="auth-verify-email-resend-link">認証メールを再送する</button>
        </form>
      </div>
    </div>
  </main>
</body>

</html>
