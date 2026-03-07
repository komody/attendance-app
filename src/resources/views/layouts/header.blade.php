@php
if (!isset($headerType)) {
    if (auth()->check()) {
        $headerType = 'login';
    } else {
        $headerType = 'not-login';
    }
}
@endphp

<header class="header">
  <div class="header-wrapper">
    <h1 class="header-title">
      <a href="{{ auth()->check() ? route('attendance.index') : url('/') }}">{{ config('app.name') }}</a>
    </h1>
    <nav class="header-nav">
      @if($headerType === 'login')
      <ul class="header-nav-list">
        <li class="header-nav-item">
          <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
          </form>
          <a href="#" class="nav-logout-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">ログアウト</a>
        </li>
      </ul>
      @elseif($headerType === 'not-login' || $headerType === 'login-page')
      <ul class="header-nav-list">
        <li class="header-nav-item">
          <a href="{{ route('login') }}" class="nav-login-link">ログイン</a>
        </li>
        <li class="header-nav-item">
          <a href="{{ route('register') }}" class="nav-register-link">会員登録</a>
        </li>
      </ul>
      @endif
    </nav>
  </div>
</header>
