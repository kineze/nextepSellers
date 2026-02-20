<div style="font-family: Arial, sans-serif; background: #f1f5f9; padding: 28px;">
  <div style="max-width: 640px; margin: auto; background: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0;">
    <div style="background: linear-gradient(135deg, #0f172a, #1e293b); padding: 22px 26px;">
      <p style="margin: 0; font-size: 12px; letter-spacing: .18em; text-transform: uppercase; color: #cbd5e1;">Nextep Team</p>
      <h2 style="margin: 8px 0 0; color: #ffffff;">You're invited to join {{ $invitation->team->name }}</h2>
    </div>

    <div style="padding: 26px;">
      @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::registration()))
      <p style="margin-top: 0; color: #334155;">If you do not have an account yet, create one first and then accept the invitation.</p>

      <div style="margin: 18px 0; text-align: center;">
        <a href="{{ route('register') }}" style="display: inline-block; background: #0f172a; color: #ffffff; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: 700;">
          Create Account
        </a>
      </div>
      @endif

      <p style="color: #334155;">Accept the invitation using the button below:</p>

      <div style="margin: 18px 0; text-align: center;">
        <a href="{{ $acceptUrl }}" style="display: inline-block; background: #0f172a; color: #ffffff; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: 700;">
          Accept Invitation
        </a>
      </div>

      <p style="margin: 0; color: #64748b; font-size: 13px;">If you did not expect this invitation, you may ignore this email.</p>
    </div>
  </div>
</div>
