<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification OTP</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <div style="max-width:620px;margin:28px auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:28px;">
        <h1 style="margin:0 0 10px;font-size:22px;color:#0f172a;">Verify your email</h1>
        <p style="margin:0 0 16px;font-size:14px;color:#334155;">
            Hi {{ $name ?: 'Seller' }}, use this one-time verification code to continue your seller registration.
        </p>

        <div style="margin:20px 0;padding:14px 16px;border-radius:10px;background:#0f172a;color:#ffffff;font-size:28px;letter-spacing:10px;font-weight:700;text-align:center;">
            {{ $otp }}
        </div>

        <p style="margin:0;font-size:13px;color:#475569;">
            This code expires in 10 minutes. If you did not request this, you can ignore this email.
        </p>
    </div>
</body>
</html>
