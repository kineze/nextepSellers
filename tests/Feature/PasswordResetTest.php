<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\BrevoMailer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Features;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_links_to_forgot_password_page(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Forgot your password?');
        $response->assertSee(route('password.request'), false);
    }

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');
        }

        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested_through_brevo(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');
        }

        $user = User::factory()->create();
        $resetUrl = null;
        $this->mock(BrevoMailer::class)
            ->shouldReceive('sendPasswordResetLinkEmail')
            ->once()
            ->withArgs(function (string $email, string $name, string $url) use ($user, &$resetUrl) {
                $resetUrl = $url;

                return $email === $user->email && $name === $user->name;
            })
            ->andReturnTrue();

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertNotNull($resetUrl);
        $this->assertStringContainsString('/reset-password/', $resetUrl);
        $this->assertStringContainsString(urlencode($user->email), $resetUrl);
    }

    public function test_forgot_password_does_not_create_an_unknown_user(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');
        }

        $this->mock(BrevoMailer::class)
            ->shouldNotReceive('sendPasswordResetLinkEmail');

        $response = $this->post('/forgot-password', [
            'email' => 'missing@example.com',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', [
            'email' => 'missing@example.com',
        ]);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');
        }

        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $response = $this->get('/reset-password/'.$token.'?email='.urlencode($user->email));

        $response->assertStatus(200);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');
        }

        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
    }
}
