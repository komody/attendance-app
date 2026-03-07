<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * 勤怠画面を表示
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $user->load('status');

        $today = Carbon::today();
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', $today)
            ->first();

        return view('attendance.index', [
            'user' => $user,
            'todayAttendance' => $todayAttendance,
            'today' => $today,
        ]);
    }

    /**
     * 出勤
     */
    public function clockIn(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        $exists = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', $today)
            ->exists();

        if ($exists) {
            return redirect()->route('attendance.index')->with('error', '本日は既に出勤済みです。');
        }

        DB::transaction(function () use ($user, $today) {
            Attendance::create([
                'user_id' => $user->id,
                'attendance_date' => $today,
                'clock_in_time' => Carbon::now()->format('H:i:s'),
                'clock_out_time' => null,
            ]);

            $user->update(['status_id' => 2]); // 出勤中
        });

        return redirect()->route('attendance.index')->with('message', '出勤しました。');
    }

    /**
     * 退勤
     */
    public function clockOut(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', $today)
            ->first();

        if (!$attendance || $attendance->clock_out_time) {
            return redirect()->route('attendance.index')->with('error', '退勤処理に失敗しました。');
        }

        // 休憩中の場合は休憩戻を先に実行する必要がある
        $openBreak = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end_time')
            ->first();

        if ($openBreak) {
            return redirect()->route('attendance.index')->with('error', '休憩を終了してから退勤してください。');
        }

        DB::transaction(function () use ($user, $attendance) {
            $attendance->update([
                'clock_out_time' => Carbon::now()->format('H:i:s'),
            ]);

            $user->update(['status_id' => 4]); // 退勤済
        });

        return redirect()->route('attendance.index')->with('message', 'お疲れ様でした。');
    }

    /**
     * 休憩入
     */
    public function breakStart(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', $today)
            ->first();

        if (!$attendance || $attendance->clock_out_time) {
            return redirect()->route('attendance.index')->with('error', '休憩を開始できません。');
        }

        $openBreak = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end_time')
            ->first();

        if ($openBreak) {
            return redirect()->route('attendance.index')->with('error', '既に休憩中です。');
        }

        DB::transaction(function () use ($user, $attendance) {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start_time' => Carbon::now()->format('H:i:s'),
                'break_end_time' => null,
            ]);

            $user->update(['status_id' => 3]); // 休憩中
        });

        return redirect()->route('attendance.index')->with('message', '休憩を開始しました。');
    }

    /**
     * 休憩戻
     */
    public function breakEnd(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', $today)
            ->first();

        if (!$attendance) {
            return redirect()->route('attendance.index')->with('error', '休憩を終了できません。');
        }

        $openBreak = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end_time')
            ->first();

        if (!$openBreak) {
            return redirect()->route('attendance.index')->with('error', '休憩中ではありません。');
        }

        DB::transaction(function () use ($user, $openBreak) {
            $openBreak->update([
                'break_end_time' => Carbon::now()->format('H:i:s'),
            ]);

            $user->update(['status_id' => 2]); // 出勤中
        });

        return redirect()->route('attendance.index')->with('message', '休憩を終了しました。');
    }
}
