<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\LoginOtpNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthOtpLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('mail.default', 'smtp');
        config()->set('mail.mailers.smtp.host', 'smtp.example.com');
        config()->set('mail.mailers.smtp.port', 587);
        config()->set('mail.mailers.smtp.username', 'tester');
        config()->set('mail.mailers.smtp.password', 'secret');
        config()->set('mail.from.address', 'noreply@example.com');
    }

    public function test_login_page_shows_link_to_otp_page(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee(route('login.otp'), false);
        $response->assertSee('aria-label="Login pakai OTP"', false);
    }

    public function test_otp_page_shows_send_token_form(): void
    {
        $response = $this->get(route('login.otp'));

        $response->assertOk();
        $response->assertSee('Kirim Token OTP');
        $response->assertSee('masukkan email atau username');
    }

    public function test_user_can_request_login_otp_by_username(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'username' => 'anggota_1',
            'email' => 'anggota1@example.com',
            'is_active' => true,
        ]);

        $response = $this->post(route('login.otp.send'), [
            'identifier' => 'anggota_1',
        ]);

        $response->assertRedirect(route('login.otp'));
        $response->assertSessionHas('login_otp_user_id', $user->id);
        $this->assertDatabaseHas('login_otp_tokens', ['user_id' => $user->id]);
        Notification::assertSentTo($user, LoginOtpNotification::class);
    }

    public function test_user_can_login_with_valid_otp(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'username' => 'anggota_otp',
            'email' => 'otp@example.com',
            'is_active' => true,
        ]);

        $this->post(route('login.otp.send'), [
            'identifier' => 'anggota_otp',
        ])->assertRedirect(route('login.otp'));

        $sentToken = null;

        Notification::assertSentTo($user, LoginOtpNotification::class, function (LoginOtpNotification $notification) use (&$sentToken) {
            $sentToken = $notification->token;

            return true;
        });

        $response = $this->withSession([
            'login_otp_user_id' => $user->id,
        ])->post(route('login.otp.verify'), [
            'otp' => $sentToken,
        ]);

        $response->assertRedirect(route('profile.show'));
        $this->assertAuthenticatedAs($user->fresh());
        $this->assertDatabaseMissing('login_otp_tokens', ['user_id' => $user->id]);
    }

    public function test_otp_login_rejects_duplicate_email_lookup(): void
    {
        User::factory()->create([
            'username' => 'user_satu',
            'email' => 'shared@example.com',
            'is_active' => true,
        ]);

        User::factory()->create([
            'username' => 'user_dua',
            'email' => 'shared@example.com',
            'is_active' => true,
        ]);

        $response = $this->post(route('login.otp.send'), [
            'identifier' => 'shared@example.com',
        ]);

        $response->assertSessionHasErrors('identifier');
        $this->assertGuest();
        $this->assertDatabaseCount('login_otp_tokens', 0);
    }
}
