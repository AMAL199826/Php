<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public string $fromEmail  = 'noreply@legacycrm.test';
    public string $fromName   = 'Legacy CRM';
    public string $recipients = '';

    public string $userAgent = 'CodeIgniter';

    // Use 'smtp' so it actually sends via Mailtrap instead of the local mail() function
    public string $protocol = 'smtp';

    public string $mailPath = '/usr/sbin/sendmail';

    // ---- Mailtrap SMTP settings ----
    // Get these from your Mailtrap inbox: Settings > SMTP Settings > "Show credentials"
    public string $SMTPHost = 'sandbox.smtp.mailtrap.io';
    public string $SMTPAuthMethod = 'login';
    public string $SMTPUser = 'YOUR_MAILTRAP_USERNAME';
    public string $SMTPPass = 'YOUR_MAILTRAP_PASSWORD';
    public int $SMTPPort = 2525;
    public int $SMTPTimeout = 10;
    public bool $SMTPKeepAlive = false;
    public string $SMTPCrypto = 'tls';

    public bool $wordWrap = true;
    public int $wrapChars = 76;

    // HTML emails
    public string $mailType = 'html';
    public string $charset = 'UTF-8';

    public bool $validate = false;
    public int $priority = 3;
    public string $CRLF = "\r\n";
    public string $newline = "\r\n";
    public bool $BCCBatchMode = false;
    public int $BCCBatchSize = 200;
    public bool $DSN = false;
}