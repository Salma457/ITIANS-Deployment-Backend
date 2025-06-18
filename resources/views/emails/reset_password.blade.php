<!DOCTYPE html>
<html>

<head>
    <title>Reset Your Password</title>
    <style>
        body {
            background: #fff;
            color: #111;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 500px;
            margin: 40px auto;
            background: #fff;
            border: 1px solid #eee;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
            padding: 32px 28px 24px 28px;
        }

        h1 {
            color: #d32f2f;
            font-size: 2rem;
            margin-bottom: 18px;
        }

        p {
            font-size: 1.08rem;
            margin: 12px 0;
        }

        .action-button {
            display: inline-block;
            background-color: #d32f2f;
            color: #fff !important;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 20px;
            font-weight: bold;
            margin-top: 24px;
            text-align: center;
        }

        .footer {
            margin-top: 32px;
            font-size: 0.98rem;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 16px;
            text-align: center;
        }

        .highlight {
            color: #d32f2f;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Password Reset Request</h1>

        <p>Hello,</p>

        <p>You are receiving this email because we received a request to reset the password for your account.</p>

        <p>Please click the button below to reset your password:</p>

        <p style="text-align: center; color: white;">
            <a href="http://localhost:5173/reset-password?token={{ $token }}&email={{ $email }}" class="action-button">
                Reset Password
            </a>
        </p>

        <p><span class="highlight">Note:</span> This link will expire in 60 minutes.</p>

        <p>If you did not request a password reset, no further action is required.</p>

        <div class="footer">
            Best regards,<br>
            <span class="highlight">The ITIANS Team</span>
        </div>
    </div>
</body>

</html>
