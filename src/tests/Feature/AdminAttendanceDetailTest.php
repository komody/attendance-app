<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tests\SeedsRequiredData;

/**
 * 13. 勤怠詳細情報取得・修正機能（管理者）
 */
class AdminAttendanceDetailTest extends TestCase
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

    private function createAttendanceWithBreak(User $user): Attendance
    {
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

        return $attendance->fresh(['breaks']);
    }

    public function test_detail_screen_matches_selected_record(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendanceWithBreak($user);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.detail', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee('テストユーザー');
        $response->assertSee('2025年');
        $response->assertSee('3月15日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_clock_in_after_clock_out_shows_validation_error(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendanceWithBreak($user);

        $response = $this->actingAs($admin, 'admin')->post(
            route('admin.attendance.detail.update', $attendance->id),
            [
                'corrected_clock_in_time' => '19:00',
                'corrected_clock_out_time' => '09:00',
                'remarks' => '管理者修正',
                'breaks' => [
                    0 => [
                        'break_id' => $attendance->breaks->first()->id,
                        'corrected_break_start' => '12:00',
                        'corrected_break_end' => '13:00',
                    ],
                ],
            ]
        );

        $response->assertSessionHasErrors('corrected_clock_in_time');
        $this->assertStringContainsString(
            '出勤時間もしくは退勤時間が不適切な値です',
            $response->getSession()->get('errors')->get('corrected_clock_in_time')[0] ?? ''
        );
    }

    public function test_break_start_after_clock_out_shows_validation_error(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendanceWithBreak($user);

        $response = $this->actingAs($admin, 'admin')->post(
            route('admin.attendance.detail.update', $attendance->id),
            [
                'corrected_clock_in_time' => '09:00',
                'corrected_clock_out_time' => '18:00',
                'remarks' => '管理者修正',
                'breaks' => [
                    0 => [
                        'break_id' => $attendance->breaks->first()->id,
                        'corrected_break_start' => '19:00',
                        'corrected_break_end' => '20:00',
                    ],
                ],
            ]
        );

        $messages = $response->getSession()->get('errors')->all();
        $this->assertTrue(in_array('休憩時間が不適切な値です', $messages, true));
    }

    public function test_break_end_after_clock_out_shows_validation_error(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendanceWithBreak($user);

        $response = $this->actingAs($admin, 'admin')->post(
            route('admin.attendance.detail.update', $attendance->id),
            [
                'corrected_clock_in_time' => '09:00',
                'corrected_clock_out_time' => '18:00',
                'remarks' => '管理者修正',
                'breaks' => [
                    0 => [
                        'break_id' => $attendance->breaks->first()->id,
                        'corrected_break_start' => '17:00',
                        'corrected_break_end' => '19:00',
                    ],
                ],
            ]
        );

        $messages = $response->getSession()->get('errors')->all();
        $this->assertTrue(in_array('休憩時間もしくは退勤時間が不適切な値です', $messages, true));
    }

    public function test_empty_remarks_shows_validation_error(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendanceWithBreak($user);

        $response = $this->actingAs($admin, 'admin')->post(
            route('admin.attendance.detail.update', $attendance->id),
            [
                'corrected_clock_in_time' => '09:00',
                'corrected_clock_out_time' => '18:00',
                'remarks' => '',
                'breaks' => [
                    0 => [
                        'break_id' => $attendance->breaks->first()->id,
                        'corrected_break_start' => '12:00',
                        'corrected_break_end' => '13:00',
                    ],
                ],
            ]
        );

        $response->assertSessionHasErrors('remarks');
        $this->assertStringContainsString(
            '備考を記入してください',
            $response->getSession()->get('errors')->get('remarks')[0] ?? ''
        );
    }
}
