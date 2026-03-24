<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tests\SeedsRequiredData;

/**
 * 6. 出勤機能
 */
class ClockInTest extends TestCase
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

    public function test_clock_in_button_is_visible_and_clock_in_succeeds(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 20, 9, 0, 0));
        $user = $this->createVerifiedUser();

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('出勤');

        $response = $this->actingAs($user)->post(route('attendance.clock-in'));
        $response->assertRedirect(route('attendance.index'));
        $response->assertSessionHas('message', '出勤しました。');

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('出勤中');

        Carbon::setTestNow();
    }

    public function test_clock_in_button_is_not_displayed_after_clock_out(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 20, 19, 0, 0));
        $user = $this->createVerifiedUser();
        Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::today(),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertDontSee('出勤');
        $response->assertSee('お疲れ様でした');

        Carbon::setTestNow();
    }

    public function test_clock_in_time_is_recorded_on_attendance_list(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 20, 9, 5, 0));
        $user = $this->createVerifiedUser();

        $this->actingAs($user)->post(route('attendance.clock-in'));

        $response = $this->actingAs($user)->get(route('attendance.list', ['year' => 2025, 'month' => 3]));
        $response->assertStatus(200);
        $response->assertSee('09:05');

        Carbon::setTestNow();
    }
}
