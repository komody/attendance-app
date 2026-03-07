<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BreaksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 各出勤履歴に休憩を追加（昼1時間、夕方15〜30分）
     *
     * @return void
     */
    public function run()
    {
        $attendances = DB::table('attendances')->orderBy('attendance_date')->get();

        $breaks = [];
        foreach ($attendances as $attendance) {
            // 昼休憩: 12:00〜13:00（1時間）
            $breaks[] = [
                'attendance_id' => $attendance->id,
                'break_start_time' => '12:00:00',
                'break_end_time' => '13:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // 夕方休憩: 15:00〜15:15〜15:30（15〜30分のランダム）
            $eveningMinutes = [15, 20, 25, 30][mt_rand(0, 3)];
            $breakEnd = sprintf('15:%02d:00', $eveningMinutes);

            $breaks[] = [
                'attendance_id' => $attendance->id,
                'break_start_time' => '15:00:00',
                'break_end_time' => $breakEnd,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('breaks')->insert($breaks);
    }
}
