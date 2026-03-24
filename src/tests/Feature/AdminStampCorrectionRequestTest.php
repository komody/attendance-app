<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\CorrectionApplication;
use App\Models\CorrectionBreak;
use App\Models\CorrectionStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tests\SeedsRequiredData;

/**
 * 15. 勤怠情報修正機能（管理者・修正申請承認）
 */
class AdminStampCorrectionRequestTest extends TestCase
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

    private function createAttendance(User $user): Attendance
    {
        return Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::create(2025, 3, 15),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);
    }

    public function test_pending_tab_lists_all_users_pending_requests(): void
    {
        $admin = $this->createAdmin();
        $pendingStatus = CorrectionStatus::where('name', '承認待ち')->firstOrFail();

        $user1 = $this->createVerifiedUser('ユーザー1', 'u1@example.com');
        $user2 = $this->createVerifiedUser('ユーザー2', 'u2@example.com');
        $a1 = $this->createAttendance($user1);
        $a2 = $this->createAttendance($user2);

        CorrectionApplication::create([
            'user_id' => $user1->id,
            'attendance_id' => $a1->id,
            'corrected_clock_in_time' => '09:30:00',
            'corrected_clock_out_time' => '18:30:00',
            'remarks' => '申請A',
            'correction_status_id' => $pendingStatus->id,
        ]);
        CorrectionApplication::create([
            'user_id' => $user2->id,
            'attendance_id' => $a2->id,
            'corrected_clock_in_time' => '10:00:00',
            'corrected_clock_out_time' => '19:00:00',
            'remarks' => '申請B',
            'correction_status_id' => $pendingStatus->id,
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('stamp_correction_request.list', ['tab' => 'pending']));

        $response->assertStatus(200);
        $response->assertSee('ユーザー1');
        $response->assertSee('ユーザー2');
        $response->assertSee('申請A');
        $response->assertSee('申請B');
    }

    public function test_approved_tab_lists_all_users_approved_requests(): void
    {
        $admin = $this->createAdmin();
        $approvedStatus = CorrectionStatus::where('name', '承認済み')->firstOrFail();

        $user1 = $this->createVerifiedUser('ユーザー1', 'u1@example.com');
        $user2 = $this->createVerifiedUser('ユーザー2', 'u2@example.com');
        $a1 = $this->createAttendance($user1);
        $a2 = $this->createAttendance($user2);

        CorrectionApplication::create([
            'user_id' => $user1->id,
            'attendance_id' => $a1->id,
            'corrected_clock_in_time' => '09:30:00',
            'corrected_clock_out_time' => '18:30:00',
            'remarks' => '承認済A',
            'correction_status_id' => $approvedStatus->id,
        ]);
        CorrectionApplication::create([
            'user_id' => $user2->id,
            'attendance_id' => $a2->id,
            'corrected_clock_in_time' => '10:00:00',
            'corrected_clock_out_time' => '19:00:00',
            'remarks' => '承認済B',
            'correction_status_id' => $approvedStatus->id,
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('stamp_correction_request.list', ['tab' => 'approved']));

        $response->assertStatus(200);
        $response->assertSee('ユーザー1');
        $response->assertSee('ユーザー2');
        $response->assertSee('承認済A');
        $response->assertSee('承認済B');
    }

    public function test_approve_detail_screen_shows_request_contents(): void
    {
        $admin = $this->createAdmin();
        $pendingStatus = CorrectionStatus::where('name', '承認待ち')->firstOrFail();
        $user = $this->createVerifiedUser('申請者', 'applicant@example.com');
        $attendance = $this->createAttendance($user);

        $application = CorrectionApplication::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'corrected_clock_in_time' => '09:45:00',
            'corrected_clock_out_time' => '18:45:00',
            'remarks' => '打刻忘れのため',
            'correction_status_id' => $pendingStatus->id,
        ]);
        CorrectionBreak::create([
            'correction_application_id' => $application->id,
            'break_id' => null,
            'corrected_break_start' => '12:00:00',
            'corrected_break_end' => '13:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(
            route('stamp_correction_request.approve', $application->id)
        );

        $response->assertStatus(200);
        $response->assertSee('申請者');
        $response->assertSee('打刻忘れのため');
        $response->assertSee('09:45');
        $response->assertSee('18:45');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    public function test_approve_updates_application_and_attendance(): void
    {
        $admin = $this->createAdmin();
        $pendingStatus = CorrectionStatus::where('name', '承認待ち')->firstOrFail();
        $approvedStatus = CorrectionStatus::where('name', '承認済み')->firstOrFail();
        $user = $this->createVerifiedUser('申請者', 'applicant@example.com');
        $attendance = $this->createAttendance($user);

        $application = CorrectionApplication::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'corrected_clock_in_time' => '08:30:00',
            'corrected_clock_out_time' => '17:30:00',
            'remarks' => '承認テスト',
            'correction_status_id' => $pendingStatus->id,
        ]);
        CorrectionBreak::create([
            'correction_application_id' => $application->id,
            'break_id' => null,
            'corrected_break_start' => '12:00:00',
            'corrected_break_end' => '12:30:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->post(
            route('stamp_correction_request.approve', $application->id)
        );

        $response->assertRedirect(route('stamp_correction_request.approve', $application->id));
        $response->assertSessionHas('message', '申請を承認しました。');

        $application->refresh();
        $attendance->refresh();

        $this->assertSame($approvedStatus->id, (int) $application->correction_status_id);
        $this->assertSame($admin->id, (int) $application->approved_admin_id);
        $this->assertNotNull($application->approval_date);

        $this->assertSame('08:30:00', substr((string) $attendance->clock_in_time, 0, 8));
        $this->assertSame('17:30:00', substr((string) $attendance->clock_out_time, 0, 8));

        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $attendance->id,
            'break_start_time' => '12:00:00',
            'break_end_time' => '12:30:00',
        ]);
    }
}
