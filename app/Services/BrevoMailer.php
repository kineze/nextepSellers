<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Brevo\Client\Configuration;
use Brevo\Client\Api\TransactionalEmailsApi;
use GuzzleHttp\Client as GuzzleClient;

class BrevoMailer
{
    protected $apiKey;
    protected $apiInstance;

    public function __construct()
    {
        $this->apiKey = config('services.brevo.key');

        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $this->apiKey);
        $this->apiInstance = new TransactionalEmailsApi(new GuzzleClient(), $config);
    }

    public function sendWelcomeEmail($toEmail, $toName, $password)
    {
        return Http::withHeaders([
            'api-key' => $this->apiKey,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => 'Software Engineering',
                'email' => 'helliumgk@gmail.com',
            ],
            'to' => [
                ['email' => $toEmail, 'name' => $toName],
            ],
            'subject' => 'Welcome to the Team!',
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

}
