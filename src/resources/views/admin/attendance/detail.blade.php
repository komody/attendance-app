<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠詳細（管理者） - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/detail.css') }}">
</head>

<body>
    @include('layouts.header', ['headerType' => $headerType ?? 'admin'])

    <main class="attendance-detail">
        <div class="attendance-detail-container">
            <h2 class="attendance-detail-title">勤怠詳細</h2>

            <div class="attendance-detail-card">
                @if($canEdit)
                <form action="{{ route('admin.attendance.detail.update', $attendance->id) }}" method="POST" class="attendance-detail-form" novalidate>
                    @csrf
                    @endif

                    <dl class="attendance-detail-list">
                        <div class="attendance-detail-row">
                            <dt class="attendance-detail-label">名前</dt>
                            <dd class="attendance-detail-value attendance-detail-label-name">{{ $userName }}</dd>
                        </div>
                        <div class="attendance-detail-row">
                            <dt class="attendance-detail-label">日付</dt>
                            <dd class="attendance-detail-value">
                                <span class="attendance-detail-date-year">{{ $displayDate->format('Y年') }}</span>
                                <span class="attendance-detail-date-month">{{ $displayDate->format('n月j日') }}</span>
                            </dd>
                        </div>
                        <div class="attendance-detail-row">
                            <dt class="attendance-detail-label">出勤・退勤</dt>
                            <dd class="attendance-detail-value">
                                @if($canEdit)
                                <div class="attendance-detail-field">
                                    <span class="attendance-detail-time-inputs">
                                        <input type="time" name="corrected_clock_in_time" value="{{ old('corrected_clock_in_time', $clockIn) }}" class="attendance-detail-input @error('corrected_clock_in_time') attendance-detail-input--error @enderror" required>
                                        <span class="attendance-detail-time-separator">~</span>
                                        <input type="time" name="corrected_clock_out_time" value="{{ old('corrected_clock_out_time', $clockOut) }}" class="attendance-detail-input @error('corrected_clock_out_time') attendance-detail-input--error @enderror" required>
                                    </span>
                                    @error('corrected_clock_in_time')
                                    <p class="attendance-detail-field-error">{{ $message }}</p>
                                    @enderror
                                    @error('corrected_clock_out_time')
                                    <p class="attendance-detail-field-error">{{ $message }}</p>
                                    @enderror
                                </div>
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
                                <div class="attendance-detail-field">
                                    <input type="hidden" name="breaks[{{ $index }}][break_id]" value="{{ $break['break_id'] }}">
                                    <span class="attendance-detail-time-inputs">
                                        <input type="time" name="breaks[{{ $index }}][corrected_break_start]" value="{{ old("breaks.{$index}.corrected_break_start", $break['start'] ?? '') }}" class="attendance-detail-input @error("breaks.{$index}.corrected_break_start") attendance-detail-input--error @enderror @error("breaks.{$index}.corrected_break_end") attendance-detail-input--error @enderror" required>
                                        <span class="attendance-detail-time-separator">~</span>
                                        <input type="time" name="breaks[{{ $index }}][corrected_break_end]" value="{{ old("breaks.{$index}.corrected_break_end", $break['end'] ?? '') }}" class="attendance-detail-input @error("breaks.{$index}.corrected_break_start") attendance-detail-input--error @enderror @error("breaks.{$index}.corrected_break_end") attendance-detail-input--error @enderror" required>
                                    </span>
                                    @error("breaks.{$index}.corrected_break_start")
                                    <p class="attendance-detail-field-error">{{ $message }}</p>
                                    @enderror
                                    @error("breaks.{$index}.corrected_break_end")
                                    <p class="attendance-detail-field-error">{{ $message }}</p>
                                    @enderror
                                </div>
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
                            <dt class="attendance-detail-label">休憩{{ count($breaksData) + 1 }}</dt>
                            <dd class="attendance-detail-value">
                                <div class="attendance-detail-field">
                                    <span class="attendance-detail-time-inputs">
                                        <input type="time" name="new_breaks[0][corrected_break_start]" value="{{ old('new_breaks.0.corrected_break_start') }}" class="attendance-detail-input @if($errors->hasAny(['breaks', 'new_breaks.0.corrected_break_start', 'new_breaks.0.corrected_break_end'])) attendance-detail-input--error @endif">
                                        <span class="attendance-detail-time-separator">~</span>
                                        <input type="time" name="new_breaks[0][corrected_break_end]" value="{{ old('new_breaks.0.corrected_break_end') }}" class="attendance-detail-input @if($errors->hasAny(['breaks', 'new_breaks.0.corrected_break_start', 'new_breaks.0.corrected_break_end'])) attendance-detail-input--error @endif">
                                    </span>
                                    @error('breaks')
                                    <p class="attendance-detail-field-error">{{ $message }}</p>
                                    @enderror
                                    @error('new_breaks.0.corrected_break_start')
                                    <p class="attendance-detail-field-error">{{ $message }}</p>
                                    @enderror
                                    @error('new_breaks.0.corrected_break_end')
                                    <p class="attendance-detail-field-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </dd>
                        </div>
                        @endif
                        <div class="attendance-detail-row">
                            <dt class="attendance-detail-label">備考</dt>
                            <dd class="attendance-detail-value">
                                @if($canEdit)
                                <div class="attendance-detail-field">
                                    <input type="text" name="remarks" value="{{ old('remarks', $remarks) }}" class="attendance-detail-input attendance-detail-text-area @error('remarks') attendance-detail-input--error @enderror" placeholder="備考を入力" required>
                                    @error('remarks')
                                    <p class="attendance-detail-field-error">{{ $message }}</p>
                                    @enderror
                                </div>
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
