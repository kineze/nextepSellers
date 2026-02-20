<div style="font-family: Arial, sans-serif; background: #f1f5f9; padding: 28px;">
  <div style="max-width: 640px; margin: auto; background: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0;">
    <div style="background: linear-gradient(135deg, #0f766e, #0f766e); padding: 22px 26px;">
      <p style="margin: 0; font-size: 12px; letter-spacing: .18em; text-transform: uppercase; color: #ccfbf1;">Nextep Support</p>
      <h2 style="margin: 8px 0 0; color: #ffffff;">Your account has been unblocked</h2>
    </div>

    <div style="padding: 26px;">
      <p style="margin-top: 0; color: #334155;">
        Hi <strong>{{ $name }}</strong>, your Nextep account is now active again.
      </p>

      <div style="background: #ecfeff; border: 1px solid #99f6e4; border-radius: 12px; padding: 14px; margin: 16px 0;">
        <p style="margin: 0; color: #115e59;">
          You can now log in and access the site.
        </p>
      </div>

      <div style="margin: 22px 0; text-align: center;">
        <a href="{{ url('/login') }}" style="display: inline-block; background: #0f766e; color: #ffffff; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: 700;">
          Login to Nextep
        </a>
      </div>

      <p style="margin: 0; color: #475569;">
        Need help? Contact support at
        <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>.
      </p>
    </div>
  </div>
</div>
