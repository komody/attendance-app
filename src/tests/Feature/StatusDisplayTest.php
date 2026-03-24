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
 * 5. ステータス確認機能
 */
class StatusDisplayTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    private function createVerifiedUser(int $statusId = 1): User
    {
        return User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'first_login_email_verified_at' => now(),
            'status_id' => $statusId,
        ]);
    }

    public function test_status_displays_off_duty_when_not_clocked_in(): void
    {
        $user = $this->createVerifiedUser(1);
        Carbon::setTestNow(Carbon::create(2025, 3, 20, 10, 0, 0));

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertStatus(200);
        $response->assertSee('勤務外');

        Carbon::setTestNow();
    }

    public function test_status_displays_at_work_when_clocked_in(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 20, 12, 0, 0));
        $user = $this->createVerifiedUser(2);
        Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::today(),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => null,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertStatus(200);
        $response->assertSee('出勤中');

        Carbon::setTestNow();
    }

    public function test_status_displays_on_break_when_on_break(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 20, 12, 30, 0));
        $user = $this->createVerifiedUser(3);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::today(),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => null,
        ]);
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start_time' => '12:00:00',
            'break_end_time' => null,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertStatus(200);
        $response->assertSee('休憩中');

        Carbon::setTestNow();
    }

    public function test_status_displays_clocked_out_when_clocked_out(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 20, 19, 0, 0));
        $user = $this->createVerifiedUser(1);
        Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::today(),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertStatus(200);
        $response->assertSee('退勤済');

        Carbon::setTestNow();
    }
}
