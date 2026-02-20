<div style="font-family: Arial, sans-serif; background: #f1f5f9; padding: 28px;">
  <div style="max-width: 640px; margin: auto; background: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0;">
    <div style="background: linear-gradient(135deg, #7f1d1d, #991b1b); padding: 22px 26px;">
      <p style="margin: 0; font-size: 12px; letter-spacing: .18em; text-transform: uppercase; color: #fecaca;">Nextep Support</p>
      <h2 style="margin: 8px 0 0; color: #ffffff;">Your account has been blocked</h2>
    </div>

    <div style="padding: 26px;">
      <p style="margin-top: 0; color: #334155;">Hi <strong>{{ $name }}</strong>, your Nextep account access has been blocked by our team.</p>

      <div style="background: #fff1f2; border: 1px solid #fecdd3; border-radius: 12px; padding: 14px; margin: 16px 0;">
        <p style="margin: 0 0 8px; font-weight: 700; color: #9f1239;">Reason</p>
        <p style="margin: 0; color: #9f1239;">{{ $reason }}</p>
      </div>

      <p style="margin: 0; color: #475569;">
        If you believe this is a mistake or need help, please contact support at
        <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>.
      </p>
    </div>
  </div>
</div>
