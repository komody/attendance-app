<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BreakTime;
use App\Models\CorrectionApplication;
use App\Models\CorrectionStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StampCorrectionRequestController extends Controller
{
    /**
     * 修正申請承認画面を表示（FN050）
     */
    public function showApprove(int $attendance_correct_request_id)
    {
        $application = CorrectionApplication::with(['attendance.user', 'attendance.breaks', 'correctionStatus', 'correctionBreaks'])
            ->findOrFail($attendance_correct_request_id);

        $attendance = $application->attendance;
        $isPending = $application->correctionStatus?->name === '承認待ち';
        $hasApproved = $attendance->correctionApplications()
            ->whereHas('correctionStatus', fn ($q) => $q->where('name', '承認済み'))
            ->exists();

        $clockIn = Carbon::parse($application->corrected_clock_in_time)->format('H:i');
        $clockOut = Carbon::parse($application->corrected_clock_out_time)->format('H:i');
        $remarks = $application->remarks;
        $breaksData = $application->correctionBreaks->map(fn ($cb) => [
            'start' => Carbon::parse($cb->corrected_break_start)->format('H:i'),
            'end' => Carbon::parse($cb->corrected_break_end)->format('H:i'),
        ])->values()->all();

        return view('stamp_correction_request.approve', [
            'headerType' => 'admin',
            'pendingApplication' => $isPending ? $application : null,
            'userName' => $attendance->user->name,
            'displayDate' => $attendance->attendance_date,
            'clockIn' => $clockIn,
            'clockOut' => $clockOut,
            'breaksData' => $breaksData,
            'remarks' => $remarks,
            'isPending' => $isPending,
        ]);
    }

    /**
     * 修正申請を承認（FN051）
     */
    public function approveCorrection(int $attendance_correct_request_id)
    {
        $application = CorrectionApplication::with(['attendance', 'correctionBreaks'])
            ->whereHas('correctionStatus', fn ($q) => $q->where('name', '承認待ち'))
            ->findOrFail($attendance_correct_request_id);

        $attendance = $application->attendance;
        $admin = Auth::guard('admin')->user();
        $approvedStatus = CorrectionStatus::where('name', '承認済み')->firstOrFail();

        DB::transaction(function () use ($application, $attendance, $admin, $approvedStatus) {
            $application->update([
                'correction_status_id' => $approvedStatus->id,
                'approved_admin_id' => $admin->id,
                'approval_date' => now(),
            ]);

            $attendance->update([
                'clock_in_time' => $application->corrected_clock_in_time,
                'clock_out_time' => $application->corrected_clock_out_time,
            ]);

            $attendance->breaks()->delete();
            foreach ($application->correctionBreaks as $cb) {
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start_time' => $cb->corrected_break_start,
                    'break_end_time' => $cb->corrected_break_end,
                ]);
            }
        });

        return redirect()
            ->route('stamp_correction_request.approve', $application->id)
            ->with('message', '申請を承認しました。');
    }

    /**
     * 管理者用：全ユーザーの修正申請一覧を表示（FN047, FN048）
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'pending');

        $statusName = $tab === 'approved' ? '承認済み' : '承認待ち';
        $status = CorrectionStatus::where('name', $statusName)->first();

        $applications = CorrectionApplication::query()
            ->when($status, fn ($q) => $q->where('correction_status_id', $status->id))
            ->with(['attendance', 'correctionStatus', 'user'])
            ->orderByDesc('created_at')
            ->get();

        return view('stamp_correction_request.list', [
            'headerType' => 'admin',
            'applications' => $applications,
            'activeTab' => $tab === 'approved' ? 'approved' : 'pending',
        ]);
    }
}
