<!DOCTYPE html>
<html>
<head>
    <title>Contact Email</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 40px auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <tr>
            <td style="padding: 40px 30px; text-align: center; background-color: #4a90e2; border-radius: 8px 8px 0 0;">
                <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: bold;">New Contact Message</h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 30px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="padding-bottom: 20px;">
                            <p style="margin: 0; font-size: 16px; color: #333333;">
                                <strong style="color: #4a90e2;">From:</strong> {{ $email }}
                            </p>
                        </td>
                    </tr>
                      <tr>
                        <td style="padding-bottom: 20px;">
                            <p style="margin: 0; font-size: 16px; color: #333333;">
                                <strong style="color: #4a90e2;">name:</strong> {{ $name }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 20px;">
                            <p style="margin: 0; font-size: 16px; color: #333333;">
                                <strong style="color: #4a90e2;">Message:</strong> {{ $msg }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 30px; text-align: center; background-color: #f8f8f8; border-radius: 0 0 8px 8px;">
                <p style="margin: 0; font-size: 14px; color: #666666;">
                    This email was sent from your website's contact form.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>