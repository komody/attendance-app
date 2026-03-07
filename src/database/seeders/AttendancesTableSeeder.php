<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 1〜2ヶ月分の出勤履歴を作成
     *
     * @return void
     */
    public function run()
    {
        $userId = 1; // テストユーザー

        $attendances = [];
        $startDate = Carbon::now()->subMonths(2)->startOfMonth();
        $endDate = Carbon::now()->subDay();

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // 土日はスキップ
            if ($date->isWeekend()) {
                continue;
            }

            // 出勤 9:00、退勤 18:00（所定労働時間）
            $clockIn = $date->copy()->setTime(9, 0, 0);
            $clockOut = $date->copy()->setTime(18, 0, 0);

            $attendances[] = [
                'user_id' => $userId,
                'attendance_date' => $date->format('Y-m-d'),
                'clock_in_time' => $clockIn->format('H:i:s'),
                'clock_out_time' => $clockOut->format('H:i:s'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('attendances')->insert($attendances);
    }
}
