@php
$headerType = $headerType ?? 'login-page';
@endphp

<header class="header">
  <div class="header-wrapper">
    <h1 class="header-title">
      @if($headerType === 'login-page')
        <img src="{{ asset('images/layouts/title.svg') }}" alt="{{ config('app.name') }}" class="header-logo-img">
      @elseif($headerType === 'user')
        <a href="{{ route('attendance.index') }}" class="header-logo-link">
          <img src="{{ asset('images/layouts/title.svg') }}" alt="{{ config('app.name') }}" class="header-logo-img">
        </a>
      @elseif($headerType === 'admin')
        <a href="{{ route('admin.attendance.list') }}" class="header-logo-link">
          <img src="{{ asset('images/layouts/title.svg') }}" alt="{{ config('app.name') }}" class="header-logo-img">
        </a>
      @endif
    </h1>
    @if($headerType !== 'login-page')
    <nav class="header-nav">
      <ul class="header-nav-list">
        @if($headerType === 'user')
          <li class="header-nav-item">
            <a href="{{ route('attendance.index') }}" class="header-nav-link">勤怠</a>
          </li>
          <li class="header-nav-item">
            <a href="{{ route('attendance.list', ['year' => now()->year, 'month' => now()->month]) }}" class="header-nav-link">勤怠一覧</a>
          </li>
          <li class="header-nav-item">
            <a href="{{ route('stamp_correction_request.list') }}" class="header-nav-link">申請</a>
          </li>
          <li class="header-nav-item">
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
              @csrf
            </form>
            <a href="#" class="header-nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">ログアウト</a>
          </li>
        @elseif($headerType === 'admin')
          <li class="header-nav-item">
            <a href="{{ route('admin.attendance.list') }}" class="header-nav-link">勤怠一覧</a>
          </li>
          <li class="header-nav-item">
            <a href="{{ route('admin.staff.list') }}" class="header-nav-link">スタッフ一覧</a>
          </li>
          <li class="header-nav-item">
            <a href="{{ route('admin.stamp_correction_request.list') }}" class="header-nav-link">申請一覧</a>
          </li>
          <li class="header-nav-item">
            <form id="admin-logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
              @csrf
            </form>
            <a href="#" class="header-nav-link" onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">ログアウト</a>
          </li>
        @endif
      </ul>
    </nav>
    @endif
  </div>
</header>
