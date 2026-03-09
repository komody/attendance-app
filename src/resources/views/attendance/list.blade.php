<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠一覧 - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/attendance/list.css') }}">
</head>

<body>
    @include('layouts.header', ['headerType' => $headerType ?? 'user'])

    <main class="attendance-list">
        <div class="attendance-list-container">
            <h2 class="attendance-list-title">勤怠一覧</h2>

            <nav class="attendance-list-month-nav">
                <a href="{{ route('attendance.list', ['year' => $prevYear, 'month' => $prevMonth]) }}" class="attendance-list-month-link">←前月</a>
                <span class="attendance-list-month-display">
                    <span class="attendance-list-month-icon">📅</span>
                    {{ sprintf('%d/%02d', $currentYear, $currentMonth) }}
                </span>
                <a href="{{ route('attendance.list', ['year' => $nextYear, 'month' => $nextMonth]) }}" class="attendance-list-month-link">翌月→</a>
            </nav>

            <table class="attendance-list-table">
                <thead>
                    <tr>
                        <th>日付</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($calendar as $row)
                    <tr>
                        <td>{{ $row['date']->format('m/d') }}({{ ['日','月','火','水','木','金','土'][$row['date']->dayOfWeek] }})
                        </td>
                        <td>{{ $row['clock_in'] ?? '' }}</td>
                        <td>{{ $row['clock_out'] ?? '' }}</td>
                        <td>{{ $row['break_minutes'] > 0 ? floor($row['break_minutes'] / 60) . ':' . str_pad($row['break_minutes'] % 60, 2, '0', STR_PAD_LEFT) : '' }}</td>
                        <td>
                            @if($row['work_minutes'] !== null)
                                {{ floor($row['work_minutes'] / 60) . ':' . str_pad($row['work_minutes'] % 60, 2, '0', STR_PAD_LEFT) }}
                            @else
                                {{ '' }}
                            @endif
                        </td>
                        <td>
                            @if($row['attendance'])
                                <a href="{{ route('attendance.detail', $row['attendance']->id) }}" class="attendance-list-detail-link">詳細</a>
                            @else
                                <a href="{{ route('attendance.detail.date', ['year' => $row['date']->year, 'month' => $row['date']->month, 'day' => $row['date']->day]) }}" class="attendance-list-detail-link">詳細</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>
