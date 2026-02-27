<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Services\BrevoMailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SellerProfileController extends Controller
{
    public function __construct(private readonly BrevoMailer $mailer)
    {
    }

    public function show(Request $request)
    {
        $seller = $request->user()?->seller?->load(['businessInformation', 'bankDetail.bank']);
        if (!$seller) {
            return response()->json([
                'message' => 'Seller profile not found for this user.',
            ], 422);
        }

        return response()->json([
            'seller' => [
                'id' => (int) $seller->id,
                'first_name' => $seller->first_name,
                'last_name' => $seller->last_name,
                'email' => $seller->email,
                'phone' => $seller->phone,
                'seller_image' => $seller->seller_image,
                'seller_type' => $seller->seller_type,
                'tax_number' => $seller->tax_number,
                'nic_number' => $seller->nic_number,
                'business_information' => $seller->businessInformation,
                'bank_detail' => $seller->bankDetail,
            ],
        ]);
    }

    public function sendEmailOtp(Request $request)
    {
        $seller = $request->user()?->seller;
        if (!$seller) {
            return response()->json([
                'message' => 'Seller profile not found for this user.',
            ], 422);
        }

        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('sellers', 'email')->ignore($seller->id),
                Rule::unique('users', 'email')->ignore($request->user()?->id),
            ],
        ]);

        $newEmail = strtolower(trim($validated['email']));
        $currentEmail = strtolower(trim((string) $seller->email));
        if ($newEmail === $currentEmail) {
            return response()->json([
                'message' => 'New email must be different from current email.',
            ], 422);
        }

        $otp = (string) random_int(100000, 999999);
        $otpKey = $this->getEmailOtpKey($seller->id, $newEmail);
        $verifiedKey = $this->getEmailVerifiedKey($seller->id, $newEmail);

        Cache::put($otpKey, Hash::make($otp), now()->addMinutes(10));
        Cache::forget($verifiedKey);

        $sent = $this->mailer->sendSellerEmailOtp(
            $validated['email'],
            trim($seller->first_name . ' ' . $seller->last_name) ?: 'Seller',
            $otp
        );

        if (!$sent) {
            return response()->json([
                'message' => 'Unable to send verification code right now. Please try again.',
            ], 500);
        }

        return response()->json([
            'message' => 'Verification code sent to your new email.',
        ]);
    }

    public function verifyEmailOtp(Request $request)
    {
        $seller = $request->user()?->seller;
        if (!$seller) {
            return response()->json([
                'message' => 'Seller profile not found for this user.',
            ], 422);
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'otp' => ['required', 'digits:6'],
        ]);

        $newEmail = strtolower(trim($validated['email']));
        $otpKey = $this->getEmailOtpKey($seller->id, $newEmail);
        $verifiedKey = $this->getEmailVerifiedKey($seller->id, $newEmail);
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

    public function update(Request $request)
    {
        $seller = $request->user()?->seller?->load(['businessInformation', 'bankDetail']);
        if (!$seller) {
            return response()->json([
                'message' => 'Seller profile not found for this user.',
            ], 422);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('sellers', 'email')->ignore($seller->id),
                Rule::unique('users', 'email')->ignore($request->user()?->id),
            ],
            'phone' => ['nullable', 'string', 'max:25'],
            'seller_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $newEmail = strtolower(trim($validated['email']));
        $currentEmail = strtolower(trim((string) $seller->email));
        if ($newEmail !== $currentEmail) {
            $verifiedKey = $this->getEmailVerifiedKey($seller->id, $newEmail);
            if (!Cache::get($verifiedKey)) {
                return response()->json([
                    'message' => 'Please verify your new email before saving profile changes.',
                    'errors' => [
                        'email' => ['Email verification is required for email changes.'],
                    ],
                ], 422);
            }

            Cache::forget($verifiedKey);
            $seller->email_verified = true;
        }

        if ($request->hasFile('seller_image')) {
            $newImagePath = $request->file('seller_image')->store('seller-documents/profile', 'public');
            if ($seller->seller_image) {
                Storage::disk('public')->delete($seller->seller_image);
            }
            $seller->seller_image = $newImagePath;
        }

        $seller->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => isset($validated['phone']) && $validated['phone'] !== '' ? $validated['phone'] : null,
            'seller_image' => $seller->seller_image,
            'email_verified' => $seller->email_verified,
        ]);

        if ($request->user()) {
            $request->user()->update([
                'name' => trim(($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? '')),
                'email' => $validated['email'],
            ]);
        }

        $seller->load(['businessInformation', 'bankDetail']);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'seller' => [
                'id' => (int) $seller->id,
                'first_name' => $seller->first_name,
                'last_name' => $seller->last_name,
                'email' => $seller->email,
                'phone' => $seller->phone,
                'seller_image' => $seller->seller_image,
                'seller_type' => $seller->seller_type,
                'tax_number' => $seller->tax_number,
                'nic_number' => $seller->nic_number,
                'business_information' => $seller->businessInformation,
                'bank_detail' => $seller->bankDetail,
            ],
        ]);
    }

    public function bankDetails(Request $request)
    {
        $seller = $request->user()?->seller?->load('bankDetail.bank');
        if (!$seller) {
            return response()->json([
                'message' => 'Seller profile not found for this user.',
            ], 422);
        }

        return response()->json([
            'bank_detail' => $seller->bankDetail,
        ]);
    }

    public function banks()
    {
        $banks = Bank::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'banks' => $banks,
        ]);
    }

    public function updateBankDetails(Request $request)
    {
        $seller = $request->user()?->seller;
        if (!$seller) {
            return response()->json([
                'message' => 'Seller profile not found for this user.',
            ], 422);
        }

        $validated = $request->validate([
            'bank_id' => ['required', 'integer', 'exists:banks,id'],
            'account_no' => ['required', 'string', 'max:255'],
            'swift_code' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'branch' => ['nullable', 'string', 'max:255'],
        ]);

        $bank = Bank::query()->findOrFail((int) $validated['bank_id']);

        $bankDetail = $seller->bankDetail()->updateOrCreate(
            ['seller_id' => $seller->id],
            [
                'bank_id' => (int) $validated['bank_id'],
                'bank' => $bank->name,
                'account_no' => $validated['account_no'],
                'swift_code' => $validated['swift_code'] ?? null,
                'name' => $validated['name'],
                'branch' => $validated['branch'] ?? null,
            ]
        );

        $bankDetail->load('bank:id,name');

        return response()->json([
            'message' => 'Bank details updated successfully.',
            'bank_detail' => $bankDetail,
        ]);
    }

    private function getEmailOtpKey(int $sellerId, string $email): string
    {
        return 'seller-profile:email-otp:' . $sellerId . ':' . sha1(strtolower(trim($email)));
    }

    private function getEmailVerifiedKey(int $sellerId, string $email): string
    {
        return 'seller-profile:email-verified:' . $sellerId . ':' . sha1(strtolower(trim($email)));
    }
}
