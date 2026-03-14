<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠 - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
</head>

<body>
    @include('layouts.header', ['headerType' => 'user'])

    <main class="attendance">
        <div class="attendance-container">
            @if(session('message'))
            <p class="attendance-message attendance-message--success">{{ session('message') }}</p>
            @endif
            @if(session('error'))
            <p class="attendance-message attendance-message--error">{{ session('error') }}</p>
            @endif

            @php
            $badgeLabel = '勤務外';
            if ($todayAttendance) {
            if ($todayAttendance->clock_out_time) {
            $badgeLabel = '退勤済';
            } else {
            $badgeLabel = ($user->status_id ?? 1) === 3 ? '休憩中' : '出勤中';
            }
            }
            @endphp
            <div class="attendance-status-badge">
                {{ $badgeLabel }}
            </div>

            <p class="attendance-date" id="current-date"></p>
            <p class="attendance-time" id="current-time"></p>

            @php
            $hasTodayAttendance = $todayAttendance !== null;
            $isClockedOut = $hasTodayAttendance && $todayAttendance->clock_out_time !== null;
            $statusId = $user->status_id ?? 1;
            @endphp

            @if(!$hasTodayAttendance)
            {{-- 当日の勤怠なし（出勤可能）: 出勤ボタン --}}
            <form action="{{ route('attendance.clock-in') }}" method="POST" class="attendance-actions">
                @csrf
                <button type="submit" class="attendance-btn attendance-btn--primary">出勤</button>
            </form>
            @elseif($isClockedOut)
            {{-- 退勤済: お疲れ様でした --}}
            <p class="attendance-greeting">お疲れ様でした。</p>
            @elseif($statusId === 3)
            {{-- 休憩中: 休憩戻ボタン --}}
            <form action="{{ route('attendance.break-end') }}" method="POST" class="attendance-actions">
                @csrf
                <button type="submit" class="attendance-btn attendance-btn--secondary">休憩戻</button>
            </form>
            @else
            {{-- 出勤中: 退勤・休憩入ボタン --}}
            <div class="attendance-actions">
                <form action="{{ route('attendance.clock-out') }}" method="POST" class="attendance-action-form">
                    @csrf
                    <button type="submit" class="attendance-btn attendance-btn--primary">退勤</button>
                </form>
                <form action="{{ route('attendance.break-start') }}" method="POST" class="attendance-action-form">
                    @csrf
                    <button type="submit" class="attendance-btn attendance-btn--secondary">休憩入</button>
                </form>
            </div>
            @endif
        </div>
    </main>

    <script src="{{ asset('js/attendance/clock.js') }}"></script>
</body>

</html>