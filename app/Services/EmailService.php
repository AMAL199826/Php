<?php

namespace App\Services;

use Config\Email as EmailConfig;

class EmailService
{
    /**
     * Send the welcome email to a newly created customer.
     * Returns true/false. Never throws — failures are logged only,
     * so the calling code (e.g. customer creation) never breaks.
     */
    public function sendWelcomeEmail(array $customer): bool
    {
        if (empty($customer['email'])) {
            log_message('error', 'EmailService: cannot send welcome email, customer has no email. customer_id=' . ($customer['id'] ?? 'unknown'));
            return false;
        }

        $html = view('emails/welcome', ['customer' => $customer]);

        return $this->send(
            $customer['email'],
            'Welcome to Legacy CRM, ' . $customer['name'] . '!',
            $html
        );
    }

    /**
     * Generic sender used by all email types. Wrapped in try/catch so
     * SMTP failures never bubble up and break the calling controller.
     */
    public function send(string $to, string $subject, string $htmlBody): bool
    {
        try {
            $email = \Config\Services::email(new EmailConfig());

            $email->setTo($to);
            $email->setSubject($subject);
            $email->setMessage($htmlBody);

            $sent = $email->send();

            if (!$sent) {
                log_message('error', 'EmailService: send() returned false for [' . $to . ']. Debug: ' . $email->printDebugger(['headers']));
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            log_message('error', 'EmailService: exception sending email to [' . $to . ']: ' . $e->getMessage());
            return false;
        }
    }
}