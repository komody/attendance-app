<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectionApplication;
use App\Models\CorrectionStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tests\SeedsRequiredData;

/**
 * 11. 勤怠詳細情報修正機能（一般ユーザー）
 */
class AttendanceCorrectionTest extends TestCase
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
        return $attendance;
    }

    public function test_clock_in_after_clock_out_displays_validation_error(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendanceWithBreak($user);

        $response = $this->actingAs($user)->post(route('attendance.correction.store'), [
            'attendance_id' => $attendance->id,
            'corrected_clock_in_time' => '19:00',
            'corrected_clock_out_time' => '09:00',
            'breaks' => [
                0 => [
                    'break_id' => $attendance->breaks->first()->id,
                    'corrected_break_start' => '12:00',
                    'corrected_break_end' => '13:00',
                ],
            ],
            'remarks' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors('corrected_clock_in_time');
        $this->assertStringContainsString(
            '出勤時間もしくは退勤時間が不適切な値です',
            $response->getSession()->get('errors')->get('corrected_clock_in_time')[0] ?? ''
        );
    }

    public function test_break_start_after_clock_out_displays_validation_error(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendanceWithBreak($user);

        $response = $this->actingAs($user)->post(route('attendance.correction.store'), [
            'attendance_id' => $attendance->id,
            'corrected_clock_in_time' => '09:00',
            'corrected_clock_out_time' => '18:00',
            'breaks' => [
                0 => [
                    'break_id' => $attendance->breaks->first()->id,
                    'corrected_break_start' => '19:00',
                    'corrected_break_end' => '20:00',
                ],
            ],
            'remarks' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors();
        $messages = $response->getSession()->get('errors')->all();
        $this->assertTrue(
            in_array('休憩時間が不適切な値です', $messages, true),
            '休憩時間が不適切な値です のエラーメッセージが含まれること'
        );
    }

    public function test_break_end_after_clock_out_displays_validation_error(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendanceWithBreak($user);

        $response = $this->actingAs($user)->post(route('attendance.correction.store'), [
            'attendance_id' => $attendance->id,
            'corrected_clock_in_time' => '09:00',
            'corrected_clock_out_time' => '18:00',
            'breaks' => [
                0 => [
                    'break_id' => $attendance->breaks->first()->id,
                    'corrected_break_start' => '17:00',
                    'corrected_break_end' => '19:00',
                ],
            ],
            'remarks' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors();
        $messages = $response->getSession()->get('errors')->all();
        $this->assertTrue(
            in_array('休憩時間もしくは退勤時間が不適切な値です', $messages, true),
            '休憩時間もしくは退勤時間が不適切な値です のエラーメッセージが含まれること'
        );
    }

    public function test_empty_remarks_displays_validation_error(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendanceWithBreak($user);

        $response = $this->actingAs($user)->post(route('attendance.correction.store'), [
            'attendance_id' => $attendance->id,
            'corrected_clock_in_time' => '09:00',
            'corrected_clock_out_time' => '18:00',
            'breaks' => [
                0 => [
                    'break_id' => $attendance->breaks->first()->id,
                    'corrected_break_start' => '12:00',
                    'corrected_break_end' => '13:00',
                ],
            ],
            'remarks' => '',
        ]);

        $response->assertSessionHasErrors('remarks');
        $this->assertStringContainsString(
            '備考を記入してください',
            $response->getSession()->get('errors')->get('remarks')[0] ?? ''
        );
    }

    public function test_correction_application_executes_successfully(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendanceWithBreak($user);

        $response = $this->actingAs($user)->post(route('attendance.correction.store'), [
            'attendance_id' => $attendance->id,
            'corrected_clock_in_time' => '09:30',
            'corrected_clock_out_time' => '18:30',
            'breaks' => [
                0 => [
                    'break_id' => $attendance->breaks->first()->id,
                    'corrected_break_start' => '12:00',
                    'corrected_break_end' => '13:00',
                ],
            ],
            'remarks' => '遅刻のため修正申請',
        ]);

        $response->assertRedirect(route('attendance.detail', $attendance->id));
        $response->assertSessionHas('message', '修正申請を送信しました。');

        $this->assertDatabaseHas('correction_applications', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'remarks' => '遅刻のため修正申請',
        ]);
    }

    public function test_pending_approval_list_displays_user_applications(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendanceWithBreak($user);
        $pendingStatus = CorrectionStatus::where('name', '承認待ち')->first();
        CorrectionApplication::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'corrected_clock_in_time' => '09:30',
            'corrected_clock_out_time' => '18:30',
            'remarks' => '修正申請',
            'correction_status_id' => $pendingStatus->id,
        ]);

        $response = $this->actingAs($user)->get(route('stamp_correction_request.list', ['tab' => 'pending']));

        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee('修正申請');
    }

    public function test_approved_list_displays_approved_applications(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendanceWithBreak($user);
        $approvedStatus = CorrectionStatus::where('name', '承認済み')->first();
        CorrectionApplication::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'corrected_clock_in_time' => '09:30',
            'corrected_clock_out_time' => '18:30',
            'remarks' => '承認済み申請',
            'correction_status_id' => $approvedStatus->id,
        ]);

        $response = $this->actingAs($user)->get(route('stamp_correction_request.list', ['tab' => 'approved']));

        $response->assertStatus(200);
        $response->assertSee('承認済み');
        $response->assertSee('承認済み申請');
    }

    public function test_detail_button_transitions_to_attendance_detail_screen(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendanceWithBreak($user);
        $pendingStatus = CorrectionStatus::where('name', '承認待ち')->first();
        CorrectionApplication::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'corrected_clock_in_time' => '09:30',
            'corrected_clock_out_time' => '18:30',
            'remarks' => '修正申請',
            'correction_status_id' => $pendingStatus->id,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.detail', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee('テストユーザー');
    }
}
