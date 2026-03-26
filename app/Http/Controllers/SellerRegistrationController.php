<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use App\Services\BrevoMailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SellerRegistrationController extends Controller
{
    public function __construct(private readonly BrevoMailer $mailer)
    {
    }

    public function sendEmailOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:sellers,email'],
            'first_name' => ['nullable', 'string', 'max:255'],
        ], [
            'email.unique' => 'This email is already used for seller registration.',
        ]);

        $normalizedEmail = strtolower(trim($validated['email']));

        $otp = (string) random_int(100000, 999999);
        $otpKey = $this->getEmailOtpKey($normalizedEmail);
        $verifiedKey = $this->getEmailVerifiedKey($normalizedEmail);

        Cache::put($otpKey, Hash::make($otp), now()->addMinutes(10));
        Cache::forget($verifiedKey);

        $sent = $this->mailer->sendSellerEmailOtp(
            $normalizedEmail,
            $validated['first_name'] ?? 'Seller',
            $otp
        );

        if (!$sent) {
            return response()->json([
                'message' => 'Unable to send verification code right now. Please try again.',
            ], 500);
        }

        return response()->json([
            'message' => 'Verification code sent to your email.',
        ]);
    }

    public function verifyEmailOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'otp' => ['required', 'digits:6'],
        ]);

        $otpKey = $this->getEmailOtpKey($validated['email']);
        $verifiedKey = $this->getEmailVerifiedKey($validated['email']);
        $storedHash = Cache::get($otpKey);

        if (!$storedHash || !Hash::check($validated['otp'], $storedHash)) {
            return response()->json([
                'message' => 'Invalid or expired verification code.',
            ], 422);
        }

        Cache::forget($otpKey);
        Cache::put($verifiedKey, true, now()->addMinutes(30));

        return response()->json([
            'message' => 'Email verified successfully.',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:sellers,email'],
            'phone' => ['nullable', 'string', 'max:25'],
            'seller_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'email_verified' => ['required', 'boolean'],
            'seller_type' => ['required', Rule::in(['individual', 'business'])],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'nic_number' => ['nullable', 'string', 'max:255'],
            'nic_front' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'nic_back' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'agreement_accepted' => ['accepted'],

            'business_name' => ['required_if:seller_type,business', 'nullable', 'string', 'max:255'],
            'business_registration_number' => ['nullable', 'string', 'max:255'],
            'business_type' => ['nullable', 'string', 'max:255'],
            'business_registered_date' => ['nullable', 'date'],
            'address_line_1' => ['required_if:seller_type,business', 'nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required_if:seller_type,business', 'nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:255'],
            'business_registration_document' => ['required_if:seller_type,business', 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        if (!$validated['email_verified']) {
            return response()->json([
                'message' => 'Email verification is required.',
                'errors' => [
                    'verification' => ['Please verify your email before continuing.'],
                ],
            ], 422);
        }

        if ($validated['email_verified']) {
            $verifiedKey = $this->getEmailVerifiedKey($validated['email']);

            if (!Cache::get($verifiedKey)) {
                return response()->json([
                    'message' => 'Email verification is required.',
                    'errors' => [
                        'email' => ['Please verify your email again before submitting.'],
                    ],
                ], 422);
            }
        }

        $referralCodeFromCookie = strtoupper(trim((string) $request->cookie('seller_aff_ref', '')));
        $affiliateSellerId = null;
        if ($referralCodeFromCookie !== '') {
            $affiliateSellerId = Seller::query()
                ->where('referral_code', $referralCodeFromCookie)
                ->value('id');
        }

        DB::transaction(function () use ($request, $validated, $affiliateSellerId) {
            $nicFrontPath = $request->file('nic_front')->store('seller-documents/nic', 'public');
            $nicBackPath = $request->file('nic_back')->store('seller-documents/nic', 'public');
            $sellerImagePath = $request->file('seller_image')
                ?->store('seller-documents/profile', 'public');

            $seller = Seller::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'seller_image' => $sellerImagePath,
                'seller_type' => $validated['seller_type'],
                'tax_number' => $validated['tax_number'] ?? null,
                'nic_number' => $validated['nic_number'] ?? null,
                'nic_front' => $nicFrontPath,
                'nic_back' => $nicBackPath,
                'email_verified' => (bool) $validated['email_verified'],
                'phone_verified' => false,
                'agreement_accepted' => true,
                'status' => 'pending',
                'affiliate_seller_id' => $affiliateSellerId,
            ]);

            if ($validated['seller_type'] === 'business') {
                $businessRegistrationDocumentPath = $request->file('business_registration_document')
                    ?->store('seller-documents/business', 'public');

                $seller->businessInformation()->create([
                    'business_name' => $validated['business_name'],
                    'business_registration_number' => $validated['business_registration_number'] ?? null,
                    'business_type' => $validated['business_type'] ?? null,
                    'business_registered_date' => $validated['business_registered_date'] ?? null,
                    'address_line_1' => $validated['address_line_1'],
                    'address_line_2' => $validated['address_line_2'] ?? null,
                    'city' => $validated['city'],
                    'district' => $validated['district'] ?? null,
                    'postal_code' => $validated['postal_code'] ?? null,
                    'country' => $validated['country'] ?? 'Sri Lanka',
                    'business_registration_document' => $businessRegistrationDocumentPath,
                ]);
            }
        });

        if ($validated['email_verified']) {
            Cache::forget($this->getEmailVerifiedKey($validated['email']));
        }

        Cookie::queue(Cookie::forget('seller_aff_ref'));

        return response()->json([
            'message' => 'Seller registration submitted successfully. Our team will review your application.',
        ], 201);
    }

    private function getEmailOtpKey(string $email): string
    {
        return 'seller-registration:email-otp:' . sha1(strtolower(trim($email)));
    }

    private function getEmailVerifiedKey(string $email): string
    {
        return 'seller-registration:email-verified:' . sha1(strtolower(trim($email)));
    }
}
