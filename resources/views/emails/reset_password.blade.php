<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      color: #333;
      padding: 20px;
    }
    .container {
      background-color: #fff;
      padding: 20px;
      border-radius: 5px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    h2 {
      color: #333;
    }
    .reset-code {
      background-color: #f0f0f0;
      border: 1px solid #ddd;
      padding: 10px;
      font-size: 24px;
      font-weight: bold;
      text-align: center;
      letter-spacing: 2px;
      margin: 20px 0;
    }
    p {
      margin: 10px 0;
    }
    .footer {
      font-size: 12px;
      color: #888;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Reset Password</h2>
    <p>We received a request to reset your password. Please use the following code to reset your password:</p>

    <div class="reset-code">
      {{ $code }}
    </div>

    <p>If you didn't request a password reset, you can safely ignore this email.</p>
    <p>Thanks,</p>
    <p>FloodWatch Team</p>

    <div class="footer">
      <p>If you have any issues, feel free to contact us at mama.aquino.up@phinmaed.com</p>
    </div>
  </div>
</body>
</html>