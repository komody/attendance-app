<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠一覧（管理者） - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/list.css') }}">
</head>

<body>
    @include('layouts.header', ['headerType' => $headerType ?? 'admin'])

    <main class="attendance-list">
        <div class="attendance-list-container">
            <h2 class="attendance-list-title">{{ $date->format('Y年n月j日') }}の勤怠</h2>

            <nav class="attendance-list-month-nav">
                <a href="{{ route('admin.attendance.list.date', ['year' => $prevYear, 'month' => $prevMonth, 'day' => $prevDay]) }}" class="attendance-list-month-link">←前日</a>
                <span class="attendance-list-month-display">
                    <span class="attendance-list-month-icon">📅</span>
                    {{ $date->format('Y/m/d') }}
                </span>
                <a href="{{ route('admin.attendance.list.date', ['year' => $nextYear, 'month' => $nextMonth, 'day' => $nextDay]) }}" class="attendance-list-month-link">翌日→</a>
            </nav>

            <table class="attendance-list-table">
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                    <tr>
                        <td>{{ $row['user_name'] }}</td>
                        <td>{{ $row['clock_in'] }}</td>
                        <td>{{ $row['clock_out'] }}</td>
                        <td>{{ $row['break_minutes'] > 0 ? floor($row['break_minutes'] / 60) . ':' . str_pad($row['break_minutes'] % 60, 2, '0', STR_PAD_LEFT) : '' }}</td>
                        <td>
                            @if($row['work_minutes'] !== null)
                                {{ floor($row['work_minutes'] / 60) . ':' . str_pad($row['work_minutes'] % 60, 2, '0', STR_PAD_LEFT) }}
                            @else
                                {{ '' }}
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.attendance.detail', $row['attendance']->id) }}" class="attendance-list-detail-link">詳細</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="attendance-list-empty">この日の勤怠データはありません</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>
