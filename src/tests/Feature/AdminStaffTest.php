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
 * 14. ユーザー情報取得機能（管理者）
 */
class AdminStaffTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    private function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'テスト管理者',
            'email' => 'admin-staff@example.com',
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

    public function test_staff_list_shows_name_and_email_for_all_users(): void
    {
        $admin = $this->createAdmin();
        $this->createVerifiedUser('一郎', 'ichiro@example.com');
        $this->createVerifiedUser('二郎', 'jiro@example.com');

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.staff.list'));

        $response->assertStatus(200);
        $response->assertSee('一郎');
        $response->assertSee('ichiro@example.com');
        $response->assertSee('二郎');
        $response->assertSee('jiro@example.com');
    }

    public function test_selected_user_attendance_list_displays_correctly(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 20, 10, 0, 0));
        $admin = $this->createAdmin();
        $user = $this->createVerifiedUser('対象ユーザー', 'target@example.com');

        Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::create(2025, 3, 10),
            'clock_in_time' => '08:30:00',
            'clock_out_time' => '17:30:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.staff', $user->id));

        $response->assertStatus(200);
        $response->assertSee('対象ユーザーさんの勤怠');
        $response->assertSee('08:30');
        $response->assertSee('17:30');

        Carbon::setTestNow();
    }

    public function test_previous_month_navigation_on_staff_attendance(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 20, 10, 0, 0));
        $admin = $this->createAdmin();
        $user = $this->createVerifiedUser('月次ユーザー', 'month@example.com');

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.staff.date', ['id' => $user->id, 'year' => 2025, 'month' => 2]));

        $response->assertStatus(200);
        $response->assertSee('2025/02');

        Carbon::setTestNow();
    }

    public function test_next_month_navigation_on_staff_attendance(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 20, 10, 0, 0));
        $admin = $this->createAdmin();
        $user = $this->createVerifiedUser('月次ユーザー2', 'month2@example.com');

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.staff.date', ['id' => $user->id, 'year' => 2025, 'month' => 4]));

        $response->assertStatus(200);
        $response->assertSee('2025/04');

        Carbon::setTestNow();
    }

    public function test_detail_link_from_staff_attendance_goes_to_admin_detail(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 20, 10, 0, 0));
        $admin = $this->createAdmin();
        $user = $this->createVerifiedUser('詳細ユーザー', 'detail@example.com');

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::create(2025, 3, 5),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.detail', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee('詳細ユーザー');
        $response->assertSee('3月5日');

        Carbon::setTestNow();
    }
}
