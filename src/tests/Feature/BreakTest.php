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
 * 7. 休憩機能
 */
class BreakTest extends TestCase
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

    public function test_break_start_button_is_visible_and_break_start_succeeds(): void
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
        $response->assertSee('休憩入');

        $response = $this->actingAs($user)->post(route('attendance.break-start'));
        $response->assertRedirect(route('attendance.index'));
        $response->assertSessionHas('message', '休憩を開始しました。');

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('休憩中');

        Carbon::setTestNow();
    }

    public function test_break_start_button_appears_again_after_multiple_breaks(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 20, 14, 0, 0));
        $user = $this->createVerifiedUser(2);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::today(),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => null,
        ]);
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start_time' => '12:00:00',
            'break_end_time' => '13:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('休憩入');

        Carbon::setTestNow();
    }

    public function test_break_end_button_works_and_status_changes_to_at_work(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 20, 13, 0, 0));
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
        $response->assertSee('休憩戻');

        $response = $this->actingAs($user)->post(route('attendance.break-end'));
        $response->assertRedirect(route('attendance.index'));
        $response->assertSessionHas('message', '休憩を終了しました。');

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('出勤中');

        Carbon::setTestNow();
    }

    public function test_break_times_are_recorded_on_attendance_list(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 20, 14, 0, 0));
        $user = $this->createVerifiedUser(2);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::today(),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => null,
        ]);
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start_time' => '12:00:00',
            'break_end_time' => '13:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list', ['year' => 2025, 'month' => 3]));
        $response->assertStatus(200);
        // 60分の休憩は「1:00」として表示される
        $response->assertSee('1:00');

        Carbon::setTestNow();
    }
}
