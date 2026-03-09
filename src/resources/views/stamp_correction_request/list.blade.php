<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>申請一覧 - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/stamp_correction_request/list.css') }}">
</head>

<body>
    @include('layouts.header', ['headerType' => $headerType ?? 'user'])

    <main class="correction-list">
        <div class="correction-list-container">
            <h2 class="correction-list-title">申請一覧</h2>

            <nav class="correction-list-tabs">
                <a href="{{ route('stamp_correction_request.list', ['tab' => 'pending']) }}"
                    class="correction-list-tab {{ ($activeTab ?? 'pending') === 'pending' ? 'correction-list-tab--active' : '' }}">承認待ち</a>
                <a href="{{ route('stamp_correction_request.list', ['tab' => 'approved']) }}"
                    class="correction-list-tab {{ ($activeTab ?? 'pending') === 'approved' ? 'correction-list-tab--active' : '' }}">承認済み</a>
            </nav>

            <table class="correction-list-table">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications ?? [] as $application)
                    <tr>
                        <td>{{ $application->correctionStatus?->name ?? '-' }}</td>
                        <td>{{ $application->user?->name ?? '-' }}</td>
                        <td>{{ $application->attendance?->attendance_date?->format('Y/m/d') ?? '-' }}</td>
                        <td>{{ $application->remarks ?? '-' }}</td>
                        <td>{{ $application->created_at?->format('Y/m/d') ?? '-' }}</td>
                        <td>
                            <a href="{{ route('attendance.detail', $application->attendance_id) }}" class="correction-list-detail-link">詳細</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="correction-list-empty">申請がありません</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>
