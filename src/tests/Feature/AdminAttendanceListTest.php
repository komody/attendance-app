<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tests\SeedsRequiredData;

/**
 * 12. 勤怠一覧情報取得機能（管理者）
 */
class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    private function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'テスト管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);
    }

    private function createVerifiedUser(string $name, string $email): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'first_login_email_verified_at' => now(),
            'status_id' => 1,
        ]);
    }

    public function test_all_users_daily_attendance_is_displayed(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 20, 10, 0, 0));
        $admin = $this->createAdmin();
        $userA = $this->createVerifiedUser('ユーザーA', 'a@example.com');
        $userB = $this->createVerifiedUser('ユーザーB', 'b@example.com');

        Attendance::create([
            'user_id' => $userA->id,
            'attendance_date' => Carbon::today(),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);
        Attendance::create([
            'user_id' => $userB->id,
            'attendance_date' => Carbon::today(),
            'clock_in_time' => '10:00:00',
            'clock_out_time' => '19:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('ユーザーA');
        $response->assertSee('ユーザーB');
        $response->assertSee('09:00');
        $response->assertSee('10:00');
        $response->assertSee('18:00');
        $response->assertSee('19:00');

        Carbon::setTestNow();
    }

    public function test_current_date_is_displayed_on_list_screen(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 20, 10, 0, 0));
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('2025年3月20日');
        $response->assertSee('2025/03/20');

        Carbon::setTestNow();
    }

    public function test_previous_day_link_shows_previous_day_attendance(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 20, 10, 0, 0));
        $admin = $this->createAdmin();
        $user = $this->createVerifiedUser('ユーザーA', 'a@example.com');

        Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::create(2025, 3, 19),
            'clock_in_time' => '08:00:00',
            'clock_out_time' => '17:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(
            route('admin.attendance.list.date', ['year' => 2025, 'month' => 3, 'day' => 19])
        );

        $response->assertStatus(200);
        $response->assertSee('2025年3月19日');
        $response->assertSee('ユーザーA');
        $response->assertSee('08:00');

        Carbon::setTestNow();
    }

    public function test_next_day_link_shows_next_day_attendance(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 20, 10, 0, 0));
        $admin = $this->createAdmin();
        $user = $this->createVerifiedUser('ユーザーA', 'a@example.com');

        Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::create(2025, 3, 21),
            'clock_in_time' => '11:00:00',
            'clock_out_time' => '20:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(
            route('admin.attendance.list.date', ['year' => 2025, 'month' => 3, 'day' => 21])
        );

        $response->assertStatus(200);
        $response->assertSee('2025年3月21日');
        $response->assertSee('ユーザーA');
        $response->assertSee('11:00');

        Carbon::setTestNow();
    }
}
