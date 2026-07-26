@php
    $permissions = implode(', ', (array) ($user['permissions'] ?? []));
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Your Adxon CMS Access</title>
</head>
<body style="margin:0;background:#f6f7fb;color:#171a21;font-family:Arial,Helvetica,sans-serif;">
    <div style="max-width:640px;margin:0 auto;padding:32px 20px;">
        <div style="background:#070b12;color:#ffffff;border-radius:12px 12px 0 0;padding:24px 28px;">
            <h1 style="margin:0;font-size:24px;">Adxon CMS Access</h1>
            <p style="margin:8px 0 0;color:#f2c65b;">Your account is ready.</p>
        </div>
        <div style="background:#ffffff;border:1px solid #e6e8ee;border-top:0;border-radius:0 0 12px 12px;padding:28px;">
            <p style="margin-top:0;">Hi {{ $user['name'] ?? 'User' }},</p>
            <p>Your Adxon CMS account has been created with the role <strong>{{ $user['role'] ?? 'User' }}</strong>.</p>

            <table style="width:100%;border-collapse:collapse;margin:20px 0;">
                <tr>
                    <td style="padding:10px;border:1px solid #eceff4;background:#fafafa;">Login URL</td>
                    <td style="padding:10px;border:1px solid #eceff4;"><a href="{{ $loginUrl }}">{{ $loginUrl }}</a></td>
                </tr>
                <tr>
                    <td style="padding:10px;border:1px solid #eceff4;background:#fafafa;">Username</td>
                    <td style="padding:10px;border:1px solid #eceff4;">{{ $user['email'] ?? '' }}</td>
                </tr>
                <tr>
                    <td style="padding:10px;border:1px solid #eceff4;background:#fafafa;">Password</td>
                    <td style="padding:10px;border:1px solid #eceff4;">{{ $password }}</td>
                </tr>
                <tr>
                    <td style="padding:10px;border:1px solid #eceff4;background:#fafafa;">Enabled Features</td>
                    <td style="padding:10px;border:1px solid #eceff4;">{{ $permissions ?: 'No modules enabled' }}</td>
                </tr>
            </table>

            <p style="margin-bottom:0;">Please log in and keep this password secure.</p>
        </div>
    </div>
</body>
</html>
