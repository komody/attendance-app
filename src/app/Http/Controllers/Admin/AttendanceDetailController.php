<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminAttendanceUpdateRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectionApplication;
use App\Models\CorrectionStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceDetailController extends Controller
{
    /**
     * 管理者用勤怠詳細を表示
     */
    public function show(int $id)
    {
        $attendance = Attendance::with(['user', 'breaks', 'correctionApplications.correctionStatus', 'correctionApplications.correctionBreaks'])->findOrFail($id);

        $pendingApplication = $attendance->correctionApplications()
            ->whereHas('correctionStatus', fn ($q) => $q->where('name', '承認待ち'))
            ->with('correctionBreaks')
            ->first();

        $isPending = (bool) $pendingApplication;
        $canEdit = !$isPending;

        if ($pendingApplication) {
            $clockIn = Carbon::parse($pendingApplication->corrected_clock_in_time)->format('H:i');
            $clockOut = Carbon::parse($pendingApplication->corrected_clock_out_time)->format('H:i');
            $remarks = $pendingApplication->remarks;
            $breaksData = $pendingApplication->correctionBreaks->map(fn ($cb) => [
                'start' => Carbon::parse($cb->corrected_break_start)->format('H:i'),
                'end' => Carbon::parse($cb->corrected_break_end)->format('H:i'),
            ])->values()->all();
        } else {
            $clockIn = $attendance->clock_in_time ? Carbon::parse($attendance->clock_in_time)->format('H:i') : '';
            $clockOut = $attendance->clock_out_time ? Carbon::parse($attendance->clock_out_time)->format('H:i') : '';
            $latestApplication = $attendance->correctionApplications()
                ->orderByDesc('created_at')
                ->first();
            $remarks = $latestApplication?->remarks ?? '';
            $breaksData = $attendance->breaks->map(fn ($b) => [
                'start' => $b->break_start_time ? Carbon::parse($b->break_start_time)->format('H:i') : '',
                'end' => $b->break_end_time ? Carbon::parse($b->break_end_time)->format('H:i') : '',
                'break_id' => $b->id,
            ])->values()->all();
        }

        return view('admin.attendance.detail', [
            'headerType' => 'admin',
            'attendance' => $attendance,
            'userName' => $attendance->user->name,
            'displayDate' => $attendance->attendance_date,
            'clockIn' => $clockIn,
            'clockOut' => $clockOut,
            'breaksData' => $breaksData,
            'remarks' => $remarks,
            'isPending' => $isPending,
            'canEdit' => $canEdit,
        ]);
    }

    /**
     * 管理者による勤怠修正（出勤・退勤・休憩・備考）
     */
    public function update(AdminAttendanceUpdateRequest $request, int $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);
        $admin = Auth::guard('admin')->user();

        // 承認待ちの申請が既にある場合は拒否
        $pendingApplication = $attendance->correctionApplications()
            ->whereHas('correctionStatus', fn ($q) => $q->where('name', '承認待ち'))
            ->first();

        if ($pendingApplication) {
            return redirect()
                ->route('admin.attendance.detail', $attendance->id)
                ->with('error', '承認待ちの申請があるため修正できません。');
        }

        $validated = $request->validated();

        DB::transaction(function () use ($attendance, $validated, $admin) {
            // 出勤・退勤を更新
            $attendance->update([
                'clock_in_time' => $validated['corrected_clock_in_time'],
                'clock_out_time' => $validated['corrected_clock_out_time'],
            ]);

            // 既存の休憩を更新
            $breaks = $validated['breaks'] ?? [];
            foreach ($breaks as $breakData) {
                if (empty($breakData['break_id']) || empty($breakData['corrected_break_start']) || empty($breakData['corrected_break_end'])) {
                    continue;
                }
                BreakTime::where('id', $breakData['break_id'])
                    ->where('attendance_id', $attendance->id)
                    ->update([
                        'break_start_time' => $breakData['corrected_break_start'],
                        'break_end_time' => $breakData['corrected_break_end'],
                    ]);
            }

            // 新しい休憩を追加
            $newBreaks = $validated['new_breaks'] ?? [];
            foreach ($newBreaks as $breakData) {
                if (!empty($breakData['corrected_break_start']) && !empty($breakData['corrected_break_end'])) {
                    BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'break_start_time' => $breakData['corrected_break_start'],
                        'break_end_time' => $breakData['corrected_break_end'],
                    ]);
                }
            }

            // 備考を correction_applications に保存
            $approvedStatus = CorrectionStatus::where('name', '承認済み')->firstOrFail();
            CorrectionApplication::create([
                'user_id' => $attendance->user_id,
                'attendance_id' => $attendance->id,
                'corrected_clock_in_time' => $validated['corrected_clock_in_time'],
                'corrected_clock_out_time' => $validated['corrected_clock_out_time'],
                'remarks' => $validated['remarks'],
                'correction_status_id' => $approvedStatus->id,
                'approved_admin_id' => $admin->id,
                'approval_date' => now(),
            ]);
        });

        $date = $attendance->attendance_date;

        return redirect()
            ->route('admin.attendance.list.date', [
                'year' => $date->year,
                'month' => $date->month,
                'day' => $date->day,
            ])
            ->with('message', '修正しました。');
    }
}
