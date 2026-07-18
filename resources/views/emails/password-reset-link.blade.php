<div style="font-family: Arial, sans-serif; background: #f1f5f9; padding: 28px;">
  <div style="max-width: 640px; margin: auto; overflow: hidden; border: 1px solid #e2e8f0; border-radius: 16px; background: #ffffff;">
    <div style="background: linear-gradient(135deg, #0f172a, #1e293b); padding: 22px 26px;">
      <p style="margin: 0; color: #cbd5e1; font-size: 12px; letter-spacing: .18em; text-transform: uppercase;">Nextep</p>
      <h2 style="margin: 8px 0 0; color: #ffffff;">Reset your password</h2>
    </div>

    <div style="padding: 26px;">
      <p style="margin-top: 0; color: #334155;">Hi <strong>{{ $name }}</strong>,</p>
      <p style="color: #475569; line-height: 1.6;">We received a request to reset the password for your Nextep account. Use the button below to choose a new password.</p>

      <div style="margin: 24px 0; text-align: center;">
        <a href="{{ $resetUrl }}" style="display: inline-block; border-radius: 10px; background: #0f172a; padding: 13px 24px; color: #ffffff; font-weight: 700; text-decoration: none;">
          Reset Password
        </a>
      </div>

      <p style="color: #64748b; font-size: 13px; line-height: 1.6;">This password reset link will expire automatically. If you did not request a password reset, you can safely ignore this email.</p>
      <p style="margin-bottom: 0; color: #94a3b8; font-size: 12px; word-break: break-all;">If the button does not work, copy this link into your browser:<br>{{ $resetUrl }}</p>
    </div>
  </div>
</div>
