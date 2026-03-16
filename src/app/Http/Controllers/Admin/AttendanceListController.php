<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceListController extends Controller
{
    /**
     * 管理者用勤怠一覧を表示（日付指定）
     */
    public function index(Request $request, ?int $year = null, ?int $month = null, ?int $day = null)
    {
        $now = Carbon::now();
        $year = $year ?? $now->year;
        $month = $month ?? $now->month;
        $day = $day ?? $now->day;

        $date = Carbon::createFromDate($year, $month, $day);

        // その日に勤怠がある従業員のみ取得（元の勤怠データのみ、修正申請は考慮しない）
        $attendances = Attendance::whereDate('attendance_date', $date)
            ->with(['user', 'breaks'])
            ->orderBy('clock_in_time')
            ->get();

        $rows = $attendances->map(function (Attendance $attendance) {
            return [
                'attendance' => $attendance,
                'user_name' => $attendance->user->name,
                'clock_in' => $attendance->clock_in_time ? Carbon::parse($attendance->clock_in_time)->format('H:i') : '',
                'clock_out' => $attendance->clock_out_time ? Carbon::parse($attendance->clock_out_time)->format('H:i') : '',
                'break_minutes' => $this->calculateBreakMinutes($attendance),
                'work_minutes' => $this->calculateWorkMinutes($attendance),
            ];
        });

        $prevDate = $date->copy()->subDay();
        $nextDate = $date->copy()->addDay();

        return view('admin.attendance.list', [
            'headerType' => 'admin',
            'date' => $date,
            'rows' => $rows,
            'prevYear' => $prevDate->year,
            'prevMonth' => $prevDate->month,
            'prevDay' => $prevDate->day,
            'nextYear' => $nextDate->year,
            'nextMonth' => $nextDate->month,
            'nextDay' => $nextDate->day,
        ]);
    }

    /**
     * 休憩時間の合計（分）を計算
     */
    private function calculateBreakMinutes(Attendance $attendance): int
    {
        $total = 0;

        foreach ($attendance->breaks as $break) {
            if ($break->break_start_time && $break->break_end_time) {
                $start = Carbon::parse($break->break_start_time);
                $end = Carbon::parse($break->break_end_time);
                $total += $end->diffInMinutes($start);
            }
        }

        return $total;
    }

    /**
     * 勤務時間（分）を計算。退勤未打刻の場合はnull
     */
    private function calculateWorkMinutes(Attendance $attendance): ?int
    {
        if (!$attendance->clock_in_time || !$attendance->clock_out_time) {
            return null;
        }

        $clockIn = Carbon::parse($attendance->clock_in_time);
        $clockOut = Carbon::parse($attendance->clock_out_time);
        $workMinutes = $clockOut->diffInMinutes($clockIn);
        $breakMinutes = $this->calculateBreakMinutes($attendance);

        return max(0, $workMinutes - $breakMinutes);
    }
}
