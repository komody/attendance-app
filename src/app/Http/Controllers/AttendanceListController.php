<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceListController extends Controller
{
    /**
     * 勤怠一覧を表示
     */
    public function index(Request $request, ?int $year = null, ?int $month = null)
    {
        $user = $request->user();

        // 年月が未指定の場合は今月
        $now = Carbon::now();
        $year = $year ?? $now->year;
        $month = $month ?? $now->month;

        // 有効な年月か検証
        if ($month < 1 || $month > 12) {
            return redirect()->route('attendance.list', [
                'year' => $now->year,
                'month' => $now->month,
            ]);
        }

        $date = Carbon::createFromDate($year, $month, 1);
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        // 対象月の勤怠データを取得（日付でキー化）
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('attendance_date', [$startOfMonth, $endOfMonth])
            ->with('breaks')
            ->get()
            ->keyBy(fn ($a) => $a->attendance_date->format('Y-m-d'));

        // 月の全日付を生成（1日〜月末）
        $daysInMonth = $startOfMonth->daysInMonth;
        $calendar = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);
            $dateKey = $date->format('Y-m-d');
            $attendance = $attendances->get($dateKey);

            $calendar[] = [
                'date' => $date,
                'attendance' => $attendance,
                'clock_in' => $attendance?->clock_in_time ? Carbon::parse($attendance->clock_in_time)->format('H:i') : null,
                'clock_out' => $attendance?->clock_out_time ? Carbon::parse($attendance->clock_out_time)->format('H:i') : null,
                'break_minutes' => $attendance ? $this->calculateBreakMinutes($attendance) : 0,
                'work_minutes' => $attendance ? $this->calculateWorkMinutes($attendance) : null,
            ];
        }

        $prevMonth = $startOfMonth->copy()->subMonth();
        $nextMonth = $startOfMonth->copy()->addMonth();

        return view('attendance.list', [
            'headerType' => 'user',
            'calendar' => $calendar,
            'currentYear' => $year,
            'currentMonth' => $month,
            'prevYear' => $prevMonth->year,
            'prevMonth' => $prevMonth->month,
            'nextYear' => $nextMonth->year,
            'nextMonth' => $nextMonth->month,
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
    private function calculateWorkMinutes(?Attendance $attendance): ?int
    {
        if (!$attendance || !$attendance->clock_in_time || !$attendance->clock_out_time) {
            return null;
        }

        $clockIn = Carbon::parse($attendance->clock_in_time);
        $clockOut = Carbon::parse($attendance->clock_out_time);
        $workMinutes = $clockOut->diffInMinutes($clockIn);
        $breakMinutes = $this->calculateBreakMinutes($attendance);

        return max(0, $workMinutes - $breakMinutes);
    }
}
