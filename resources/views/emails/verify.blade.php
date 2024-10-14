<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
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
        .verification-code {
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
        <h2>Email Verification</h2>
        <p>Thank you for signing up! Please verify your email address by entering the following verification code:</p>

        <div class="verification-code">
            {{ $code }}
        </div>

        <p>If you didn't request this, you can safely ignore this email.</p>
        <p>Thanks,</p>
        <p>Your App Team</p>

        <div class="footer">
            <p>If you have any issues, feel free to contact us at mama.aquino.up@phinmaed.com</p>
        </div>
    </div>
</body>
</html>