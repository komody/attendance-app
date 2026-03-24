<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\VerifyEmail;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tests\SeedsRequiredData;

/**
 * 16. メール認証機能（応用項目）
 */
class EmailVerificationTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    private function validRegisterData(): array
    {
        return [
            'name' => '認証テストユーザー',
            'email' => 'verify@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
    }

    private function createUnverifiedUser(): User
    {
        return User::create([
            'name' => '未認証ユーザー',
            'email' => 'unverified@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => null,
            'first_login_email_verified_at' => null,
            'status_id' => 1,
        ]);
    }

    /** 16.1 会員登録後に認証メールが登録メールアドレスへ送られる */
    public function test_verification_email_is_sent_after_registration(): void
    {
        Notification::fake();

        $this->post(route('register'), $this->validRegisterData());

        $user = User::where('email', 'verify@example.com')->first();
        $this->assertNotNull($user);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** 16.2 誘導画面の「認証はこちらから」でメール確認用（Mailhog）へ遷移できるリンクが表示される */
    public function test_verify_notice_shows_link_to_mail_verification_tool(): void
    {
        $user = $this->createUnverifiedUser();

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertStatus(200);
        $response->assertSee('認証はこちらから', false);
        $response->assertSee('http://localhost:8025', false);
    }

    /** 16.3 メール認証完了後に勤怠画面へリダイレクトされる */
    public function test_email_verification_redirects_to_attendance_screen(): void
    {
        $user = $this->createUnverifiedUser();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes((int) Config::get('auth.verification.expire', 60)),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $response = $this->actingAs($user)->get($url);

        $response->assertRedirect(route('attendance.index'));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
