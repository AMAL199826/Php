<?php
ob_start();
?>
    <h2 style="color:#333333; margin-top:0;">Welcome, <?= esc($customer['name']) ?>!</h2>

    <p style="color:#555555; font-size:15px; line-height:1.5;">
        Thank you for joining us. We're excited to have <strong><?= esc($customer['name']) ?></strong>
        <?= !empty($customer['company']) ? ' from <strong>' . esc($customer['company']) . '</strong>' : '' ?>
        onboard.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0; border-collapse:collapse;">
        <tr>
            <td style="padding:8px 0; color:#888; font-size:13px; width:120px;">Email</td>
            <td style="padding:8px 0; color:#333; font-size:14px;"><?= esc($customer['email']) ?></td>
        </tr>
        <?php if (!empty($customer['phone'])): ?>
        <tr>
            <td style="padding:8px 0; color:#888; font-size:13px;">Phone</td>
            <td style="padding:8px 0; color:#333; font-size:14px;"><?= esc($customer['phone']) ?></td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($customer['city'])): ?>
        <tr>
            <td style="padding:8px 0; color:#888; font-size:13px;">City</td>
            <td style="padding:8px 0; color:#333; font-size:14px;"><?= esc($customer['city']) ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <p style="color:#555555; font-size:15px; line-height:1.5;">
        If you have any questions, feel free to reach out to our support team anytime.
    </p>

    <p style="color:#555555; font-size:15px; margin-bottom:0;">— The Legacy CRM Team</p>
<?php
$content = ob_get_clean();
echo $this->include('emails/layout', ['content' => $content, 'subject' => 'Welcome to Legacy CRM']);