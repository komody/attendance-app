<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>修正申請承認（管理者） - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/stamp_correction_request/approve.css') }}">
</head>

<body>
    @include('layouts.header', ['headerType' => 'admin'])

    <main class="approve">
        <div class="approve-container">
            <h2 class="approve-title">勤怠詳細</h2>

            @if(session('message'))
            <p class="approve-message approve-message--success">{{ session('message') }}</p>
            @endif
            @if(session('error'))
            <p class="approve-message approve-message--error">{{ session('error') }}</p>
            @endif

            <div class="approve-card">
                <dl class="approve-list">
                    <div class="approve-row">
                        <dt class="approve-label">名前</dt>
                        <dd class="approve-value approve-value-name">{{ $userName }}</dd>
                    </div>
                    <div class="approve-row">
                        <dt class="approve-label">日付</dt>
                        <dd class="approve-value">
                            <span class="approve-date-year">{{ $displayDate->format('Y年') }}</span>
                            <span class="approve-date-month">{{ $displayDate->format('n月j日') }}</span>
                        </dd>
                    </div>
                    <div class="approve-row">
                        <dt class="approve-label">出勤・退勤</dt>
                        <dd class="approve-value">
                            @if($clockIn && $clockOut)
                            <span class="approve-time-inputs">
                                <span class="approve-time-start">{{ $clockIn }}</span>
                                <span class="approve-time-separator">~</span>
                                <span class="approve-time-end">{{ $clockOut }}</span>
                            </span>
                            @else
                            {{ $clockIn ?? '-' }}
                            @endif
                        </dd>
                    </div>
                    @forelse($breaksData as $index => $break)
                    <div class="approve-row">
                        <dt class="approve-label">休憩{{ $index + 1 }}</dt>
                        <dd class="approve-value">
                            @if(($break['start'] ?? '') && ($break['end'] ?? ''))
                            <span class="approve-time-inputs">
                                <span class="approve-time-start">{{ $break['start'] }}</span>
                                <span class="approve-time-separator">~</span>
                                <span class="approve-time-end">{{ $break['end'] }}</span>
                            </span>
                            @else
                            -
                            @endif
                        </dd>
                    </div>
                    @empty
                    <div class="approve-row">
                        <dt class="approve-label">休憩</dt>
                        <dd class="approve-value">-</dd>
                    </div>
                    @endforelse
                    <div class="approve-row">
                        <dt class="approve-label">備考</dt>
                        <dd class="approve-value">{{ $remarks ?: '-' }}</dd>
                    </div>
                </dl>

                @if($isPending)
                <div class="approve-approval">
                    <div class="approve-actions">
                        <button type="submit" class="approve-submit-btn">承認</button>
                    </div>
                </div>
                @else
                <div class="approve-approval">
                    <div class="approve-actions">
                        <button type="button" class="approve-submit-btn approve-submit-btn--disabled" disabled>承認済み</button>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </main>
</body>

</html>
