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
 * 8. 退勤機能
 */
class ClockOutTest extends TestCase
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

    public function test_clock_out_button_is_visible_and_clock_out_succeeds(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 20, 18, 0, 0));
        $user = $this->createVerifiedUser(2);
        Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::today(),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => null,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('退勤');

        $response = $this->actingAs($user)->post(route('attendance.clock-out'));
        $response->assertRedirect(route('attendance.index'));
        $response->assertSessionHas('message', 'お疲れ様でした。');

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('退勤済');

        Carbon::setTestNow();
    }

    public function test_clock_out_time_is_recorded_on_attendance_list(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 20, 18, 5, 0));
        $user = $this->createVerifiedUser(2);
        Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::today(),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => null,
        ]);

        $this->actingAs($user)->post(route('attendance.clock-out'));

        $response = $this->actingAs($user)->get(route('attendance.list', ['year' => 2025, 'month' => 3]));
        $response->assertStatus(200);
        $response->assertSee('18:05');

        Carbon::setTestNow();
    }
}
