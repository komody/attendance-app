<?php

namespace App\Http\Controllers;

use App\Http\Requests\CorrectionApplicationRequest;
use App\Models\Attendance;
use App\Models\CorrectionApplication;
use App\Models\CorrectionBreak;
use App\Models\CorrectionStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceDetailController extends Controller
{
    /**
     * 勤怠詳細を表示（ID指定）
     */
    public function show(Request $request, int $id)
    {
        $user = $request->user();
        $attendance = Attendance::where('user_id', $user->id)->with(['breaks', 'correctionApplications.correctionStatus', 'correctionApplications.correctionBreaks'])->findOrFail($id);

        return $this->buildDetailView($request, $attendance);
    }

    /**
     * 勤怠詳細を表示（日付指定）
     */
    public function showByDate(Request $request, int $year, int $month, int $day)
    {
        $user = $request->user();
        $date = Carbon::createFromDate($year, $month, $day);

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', $date)
            ->with(['breaks', 'correctionApplications.correctionStatus', 'correctionApplications.correctionBreaks'])
            ->first();

        return $this->buildDetailView($request, $attendance, $date);
    }

    /**
     * 修正申請を送信
     */
    public function storeCorrection(CorrectionApplicationRequest $request)
    {
        $user = $request->user();
        $attendanceId = $request->input('attendance_id');
        $attendance = Attendance::where('user_id', $user->id)->findOrFail($attendanceId);

        // 承認待ちの申請が既にある場合は拒否
        $pendingApplication = $attendance->correctionApplications()
            ->whereHas('correctionStatus', fn ($q) => $q->where('name', '承認待ち'))
            ->first();

        if ($pendingApplication) {
            return back()->with('error', '既に承認待ちの申請があります。');
        }

        $validated = $request->validated();
        $attendanceBreakIds = $attendance->breaks->pluck('id')->toArray();

        $breaks = $validated['breaks'] ?? [];
        foreach ($breaks as $breakData) {
            if (!empty($breakData['break_id']) && !in_array($breakData['break_id'], $attendanceBreakIds)) {
                return back()->with('error', '無効な休憩データです。');
            }
        }

        $pendingStatus = CorrectionStatus::where('name', '承認待ち')->firstOrFail();

        $correctionApplication = CorrectionApplication::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'corrected_clock_in_time' => $validated['corrected_clock_in_time'],
            'corrected_clock_out_time' => $validated['corrected_clock_out_time'],
            'remarks' => $validated['remarks'],
            'correction_status_id' => $pendingStatus->id,
        ]);

        foreach ($breaks as $breakData) {
            if (!empty($breakData['break_id']) && !empty($breakData['corrected_break_start']) && !empty($breakData['corrected_break_end'])) {
                CorrectionBreak::create([
                    'correction_application_id' => $correctionApplication->id,
                    'break_id' => $breakData['break_id'],
                    'corrected_break_start' => $breakData['corrected_break_start'],
                    'corrected_break_end' => $breakData['corrected_break_end'],
                ]);
            }
        }

        $newBreaks = $validated['new_breaks'] ?? [];
        foreach ($newBreaks as $breakData) {
            if (!empty($breakData['corrected_break_start']) && !empty($breakData['corrected_break_end'])) {
                CorrectionBreak::create([
                    'correction_application_id' => $correctionApplication->id,
                    'break_id' => null,
                    'corrected_break_start' => $breakData['corrected_break_start'],
                    'corrected_break_end' => $breakData['corrected_break_end'],
                ]);
            }
        }

        return redirect()->route('attendance.detail', $attendance->id)->with('message', '修正申請を送信しました。');
    }

    /**
     * 詳細ビュー用のデータを構築
     */
    private function buildDetailView(Request $request, ?Attendance $attendance, ?Carbon $date = null)
    {
        $user = $request->user();

        if (!$attendance && !$date) {
            abort(404);
        }

        $displayDate = $attendance?->attendance_date ?? $date;
        $pendingApplication = $attendance?->correctionApplications()
            ->whereHas('correctionStatus', fn ($q) => $q->where('name', '承認待ち'))
            ->with('correctionBreaks.breakTime')
            ->first();

        $isPending = (bool) $pendingApplication;
        $canEdit = $attendance && !$isPending;

        if ($pendingApplication) {
            $clockIn = Carbon::parse($pendingApplication->corrected_clock_in_time)->format('H:i');
            $clockOut = Carbon::parse($pendingApplication->corrected_clock_out_time)->format('H:i');
            $remarks = $pendingApplication->remarks;
            $breaksData = $pendingApplication->correctionBreaks->map(fn ($cb) => [
                'start' => Carbon::parse($cb->corrected_break_start)->format('H:i'),
                'end' => Carbon::parse($cb->corrected_break_end)->format('H:i'),
            ])->values()->all();
        } elseif ($attendance) {
            $clockIn = $attendance->clock_in_time ? Carbon::parse($attendance->clock_in_time)->format('H:i') : '';
            $clockOut = $attendance->clock_out_time ? Carbon::parse($attendance->clock_out_time)->format('H:i') : '';
            $remarks = '';
            $breaksData = $attendance->breaks->map(fn ($b) => [
                'start' => $b->break_start_time ? Carbon::parse($b->break_start_time)->format('H:i') : '',
                'end' => $b->break_end_time ? Carbon::parse($b->break_end_time)->format('H:i') : '',
                'break_id' => $b->id,
            ])->values()->all();
        } else {
            $clockIn = '';
            $clockOut = '';
            $remarks = '';
            $breaksData = [];
        }

        return view('attendance.detail', [
            'headerType' => 'user',
            'attendance' => $attendance,
            'displayDate' => $displayDate,
            'userName' => $user->name,
            'clockIn' => $clockIn,
            'clockOut' => $clockOut,
            'remarks' => $remarks,
            'breaksData' => $breaksData,
            'isPending' => $isPending,
            'canEdit' => $canEdit,
        ]);
    }
}
