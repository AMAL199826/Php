<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= esc($subject ?? 'Legacy CRM') ?></title>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f7; font-family: Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f7; padding: 30px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width:600px; background:#ffffff; border-radius:8px; overflow:hidden;" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="background:#0d6efd; padding:20px 30px;">
                            <h1 style="color:#ffffff; font-size:20px; margin:0;">Legacy CRM</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px;">
                            <?= $content ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 30px; background:#f4f4f7; color:#888888; font-size:12px;">
                            This is an automated message from Legacy CRM. Please do not reply.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>