<!DOCTYPE html>
<html>

<head>
    <title>Employer Registration Status</title>
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

        .status {
            display: inline-block;
            padding: 6px 18px;
            border-radius: 20px;
            font-weight: bold;
            color: #fff;
            background: #d32f2f;
            margin-bottom: 18px;
        }

        .approved {
            background: #388e3c;
        }

        .rejected {
            background: #d32f2f;
        }

        .footer {
            margin-top: 32px;
            font-size: 0.98rem;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 16px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Hello {{ $user->name }},</h1>
        <p>Your employer registration request has been reviewed.</p>
        <p class="status @if($status === 'Approved') approved @else rejected @endif">{{ $status }}</p>
        @if($status === 'Approved')
            <p>Congratulations! Your company is now registered on our platform.</p>
        @else
            <p>We regret to inform you that your employer registration request was not approved at this time.</p>
        @endif
        <div class="footer">
            Thank you,<br>
            <span style="color:#d32f2f;font-weight:bold;">The Admin Team</span>
        </div>
    </div>
</body>

</html>