<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tests\SeedsRequiredData;

/**
 * 10. 勤怠詳細情報取得機能（一般ユーザー）
 */
class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    private function createVerifiedUser(): User
    {
        return User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'first_login_email_verified_at' => now(),
            'status_id' => 1,
        ]);
    }

    public function test_name_matches_logged_in_user_on_detail_screen(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::create(2025, 3, 15),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.detail', $attendance->id));
        $response->assertStatus(200);
        $response->assertSee('テストユーザー');
    }

    public function test_date_matches_selected_date_on_detail_screen(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::create(2025, 3, 15),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.detail', $attendance->id));
        $response->assertStatus(200);
        $response->assertSee('2025年');
        $response->assertSee('3月15日');
    }

    public function test_clock_in_out_times_match_on_detail_screen(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::create(2025, 3, 15),
            'clock_in_time' => '09:15:00',
            'clock_out_time' => '18:30:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.detail', $attendance->id));
        $response->assertStatus(200);
        $response->assertSee('09:15');
        $response->assertSee('18:30');
    }

    public function test_break_times_match_on_detail_screen(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::create(2025, 3, 15),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start_time' => '12:00:00',
            'break_end_time' => '13:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.detail', $attendance->id));
        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
