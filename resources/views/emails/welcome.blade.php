<div style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
  <div style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; overflow: hidden;">
    <div style="padding: 30px;">
      <h2 style="margin-top: 0;">Welcome to Nextep</h2>
      <p>Hi <strong>{{ $name }}</strong>,</p>
      <p>Your account has been created. Here are your login details:</p>
      <ul>
        <li>Email: <strong>{{ $email }}</strong></li>
        <li>Password: <strong>{{ $password }}</strong></li>
      </ul>
      <div style="margin: 30px 0; text-align: center;">
        <a href="{{ url('/login') }}" style="background: #000; color: #fff; padding: 12px 25px; border-radius: 5px; text-decoration: none;">Login Now</a>
      </div>
      <p style="color: #888;">Please change your password after logging in for the first time.</p>
    </div>
  </div>
</div>
