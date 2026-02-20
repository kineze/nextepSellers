<div style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
  <div style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; overflow: hidden;">
    <div style="padding: 30px;">
      <h2 style="margin-top: 0;">Your Nextep account has been blocked</h2>
      <p>Hi <strong>{{ $name }}</strong>,</p>
      <p>
        Your account access has been blocked by our team.
      </p>

      <p><strong>Reason:</strong></p>
      <p style="background: #fff1f2; border: 1px solid #fecdd3; color: #9f1239; padding: 12px; border-radius: 8px;">
        {{ $reason }}
      </p>

      <p>
        If you believe this is a mistake or need help, please contact support at
        <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>.
      </p>

      <p style="color: #64748b; margin-bottom: 0;">Nextep Support Team</p>
    </div>
  </div>
</div>
