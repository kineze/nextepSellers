<div style="font-family: Arial, sans-serif; background: #f1f5f9; padding: 28px;">
  <div style="max-width: 640px; margin: auto; background: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0;">
    <div style="background: linear-gradient(135deg, #0f172a, #1e293b); padding: 22px 26px;">
      <p style="margin: 0; font-size: 12px; letter-spacing: .18em; text-transform: uppercase; color: #cbd5e1;">Nextep Seller Hub</p>
      <h2 style="margin: 8px 0 0; color: #ffffff;">Verify Your Email</h2>
    </div>

    <div style="padding: 26px;">
      <p style="margin-top: 0; color: #334155;">
        Hi <strong>{{ $name ?: 'Seller' }}</strong>, use this one-time verification code to continue your seller registration.
      </p>

      <div style="margin: 20px 0; padding: 16px; border-radius: 12px; background: #0f172a; color: #ffffff; font-size: 30px; letter-spacing: 10px; font-weight: 700; text-align: center;">
        {{ $otp }}
      </div>

      <p style="margin: 0; color: #64748b; font-size: 13px;">
        This code expires in 10 minutes. If you did not request this, you can safely ignore this email.
      </p>
    </div>
  </div>
</div>
