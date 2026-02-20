<div style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
  <div style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; overflow: hidden;">
    <div style="padding: 30px;">
      <h2 style="margin-top: 0;">Congratulations, and welcome to Nextep Seller Hub!</h2>
      <p>Hi <strong>{{ $name }}</strong>,</p>
      <p>
        Your seller account has been approved. We are excited to have you on board, and together we will build growth with Nextep.
      </p>
      <p>Here are your login details:</p>
      <ul>
        <li>Email: <strong>{{ $email }}</strong></li>
        <li>Password: <strong>{{ $password }}</strong></li>
      </ul>
      <div style="margin: 30px 0; text-align: center;">
        <a href="{{ url('/login') }}" style="background: #000; color: #fff; padding: 12px 25px; border-radius: 5px; text-decoration: none;">Login to Seller Hub</a>
      </div>
      <p style="color: #888;">Please change your password after your first login.</p>
      <p style="margin-bottom: 0;">Welcome to Nextep.</p>
    </div>
  </div>
</div>
