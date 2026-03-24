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
 * 9. 勤怠一覧情報取得機能（一般ユーザー）
 */
class AttendanceListTest extends TestCase
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

    public function test_all_personal_attendance_info_is_displayed(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 20, 10, 0, 0));
        $user = $this->createVerifiedUser();
        Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::create(2025, 3, 15),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list', ['year' => 2025, 'month' => 3]));
        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('03/15');

        Carbon::setTestNow();
    }

    public function test_default_view_is_current_month(): void
    {
        $now = Carbon::now();
        $user = $this->createVerifiedUser();

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertRedirect(route('attendance.list', ['year' => $now->year, 'month' => $now->month]));
    }

    public function test_previous_month_button_displays_previous_month(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 20, 10, 0, 0));
        $user = $this->createVerifiedUser();

        $response = $this->actingAs($user)->get(route('attendance.list', ['year' => 2025, 'month' => 2]));
        $response->assertStatus(200);
        $response->assertSee('2025/02');

        Carbon::setTestNow();
    }

    public function test_next_month_button_displays_next_month(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 20, 10, 0, 0));
        $user = $this->createVerifiedUser();

        $response = $this->actingAs($user)->get(route('attendance.list', ['year' => 2025, 'month' => 4]));
        $response->assertStatus(200);
        $response->assertSee('2025/04');

        Carbon::setTestNow();
    }

    public function test_detail_button_transitions_to_detail_page(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 20, 10, 0, 0));
        $user = $this->createVerifiedUser();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::create(2025, 3, 15),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.detail', $attendance->id));
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');

        Carbon::setTestNow();
    }
}
