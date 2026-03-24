<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tests\SeedsRequiredData;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    private function validData(): array
    {
        return [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ];
    }

    public function test_email_is_required(): void
    {
        $data = $this->validData();
        $data['email'] = '';

        $response = $this->post(route('admin.login'), $data);
        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('メールアドレスを入力してください', $response->getSession()->get('errors')->get('email')[0] ?? '');
    }

    public function test_password_is_required(): void
    {
        $data = $this->validData();
        $data['password'] = '';

        $response = $this->post(route('admin.login'), $data);
        $response->assertSessionHasErrors('password');
        $this->assertStringContainsString('パスワードを入力してください', $response->getSession()->get('errors')->get('password')[0] ?? '');
    }

    public function test_login_fails_with_incorrect_credentials(): void
    {
        $data = $this->validData();

        $response = $this->from(route('admin.login'))->post(route('admin.login'), $data);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'ログイン情報が登録されていません',
            $response->getSession()->get('errors')->get('email')[0] ?? ''
        );
    }

    public function test_login_succeeds_with_correct_credentials(): void
    {
        $admin = Admin::create([
            'name' => 'テスト管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('admin.login'), $this->validData());

        $this->assertAuthenticatedAs($admin, 'admin');
        $response->assertRedirect('/admin/attendance/list');
    }
}