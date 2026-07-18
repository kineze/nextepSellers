<?php

namespace App\Services;

use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Configuration;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Http;

class BrevoMailer
{
    protected $apiKey;

    protected $apiInstance;

    public function __construct()
    {
        $this->apiKey = config('services.brevo.key');

        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $this->apiKey);
        $this->apiInstance = new TransactionalEmailsApi(new GuzzleClient, $config);
    }

    public function sendWelcomeEmail($toEmail, $toName, $password)
    {
        return Http::withHeaders([
            'api-key' => $this->apiKey,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => 'Nextep',
                'email' => 'helliumgk@gmail.com',
            ],
            'to' => [
                ['email' => $toEmail, 'name' => $toName],
            ],
            'subject' => 'Welcome to Nextep',
            'htmlContent' => view('emails.welcome', [
                'name' => $toName,
                'email' => $toEmail,
                'password' => $password,
            ])->render(),
        ])->successful();
    }

    public function sendPasswordResetEmail($toEmail, $toName, $newPassword)
    {
        return Http::withHeaders([
            'api-key' => $this->apiKey,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => 'Software engineering',
                'email' => 'helliumgk@gmail.com',
            ],
            'to' => [
                ['email' => $toEmail, 'name' => $toName],
            ],
            'subject' => 'Your Password Was Reset',
            'htmlContent' => view('emails.password-reset', [
                'name' => $toName,
                'email' => $toEmail,
                'password' => $newPassword,
            ])->render(),
        ])->successful();
    }

    public function sendPasswordResetLinkEmail(string $toEmail, string $toName, string $resetUrl): bool
    {
        return Http::withHeaders([
            'api-key' => $this->apiKey,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => 'Nextep Support',
                'email' => 'helliumgk@gmail.com',
            ],
            'to' => [
                ['email' => $toEmail, 'name' => $toName],
            ],
            'subject' => 'Reset your Nextep password',
            'htmlContent' => view('emails.password-reset-link', [
                'name' => $toName,
                'resetUrl' => $resetUrl,
            ])->render(),
        ])->successful();
    }

    public function sendSellerEmailOtp(string $toEmail, string $toName, string $otp): bool
    {
        return Http::withHeaders([
            'api-key' => $this->apiKey,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => 'nextepSellers',
                'email' => 'helliumgk@gmail.com',
            ],
            'to' => [
                ['email' => $toEmail, 'name' => $toName],
            ],
            'subject' => 'Your Seller Registration Verification Code',
            'htmlContent' => view('emails.seller-email-otp', [
                'name' => $toName,
                'otp' => $otp,
            ])->render(),
        ])->successful();
    }

    public function sendSellerOnboardingEmail(
        string $toEmail,
        string $toName,
        string $password,
        string $levelName,
        int $points
    ): bool {
        return Http::withHeaders([
            'api-key' => $this->apiKey,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => 'Nextep',
                'email' => 'helliumgk@gmail.com',
            ],
            'to' => [
                ['email' => $toEmail, 'name' => $toName],
            ],
            'subject' => 'Congratulations! Welcome to Nextep Seller Hub',
            'htmlContent' => view('emails.seller-onboarding', [
                'name' => $toName,
                'email' => $toEmail,
                'password' => $password,
                'level_name' => $levelName,
                'points' => $points,
            ])->render(),
        ])->successful();
    }

    public function sendUserBlockedEmail(string $toEmail, string $toName, string $reason): bool
    {
        $supportEmail = config('mail.from.address', 'support@nextep.com');

        return Http::withHeaders([
            'api-key' => $this->apiKey,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => 'Nextep Support',
                'email' => 'helliumgk@gmail.com',
            ],
            'to' => [
                ['email' => $toEmail, 'name' => $toName],
            ],
            'subject' => 'Your Nextep account has been blocked',
            'htmlContent' => view('emails.user-blocked', [
                'name' => $toName,
                'reason' => $reason,
                'supportEmail' => $supportEmail,
            ])->render(),
        ])->successful();
    }

    public function sendUserUnblockedEmail(string $toEmail, string $toName): bool
    {
        $supportEmail = config('mail.from.address', 'support@nextep.com');

        return Http::withHeaders([
            'api-key' => $this->apiKey,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => 'Nextep Support',
                'email' => 'helliumgk@gmail.com',
            ],
            'to' => [
                ['email' => $toEmail, 'name' => $toName],
            ],
            'subject' => 'Your Nextep account has been unblocked',
            'htmlContent' => view('emails.user-unblocked', [
                'name' => $toName,
                'supportEmail' => $supportEmail,
            ])->render(),
        ])->successful();
    }
}
