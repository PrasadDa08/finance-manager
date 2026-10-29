<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Verification Code</title>
</head>

<body style="margin:0;padding:0;background:#EEFCF8;font-family:Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#EEFCF8;padding:24px 12px;">
        <tr>
            <td align="center">
                <table width="100%" style="max-width:480px;background:#ffffff;border:1px solid #99F6E4;border-radius:16px;padding:24px;">
                    <tr>
                        <td>
                            <div style="width:40px;height:40px;background:#7C3AED;color:#fff;border-radius:10px;text-align:center;line-height:40px;font-weight:bold;margin-bottom:16px;">
                                FM
                            </div>

                            <h1 style="margin:0 0 8px;color:#134E4A;font-size:20px;">
                                Verify your email
                            </h1>

                            <p style="margin:0 0 16px;color:#5B7A76;font-size:14px;line-height:1.5;">
                                Hi {{ $name }}, use this code to complete your Finance Manager registration.
                            </p>

                            <div style="background:#F0FDFA;border:1px solid #99F6E4;border-radius:12px;padding:16px;text-align:center;margin:0 0 16px;">
                                <div style="font-size:28px;letter-spacing:8px;font-weight:bold;color:#134E4A;">
                                    {{ $otp }}
                                </div>
                            </div>

                            <p style="margin:0 0 8px;color:#5B7A76;font-size:13px;">
                                This code expires in <strong>10 minutes</strong>.
                            </p>
                            <p style="margin:0;color:#5B7A76;font-size:13px;">
                                If you didn’t request this, you can ignore this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>