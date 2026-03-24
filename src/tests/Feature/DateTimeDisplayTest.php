<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tests\SeedsRequiredData;

/**
 * 4. 日時取得機能
 */
class DateTimeDisplayTest extends TestCase
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

    public function test_current_date_and_time_are_displayed_on_attendance_screen(): void
    {
        $fixedNow = Carbon::create(2025, 3, 20, 14, 30, 0);
        Carbon::setTestNow($fixedNow);

        $user = $this->createVerifiedUser();

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertStatus(200);
        $response->assertSee('2025年3月20日(木)');
        $response->assertSee('14:30');

        Carbon::setTestNow();
    }
}
