<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\SeedsRequiredData;

class RegisterTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    private function validData(): array
    {
        return [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
    }

    public function test_name_is_required(): void
    {
        $data = $this->validData();
        $data['name'] = '';

        $response = $this->post(route('register'), $data);
        $response->assertSessionHasErrors('name');
        $this->assertStringContainsString('お名前を入力してください', $response->getSession()->get('errors')->get('name')[0] ?? '');
    }

    public function test_email_is_required(): void
    {
        $data = $this->validData();
        $data['email'] = '';

        $response = $this->post(route('register'), $data);
        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('メールアドレスを入力してください', $response->getSession()->get('errors')->get('email')[0] ?? '');
    }

    public function test_password_must_be_at_least_8_characters(): void
    {
        $data = $this->validData();
        $data['password'] = '1234567';
        $data['password_confirmation'] = '1234567';

        $response = $this->post(route('register'), $data);
        $response->assertSessionHasErrors('password');
        $this->assertStringContainsString('パスワードは8文字以上で入力してください', $response->getSession()->get('errors')->get('password')[0] ?? '');
    }

    public function test_password_confirmation_must_match(): void
    {
        $data = $this->validData();
        $data['password_confirmation'] = 'different123';

        $response = $this->post(route('register'), $data);
        $response->assertSessionHasErrors('password');
        $this->assertStringContainsString('パスワードと一致しません', $response->getSession()->get('errors')->get('password')[0] ?? '');
    }

    public function test_password_is_required(): void
    {
        $data = $this->validData();
        $data['password'] = '';
        $data['password_confirmation'] = '';

        $response = $this->post(route('register'), $data);
        $response->assertSessionHasErrors('password');
        $this->assertStringContainsString('パスワードを入力してください', $response->getSession()->get('errors')->get('password')[0] ?? '');
    }

    public function test_user_can_register_with_valid_data(): void
    {
        $data = $this->validData();

        $response = $this->post(route('register'), $data);

        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);

        $response->assertRedirect(route('verification.notice'));
    }
}