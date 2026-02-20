<div style="font-family: Arial, sans-serif; background: #f1f5f9; padding: 28px;">
  <div style="max-width: 640px; margin: auto; background: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0;">
    <div style="background: linear-gradient(135deg, #0f172a, #1e293b); padding: 22px 26px;">
      <p style="margin: 0; font-size: 12px; letter-spacing: .18em; text-transform: uppercase; color: #cbd5e1;">Nextep</p>
      <h2 style="margin: 8px 0 0; color: #ffffff;">Password Reset Completed</h2>
    </div>

    <div style="padding: 26px;">
      <p style="margin-top: 0; color: #334155;">Hi <strong>{{ $name }}</strong>, your password has been reset.</p>

      <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 14px; margin: 16px 0;">
        <p style="margin: 0 0 8px; font-weight: 700; color: #1e3a8a;">New Login Credentials</p>
        <p style="margin: 0; color: #1e40af;">
          Email: <strong>{{ $email }}</strong><br />
          New Password: <strong>{{ $password }}</strong>
        </p>
      </div>

      <div style="margin: 22px 0; text-align: center;">
        <a href="{{ url('/login') }}" style="display: inline-block; background: #0f172a; color: #ffffff; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: 700;">
          Login Now
        </a>
      </div>

      <p style="margin: 0; color: #64748b; font-size: 13px;">If you did not request this change, please contact support immediately.</p>
    </div>
  </div>
</div>
