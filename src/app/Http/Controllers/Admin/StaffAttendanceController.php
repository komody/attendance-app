<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StaffAttendanceController extends Controller
{
    /**
     * スタッフ別月次勤怠一覧を表示（FN043, FN044, FN046）
     */
    public function index(Request $request, int $id, ?int $year = null, ?int $month = null)
    {
        $user = User::findOrFail($id);

        $now = Carbon::now();
        $year = $year ?? $now->year;
        $month = $month ?? $now->month;

        if ($month < 1 || $month > 12) {
            return redirect()->route('admin.attendance.staff', ['id' => $id]);
        }

        $date = Carbon::createFromDate($year, $month, 1);
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('attendance_date', [$startOfMonth, $endOfMonth])
            ->with('breaks')
            ->get()
            ->keyBy(fn ($a) => $a->attendance_date->format('Y-m-d'));

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

        return view('admin.attendance.staff', [
            'headerType' => 'admin',
            'user' => $user,
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
     * CSV出力（FN045）
     */
    public function csv(Request $request, int $id, ?int $year = null, ?int $month = null): StreamedResponse
    {
        $user = User::findOrFail($id);

        $now = Carbon::now();
        $year = $year ?? $now->year;
        $month = $month ?? $now->month;

        if ($month < 1 || $month > 12) {
            $year = $now->year;
            $month = $now->month;
        }

        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('attendance_date', [$startOfMonth, $endOfMonth])
            ->with('breaks')
            ->orderBy('attendance_date')
            ->get();

        $safeName = preg_replace('/[\\\\\/:*?"<>|]/', '_', $user->name);
        $filename = sprintf('%s_%d%02d.csv', $safeName, $year, $month);

        return response()->streamDownload(function () use ($attendances) {
            $stream = fopen('php://output', 'w');
            fprintf($stream, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($stream, ['日付', '出勤', '退勤', '休憩', '合計']);

            foreach ($attendances as $attendance) {
                $breakMinutes = $this->calculateBreakMinutes($attendance);
                $workMinutes = $this->calculateWorkMinutes($attendance);

                $clockIn = $attendance->clock_in_time ? Carbon::parse($attendance->clock_in_time)->format('H:i') : '';
                $clockOut = $attendance->clock_out_time ? Carbon::parse($attendance->clock_out_time)->format('H:i') : '';
                $breakStr = $breakMinutes > 0
                    ? sprintf('%d:%02d', floor($breakMinutes / 60), $breakMinutes % 60)
                    : '';
                $workStr = $workMinutes !== null
                    ? sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60)
                    : '';

                fputcsv($stream, [
                    $attendance->attendance_date->format('Y/m/d'),
                    $clockIn,
                    $clockOut,
                    $breakStr,
                    $workStr,
                ]);
            }
            fclose($stream);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

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
