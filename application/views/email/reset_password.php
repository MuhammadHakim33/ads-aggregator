<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Reset Your Password</title>
    <style>
        body {
            font-family: sans-serif;
            background-color: #f8f9fa;
            color: #212529;
            padding: 20px;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .header {
            background-color: #0d6efd;
            padding: 30px 20px;
            text-align: center;
        }

        .header svg {
            margin-bottom: 15px;
        }

        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        .content {
            padding: 30px;
        }

        .content h2 {
            margin-top: 0;
            font-size: 20px;
        }

        .content p {
            margin-bottom: 20px;
            color: #495057;
        }

        .btn {
            display: inline-block;
            background-color: #0d6efd;
            color: #ffffff !important;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 0.375rem;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }

        .footer {
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
            padding: 12px;
            text-align: center;
            font-size: 13px;
            color: #6c757d;
        }

        .text-center {
            text-align: center;
        }

        .my-30 {
            margin-top: 30px;
            margin-bottom: 30px;
        }

        .text-sm {
            font-size: 14px;
        }

        .link-url {
            word-break: break-all;
            font-size: 14px;
            color: #0d6efd;
        }

        .disclaimer {
            font-size: 14px;
            margin-top: 30px;
            border-top: 1px solid #e9ecef;
            padding-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="4" y="10" width="3" height="10" rx="1" fill="white" />
                <rect x="10" y="4" width="3" height="16" rx="1" fill="white" />
                <rect x="16" y="8" width="3" height="12" rx="1" fill="white" />
            </svg>
            <h1>Kontan Ad Reporter</h1>
        </div>
        <div class="content">
            <h2>Reset Your Password</h2>
            <p>Hello,</p>
            <p>We received a request to reset your password for your <strong>Kontan Ad Reporter</strong> account. If you
                made this request, please click the button below to set a new password.</p>

            <div class="text-center my-30">
                <a href="<?= $reset_link ?>" class="btn">Reset Password</a>
            </div>

            <p>If the button doesn't work, you can copy and paste the following link into your browser:</p>
            <p class="link-url"><a href="<?= $reset_link ?>"><?= $reset_link ?></a></p>

            <p class="text-sm"><em>This link will expire in 1 hour.</em></p>

            <p class="disclaimer">
                If you didn't request a password reset, you can safely ignore this email. Your password will remain
                unchanged.
            </p>
        </div>
        <div class="footer">
            &copy; <?= date('Y') ?> Kontan Ad Reporter. All rights reserved.
        </div>
    </div>
</body>

</html>