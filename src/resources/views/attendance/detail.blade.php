<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠詳細 - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">
</head>

<body>
    @include('layouts.header', ['headerType' => $headerType ?? 'user'])

    <main class="attendance-detail">
        <div class="attendance-detail-container">
            <h2 class="attendance-detail-title">勤怠詳細</h2>

            @if(session('message'))
            <p class="attendance-detail-message attendance-detail-message--success">{{ session('message') }}</p>
            @endif
            @if(session('error'))
            <p class="attendance-detail-message attendance-detail-message--error">{{ session('error') }}</p>
            @endif
            @if($errors->any())
            <div class="attendance-detail-message attendance-detail-message--error">
                <ul class="attendance-detail-error-list">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="attendance-detail-card">
                @if($canEdit)
                <form action="{{ route('attendance.correction.store') }}" method="POST" class="attendance-detail-form">
                    @csrf
                    <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                @endif

                <dl class="attendance-detail-list">
                    <div class="attendance-detail-row">
                        <dt class="attendance-detail-label">名前</dt>
                        <dd class="attendance-detail-value">{{ $userName }}</dd>
                    </div>
                    <div class="attendance-detail-row">
                        <dt class="attendance-detail-label">日付</dt>
                        <dd class="attendance-detail-value">
                            {{ $displayDate->format('Y年') }} {{ $displayDate->format('n月j日') }}
                        </dd>
                    </div>
                    <div class="attendance-detail-row">
                        <dt class="attendance-detail-label">出勤・退勤</dt>
                        <dd class="attendance-detail-value">
                            @if($canEdit)
                            <span class="attendance-detail-time-inputs">
                                <input type="time" name="corrected_clock_in_time" value="{{ old('corrected_clock_in_time', $clockIn) }}" class="attendance-detail-input @error('corrected_clock_in_time') attendance-detail-input--error @enderror" required>
                                <span class="attendance-detail-time-separator">~</span>
                                <input type="time" name="corrected_clock_out_time" value="{{ old('corrected_clock_out_time', $clockOut) }}" class="attendance-detail-input @error('corrected_clock_out_time') attendance-detail-input--error @enderror" required>
                            </span>
                            @else
                            {{ $clockIn && $clockOut ? "{$clockIn} ~ {$clockOut}" : ($clockIn ?? '-') }}
                            @endif
                        </dd>
                    </div>
                    @foreach($breaksData as $index => $break)
                    <div class="attendance-detail-row">
                        <dt class="attendance-detail-label">休憩{{ $index + 1 }}</dt>
                        <dd class="attendance-detail-value">
                            @if($canEdit && isset($break['break_id']))
                            <input type="hidden" name="breaks[{{ $index }}][break_id]" value="{{ $break['break_id'] }}">
                            <span class="attendance-detail-time-inputs">
                                <input type="time" name="breaks[{{ $index }}][corrected_break_start]" value="{{ old("breaks.{$index}.corrected_break_start", $break['start'] ?? '') }}" class="attendance-detail-input" required>
                                <span class="attendance-detail-time-separator">~</span>
                                <input type="time" name="breaks[{{ $index }}][corrected_break_end]" value="{{ old("breaks.{$index}.corrected_break_end", $break['end'] ?? '') }}" class="attendance-detail-input" required>
                            </span>
                            @else
                            {{ ($break['start'] ?? '') && ($break['end'] ?? '') ? ($break['start'] . ' ~ ' . $break['end']) : '-' }}
                            @endif
                        </dd>
                    </div>
                    @endforeach
                    @if($canEdit && count($breaksData) === 0)
                    <div class="attendance-detail-row attendance-detail-row--empty">
                        <dt class="attendance-detail-label">休憩</dt>
                        <dd class="attendance-detail-value">
                            <span class="attendance-detail-hint">（休憩データがありません）</span>
                        </dd>
                    </div>
                    @endif
                    @if($canEdit)
                    <div class="attendance-detail-row">
                        <dt class="attendance-detail-label">休憩+1</dt>
                        <dd class="attendance-detail-value">
                            <span class="attendance-detail-time-inputs">
                                <input type="time" name="new_breaks[0][corrected_break_start]" value="{{ old('new_breaks.0.corrected_break_start') }}" class="attendance-detail-input">
                                <span class="attendance-detail-time-separator">~</span>
                                <input type="time" name="new_breaks[0][corrected_break_end]" value="{{ old('new_breaks.0.corrected_break_end') }}" class="attendance-detail-input">
                            </span>
                            <span class="attendance-detail-hint">（追加する場合は両方入力）</span>
                        </dd>
                    </div>
                    @endif
                    <div class="attendance-detail-row">
                        <dt class="attendance-detail-label">備考</dt>
                        <dd class="attendance-detail-value">
                            @if($canEdit)
                            <input type="text" name="remarks" value="{{ old('remarks', $remarks) }}" class="attendance-detail-input attendance-detail-input--full @error('remarks') attendance-detail-input--error @enderror" placeholder="申請時に備考を入力" required>
                            @else
                            {{ $remarks ?: '-' }}
                            @endif
                        </dd>
                    </div>
                </dl>

                @if($canEdit)
                <div class="attendance-detail-actions">
                    <button type="submit" class="attendance-detail-submit-btn">修正</button>
                </div>
                </form>
                @endif
            </div>

            @if($isPending)
            <p class="attendance-detail-note">*承認待ちのため修正はできません。</p>
            @endif
        </div>
    </main>
</body>

</html>
