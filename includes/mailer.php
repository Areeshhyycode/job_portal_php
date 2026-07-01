<?php
/**
 * Lightweight mailer.
 * Tries PHP's mail() (works when an SMTP server / sendmail is configured).
 * ALWAYS logs the message to storage/mail.log so the feature is demonstrable
 * on a plain XAMPP install where outbound mail isn't set up.
 */
function send_mail(string $to, string $subject, string $body): bool
{
    $logDir = dirname(__DIR__) . '/storage';
    if (!is_dir($logDir)) @mkdir($logDir, 0777, true);

    $headers = 'From: ' . APP_NAME . ' <no-reply@jobhive.test>' . "\r\n" .
               'Content-Type: text/plain; charset=UTF-8' . "\r\n";

    $sent = @mail($to, $subject, $body, $headers);

    $entry = sprintf(
        "[%s] TO:%s | %s\n%s\n%s\n\n",
        date('Y-m-d H:i:s'),
        $to,
        $subject,
        $sent ? '(delivered via mail())' : '(logged only — no SMTP configured)',
        $body
    );
    @file_put_contents($logDir . '/mail.log', $entry, FILE_APPEND);

    return $sent;
}
